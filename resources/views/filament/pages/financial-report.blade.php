<x-filament-panels::page>
    @php
        $sourcesCount = count($this->report['by_source'] ?? []);
        $usersCount = count($this->report['by_user'] ?? []);
    @endphp

    <section class="fi-theme-hero mb-6">
        <div class="fi-theme-grid"></div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <span class="fi-theme-pill text-xs font-semibold uppercase tracking-[0.24em]">
                    Finance insights
                </span>

                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                        Финансовая сводка в одном экране
                    </h1>

                    <p class="max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Фильтруйте период, сравнивайте доходы по источникам и исполнителям и быстро находите изменения в потоке заказов.
                    </p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="fi-theme-stat">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Источников в отчете</span>
                    <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $sourcesCount }}</span>
                </div>

                <div class="fi-theme-stat">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Исполнителей в отчете</span>
                    <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $usersCount }}</span>
                </div>
            </div>
        </div>
    </section>

    <form wire:submit="applyFilters" class="space-y-6">
        {{ $this->form }}

        <div class="fi-theme-card rounded-2xl border border-amber-200/70 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/20 dark:text-amber-100">
            Скачать подробный отчет в Excel можно во вкладке
            <a
                href="{{ $this->getOrdersPageUrl() }}"
                class="font-semibold underline underline-offset-2 transition hover:text-amber-700 dark:hover:text-amber-200"
            >
                "Заказы"
            </a>.
        </div>

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-funnel">
                Применить фильтр
            </x-filament::button>
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="fi-theme-surface fi-theme-card fi-theme-delay-1 p-5">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Доход по источникам
                </h2>
            </div>

            <div class="space-y-3">
                @forelse ($this->report['by_source'] ?? [] as $row)
                    <div class="fi-theme-card flex items-center justify-between gap-4 px-4 py-3">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $row['name'] }}
                        </span>

                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $this->formatMoney($row['income']) }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white/40 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/15 dark:bg-white/5 dark:text-gray-400">
                        За выбранный период данных нет
                    </div>
                @endforelse
            </div>
        </section>

        <section class="fi-theme-surface fi-theme-card fi-theme-delay-2 p-5">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Доход по исполнителям
                </h2>
            </div>

            <div class="space-y-3">
                @forelse ($this->report['by_user'] ?? [] as $row)
                    <div class="fi-theme-card flex items-center justify-between gap-4 px-4 py-3">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $row['name'] }}
                        </span>

                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $this->formatMoney($row['income']) }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white/40 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/15 dark:bg-white/5 dark:text-gray-400">
                        За выбранный период данных нет
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
