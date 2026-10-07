import Chart from 'chart.js/auto';

function baseOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
    };
}

export default function dashboardCharts({ scanStats, riskLevels, statusCounts }) {
    let scanChart = null;

    return {
        period: 'monthly',
        periodLabels: { weekly: 'Mingguan', monthly: 'Bulanan', yearly: 'Tahunan' },

        setPeriod(period) {
            this.period = period;
            const series = scanStats[period];
            scanChart.data.labels = series.map((m) => m.label);
            scanChart.data.datasets[0].data = series.map((m) => m.count);
            scanChart.update();
        },

        init() {
            const initial = scanStats[this.period];

            scanChart = new Chart(this.$refs.scanChart, {
                type: 'line',
                data: {
                    labels: initial.map((m) => m.label),
                    datasets: [{
                        label: 'Jumlah Scan',
                        data: initial.map((m) => m.count),
                        borderColor: '#24B9AD',
                        backgroundColor: 'rgba(36, 185, 173, 0.12)',
                        pointBackgroundColor: '#24B9AD',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4,
                    }],
                },
                options: baseOptions(),
            });

            new Chart(this.$refs.statusChart, {
                type: 'doughnut',
                data: {
                    labels: ['Aman', 'Perlu Pemeriksaan', 'Terindikasi'],
                    datasets: [{
                        data: [statusCounts.safe, statusCounts.needs_review, statusCounts.flagged],
                        backgroundColor: ['#24B9AD', '#F3DA52', '#1A3A65'],
                        borderWidth: 0,
                        hoverOffset: 4,
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } },
            });

            new Chart(this.$refs.riskChart, {
                type: 'bar',
                data: {
                    labels: Object.keys(riskLevels),
                    datasets: [{
                        label: 'Jumlah Website',
                        data: Object.values(riskLevels),
                        backgroundColor: ['#24B9AD', '#F3DA52', '#1A3A65', '#94A3B8'],
                        borderRadius: 8,
                        maxBarThickness: 36,
                    }],
                },
                options: baseOptions(),
            });
        },
    };
}
