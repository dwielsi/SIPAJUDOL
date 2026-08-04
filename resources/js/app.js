import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import serverTable from './server-table';
import dashboardCharts from './dashboard-charts';
import riskTrendChart from './risk-trend-chart';
import scanProgress from './scan-progress';
import notificationBell from './notification-bell';

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.data('serverTable', serverTable);
Alpine.data('dashboardCharts', dashboardCharts);
Alpine.data('riskTrendChart', riskTrendChart);
Alpine.data('scanProgress', scanProgress);
Alpine.data('notificationBell', notificationBell);

Alpine.store('theme', {
    dark: localStorage.getItem('theme') === 'dark'
        || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),

    init() {
        this.apply();
    },

    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        this.apply();
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
    },
});

Alpine.store('sidebar', {
    collapsed: localStorage.getItem('sidebar-collapsed') === '1',

    toggle() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
    },
});

Alpine.start();
