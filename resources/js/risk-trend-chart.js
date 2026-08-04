import Chart from 'chart.js/auto';

export default function riskTrendChart({ labels, scores }) {
    return {
        init() {
            new Chart(this.$refs.riskTrendChart, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Skor Risiko',
                        data: scores,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100 } },
                },
            });
        },
    };
}
