import Chart from 'chart.js/auto';

function baseOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
    };
}

export default function dashboardCharts({ monthly, riskLevels, statusCounts }) {
    return {
        init() {
            new Chart(this.$refs.monthlyChart, {
                type: 'line',
                data: {
                    labels: monthly.map((m) => m.label),
                    datasets: [{
                        label: 'Jumlah Scan',
                        data: monthly.map((m) => m.count),
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.35,
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
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                        borderWidth: 0,
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
            });

            new Chart(this.$refs.riskChart, {
                type: 'bar',
                data: {
                    labels: Object.keys(riskLevels),
                    datasets: [{
                        label: 'Jumlah Website',
                        data: Object.values(riskLevels),
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                        borderRadius: 6,
                    }],
                },
                options: baseOptions(),
            });
        },
    };
}
