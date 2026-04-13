<?php
// Öğretmenleri getir - Sadece öğretmen rolüne sahip kullanıcıları filtrele
$teachers = $viewModel->filterUsers('teacher');
?>

<div class="content-area">
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title mb-4 md:mb-0">Öğretmen Yönetimi</h1>

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-3">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="teachers">
                <div class="flex w-full">
                    <input type="text" name="search" placeholder="Öğretmen ara..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
              
            <!-- Kullanıcı Ekle Butonu -->
            <a href="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=add_user" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-user-plus mr-2"></i>
                Yeni Öğretmen Ekle
            </a>
        </div>
    </div>
    
    <!-- Öğretmen Listesi -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ADI SOYADI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-POSTA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BRANŞ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DENEYİM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OKUL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DURUM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KAYIT TARİHİ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İŞLEMLER</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($teachers && count($teachers) > 0): ?>
                    <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($teacher['id']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($teacher['email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($teacher['branch_name'] ?? 'Belirtilmemiş'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($teacher['experience'] ?? 'Belirtilmemiş'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($teacher['school_name'] ?? 'Atanmamış'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $teacher['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $teacher['status'] == 'active' ? 'Aktif' : 'Pasif'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('d.m.Y H:i', strtotime($teacher['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-center space-x-3">
                                <button onclick="showUserModal(<?php echo $teacher['id']; ?>, '<?php echo htmlspecialchars($teacher['name']); ?>', '<?php echo htmlspecialchars($teacher['email']); ?>', '<?php echo htmlspecialchars($teacher['phone'] ?? 'Belirtilmemiş'); ?>', '<?php echo htmlspecialchars($teacher['birth_date'] ? date('d.m.Y', strtotime($teacher['birth_date'])) : 'Belirtilmemiş'); ?>', '<?php echo $teacher['status'] == 'active' ? 'Aktif' : 'Pasif'; ?>', '<?php echo date('d.m.Y H:i', strtotime($teacher['created_at'])); ?>')" class="text-primary-600 hover:text-primary-900 flex items-center" title="Önizle">
                                    <i class="fas fa-eye mr-1"></i> Önizle
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                            Henüz kayıtlı öğretmen bulunmuyor.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Kullanıcı Önizleme Modalı -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-xl font-medium text-gray-900">Öğretmen Bilgileri</h3>
            <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">ID</p>
                    <p id="modal-user-id" class="text-base text-gray-900"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">ADI SOYADI</p>
                    <p id="modal-user-name" class="text-base text-gray-900"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">E-POSTA</p>
                    <p id="modal-user-email" class="text-base text-gray-900"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">TELEFON</p>
                    <p id="modal-user-phone" class="text-base text-gray-900"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">DOĞUM TARİHİ</p>
                    <p id="modal-user-birth-date" class="text-base text-gray-900"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">ROL</p>
                    <p class="text-base text-gray-900">Öğretmen</p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">DURUM</p>
                    <p id="modal-user-status" class="text-base text-gray-900"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">KAYIT TARİHİ</p>
                    <p id="modal-user-created-at" class="text-base text-gray-900"></p>
                </div>
            </div>
            
            <div class="border-t pt-4 mt-4">
                <h4 class="text-lg font-medium text-gray-900 mb-3">Eğitim ve Deneyim Bilgileri</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-500">BRANŞ</p>
                        <p id="modal-user-branch" class="text-base text-gray-900">-</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-500">TECRÜBE</p>
                        <p id="modal-user-experience" class="text-base text-gray-900">-</p>
                    </div>
                    <div class="space-y-2 col-span-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500">EĞİTİM BİLGİLERİ</p>
                        <p id="modal-user-education" class="text-base text-gray-900">-</p>
                    </div>
                    <div class="space-y-2 col-span-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500">SERTİFİKALAR</p>
                        <p id="modal-user-certificates" class="text-base text-gray-900">-</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t pt-4 mt-4">
                <h4 class="text-lg font-medium text-gray-900 mb-3">İletişim ve Konum Bilgileri</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-500">ADRES</p>
                        <p id="modal-user-address" class="text-base text-gray-900">-</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-500">ŞEHİR</p>
                        <p id="modal-user-city" class="text-base text-gray-900">-</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-500">ÜLKE</p>
                        <p id="modal-user-country" class="text-base text-gray-900">-</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeUserModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                Kapat
            </button>
        </div>
    </div>
</div>

<script>
// Modal işlemleri için JavaScript kodları
function showUserModal(id, name, email, phone, birthDate, status, createdAt) {
    document.getElementById('modal-user-id').textContent = id;
    document.getElementById('modal-user-name').textContent = name;
    document.getElementById('modal-user-email').textContent = email;
    document.getElementById('modal-user-phone').textContent = phone;
    document.getElementById('modal-user-birth-date').textContent = birthDate;
    document.getElementById('modal-user-status').textContent = status;
    document.getElementById('modal-user-created-at').textContent = createdAt;
    
    // Öğretmen detaylarını AJAX ile getir
    csrfFetch('<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=get_user_details&id=' + id)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const user = data.user;
                
                // Eğitim ve Deneyim Bilgileri
                document.getElementById('modal-user-branch').textContent = user.branch_name || '-';
                document.getElementById('modal-user-experience').textContent = user.experience ? user.experience + ' yıl' : '-';
                document.getElementById('modal-user-education').textContent = user.education || '-';
                document.getElementById('modal-user-certificates').textContent = user.certifications || '-';
                
                // İletişim ve Konum Bilgileri
                document.getElementById('modal-user-address').textContent = user.address || '-';
                document.getElementById('modal-user-city').textContent = user.city || '-';
                document.getElementById('modal-user-country').textContent = user.country || '-';
            }
        })
        .catch(error => {
            console.error('Kullanıcı detayları alınırken hata oluştu:', error);
        });
    
    document.getElementById('userModal').classList.remove('hidden');
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}

// Modal dışına tıklandığında kapat
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUserModal();
    }
});
</script>