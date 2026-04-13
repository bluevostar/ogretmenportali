<?php
// Filtre parametresi
$status_filter = isset($_GET['status_filter']) ? clean_input($_GET['status_filter']) : '';

// Başvuruları getir
$applications = $viewModel->getAllApplications($status_filter);

// Başvuruların dizi olduğundan ve boş olmadığından emin ol
if (!is_array($applications)) {
    $applications = [];
}
?>

<style>
/* Modal animations */
#applicationModal, #rejectModal {
    transition: opacity 0.2s ease-in-out;
    opacity: 0;
}

#applicationModal.show, #rejectModal.show {
    opacity: 1;
}

/* Table hover effects */
.hover\:bg-gray-50:hover {
    transition: background-color 0.15s ease-in-out;
}

/* Button hover transitions */
button {
    transition: all 0.2s ease-in-out;
}

/* Status badge transitions */
.rounded-full {
    transition: transform 0.2s ease-in-out;
}

.rounded-full:hover {
    transform: scale(1.05);
}

/* Input focus animations */
input:focus, select:focus {
    transition: all 0.2s ease-in-out;
}

/* Table loading skeleton animation */
@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

.animate-shimmer {
    animation: shimmer 2s infinite linear;
    background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
    background-size: 1000px 100%;
}
</style>

