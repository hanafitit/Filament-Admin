<x-filament-panels::page>
    @php
        $totalOrders = collect($this->board)->sum(fn ($column) => count($column['orders']));
        $activeStatuses = collect($this->board)->filter(fn ($column) => count($column['orders']) > 0)->count();
    @endphp

    <div
        x-data="{
            draggingOrderId: null,
            pendingOrderId: null,
            pendingStatusId: null,
            hoverStatusId: null,
            dragSourceElement: null,
            dragPlaceholderHeight: null,
            cardPositions: {},
            autoScrollFrame: null,
            autoScrollDirection: 0,
            autoScrollSpeed: 15,
            autoScrollSensitivity: 60,
            captureCardPositions() {
                this.cardPositions = Array.from(this.$refs.board.querySelectorAll('[data-kanban-card]'))
                    .reduce((positions, element) => {
                        positions[element.dataset.orderId] = element.getBoundingClientRect();

                        return positions;
                    }, {});
            },
            animateCardReflow() {
                const previousPositions = this.cardPositions;

                if (! Object.keys(previousPositions).length) {
                    return;
                }

                requestAnimationFrame(() => {
                    this.$refs.board.querySelectorAll('[data-kanban-card]').forEach((element) => {
                        const previousRect = previousPositions[element.dataset.orderId];

                        if (! previousRect) {
                            return;
                        }

                        const nextRect = element.getBoundingClientRect();
                        const deltaX = previousRect.left - nextRect.left;
                        const deltaY = previousRect.top - nextRect.top;

                        if (deltaX === 0 && deltaY === 0) {
                            return;
                        }

                        element.animate(
                            [
                                { transform: `translate(${deltaX}px, ${deltaY}px)` },
                                { transform: 'translate(0, 0)' },
                            ],
                            {
                                duration: 220,
                                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                            },
                        );
                    });

                    this.cardPositions = {};
                });
            },
            hideDragSource(orderId) {
                requestAnimationFrame(() => {
                    if (this.draggingOrderId !== orderId || ! this.dragSourceElement) {
                        return;
                    }

                    this.dragSourceElement.classList.add('opacity-0');
                });
            },
            restoreDragSource() {
                if (! this.dragSourceElement) {
                    return;
                }

                this.dragSourceElement.classList.remove('opacity-0');
                this.dragSourceElement = null;
                this.dragPlaceholderHeight = null;
                this.hoverStatusId = null;
            },
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
        class="space-y-6"
    >
        <section class="fi-theme-hero">
            <div class="fi-theme-grid"></div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <span class="fi-theme-pill text-xs font-semibold uppercase tracking-[0.24em]">
                        Workflow board
                    </span>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                            Управление заказами в живом канбане
                        </h1>

                        <p class="max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-300">
                            Перетаскивайте карточки между статусами, быстро считывайте нагрузку по колонкам и держите весь поток работ под рукой.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="fi-theme-stat">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Всего заказов</span>
                        <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $totalOrders }}</span>
                    </div>

                    <div class="fi-theme-stat">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Статусов активны</span>
                        <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $activeStatuses }}</span>
                    </div>

                    <div class="fi-theme-stat">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Колонок всего</span>
                        <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ count($this->board) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <div
            x-ref="board"
            class="kanban-scrollbar-hidden flex items-start gap-4 overflow-x-auto pb-3 md:grid md:grid-cols-4 md:gap-4 md:overflow-visible"
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
                    class="fi-theme-surface fi-theme-card fi-theme-delay-{{ ($loop->index % 4) + 1 }} w-[17.5rem] shrink-0 p-3.5 md:w-auto md:min-w-0 md:p-4"
                    x-bind:class="pendingStatusId === {{ $column['id'] }} ? 'ring-2 ring-primary-300 dark:ring-primary-500/60' : ''"
                    x-on:dragenter.prevent="if (draggingOrderId) hoverStatusId = {{ $column['id'] }}"
                    x-on:dragover.prevent
                    x-on:drop.prevent="
                        const orderId = Number(event.dataTransfer.getData('text/plain'));

                        if (! orderId) {
                            return;
                        }

                        captureCardPositions();
                        pendingOrderId = orderId;
                        pendingStatusId = {{ $column['id'] }};
                        hoverStatusId = null;
                        stopAutoScroll();

                        $wire.moveOrder(orderId, {{ $column['id'] }})
                            .then(() => {
                                draggingOrderId = null;
                                pendingOrderId = null;
                                pendingStatusId = null;
                                animateCardReflow();
                                restoreDragSource();
                            })
                            .catch(() => {
                                pendingOrderId = null;
                                pendingStatusId = null;
                                draggingOrderId = null;
                                cardPositions = {};
                                restoreDragSource();
                            });
                    "
                    x-on:dragleave="if (! $el.contains($event.relatedTarget) && hoverStatusId === {{ $column['id'] }}) hoverStatusId = null"
                >
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <div class="min-w-0 space-y-1">
                            <div lang="ru" class="fi-theme-pill mb-2 w-fit px-3 py-1.5 text-sm font-semibold">
                                {{ $column['title'] }}
                            </div>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ count($column['orders']) }} заказ(ов)
                            </p>
                        </div>

                        <span class="{{ $dotClass }} inline-flex h-3 w-3 rounded-full shadow-[0_0_20px_currentColor]"></span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse ($column['orders'] as $order)
                            <article
                                wire:key="kanban-order-{{ $order['id'] }}"
                                class="fi-theme-card min-h-[7.5rem] min-w-0 cursor-move border border-white/10 p-3.5"
                                data-kanban-card
                                data-order-id="{{ $order['id'] }}"
                                draggable="true"
                                x-on:dragstart="
                                    draggingOrderId = {{ $order['id'] }};
                                    dragSourceElement = event.currentTarget;
                                    dragPlaceholderHeight = `${event.currentTarget.offsetHeight}px`;
                                    hoverStatusId = {{ $column['id'] }};
                                    event.dataTransfer.effectAllowed = 'move';
                                    event.dataTransfer.setData('text/plain', '{{ $order['id'] }}');
                                    hideDragSource({{ $order['id'] }});
                                "
                                x-on:dragend="
                                    draggingOrderId = null;
                                    hoverStatusId = null;
                                    dragPlaceholderHeight = null;
                                    stopAutoScroll();

                                    if (pendingOrderId !== {{ $order['id'] }}) {
                                        pendingStatusId = null;
                                        restoreDragSource();
                                    }
                                "
                            >
                                <div class="flex h-full flex-col gap-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 lang="ru" class="kanban-text-wrap min-w-0 flex-1 text-sm font-semibold leading-snug text-gray-950 dark:text-white">
                                            {{ $order['title'] }}
                                        </h3>

                                        @if ($order['can_delete'])
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
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

                                    <div class="space-y-1 text-xs leading-5 text-gray-600 dark:text-gray-300">
                                        @foreach ($order['meta_lines'] as $line)
                                            <p lang="ru" class="kanban-text-wrap rounded-lg bg-white/45 px-2.5 py-1.5 dark:bg-white/5">
                                                {{ $line }}
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/40 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/15 dark:bg-white/5 dark:text-gray-400">
                                Перетащите сюда заказ
                            </div>
                        @endforelse

                        <div
                            x-cloak
                            x-show="draggingOrderId && hoverStatusId === {{ $column['id'] }}"
                            class="kanban-drop-placeholder"
                            x-bind:style="dragPlaceholderHeight ? `height: ${dragPlaceholderHeight};` : ''"
                        ></div>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
