<?php
// View verilerini al - $viewData zaten teacher_panel.php'de hazırlandı
$profile = $viewData['profile'] ?? [];
$applications = $viewData['applications'] ?? [];
?>

<div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Hoş Geldiniz, <?= htmlspecialchars($profile['name'] ?? 'Öğretmen') ?></h1>
            <p class="text-gray-600 mt-1">Son aktiviteleriniz ve bekleyen başvurularınız.</p>
        </div>
        
        
    </div>
    
    <!-- İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Toplam Başvuru</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?= is_array($applications) ? count($applications) : 0 ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-teal-50 p-6 rounded-xl border border-green-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Onaylanan</p>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?= is_array($applications) ? count(array_filter($applications, function($a) { return $a['status'] === 'approved'; })) : 0 ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 p-6 rounded-xl border border-yellow-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Bekleyen</p>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?= is_array($applications) ? count(array_filter($applications, function($a) { return $a['status'] === 'pending'; })) : 0 ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Son Başvurular -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Son Başvurularınız</h2>
        </div>
        
        <?php if (!is_array($applications) || empty($applications)): ?>
            <div class="p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 mb-4">Henüz hiç başvuru yapmadınız.</p>
                <a href="?action=apply" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    İlk Başvurunuzu Yapın
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Okul</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        // Son 5 başvuruyu göster
                        $recentApplications = is_array($applications) ? array_slice($applications, 0, 5) : [];
                        foreach ($recentApplications as $application): 
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if (!empty($application['school_logo'])): ?>
                                            <img class="h-10 w-10 rounded-full mr-3" src="<?= htmlspecialchars($application['school_logo']) ?>" alt="<?= htmlspecialchars($application['school_name']) ?>">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <span class="text-gray-500 font-semibold"><?= substr($application['school_name'], 0, 1) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($application['school_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($application['school_location']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= date('d.m.Y', strtotime($application['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'interview' => 'bg-blue-100 text-blue-800'
                                    ];
                                    
                                    $statusLabels = [
                                        'pending' => 'Beklemede',
                                        'approved' => 'Onaylandı',
                                        'rejected' => 'Reddedildi',
                                        'interview' => 'Görüşme'
                                    ];
                                    
                                    $statusClass = $statusClasses[$application['status']] ?? 'bg-gray-100 text-gray-800';
                                    $statusLabel = $statusLabels[$application['status']] ?? 'Bilinmiyor';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="?action=application_detail&id=<?= $application['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Detay</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (is_array($applications) && count($applications) > 5): ?>
                <div class="px-6 py-3 bg-gray-50 text-right">
                    <a href="?action=applications" class="text-sm text-indigo-600 hover:text-indigo-900">Tüm Başvurularınızı Görüntüleyin &rarr;</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Profil Tamamlama -->
    <?php
    // Profil tamamlama yüzdesi hesaplaması
    $profileFields = [
        'name', 'email', 'phone', 'about', 'job_title', 'profile_photo'
    ];
    
    $completedFields = 0;
    foreach ($profileFields as $field) {
        if (!empty($profile[$field])) {
            $completedFields++;
        }
    }
    
    $completionPercentage = round(($completedFields / count($profileFields)) * 100);
    ?>
    
    <div class="mt-8 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Profil Tamamlama</h2>
        </div>
        
        <div class="p-6">
            <div class="flex justify-between items-center mb-2">
                <div class="text-sm font-medium text-gray-700">Tamamlanan: <?= $completionPercentage ?>%</div>
                <div class="text-sm text-gray-500"><?= $completedFields ?>/<?= count($profileFields) ?></div>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: <?= $completionPercentage ?>%"></div>
            </div>
            
            <?php if ($completionPercentage < 100): ?>
                <div class="mt-4">
                    <a href="?action=profile" class="text-sm text-indigo-600 hover:text-indigo-900">Profilinizi tamamlayın &rarr;</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 