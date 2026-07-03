<x-filament-panels::page>
    <div
        x-data="{
            draggingOrderId: null,
            pendingOrderId: null,
            pendingStatusId: null,
            autoScrollFrame: null,
            autoScrollDirection: 0,
            autoScrollSpeed: 15,
            autoScrollSensitivity: 60,
            startAutoScroll(direction) {
                this.autoScrollDirection = direction;

                if (this.autoScrollFrame) {
                    return;
                }

                const tick = () => {
                    if (! this.autoScrollDirection) {
                        this.autoScrollFrame = null;
                        return;
                    }

                    this.$refs.board.scrollLeft += this.autoScrollDirection * this.autoScrollSpeed;
                    this.autoScrollFrame = requestAnimationFrame(tick);
                };

                this.autoScrollFrame = requestAnimationFrame(tick);
            },
            stopAutoScroll() {
                this.autoScrollDirection = 0;

                if (this.autoScrollFrame) {
                    cancelAnimationFrame(this.autoScrollFrame);
                    this.autoScrollFrame = null;
                }
            },
            handleBoardDrag(event) {
                const rect = this.$refs.board.getBoundingClientRect();

                if (event.clientX <= rect.left + this.autoScrollSensitivity) {
                    this.startAutoScroll(-1);
                    return;
                }

                if (event.clientX >= rect.right - this.autoScrollSensitivity) {
                    this.startAutoScroll(1);
                    return;
                }

                this.stopAutoScroll();
            }
        }"
    >
        <div
            x-ref="board"
            class="kanban-scrollbar-hidden flex items-start gap-3 overflow-x-auto pb-3 md:grid md:grid-cols-4 md:gap-4 md:overflow-visible"
            x-on:dragover="handleBoardDrag($event)"
            x-on:drop="stopAutoScroll()"
            x-on:dragleave="if (! $el.contains($event.relatedTarget)) stopAutoScroll()"
        >
            @foreach ($this->board as $column)
                @php
                    $dotClass = match ($column['color']) {
                        'warning' => 'bg-warning-500',
                        'info' => 'bg-info-500',
                        'success' => 'bg-success-500',
                        'primary' => 'bg-primary-500',
                        'danger' => 'bg-danger-500',
                        default => 'bg-gray-400',
                    };
                @endphp

                <section
                    wire:key="kanban-column-{{ $column['id'] }}"
                    class="w-[16rem] shrink-0 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 transition dark:bg-gray-900 dark:ring-white/10 md:w-auto md:min-w-0 md:p-3"
                    x-bind:class="pendingStatusId === {{ $column['id'] }} ? 'ring-2 ring-primary-300 dark:ring-primary-500/60' : ''"
                    x-on:dragover.prevent
                    x-on:drop.prevent="
                        const orderId = Number(event.dataTransfer.getData('text/plain'));

                        if (! orderId) {
                            return;
                        }

                        pendingOrderId = orderId;
                        pendingStatusId = {{ $column['id'] }};
                        stopAutoScroll();

                        $wire.moveOrder(orderId, {{ $column['id'] }})
                            .then(() => {
                                draggingOrderId = null;
                                pendingOrderId = null;
                                pendingStatusId = null;
                            })
                            .catch(() => {
                                pendingOrderId = null;
                                pendingStatusId = null;
                            });
                    "
                >
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <div class="min-w-0 space-y-1">
                            <h2 lang="ru" class="kanban-text-wrap text-sm font-semibold leading-tight text-gray-950 dark:text-white">
                                {{ $column['title'] }}
                            </h2>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ count($column['orders']) }} заказ(ов)
                            </p>
                        </div>

                        <span class="{{ $dotClass }} inline-flex h-3 w-3 rounded-full"></span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($column['orders'] as $order)
                            <article
                                wire:key="kanban-order-{{ $order['id'] }}"
                                class="min-w-0 cursor-move rounded-xl border border-gray-200 bg-gray-50 p-3 shadow-sm transition hover:border-primary-300 hover:shadow-md dark:border-white/10 dark:bg-white/5"
                                draggable="true"
                                x-show="pendingOrderId !== {{ $order['id'] }}"
                                x-on:dragstart="
                                    draggingOrderId = {{ $order['id'] }};
                                    event.dataTransfer.effectAllowed = 'move';
                                    event.dataTransfer.setData('text/plain', '{{ $order['id'] }}');
                                "
                                x-on:dragend="
                                    draggingOrderId = null;
                                    stopAutoScroll();

                                    if (pendingOrderId !== {{ $order['id'] }}) {
                                        pendingStatusId = null;
                                    }
                                "
                            >
                                <div class="space-y-1.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 lang="ru" class="kanban-text-wrap min-w-0 text-sm font-semibold leading-snug text-gray-950 dark:text-white">
                                            {!! nl2br(e($order['title'])) !!}
                                        </h3>

                                        @if ($order['can_delete'])
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                                title="Удалить заказ"
                                                x-on:click.stop="
                                                    if (! confirm('Удалить этот заказ?')) {
                                                        return;
                                                    }

                                                    $wire.deleteOrder({{ $order['id'] }});
                                                "
                                            >
                                                <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M8.75 2.5a1.25 1.25 0 0 0-1.18.84L7.2 4.5H5.75a.75.75 0 0 0 0 1.5h.38l.67 8.06A2.25 2.25 0 0 0 9.04 16.5h1.92a2.25 2.25 0 0 0 2.24-2.44l.67-8.06h.38a.75.75 0 0 0 0-1.5H12.8l-.37-1.16a1.25 1.25 0 0 0-1.18-.84h-2.5Zm2.25 2L10.64 3.6a.25.25 0 0 0-.24-.1H9.6a.25.25 0 0 0-.24.1L9 4.5h2Zm-1.75 3a.75.75 0 0 1 .75.75v4a.75.75 0 0 1-1.5 0v-4a.75.75 0 0 1 .75-.75Zm3.25.75a.75.75 0 0 0-1.5 0v4a.75.75 0 0 0 1.5 0v-4Z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="space-y-0.5 text-xs leading-5 text-gray-600 dark:text-gray-300">
                                        @foreach ($order['meta_lines'] as $line)
                                            <p lang="ru" class="kanban-text-wrap">
                                                {{ $line }}
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                                Перетащите сюда заказ
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
