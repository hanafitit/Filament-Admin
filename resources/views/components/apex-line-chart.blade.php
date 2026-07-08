@props([
    'labels' => [],
    'values' => [],
    'title' => 'Sales',
])

<div x-data="{
    labels: {{ json_encode($labels) }},
    values: {{ json_encode($values) }},
    init() {
        if (typeof ApexCharts === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
            script.onload = () => this.renderChart();
            document.head.appendChild(script);
        } else {
            this.renderChart();
        }
    },
    renderChart() {
        let chart = new ApexCharts(this.$refs.chart, {
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true
                }
            },
            series: [{
                name: '{{ $title }}',
                data: this.values
            }],
            xaxis: {
                categories: this.labels
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#3b82f6'],
            grid: {
                strokeDashArray: 4
            },
            markers: {
                size: 4
            }
        });
        chart.render();
    }
}" wire:ignore>
    <div x-ref="chart"></div>
</div>
