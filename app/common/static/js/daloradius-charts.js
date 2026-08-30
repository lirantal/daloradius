(function () {
    if (window.daloRadiusCharts) {
        return;
    }
    window.daloRadiusCharts = true;

    function showError(canvas) {
        var message = document.createElement('p');
        message.className = 'text-danger';
        message.textContent = 'Unable to load chart data.';
        canvas.parentNode.replaceChild(message, canvas);
    }

    function render(canvas) {
        fetch(canvas.dataset.chartSource, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Chart request failed');
                }
                return response.json();
            })
            .then(function (config) {
                new Chart(canvas.getContext('2d'), config);
            })
            .catch(function () {
                showError(canvas);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('canvas[data-chart-source]').forEach(render);
    });
}());
