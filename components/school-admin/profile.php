<?php
// Profil bilgilerini getir
$profile = $viewModel->getSettings();
$schoolInfo = $viewModel->getSchoolInfo();
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sol Kısım - Profil Özeti -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col items-center">
                <div class="w-32 h-32 relative">
                    <?php if ($profile['profile_image']): ?>
                    <img class="w-32 h-32 rounded-full" src="<?php echo BASE_URL . '/' . $profile['profile_image']; ?>" alt="Profile">
                    <?php else: ?>
                    <div class="w-32 h-32 rounded-full bg-primary-100 flex items-center justify-center">
                        <i class="fas fa-user-tie text-4xl text-primary-500"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <h2 class="mt-4 text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
                <p class="text-sm text-gray-500">Okul Yöneticisi</p>
                
                <div class="mt-6 w-full">
                    <div class="flex items-center justify-between py-2 border-b border-gray-200">
                        <span class="text-sm text-gray-500">E-posta</span>
                        <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-gray-200">
                        <span class="text-sm text-gray-500">Telefon</span>
                        <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($profile['phone']); ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-500">Katılma Tarihi</span>
                        <span class="text-sm font-medium text-gray-900"><?php echo date('d.m.Y', strtotime($profile['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sağ Kısım - Okul Bilgileri ve İstatistikler -->
    <div class="lg:col-span-2">
        <!-- Okul Bilgileri -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Okul Bilgileri</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Okul Adı</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($schoolInfo['name']); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500">Kuruluş Yılı</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($schoolInfo['founded_year']); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500">Adres</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($schoolInfo['address']); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500">İletişim</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($schoolInfo['phone']); ?></p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500">Hakkında</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($schoolInfo['description'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- İstatistikler -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">İstatistikler</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-blue-100">
                            <i class="fas fa-file-alt text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-900">Toplam Başvuru</p>
                            <p class="text-lg font-semibold text-blue-700"><?php echo $schoolInfo['stats']['total_applications']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-green-100">
                            <i class="fas fa-user-check text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-900">Aktif Öğretmen</p>
                            <p class="text-lg font-semibold text-green-700"><?php echo $schoolInfo['stats']['active_teachers']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-purple-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-purple-100">
                            <i class="fas fa-book text-purple-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-900">Branş Sayısı</p>
                            <p class="text-lg font-semibold text-purple-700"><?php echo $schoolInfo['stats']['branch_count']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Son Aktiviteler -->
            <div class="mt-6">
                <h4 class="text-base font-medium text-gray-900 mb-3">Son Aktiviteler</h4>
                <div class="space-y-4">
                    <?php foreach ($schoolInfo['recent_activities'] as $activity): ?>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                <i class="<?php echo $activity['icon']; ?> text-gray-500"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-900"><?php echo htmlspecialchars($activity['description']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $activity['date']; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>