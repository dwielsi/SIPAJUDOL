export default function serverTable(endpoint, columns, filters = {}) {
    return {
        rows: [],
        columns,
        filters,
        loading: true,
        search: '',
        page: 1,
        perPage: 10,
        total: 0,
        filtered: 0,
        draw: 0,

        init() {
            this.fetchRows();
            this.$watch('search', () => {
                this.page = 1;
                this.fetchRows();
            });
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filtered / this.perPage));
        },

        goToPage(page) {
            if (page < 1 || page > this.lastPage) return;
            this.page = page;
            this.fetchRows();
        },

        fetchRows() {
            this.loading = true;
            this.draw++;

            const params = new URLSearchParams({
                draw: this.draw,
                start: (this.page - 1) * this.perPage,
                length: this.perPage,
                'search[value]': this.search,
                ...this.filters,
            });

            this.columns.forEach((col, i) => {
                params.append(`columns[${i}][data]`, col);
                params.append(`columns[${i}][searchable]`, 'true');
                params.append(`columns[${i}][orderable]`, 'false');
            });

            fetch(`${endpoint}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((res) => res.json())
                .then((json) => {
                    this.rows = json.data;
                    this.total = json.recordsTotal;
                    this.filtered = json.recordsFiltered;
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                });
        },
    };
}
