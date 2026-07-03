<x-filament-panels::page>
    <form wire:submit="applyFilters" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" icon="heroicon-o-funnel">
                Применить фильтр
            </x-filament::button>
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Доход по источникам
                </h2>
            </div>

            <div class="space-y-3">
                @forelse ($this->report['by_source'] ?? [] as $row)
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/5">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $row['name'] }}
                        </span>

                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $this->formatMoney($row['income']) }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                        За выбранный период данных нет
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Доход по исполнителям
                </h2>
            </div>

            <div class="space-y-3">
                @forelse ($this->report['by_user'] ?? [] as $row)
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/5">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            {{ $row['name'] }}
                        </span>

                        <span class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $this->formatMoney($row['income']) }}
                        </span>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                        За выбранный период данных нет
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
