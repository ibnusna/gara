
document.addEventListener('DOMContentLoaded', () => {

    
    const API_URL = 'ajax/get_dashboard_data.php';

    
    const chartColors = {
        blue: 'rgba(54, 162, 235, 0.8)',
        green: 'rgba(75, 192, 192, 0.8)',
        yellow: 'rgba(255, 206, 86, 0.8)',
        red: 'rgba(255, 99, 132, 0.8)',
        purple: 'rgba(153, 102, 255, 0.8)',
        orange: 'rgba(255, 159, 64, 0.8)',
        grey: 'rgba(201, 203, 207, 0.8)'
    };
    
    const chartBorderColors = {
        blue: 'rgba(54, 162, 235, 1)',
        green: 'rgba(75, 192, 192, 1)',
        yellow: 'rgba(255, 206, 86, 1)',
        red: 'rgba(255, 99, 132, 1)',
        purple: 'rgba(153, 102, 255, 1)',
        orange: 'rgba(255, 159, 64, 1)',
        grey: 'rgba(201, 203, 207, 1)'
    };


    




    async function fetchChartData(chartType) {
        try {
            const response = await fetch(`${API_URL}?chart=${chartType}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        } catch (error) {
            console.error(`Gagal mengambil data untuk chart '${chartType}':`, error);
            return null; 
        }
    }


    


    async function renderAttendanceTrendChart() {
        const ctx = document.getElementById('attendanceTrendChart')?.getContext('2d');
        if (!ctx) return; 

        const data = await fetchChartData('attendance_trend');
        if (!data || !data.labels || !data.values) {
            ctx.canvas.parentElement.innerHTML = '<p class="text-center text-muted">Gagal memuat data tren kehadiran.</p>';
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Persentase Kehadiran (%)',
                    data: data.values,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: chartBorderColors.blue,
                    borderWidth: 2,
                    tension: 0.3, 
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100, 
                        ticks: {
                            callback: function(value) { return value + "%" }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` Kehadiran: ${context.parsed.y}%`;
                            }
                        }
                    }
                }
            }
        });
    }


    


    async function renderGradeDistributionChart() {
        const ctx = document.getElementById('gradeDistributionChart')?.getContext('2d');
        if (!ctx) return;

        const data = await fetchChartData('grade_distribution');
        if (!data || !data.labels || !data.values || data.values.every(v => v == 0)) {
            ctx.canvas.parentElement.innerHTML = '<p class="text-center text-muted">Data nilai tugas belum cukup untuk ditampilkan.</p>';
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: data.values,
                    backgroundColor: [
                        chartColors.green, 
                        chartColors.blue, 
                        chartColors.yellow, 
                        chartColors.red
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                           label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed;
                                return label + ' siswa';
                           }
                        }
                    }
                }
            }
        });
    }

    


    async function renderAttendanceCompositionChart() {
        const ctx = document.getElementById('attendanceCompositionChart')?.getContext('2d');
        if (!ctx) return;

        const data = await fetchChartData('attendance_composition');
        if (!data || !data.labels || !data.values || data.values.every(v => v == 0)) {
             ctx.canvas.parentElement.innerHTML = '<p class="text-center text-muted">Data absensi belum tersedia.</p>';
            return;
        }

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Total',
                    data: data.values,
                    backgroundColor: [
                        chartColors.green, 
                        chartColors.yellow, 
                        chartColors.orange, 
                        chartColors.red 
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
             options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                           label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed;
                                return label;
                           }
                        }
                    }
                }
            }
        });
    }


    
    renderAttendanceTrendChart();
    renderGradeDistributionChart();
    renderAttendanceCompositionChart();

});
