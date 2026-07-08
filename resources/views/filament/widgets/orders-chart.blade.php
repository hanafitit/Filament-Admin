<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-x-3">
            <h2 class="text-lg font-bold tracking-tight">Продажи за последнюю неделю</h2>
        </div>

        <div class="mt-4">
            @php
                $chartData = $this->getData();
            @endphp

            <x-apex-line-chart
                :labels="$chartData['labels']"
                :values="$chartData['values']"
                title="Сумма заказов"
            />
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
