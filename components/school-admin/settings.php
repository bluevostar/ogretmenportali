<?php
// Ayarları getir
$settings = $viewModel->getSettings();
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profil Bilgileri -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Profil Bilgileri</h3>
            </div>
            <div class="p-6">
                <form action="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=update_profile" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_input(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>"
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>"
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="profile_image" class="block text-sm font-medium text-gray-700">Profil Fotoğrafı</label>
                            <div class="mt-1 flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <?php if (!empty($settings['profile_image'])): ?>
                                    <img class="h-16 w-16 rounded-full" src="<?php echo BASE_URL . '/' . $settings['profile_image']; ?>" alt="">
                                    <?php else: ?>
                                    <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400 text-2xl"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*"
                                       class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-save mr-2"></i>
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Şifre Değiştirme -->
        <div class="bg-white rounded-lg shadow-sm mt-6">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Şifre Değiştir</h3>
            </div>
            <div class="p-6">
                <form action="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=change_password" method="POST">
                    <?php echo csrf_input(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Mevcut Şifre</label>
                            <input type="password" id="current_password" name="current_password" required
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                            <input type="password" id="new_password" name="new_password" required
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Yeni Şifre (Tekrar)</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-key mr-2"></i>
                            Şifreyi Değiştir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bildirim Ayarları -->
    <div>
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Bildirim Ayarları</h3>
            </div>
            <div class="p-6">
                <form action="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=update_notifications" method="POST">
                    <?php echo csrf_input(); ?>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" id="email_notifications" name="email_notifications" value="1"
                                       <?php echo !empty($settings['email_notifications']) ? 'checked' : ''; ?>
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="email_notifications" class="font-medium text-gray-700">E-posta Bildirimleri</label>
                                <p class="text-gray-500 text-sm">Yeni başvurular ve güncellemeler için e-posta bildirimleri al</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" id="application_notifications" name="application_notifications" value="1"
                                       <?php echo !empty($settings['application_notifications']) ? 'checked' : ''; ?>
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="application_notifications" class="font-medium text-gray-700">Başvuru Bildirimleri</label>
                                <p class="text-gray-500 text-sm">Yeni başvurular olduğunda bildirim al</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" id="system_notifications" name="system_notifications" value="1"
                                       <?php echo !empty($settings['system_notifications']) ? 'checked' : ''; ?>
                                       class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="system_notifications" class="font-medium text-gray-700">Sistem Bildirimleri</label>
                                <p class="text-gray-500 text-sm">Sistem güncellemeleri ve duyurular için bildirim al</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-bell mr-2"></i>
                            Bildirimleri Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>