<div class="grid gap-6 sm:grid-cols-3">
    @php
        $statsData = [
            [
                'label' => 'Общий бюджет',
                'value' => (float) ($this->report['total_budget'] ?? 0),
                'description' => 'Сумма оплаченных заказов',
                'color' => 'primary',
                'accent' => 'rgba(251, 191, 36, 0.45)',
            ],
            [
                'label' => 'Комиссия бирж',
                'value' => (float) ($this->report['total_commission'] ?? 0),
                'description' => 'Удержания по заказам',
                'color' => 'warning',
                'accent' => 'rgba(245, 158, 11, 0.45)',
            ],
            [
                'label' => 'Чистый доход',
                'value' => (float) ($this->report['total_net_income'] ?? 0),
                'description' => 'После вычета комиссии',
                'color' => 'success',
                'accent' => 'rgba(16, 185, 129, 0.45)',
            ],
        ];
    @endphp

    @foreach ($statsData as $index => $stat)
        <div
            wire:key="stat-card-{{ $index }}"
            class="fi-theme-glass-card relative overflow-hidden rounded-2xl p-6 transition duration-300"
            style="will-change: transform;"
        >
            <!-- Soft background glow matching card accent color -->
            <div
                class="absolute -right-8 -top-8 h-24 w-24 rounded-full blur-2xl opacity-20 pointer-events-none"
                style="background-color: {{ $stat['accent'] }};"
            ></div>

            <div class="space-y-2">
                <!-- Stat label -->
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                    {{ $stat['label'] }}
                </span>

                <!-- Animated Stat Value -->
                <div
                    x-data="{
                        currentValue: 0,
                        targetValue: {{ $stat['value'] }},
                        init() {
                            this.currentValue = this.targetValue;
                            this.$watch('targetValue', (newVal, oldVal) => {
                                let start = null;
                                const duration = 400;
                                const fromVal = (oldVal !== undefined && oldVal !== null) ? oldVal : 0;
                                const toVal = newVal;
                                const animate = (timestamp) => {
                                    if (!start) start = timestamp;
                                    const elapsed = timestamp - start;
                                    const progress = Math.min(elapsed / duration, 1);
                                    const ease = 1 - Math.pow(1 - progress, 3);
                                    this.currentValue = fromVal + (toVal - fromVal) * ease;
                                    if (progress < 1) {
                                        requestAnimationFrame(animate);
                                    } else {
                                        this.currentValue = toVal;
                                    }
                                };
                                requestAnimationFrame(animate);
                            });
                        },
                        format(val) {
                            return new Intl.NumberFormat('ru-RU', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(val) + ' ₽';
                        }
                    }"
                    x-effect="targetValue = {{ $stat['value'] }}"
                    class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl"
                >
                    <span x-text="format(currentValue)"></span>
                </div>

                <!-- Stat Description -->
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $stat['description'] }}
                </p>
            </div>

            <!-- Sleek color indicator bar on top edge -->
            <div
                class="absolute top-0 left-0 right-0 h-[3px]"
                style="background-color: {{ $stat['accent'] }};"
            ></div>
        </div>
    @endforeach
</div>