<div class="content-area">    <!-- Başlık ve İşlem Butonları -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <!-- Başlık -->
            <h1 class="header-title mb-4 md:mb-0">Başvurular</h1>

            <!-- Sağ Bölüm: Arama, Filtreler ve Butonlar -->
            <div class="flex flex-col lg:flex-row gap-4 w-full lg:w-auto">                <!-- Arama Formu -->
                <form action="" method="get" class="flex w-full md:w-auto">
                    <input type="hidden" name="action" value="applications">
                    <div class="flex w-full">
                        <input type="text" name="search" placeholder="Başvuru ara..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                               class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Filtreler -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Durum Filtresi -->
                    <select id="statusFilter" 
                            class="block w-full sm:w-48 shadow-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tüm Durumlar</option>
                        <option value="pending">Bekleyen</option>
                        <option value="approved">Onaylanan</option>
                        <option value="rejected">Reddedilen</option>
                    </select>

                    <!-- Branş Filtresi -->
                    <select id="branchFilter" 
                            class="block w-full sm:w-48 shadow-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tüm Branşlar</option>
                        <option value="matematik">Matematik</option>
                        <option value="fizik">Fizik</option>
                        <option value="kimya">Kimya</option>
                        <option value="biyoloji">Biyoloji</option>
                        <option value="tarih">Tarih</option>
                        <option value="cografya">Coğrafya</option>
                        <option value="turkce">Türkçe</option>
                        <option value="ingilizce">İngilizce</option>
                    </select>

                    <!-- Excel Export Butonu -->
                    <button onclick="exportToExcel()" 
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>
                        Excel'e Aktar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Başvuru Listesi -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Öğretmen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Okul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branş</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($applications && count($applications) > 0): ?>
                        <?php foreach ($applications as $application): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="<?php echo !empty($application['profile_photo']) ? $application['profile_photo'] : BASE_URL . '/assets/images/avatar-placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($application['teacher_name']); ?>">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($application['teacher_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($application['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($application['school_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($application['branch_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('d.m.Y', strtotime($application['application_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusText = [
                                    'pending' => 'Bekliyor',
                                    'approved' => 'Onaylandı',
                                    'rejected' => 'Reddedildi'
                                ];
                                $statusIcon = [
                                    'pending' => 'fa-clock',
                                    'approved' => 'fa-check',
                                    'rejected' => 'fa-times'
                                ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClasses[$application['status']]; ?>">
                                    <i class="fas <?php echo $statusIcon[$application['status']]; ?> mr-1"></i>
                                    <?php echo $statusText[$application['status']]; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-3">
                                    <button onclick="viewApplication(<?php echo $application['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900" title="Görüntüle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($application['status'] === 'pending'): ?>
                                        <button onclick="approveApplication(<?php echo $application['id']; ?>)"
                                                class="text-green-600 hover:text-green-900" title="Onayla">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="rejectApplication(<?php echo $application['id']; ?>)"
                                                class="text-red-600 hover:text-red-900" title="Reddet">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center py-6">
                                    <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
                                    <p>Henüz başvuru bulunmamaktadır.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Başvuru Detay Modal -->
<div id="applicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-xl rounded-lg bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Başvuru Detayları</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="applicationDetails" class="space-y-8">
            <!-- AJAX ile yüklenecek içerik buraya gelecek -->
        </div>
    </div>
</div>

<!-- Reddetme Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-md shadow-xl rounded-lg bg-white">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Başvuruyu Reddet</h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="rejectForm" class="space-y-4">
            <input type="hidden" id="rejectApplicationId">
            <div>
                <label for="rejectionReason" class="block text-sm font-medium text-gray-700">Red Nedeni</label>
                <textarea id="rejectionReason" name="rejection_reason" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    İptal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                    Reddet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Arama ve filtreleme
document.getElementById('searchInput').addEventListener('input', filterApplications);
document.getElementById('statusFilter').addEventListener('change', filterApplications);
document.getElementById('branchFilter').addEventListener('change', filterApplications);

function filterApplications() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const branchFilter = document.getElementById('branchFilter').value;
    
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const teacherName = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const schoolName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const branch = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const status = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
        
        const matchesSearch = teacherName.includes(searchText) || schoolName.includes(searchText);
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        const matchesBranch = !branchFilter || branch.includes(branchFilter);
        
        row.style.display = matchesSearch && matchesStatus && matchesBranch ? '' : 'none';
    });
}

// Başvuru detaylarını görüntüleme
async function viewApplication(id) {
    try {
        const response = await csrfFetch(`<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=get_application&id=${id}`);
        if (!response.ok) {
            throw new Error('Başvuru detayları alınamadı');
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Başvuru detayları alınamadı');
        }

        const details = document.getElementById('applicationDetails');
        details.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Öğretmen Bilgileri -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            ${data.profile_image 
                                ? `<img src="${data.profile_image}" class="h-12 w-12 rounded-full" alt="">` 
                                : `<div class="h-12 w-12 rounded-full bg-primary-100 flex items-center justify-center">
                                    <i class="fas fa-user text-primary-500"></i>
                                   </div>`
                            }
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900">${data.teacher_name}</h4>
                            <p class="text-sm text-gray-500">${data.teacher_email}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Telefon</label>
                            <p class="mt-1 text-sm text-gray-900">${data.teacher_phone || '-'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Deneyim</label>
                            <p class="mt-1 text-sm text-gray-900">${data.experience || '0'} Yıl</p>
                        </div>
                    </div>
                </div>

                <!-- Okul ve Başvuru Bilgileri -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Okul Bilgileri</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Okul Adı</label>
                            <p class="mt-1 text-sm text-gray-900">${data.school_name}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Branş</label>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${data.branch}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Başvuru Tarihi</label>
                            <p class="mt-1 text-sm text-gray-900">${new Date(data.application_date).toLocaleDateString('tr-TR')}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Durum</label>
                            ${getStatusBadge(data.status)}
                        </div>
                    </div>
                </div>

                <!-- Eğitim ve Deneyim -->
                <div class="md:col-span-2 bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Eğitim ve Deneyim Bilgileri</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Eğitim</label>
                            <p class="mt-1 text-sm text-gray-900">${data.education || '-'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Deneyim Detayı</label>
                            <p class="mt-1 text-sm text-gray-900">${data.experience_details || '-'}</p>
                        </div>
                    </div>
                </div>

                ${data.cv_path ? `
                <!-- CV Dokümanı -->
                <div class="md:col-span-2">
                    <a href="${data.cv_path}" target="_blank" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-primary-600 hover:bg-primary-700">
                        <i class="fas fa-file-pdf mr-2"></i>
                        CV'yi Görüntüle
                    </a>
                </div>
                ` : ''}

                ${data.status === 'rejected' && data.rejection_reason ? `
                <!-- Red Nedeni -->
                <div class="md:col-span-2 bg-red-50 rounded-lg p-6">
                    <h4 class="text-lg font-medium text-red-900 mb-2">Red Nedeni</h4>
                    <p class="text-sm text-red-700">${data.rejection_reason}</p>
                </div>
                ` : ''}
            </div>
        `;

        // Modalı göster
        document.getElementById('applicationModal').classList.remove('hidden');
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: error.message || 'Başvuru detayları alınırken bir hata oluştu.',
            confirmButtonColor: '#3085d6'
        });
    }
}

// Durum badge'i oluşturma yardımcı fonksiyonu
function getStatusBadge(status) {
    const statusConfig = {
        pending: {
            class: 'bg-yellow-100 text-yellow-800',
            text: 'Bekleyen',
            icon: 'fa-clock'
        },
        approved: {
            class: 'bg-green-100 text-green-800',
            text: 'Onaylandı',
            icon: 'fa-check'
        },
        rejected: {
            class: 'bg-red-100 text-red-800',
            text: 'Reddedildi',
            icon: 'fa-times'
        }
    };

    const config = statusConfig[status] || statusConfig.pending;
    return `
        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.class}">
            <i class="fas ${config.icon} mr-1"></i>
            ${config.text}
        </span>
    `;
}

// Modal kapatma
function closeModal() {
    document.getElementById('applicationModal').classList.add('hidden');
}

// Başvuru onaylama
async function approveApplication(id) {
    try {
        const result = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu başvuruyu onaylamak istediğinize emin misiniz?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Evet, Onayla',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            Swal.fire({
                title: 'İşleniyor...',
                text: 'Başvuru onaylanıyor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await csrfFetch(`<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=approve_application`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ application_id: id })
            });

            if (!response.ok) {
                throw new Error('Sunucu yanıt vermedi');
            }

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Başvuru başarıyla onaylandı.',
                    confirmButtonColor: '#3085d6'
                });
                location.reload();
            } else {
                throw new Error(data.message || 'Başvuru onaylanırken bir hata oluştu');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: error.message || 'Başvuru onaylanırken bir hata oluştu.',
            confirmButtonColor: '#3085d6'
        });
    }
}

// Reddetme modalını açma
async function rejectApplication(id) {
    try {
        const { value: reason } = await Swal.fire({
            title: 'Başvuruyu Reddet',
            input: 'textarea',
            inputLabel: 'Red Nedeni',
            inputPlaceholder: 'Reddetme nedeninizi yazın...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Reddet',
            cancelButtonText: 'İptal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Lütfen bir red nedeni yazın!';
                }
            }
        });

        if (reason) {
            Swal.fire({
                title: 'İşleniyor...',
                text: 'Başvuru reddediliyor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await csrfFetch(`<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=reject_application`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    application_id: id,
                    rejection_reason: reason
                })
            });

            if (!response.ok) {
                throw new Error('Sunucu yanıt vermedi');
            }

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Başvuru başarıyla reddedildi.',
                    confirmButtonColor: '#3085d6'
                });
                location.reload();
            } else {
                throw new Error(data.message || 'Başvuru reddedilirken bir hata oluştu');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: error.message || 'Başvuru reddedilirken bir hata oluştu.',
            confirmButtonColor: '#3085d6'
        });
    }
}

// Excel'e aktarma fonksiyonu
function exportToExcel() {
    try {
        // Export işlemi başladığında loading göster
        Swal.fire({
            title: 'Hazırlanıyor...',
            text: 'Excel dosyası oluşturuluyor',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Status ve branch filtrelerini al
        const status = document.getElementById('statusFilter').value;
        const branch = document.getElementById('branchFilter').value;
        
        // URL parametrelerini oluştur
        const params = new URLSearchParams({
            action: 'export_applications',
            status: status,
            branch: branch
        });

        // Export URL'ini oluştur
        const exportUrl = `<?php echo BASE_URL; ?>/php/country_admin_panel.php?${params.toString()}`;
        
        // Dosyayı indir
        window.location.href = exportUrl;

        // Loading'i kapat ve başarılı mesajını göster
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Başarılı!',
                text: 'Excel dosyası indirme işlemi başlatıldı.',
                confirmButtonColor: '#3085d6'
            });
        }, 1500);
    } catch (error) {
        console.error('Export error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Excel dosyası oluşturulurken bir hata oluştu.',
            confirmButtonColor: '#3085d6'
        });
    }
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Modal açılış/kapanış animasyonları
    const modals = ['applicationModal', 'rejectModal'];
    modals.forEach(modalId => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    const modal = document.getElementById(modalId);
                    if (!modal.classList.contains('hidden')) {
                        setTimeout(() => modal.classList.add('show'), 10);
                    } else {
                        modal.classList.remove('show');
                    }
                }
            });
        });

        const modal = document.getElementById(modalId);
        observer.observe(modal, { attributes: true });
    });

    // Tablo satırları için hover efekti
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.style.transform = 'translateX(5px)';
            row.style.transition = 'transform 0.2s ease-in-out';
        });
        row.addEventListener('mouseleave', () => {
            row.style.transform = 'translateX(0)';
        });
    });
});
</script>