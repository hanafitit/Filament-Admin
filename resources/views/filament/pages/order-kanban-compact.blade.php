<x-filament-panels::page>
    <div class="mb-6 flex items-center justify-between gap-3">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Свернутые списки по статусам для быстрого обзора.
        </p>

        @if ($this->canDeleteOrders())
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500/20 dark:text-red-400 dark:hover:bg-red-500/10"
                x-data="{}"
                x-on:click="
                    if (! confirm('Очистить все заказы со статусом Сдан?')) {
                        return;
                    }

                    $wire.clearReadyOrders();
                "
            >
                Очистить
            </button>
        @endif
    </div>

    <div class="space-y-4">
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

            <details
                wire:key="compact-kanban-column-{{ $column['id'] }}"
                class="group overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <summary class="flex cursor-pointer items-center justify-between gap-3 px-5 py-4 marker:hidden">
                    <div class="flex items-center gap-3">
                        <span class="{{ $dotClass }} inline-flex h-3 w-3 rounded-full"></span>

                        <div>
                            <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $column['title'] }}
                            </h2>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ count($column['orders']) }} заказ(ов)
                            </p>
                        </div>
                    </div>

                    <svg
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="h-4 w-4 text-gray-400 transition group-open:rotate-180"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </summary>

                <div class="border-t border-gray-100 px-5 py-4 dark:border-white/10">
                    <div class="space-y-3">
                        @forelse ($column['orders'] as $order)
                            <article
                                wire:key="compact-kanban-order-{{ $order['id'] }}"
                                class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-2">
                                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                                            {!! nl2br(e($order['title'])) !!}
                                        </h3>

                                        <div class="space-y-0.5 text-xs leading-5 text-gray-600 dark:text-gray-300">
                                            @foreach ($order['meta_lines'] as $line)
                                                <p class="kanban-text-wrap">
                                                    {{ $line }}
                                                </p>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a
                                            href="{{ $order['edit_url'] }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-gray-200"
                                            title="Редактировать заказ"
                                        >
                                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                                <path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-8.8 8.8-3.364.536.536-3.364 8.8-8.8Z" />
                                            </svg>
                                        </a>

                                        @if ($order['can_delete'])
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                                title="Удалить заказ"
                                                x-data="{}"
                                                x-on:click="
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
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        Переместить в статус
                                    </label>

                                    <select
                                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-amber-400 dark:border-white/10 dark:bg-white/5 dark:text-gray-100"
                                        wire:change="moveOrder({{ $order['id'] }}, $event.target.value)"
                                    >
                                        @foreach ($this->statusOptions as $statusId => $statusTitle)
                                            <option
                                                value="{{ $statusId }}"
                                                @selected($statusId === $column['id'])
                                            >
                                                {{ $statusTitle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                                В этом статусе пока пусто
                            </div>
                        @endforelse
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</x-filament-panels::page>
