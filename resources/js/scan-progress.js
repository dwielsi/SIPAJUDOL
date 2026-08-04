export default function scanProgress(statusUrl, initialState = {}) {
    return {
        state: initialState.scan_state || 'queued',
        percent: initialState.progress_percent || 0,
        step: initialState.current_step || '',
        riskScore: initialState.risk_score ?? null,
        status: initialState.status ?? null,
        polling: null,

        init() {
            if (this.isActive()) {
                this.poll();
            }
        },

        isActive() {
            return this.state === 'queued' || this.state === 'running';
        },

        poll() {
            this.polling = setInterval(async () => {
                const res = await fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const data = await res.json();

                this.state = data.scan_state;
                this.percent = data.progress_percent;
                this.step = data.current_step;
                this.riskScore = data.risk_score;
                this.status = data.status;

                if (!this.isActive()) {
                    clearInterval(this.polling);
                    window.location.reload();
                }
            }, 2000);
        },
    };
}
