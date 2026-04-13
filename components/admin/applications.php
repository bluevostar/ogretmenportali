<?php
// Filtreleme parametrelerini al
$filters = [
    'status' => $_GET['status'] ?? null,
    'school_id' => $_GET['school_id'] ?? null,
    'branch_id' => $_GET['branch_id'] ?? null,
    'search' => $_GET['search'] ?? null,
];

// Başvuruları ViewModel üzerinden getir
$applications = $viewModel->getApplications($filters);
$applications = is_array($applications) ? $applications : [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$totalApplications = count($applications);
$totalPages = max(1, (int)ceil($totalApplications / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$applications = array_slice($applications, ($page - 1) * $perPage, $perPage);

// Filtreleme için okulları ve branşları al
$schools = $viewModel->getSchools(['status' => 'active']);
$branches = $viewModel->getBranches();
?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">Başvuru Yönetimi</h1>
        
        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="applications">
                <div class="flex w-full">
                    <input type="text" name="search" placeholder="Başvuru ara..." 
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Excel Export -->
            <button onclick="exportExcel()" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs transition">
                <i class="fas fa-file-excel mr-2"></i> Excel Dışa Aktar
            </button>
            
            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Sil
            </button>
        </div>
    </div>

    <!-- Başvuru Listesi -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden flex-1 min-h-0 flex flex-col">
        <div class="overflow-x-auto flex-1 min-h-0">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3">
                        </th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">ID</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">Öğretmen</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">Okul</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">Branş</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[90px]">Tarih</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Durum</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="8" class="px-2 py-1.5 text-center text-gray-500 text-xs">
                                Filtreye uygun başvuru bulunamadı.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <input type="checkbox" class="application-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo $app['id']; ?>">
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo $app['id']; ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo htmlspecialchars($app['teacher_name']); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo htmlspecialchars($app['school_name']); ?></td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($app['branch_name']); ?></td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500"><?php echo date('d.m.Y', strtotime($app['application_date'])); ?></td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs">
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch ($app['status']) {
                                        case 'pending':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            $statusText = 'Beklemede';
                                            break;
                                        case 'approved':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            $statusText = 'Onaylandı';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            $statusText = 'Reddedildi';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                            $statusText = ucfirst($app['status']);
                                    }
                                    ?>
                                    <span class="px-1 py-0.5 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewApplication(<?php echo $app['id']; ?>)" class="text-blue-500 hover:text-blue-700" title="Görüntüle">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <button onclick="approveApplication(<?php echo $app['id']; ?>)" class="text-green-500 hover:text-green-700" title="Onayla">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                            <button onclick="rejectApplication(<?php echo $app['id']; ?>)" class="text-red-500 hover:text-red-700" title="Reddet">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
            <p class="text-sm text-gray-600">
                Toplam <span class="font-semibold"><?php echo (int)$totalApplications; ?></span> kayıt,
                Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
            </p>
            <div class="flex items-center gap-1">
                <?php
                    $baseUrl = BASE_URL . '/php/admin_panel.php?action=applications'
                        . '&search=' . urlencode((string)($filters['search'] ?? ''))
                        . '&status=' . urlencode((string)($filters['status'] ?? ''))
                        . '&school_id=' . urlencode((string)($filters['school_id'] ?? ''))
                        . '&branch_id=' . urlencode((string)($filters['branch_id'] ?? ''));
                ?>
                <?php if ($page > 1): ?>
                    <a href="<?php echo $baseUrl . '&page=' . ($page - 1); ?>" class="px-3 py-1.5 text-xs border rounded hover:bg-gray-50 text-gray-700">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <a href="<?php echo $baseUrl . '&page=' . $i; ?>" class="px-3 py-1.5 text-xs border rounded <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo $baseUrl . '&page=' . ($page + 1); ?>" class="px-3 py-1.5 text-xs border rounded hover:bg-gray-50 text-gray-700">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    // Tümünü seç
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.application-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Tekil checkbox'lar
    document.querySelectorAll('.application-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            const allCheckboxes = document.querySelectorAll('.application-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.application-checkbox:checked');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
            }
        });
    });
    
    // Toplu silme
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', handleDeleteSelected);
    }
});

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.application-checkbox:checked').length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (selectedCount === 0) {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = `<i class="fas fa-trash mr-2"></i> Sil`;
    }
}

function handleDeleteSelected() {
    const selectedIds = Array.from(document.querySelectorAll('.application-checkbox:checked')).map(cb => cb.dataset.id);
    if (selectedIds.length === 0) {
        Swal.fire('Uyarı', 'Lütfen silmek istediğiniz başvuruları seçin.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${selectedIds.length} başvuruyu silmek istediğinizden emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'İşleniyor...',
                text: 'Başvurular siliniyor',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_selected_applications', application_ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Başarılı!', 'Seçili başvurular silindi.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Hata!', 'Başvurular silinirken bir hata oluştu.', 'error');
                }
            });
        }
    });
}

function exportExcel() {
    window.location.href = '<?php echo BASE_URL; ?>/php/admin_panel.php?action=export_applications';
}

function viewApplication(id) {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=view_application&id=' + id)
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.application) {
            Swal.fire('Hata', data.message || 'Başvuru detayı alınamadı.', 'error');
            return;
        }

        const app = data.application;
        const statusMap = {
            pending: 'Beklemede',
            approved: 'Onaylandı',
            rejected: 'Reddedildi'
        };

        Swal.fire({
            title: 'Başvuru Detayı',
            html: `
                <div style="text-align:left;font-size:14px;line-height:1.6">
                    <p><strong>ID:</strong> ${app.id ?? '-'}</p>
                    <p><strong>Öğretmen:</strong> ${app.teacher_name ?? '-'}</p>
                    <p><strong>E-posta:</strong> ${app.teacher_email ?? '-'}</p>
                    <p><strong>Telefon:</strong> ${app.teacher_phone ?? '-'}</p>
                    <p><strong>Okul:</strong> ${app.school_name ?? '-'}</p>
                    <p><strong>Branş:</strong> ${app.branch_name ?? '-'}</p>
                    <p><strong>Durum:</strong> ${statusMap[app.status] ?? (app.status ?? '-')}</p>
                    <p><strong>Tarih:</strong> ${app.application_date ?? '-'}</p>
                </div>
            `,
            confirmButtonText: 'Kapat'
        });
    })
    .catch(() => Swal.fire('Hata', 'Başvuru detayı alınırken bir hata oluştu.', 'error'));
}

function approveApplication(id) {
    Swal.fire({
        title: 'Onaylıyor musunuz?',
        text: 'Bu başvuruyu onaylamak istediğinizden emin misiniz?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, onayla'
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX ile onaylama işlemi buraya eklenecek
            Swal.fire('Başarılı!', 'Başvuru onaylandı.', 'success').then(() => location.reload());
        }
    });
}

function rejectApplication(id) {
    Swal.fire({
        title: 'Red Nedenini Girin',
        input: 'textarea',
        inputPlaceholder: 'Red nedeni...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Reddet',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // AJAX ile reddetme işlemi buraya eklenecek
            Swal.fire('Başarılı!', 'Başvuru reddedildi.', 'success').then(() => location.reload());
        }
    });
}
</script>
