(() => {
    const canvas = document.querySelector('#balance-chart');
    if (!canvas) return;

    const history = JSON.parse(canvas.dataset.history || '[]');
    const series = [
        ['trade', '#38bdf8'], ['longTerm', '#a78bfa'], ['home', '#f472b6'],
        ['bitcoin', '#f59e0b'], ['bbva', '#34d399'],
    ];
    const context = canvas.getContext('2d');

    function draw() {
        const ratio = window.devicePixelRatio || 1;
        const width = canvas.parentElement.clientWidth;
        const height = Math.min(360, Math.max(230, width * 0.42));
        canvas.width = width * ratio;
        canvas.height = height * ratio;
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);

        const padding = { top: 18, right: 14, bottom: 34, left: 58 };
        const plotW = width - padding.left - padding.right;
        const plotH = height - padding.top - padding.bottom;
        const maximum = Math.max(1, ...history.flatMap(row => series.map(([key]) => row[key])));

        context.font = '11px Poppins, sans-serif';
        context.fillStyle = '#8c8c8e';
        context.strokeStyle = '#41414e';
        context.lineWidth = 1;
        for (let step = 0; step <= 4; step++) {
            const y = padding.top + (plotH * step / 4);
            const value = maximum * (1 - step / 4);
            context.beginPath(); context.moveTo(padding.left, y); context.lineTo(width - padding.right, y); context.stroke();
            context.fillText(`${Math.round(value).toLocaleString('es-ES')} €`, 2, y + 4);
        }

        series.forEach(([key, color]) => {
            context.beginPath(); context.strokeStyle = color; context.lineWidth = 2;
            history.forEach((row, index) => {
                const x = padding.left + (history.length === 1 ? plotW / 2 : plotW * index / (history.length - 1));
                const y = padding.top + plotH - (row[key] / maximum * plotH);
                index === 0 ? context.moveTo(x, y) : context.lineTo(x, y);
            });
            context.stroke();
        });

        const first = new Date(`${history[0].date}T12:00:00`).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
        const last = new Date(`${history.at(-1).date}T12:00:00`).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
        context.fillStyle = '#8c8c8e'; context.fillText(first, padding.left, height - 8);
        context.fillText(last, width - padding.right - context.measureText(last).width, height - 8);
    }

    draw();
    new ResizeObserver(draw).observe(canvas.parentElement);
})();
