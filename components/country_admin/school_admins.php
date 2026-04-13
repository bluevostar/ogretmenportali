<?php
// Sadece okul yöneticilerini getir
$schoolAdmins = $viewModel->getSchoolAdmins($_SESSION['user_id']);

// Kullanıcı silme işlemi
if (isset($_POST['delete_user'])) {
    $userId = (int) $_POST['user_id'];
    $result = $viewModel->deleteUser($userId);
    
    if ($result) {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins&status=success&message=Kullanıcı başarıyla silindi.');
    } else {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins&status=error&message=Kullanıcı silinirken bir hata oluştu.');
    }
}

?>

<!-- JS dosyası ekle -->
<script src="<?php echo BASE_URL; ?>/js/school_admin_edit.js"></script>

<div class="content-area">
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title mb-4 md:mb-0">Okul Yöneticileri</h1>

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="school_admins">
                <div class="flex w-full">
                    <input type="text" name="search" placeholder="Yönetici ara..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
              
            <!-- Kullanıcı Ekle Butonu -->
            <a href="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=add_school_admin" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-user-plus mr-2"></i>
                Yeni Okul Yöneticisi
            </a>
            
            <!-- Toplu Silme Butonu -->
            <button id="bulkDeleteBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash-alt mr-2"></i>
                Seçilenleri Sil
            </button>
        </div>
    </div>
    
    <!-- Kullanıcı Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ADI SOYADI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-POSTA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KURUM KODU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OKUL ADI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">POZİSYON</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DURUM</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İŞLEMLER</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($schoolAdmins && count($schoolAdmins) > 0): ?>
                    <?php foreach ($schoolAdmins as $admin): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="user-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-id="<?php echo $admin['id']; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($admin['id']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($admin['name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($admin['email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($admin['school_code']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($admin['school_name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($admin['position'] ?? 'Okul Müdürü'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $admin['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $admin['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=edit_school_admin&id=<?php echo $admin['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" onclick="showDeleteModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['name']); ?>')" 
                                        class="text-red-600 hover:text-red-900" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            Henüz kayıtlı okul yöneticisi bulunmuyor.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Okul Yöneticisini Sil
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-content">
                                Bu okul yöneticisini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="post">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="delete_user" value="1">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Sil
                    </button>
                    <button type="button" onclick="hideDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        İptal
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Tekli silme işlemi için modal
function showDeleteModal(userId, userName) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('modal-content').innerText = userName + ' isimli okul yöneticisini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Toplu işlem için JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tüm öğeleri seçme/seçimi kaldırma
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    // Tekli silme işlemine AJAX ekle
    document.querySelector('#deleteModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        const adminId = document.getElementById('deleteUserId').value;
        
        // AJAX ile silme işlemi
        csrfFetch('<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=delete_school_admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: adminId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideDeleteModal();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Okul yöneticisi başarıyla silindi.',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message || 'Okul yöneticisi silinirken bir hata oluştu.',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            hideDeleteModal();
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Bir iletişim hatası oluştu.',
                confirmButtonColor: '#3085d6'
            });
        });
    });
    
    // Tümünü seç/kaldır 
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        
        updateBulkDeleteButton();
    });
    
    // Bireysel checkbox'ların durumuna göre butonun durumunu güncelle
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
            
            // Tüm checkbox'lar seçiliyse, "Tümünü Seç" checkbox'ını da seç
            const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        });
    });
    
    // Toplu silme butonunun durumunu güncelleme
    function updateBulkDeleteButton() {
        const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
        
        if (selectedCount > 0) {
            bulkDeleteBtn.disabled = false;
            bulkDeleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
    
    // Toplu silme butonuna tıklandığında
    bulkDeleteBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) return;
        
        // Modal'ı göster
        document.getElementById('bulkDeleteModal').classList.remove('hidden');
        document.getElementById('bulk-modal-content').innerText = 
            `${selectedCheckboxes.length} kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.`;
    });
    
    // Toplu silme onay butonu
    document.getElementById('confirmBulkDelete').addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                                .map(checkbox => checkbox.getAttribute('data-id'));
        
        if (selectedIds.length === 0) return;
        
        // AJAX ile silme işlemi
        csrfFetch('<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=delete_selected_school_admins', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                admin_ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            hideBulkDeleteModal();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Seçilen okul yöneticileri başarıyla silindi.',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message || 'Okul yöneticileri silinirken bir hata oluştu.',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            hideBulkDeleteModal();
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Bir iletişim hatası oluştu.',
                confirmButtonColor: '#3085d6'
            });
        });
    });
    
    // Toplu silme modalını gizle
    function hideBulkDeleteModal() {
        document.getElementById('bulkDeleteModal').classList.add('hidden');
    }
    
    // Modal kapatma butonu
    document.getElementById('bulkDeleteModal').querySelector('button[type="button"]').addEventListener('click', hideBulkDeleteModal);

    // Konsola yönlendirme URL'lerini log edelim
    console.log("School admins page loaded");
    document.querySelectorAll('a[href*="edit_school_admin"]').forEach(link => {
        console.log("Edit link found:", link.href);
        link.addEventListener('click', function(e) {
            console.log("Edit link clicked:", this.href);
        });
    });
});
</script>