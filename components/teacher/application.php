<?php
// Öğretmenin başvuru bilgilerini veritabanından çek
try {
    $stmt = $db->prepare("SELECT a.*, s.school_name, p.position_name 
                         FROM applications a 
                         LEFT JOIN schools s ON a.school_id = s.id 
                         LEFT JOIN positions p ON a.position_id = p.id 
                         WHERE a.user_id = :user_id 
                         ORDER BY a.created_at DESC");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Applications fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Başvuru bilgileri yüklenirken bir hata oluştu.";
}

// Başvuru durumları
$applicationStatus = [
    'pending' => 'Beklemede',
    'approved' => 'Onaylandı',
    'rejected' => 'Reddedildi',
    'interview' => 'Görüşme'
];

// Okulları getir
try {
    $schoolsStmt = $db->prepare("SELECT id, school_name FROM schools WHERE status = 'active' ORDER BY school_name");
    $schoolsStmt->execute();
    $schools = $schoolsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Schools fetch error: " . $e->getMessage());
    $schools = [];
}

// Pozisyonları getir
try {
    $positionsStmt = $db->prepare("SELECT id, position_name FROM positions WHERE status = 'active' ORDER BY position_name");
    $positionsStmt->execute();
    $positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Positions fetch error: " . $e->getMessage());
    $positions = [];
}
?>
<div class="px-4 sm:px-0">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Başvurularım Kartı -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Başvurularım</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Yaptığınız iş başvuruları ve durumları.</p>
            </div>
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-plus mr-2"></i>
                Yeni Başvuru
            </button>
        </div>

        <!-- Başvuru Listesi -->
        <div class="border-t border-gray-200">
            <?php if (empty($applications)): ?>
                <div class="px-4 py-5 text-center text-gray-500">
                    <i class="fas fa-clipboard-list text-4xl mb-3"></i>
                    <p>Henüz başvuru yapmamışsınız.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Okul</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pozisyon</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başvuru Tarihi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Güncelleme Tarihi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($applications as $application): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($application['school_name'] ?? 'Belirtilmemiş'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($application['position_name'] ?? 'Belirtilmemiş'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d.m.Y', strtotime($application['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusClass = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'interview' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $currentStatus = $application['status'] ?? 'pending';
                                        $statusText = $applicationStatus[$currentStatus] ?? 'Beklemede';
                                        $statusClassValue = $statusClass[$currentStatus] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClassValue; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d.m.Y', strtotime($application['updated_at'] ?? $application['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewApplication(<?php echo $application['id']; ?>)" class="text-indigo-600 hover:text-indigo-900" title="Görüntüle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($currentStatus === 'pending'): ?>
                                            <button onclick="editApplication(<?php echo $application['id']; ?>)" class="text-primary-600 hover:text-primary-800" title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteApplication(<?php echo $application['id']; ?>)" class="text-red-600 hover:text-red-800" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Başvuru Ekleme/Düzenleme Modal -->
<div id="applicationModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <form id="applicationForm" action="<?php echo BASE_URL; ?>/php/save_application.php" method="POST" class="p-6">
                <input type="hidden" name="application_id" id="application_id">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Yeni Başvuru</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-gray-700">Okul *</label>
                        <select name="school_id" id="school_id" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Okul Seçiniz</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['school_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="position_id" class="block text-sm font-medium text-gray-700">Pozisyon *</label>
                        <select name="position_id" id="position_id" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Pozisyon Seçiniz</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo $position['id']; ?>"><?php echo htmlspecialchars($position['position_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="cover_letter" class="block text-sm font-medium text-gray-700">Ön Yazı</label>
                        <textarea name="cover_letter" id="cover_letter" rows="4"
                                  placeholder="Okula iletmek istediğiniz mesajı yazınız..."
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        İptal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Başvur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Başvuru Detay Modal -->
<div id="viewApplicationModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Başvuru Detayı</h3>
                    <button type="button" onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-6" id="applicationDetails">
                    <div class="skeleton h-6 w-3/4 bg-gray-200 rounded"></div>
                    <div class="skeleton h-6 w-1/2 bg-gray-200 rounded"></div>
                    <div class="skeleton h-6 w-5/6 bg-gray-200 rounded"></div>
                    <div class="skeleton h-6 w-4/5 bg-gray-200 rounded"></div>
                    <div class="skeleton h-20 w-full bg-gray-200 rounded"></div>
                </div>

                <div class="mt-8 pt-5 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" onclick="closeViewModal()"
                                class="px-4 py-2 bg-primary-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Kapat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Modal işlemleri
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Yeni Başvuru';
    document.getElementById('applicationForm').reset();
    document.getElementById('application_id').value = '';
    document.getElementById('applicationModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('applicationModal').classList.add('hidden');
}

function closeViewModal() {
    document.getElementById('viewApplicationModal').classList.add('hidden');
}

// Başvuru düzenleme modalı
function editApplication(id) {
    // AJAX ile başvuru bilgisini çek
    fetch(`${BASE_URL}/php/get_application.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const application = data.data;
                document.getElementById('application_id').value = application.id;
                document.getElementById('school_id').value = application.school_id;
                document.getElementById('position_id').value = application.position_id;
                document.getElementById('cover_letter').value = application.cover_letter || '';
                
                document.getElementById('modalTitle').textContent = 'Başvuru Düzenle';
                document.getElementById('applicationModal').classList.remove('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message || 'Başvuru bilgisi yüklenirken bir hata oluştu.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Başvuru bilgisi yüklenirken bir hata oluştu.'
            });
        });
}

// Başvuru detayını görüntüle
function viewApplication(id) {
    document.getElementById('viewApplicationModal').classList.remove('hidden');
    document.getElementById('applicationDetails').innerHTML = `
        <div class="skeleton h-6 w-3/4 bg-gray-200 rounded"></div>
        <div class="skeleton h-6 w-1/2 bg-gray-200 rounded"></div>
        <div class="skeleton h-6 w-5/6 bg-gray-200 rounded"></div>
        <div class="skeleton h-6 w-4/5 bg-gray-200 rounded"></div>
        <div class="skeleton h-20 w-full bg-gray-200 rounded"></div>
    `;
    
    // AJAX ile başvuru bilgisini çek
    fetch(`${BASE_URL}/php/get_application.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const application = data.data;
                
                // Durum bilgisi için sınıf ve metin belirle
                const statusClass = {
                    'pending': 'bg-yellow-100 text-yellow-800',
                    'approved': 'bg-green-100 text-green-800',
                    'rejected': 'bg-red-100 text-red-800',
                    'interview': 'bg-blue-100 text-blue-800'
                };
                
                const statusText = {
                    'pending': 'Beklemede',
                    'approved': 'Onaylandı',
                    'rejected': 'Reddedildi',
                    'interview': 'Görüşme'
                };
                
                const currentStatus = application.status || 'pending';
                const statusTextValue = statusText[currentStatus] || 'Beklemede';
                const statusClassValue = statusClass[currentStatus] || 'bg-gray-100 text-gray-800';
                
                document.getElementById('applicationDetails').innerHTML = `
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Okul</h4>
                        <p class="mt-1 text-sm text-gray-900">${application.school_name || 'Belirtilmemiş'}</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Pozisyon</h4>
                        <p class="mt-1 text-sm text-gray-900">${application.position_name || 'Belirtilmemiş'}</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Durum</h4>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClassValue}">
                                ${statusTextValue}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Başvuru Tarihi</h4>
                        <p class="mt-1 text-sm text-gray-900">${new Date(application.created_at).toLocaleDateString('tr-TR')}</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Son Güncelleme</h4>
                        <p class="mt-1 text-sm text-gray-900">${new Date(application.updated_at || application.created_at).toLocaleDateString('tr-TR')}</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Ön Yazı</h4>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">${application.cover_letter || 'Ön yazı belirtilmemiş.'}</p>
                    </div>
                    
                    ${application.notes ? `
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Okul Notları</h4>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">${application.notes}</p>
                    </div>
                    ` : ''}
                `;
            } else {
                document.getElementById('applicationDetails').innerHTML = `
                    <div class="text-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p>${data.message || 'Başvuru bilgisi yüklenirken bir hata oluştu.'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('applicationDetails').innerHTML = `
                <div class="text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                    <p>Başvuru bilgisi yüklenirken bir hata oluştu.</p>
                </div>
            `;
        });
}

// Başvuru silme fonksiyonu
function deleteApplication(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu başvuru kaydı kalıcı olarak silinecektir!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX ile başvuru bilgisini sil
            fetch(`${BASE_URL}/php/delete_application.php?id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: data.message || 'Başvuru silinirken bir hata oluştu.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Başvuru silinirken bir hata oluştu.'
                });
            });
        }
    });
}

// Form doğrulama
document.getElementById('applicationForm').addEventListener('submit', function(e) {
    const schoolId = document.getElementById('school_id').value;
    const positionId = document.getElementById('position_id').value;
    
    if (!schoolId || !positionId) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen okul ve pozisyon seçiniz.',
        });
        return;
    }
});
</script> 