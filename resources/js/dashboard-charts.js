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
                        backgroundColor: ['#24B9AD', '#F3DA52', '#1A3A65'],
                        borderRadius: 8,
                        maxBarThickness: 36,
                    }],
                },
                options: baseOptions(),
            });
        },
    };
}
