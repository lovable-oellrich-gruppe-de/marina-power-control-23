...
<script>
    const ctx = document.getElementById('verbrauchChart').getContext('2d');

    const barData = {
        labels: <?= json_encode(array_column($verbrauchsdaten, 'label')) ?>,
        datasets: [{
            label: 'Verbrauch (kWh)',
            data: <?= json_encode(array_column($verbrauchsdaten, 'verbrauch')) ?>,
            backgroundColor: '#2563eb'
        }]
    };

    const lineData = {
        datasets: <?= json_encode($zeitreihen) ?>
    };

    const defaultOptions = {
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (chartType === 'bar') {
                            const tooltips = <?= json_encode(array_column($verbrauchsdaten, 'tooltip')) ?>;
                            return tooltips[context.dataIndex].split('\n');
                        } else {
                            return context.formattedValue + ' kWh';
                        }
                    }
                }
            },
            title: {
                display: true,
                text: 'Verbrauchsdaten'
            }
        },
        responsive: true
    };

    let chartType = 'bar';
    let chart = new Chart(ctx, {
        type: 'bar',
        data: barData,
        options: {
            ...defaultOptions,
            scales: {
                x: {
                    type: 'category',
                    title: {
                        display: true,
                        text: 'Zähler'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'kWh'
                    }
                }
            }
        }
    });

    document.getElementById('barBtn').addEventListener('click', () => {
        chart.destroy();
        chartType = 'bar';
        chart = new Chart(ctx, {
            type: 'bar',
            data: barData,
            options: {
                ...defaultOptions,
                scales: {
                    x: {
                        type: 'category',
                        title: {
                            display: true,
                            text: 'Zähler'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'kWh'
                        }
                    }
                }
            }
        });
    });

    document.getElementById('lineBtn').addEventListener('click', () => {
        chart.destroy();
        chartType = 'line';
        chart = new Chart(ctx, {
            type: 'line',
            data: lineData,
            options: {
                ...defaultOptions,
                interaction: {
                    mode: 'nearest',
                    intersect: false
                },
                parsing: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'yyyy-MM-dd'
                        },
                        title: {
                            display: true,
                            text: 'Datum'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'kWh'
                        }
                    }
                }
            }
        });
    });
</script>
...
