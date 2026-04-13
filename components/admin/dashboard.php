<?php
// Viewmodel örneği kontrolü ekleyelim
if (!isset($viewModel) || !($viewModel instanceof AdminViewModel)) {
    die("AdminViewModel instance not found!");
}

// Dashboard istatistiklerini al
$stats = $viewModel->getDashboardStats();

// Debug bilgisi ekleyelim
if (empty($stats)) {
    error_log("Dashboard stats are empty");
}
?>


<!-- Özel stil tanımları -->
<style>
.stat-card {
    padding: 0.75rem 1rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.75rem;
}
.card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 0.375rem;
    flex-shrink: 0;
}
.card-title {
    font-size: 0.75rem;
    line-height: 1.2;
    color: #6b7280;
    flex: 1;
}
.stat-number {
    font-size: 1.125rem;
    font-weight: 700;
    flex-shrink: 0;
}
</style>

<!-- Dashboard -->
<div class="content-area">
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title">Dashboard</h1>
        <div class="flex items-center space-x-3">
            <select id="timeRange" class="px-4 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 shadow-sm">
                <option value="7">Son 7 Gün</option>
                <option value="30">Son 30 Gün</option>
                <option value="90">Son 90 Gün</option>
                <option value="365">Son 1 Yıl</option>
            </select>
            <button onclick="exportAnalytics()" class="flex items-center px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 text-xs transition">
                <i class="fas fa-download mr-2"></i> Raporu İndir
            </button>
        </div>
    </div>
    
    <!-- İstatistik Kartları -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
        <div class="stat-card">
            <div class="card-icon bg-blue-100">
                <i class="fas fa-users text-blue-500 text-sm"></i>
            </div>
            <span class="card-title">Toplam Öğretmen</span>
            <span class="stat-number text-blue-500"><?php echo $viewModel->getTotalTeachers(); ?></span>
        </div>
        
        <div class="stat-card">
            <div class="card-icon bg-green-100">
                <i class="fas fa-file-alt text-green-500 text-sm"></i>
            </div>
            <span class="card-title">Aktif Başvurular</span>
            <span class="stat-number text-green-500"><?php echo $viewModel->getActiveApplications(); ?></span>
        </div>

        <div class="stat-card">
            <div class="card-icon bg-yellow-100">
                <i class="fas fa-clock text-yellow-500 text-sm"></i>
            </div>
            <span class="card-title">Bekleyen Başvurular</span>
            <span class="stat-number text-yellow-600"><?php echo $viewModel->getPendingApplications(); ?></span>
        </div>
        
        <div class="stat-card">
            <div class="card-icon bg-red-100">
                <i class="fas fa-times-circle text-red-500 text-sm"></i>
            </div>
            <span class="card-title">Reddedilen Başvurular</span>
            <span class="stat-number text-red-500"><?php echo $viewModel->getRejectedApplications(); ?></span>
        </div>
        
        <div class="stat-card">
            <div class="card-icon bg-purple-100">
                <i class="fas fa-check-circle text-purple-500 text-sm"></i>
            </div>
            <span class="card-title">Onaylanan Başvurular</span>
            <span class="stat-number text-purple-500"><?php echo $viewModel->getApprovedApplications(); ?></span>
        </div>
    </div>
    
    <!-- Grafik -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Başvuru İstatistikleri</h3>
            <div class="flex space-x-4">
                <button onclick="toggleChartType()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-chart-line"></i>
                </button>
                <button onclick="toggleChartType()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-chart-bar"></i>
                </button>
            </div>
                                    </div>
        <div style="height: 300px;">
            <canvas id="analyticsChart"></canvas>
                                        </div>
                                    </div>
                                </div>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chart;

// Grafik verilerini hazırla
const chartData = {
    labels: ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'],
    datasets: [
        {
            label: 'Başvurular',
            data: [65, 59, 80, 81, 56, 55, 40],
            borderColor: '#0ea5e9',
            backgroundColor: 'rgba(14, 165, 233, 0.6)',
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5
        },
        {
            label: 'Onaylananlar',
            data: [28, 48, 40, 19, 86, 27, 90],
            borderColor: '#8b5cf6',
            backgroundColor: 'rgba(139, 92, 246, 0.6)',
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5
        }
    ]
};

// Grafik ayarları
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 750,
        easing: 'easeInOutQuart'
    },
    plugins: {
        legend: {
            position: 'top',
            labels: {
                usePointStyle: true,
                padding: 20,
                boxWidth: 8
            }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            titleColor: '#1e293b',
            bodyColor: '#475569',
            borderColor: '#e2e8f0',
            borderWidth: 1,
            padding: 10,
            displayColors: true,
            callbacks: {
                label: function(context) {
                    return context.dataset.label + ': ' + context.parsed.y;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
                stepSize: 1
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    },
    interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
    }
};

// Grafiği oluştur
function initChart() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: chartOptions
    });
}

// Grafik tipini değiştir
function toggleChartType() {
    const newType = chart.config.type === 'line' ? 'bar' : 'line';
    
    // Grafik tipine göre renk ve görünüm ayarları
    if (newType === 'bar') {
        // Çubuk grafik için daha belirgin renkler
        chart.data.datasets[0].backgroundColor = 'rgba(14, 165, 233, 0.8)';
        chart.data.datasets[1].backgroundColor = 'rgba(139, 92, 246, 0.8)';
        
        // Çubuk grafik için gerekli ayarlar
        chart.data.datasets.forEach(dataset => {
            dataset.borderWidth = 1;
        });
    } else {
        // Çizgi grafik için orijinal renkler
        chart.data.datasets[0].backgroundColor = 'rgba(14, 165, 233, 0.6)';
        chart.data.datasets[1].backgroundColor = 'rgba(139, 92, 246, 0.6)';
        
        // Çizgi grafik için gerekli ayarlar
        chart.data.datasets.forEach(dataset => {
            dataset.fill = true;
        });
    }
    
    chart.config.type = newType;
    chart.update();
}

// Zaman aralığını değiştir
document.getElementById('timeRange').addEventListener('change', function(e) {
    const days = e.target.value;
    updateChartData(days);
});

// Grafik verilerini güncelle
function updateChartData(days) {
    csrfFetch(`<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_application_stats&days=${days}`)
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('tr-TR', { weekday: 'short' });
            });
            
            const applications = data.map(item => item.total_applications);
            const approved = data.map(item => item.approved_applications);
            
            chart.data.labels = labels;
            chart.data.datasets[0].data = applications;
            chart.data.datasets[1].data = approved;
            chart.update('none'); // Animasyonu devre dışı bırak
        })
        .catch(error => console.error('Veri güncellenirken hata oluştu:', error));
}

// Sayfa yüklendiğinde grafiği başlat ve verileri yükle
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    updateChartData(7); // Varsayılan olarak son 7 günün verilerini yükle
});

// Raporu indir
function exportAnalytics() {
    // Burada rapor indirme işlemini gerçekleştirebilirsiniz
    console.log('Rapor indirme işlemi başlatıldı');
}
</script>
