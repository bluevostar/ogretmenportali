<?php
// Başvuru detayı görüntüleniyor mu kontrol et
$viewApplication = isset($_GET['view']) ? (int)$_GET['view'] : null;

if ($viewApplication) {
    // Başvuru detayını göster
    $application = $viewModel->getApplication($viewApplication);
    if (!$application) {
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">Başvuru bulunamadı.</div>';
        return;
    }
    ?>
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Başvuru Detayı</h2>
            <a href="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=applications" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-primary-600 hover:bg-primary-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Geri Dön
            </a>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Öğretmen Bilgileri -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Öğretmen Bilgileri</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-20 w-20">
                                <?php if ($application['profile_image']): ?>
                                <img class="h-20 w-20 rounded-full" src="<?php echo BASE_URL . '/' . $application['profile_image']; ?>" alt="">
                                <?php else: ?>
                                <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400 text-3xl"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($application['teacher_name']); ?></h4>
                                <p class="text-gray-500"><?php echo htmlspecialchars($application['branch']); ?></p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">E-posta</label>
                                <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($application['email']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Telefon</label>
                                <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($application['phone']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Deneyim</label>
                                <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($application['experience']); ?> Yıl</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Eğitim</label>
                                <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($application['education']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Başvuru Bilgileri -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Başvuru Bilgileri</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Başvuru Tarihi</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo date('d.m.Y H:i', strtotime($application['application_date'])); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Durum</label>
                            <?php
                            $statusClass = '';
                            $statusText = '';
                            switch ($application['status']) {
                                case 'pending':
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Bekliyor';
                                    break;
                                case 'approved':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    $statusText = 'Onaylandı';
                                    break;
                                case 'rejected':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusText = 'Reddedildi';
                                    break;
                            }
                            ?>
                            <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </div>
                        <?php if ($application['status'] === 'rejected' && !empty($application['rejection_reason'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Red Nedeni</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($application['rejection_reason']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">CV</label>
                            <?php if (!empty($application['cv_file'])): ?>
                            <a href="<?php echo BASE_URL . '/' . $application['cv_file']; ?>" target="_blank" 
                               class="mt-1 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>
                                CV'yi İndir
                            </a>
                            <?php else: ?>
                            <p class="mt-1 text-sm text-gray-500">CV yüklenmemiş</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($application['status'] === 'pending'): ?>
            <!-- İşlem Butonları -->
            <div class="mt-6 border-t border-gray-200 pt-6 flex justify-end space-x-3">
                <button type="button" onclick="rejectApplication(<?php echo $application['id']; ?>)" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-times mr-2"></i>
                    Reddet
                </button>
                <button type="button" onclick="approveApplication(<?php echo $application['id']; ?>)" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-primary-600 hover:bg-primary-700">
                    <i class="fas fa-check mr-2"></i>
                    Onayla
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Red Nedeni Modal -->
    <div id="rejectionModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Red Nedeni</h3>
                    <textarea id="rejectionReason" rows="4" 
                            class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="Başvuruyu reddetme nedeninizi yazın..."></textarea>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="confirmReject()" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reddet
                    </button>
                    <button type="button" onclick="closeRejectionModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        İptal
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    let selectedApplicationId = null;
    
    function approveApplication(id) {
        if (!confirm('Bu başvuruyu onaylamak istediğinizden emin misiniz?')) return;
        
        csrfFetch('<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=approve_application', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                application_id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Başvuru onaylanırken bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu. Lütfen tekrar deneyin.');
        });
    }
    
    function rejectApplication(id) {
        selectedApplicationId = id;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }
    
    function closeRejectionModal() {
        document.getElementById('rejectionModal').classList.add('hidden');
        document.getElementById('rejectionReason').value = '';
        selectedApplicationId = null;
    }
    
    function confirmReject() {
        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Lütfen red nedenini yazın.');
            return;
        }
        
        csrfFetch('<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=reject_application', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                application_id: selectedApplicationId,
                rejection_reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Başvuru reddedilirken bir hata oluştu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu. Lütfen tekrar deneyin.');
        });
    }
    </script>
    <?php
    return;
}

// Filtre parametrelerini al
$status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$branch = isset($_GET['branch']) ? clean_input($_GET['branch']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Başvuruları getir
$applications = $viewModel->getApplications($status, $branch, $search);
$branches = $viewModel->getBranches();
?>

<!-- Filtreler -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="applications">
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Durum</label>
                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Tümü</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Bekleyen</option>
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Onaylanan</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Reddedilen</option>
                </select>
            </div>
            
            <div>
                <label for="branch" class="block text-sm font-medium text-gray-700">Branş</label>
                <select id="branch" name="branch" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Tümü</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo htmlspecialchars($b['id']); ?>" <?php echo $branch == $b['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($b['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Arama</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                       placeholder="İsim veya e-posta ara...">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-primary-600 hover:bg-primary-700">
                    <i class="fas fa-search mr-2"></i>
                    Filtrele
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Başvuru Listesi -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900">Başvurular</h3>
    </div>
    
    <?php if (empty($applications)): ?>
    <div class="p-6 text-center text-gray-500">
        <i class="fas fa-inbox text-4xl mb-4"></i>
        <p>Henüz başvuru bulunmuyor.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Öğretmen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branş</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başvuru Tarihi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($applications as $application): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <?php if ($application['profile_image']): ?>
                                <img class="h-10 w-10 rounded-full" src="<?php echo BASE_URL . '/' . $application['profile_image']; ?>" alt="">
                                <?php else: ?>
                                <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($application['teacher_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($application['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($application['branch']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d.m.Y', strtotime($application['application_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        switch ($application['status']) {
                            case 'pending':
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'Bekliyor';
                                break;
                            case 'approved':
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'Onaylandı';
                                break;
                            case 'rejected':
                                $statusClass = 'bg-red-100 text-red-800';
                                $statusText = 'Reddedildi';
                                break;
                        }
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=applications&view=<?php echo $application['id']; ?>" 
                           class="text-primary-600 hover:text-primary-900">Detay</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>