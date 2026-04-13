<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Güvenlik kontrolü - Oturum kontrolü
if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
    echo '<strong class="font-bold">Hata!</strong>';
    echo '<span class="block sm:inline"> Bu sayfaya erişim yetkiniz bulunmamaktadır.</span>';
    echo '</div>';
    return;
}

// View verilerini al
$profile = $viewData['profile'] ?? [];
$experiences = $viewData['experiences'] ?? [];
$education = $viewData['education'] ?? [];
$skills = $viewData['skills'] ?? [];

// DEBUG: $profile içeriğini ekrana yazdır (production'da false yapılmalı)
if (false) { // Test sonrası kaldırabilirsiniz
    echo '<div style="background:#fffbe6;border:2px solid #ffe58f;padding:8px 16px;margin:8px 0;font-family:monospace;font-size:13px;">';
    echo '<b>$profile DEBUG:</b><br><pre>';
    print_r($profile);
    echo '</pre></div>';
}
?>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <!-- Profil Bilgileri -->
    <div class="px-6 py-4">
        <div class="flex justify-end mb-4">
            <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors" data-modal-target="editProfileModal">
                Profili Düzenle
            </button>
        </div>
        
        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Hakkımda</h2>
            <p class="text-gray-600">
                <?= nl2br(htmlspecialchars($profile['about'] ?? 'Henüz hakkında bilgisi girilmemiş.')) ?>
            </p>
        </div>
        
        <!-- İletişim Bilgileri -->
        <div class="border-t border-gray-200 mt-6 pt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">İletişim Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-gray-600">
                        <?= htmlspecialchars(($profile['city'] ?? 'İl belirtilmemiş') . ' / ' . ($profile['county'] ?? 'İlçe belirtilmemiş')) ?>
                        <?php if (!empty($profile['address'])): ?> - <?= htmlspecialchars($profile['address']) ?><?php endif; ?>
                    </span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span class="text-gray-600"><?= htmlspecialchars($profile['phone'] ?? 'Telefon belirtilmemiş') ?></span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-gray-600"><?= htmlspecialchars($profile['email'] ?? 'E-posta belirtilmemiş') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Deneyim -->
        <div class="border-t border-gray-200 mt-6 pt-6">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-gray-800">Deneyim</h2>
                <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" data-modal-target="addExperienceModal">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Deneyim Ekle
                    </span>
                </button>
            </div>
            
            <?php if (empty($experiences)): ?>
                <div class="py-4 text-center border border-dashed border-gray-300 rounded-lg">
                    <p class="text-gray-500">Henüz deneyim bilgisi eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($experiences as $experience): ?>
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($experience['position']) ?></h3>
                                    <p class="text-gray-600"><?= htmlspecialchars($experience['company']) ?></p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= date('M Y', strtotime($experience['start_date'])) ?> - 
                                    <?= !empty($experience['end_date']) ? date('M Y', strtotime($experience['end_date'])) : 'Halen Devam Ediyor' ?>
                                </div>
                            </div>
                            <?php if (!empty($experience['description'])): ?>
                                <p class="mt-2 text-gray-600 text-sm"><?= nl2br(htmlspecialchars($experience['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Eğitim -->
        <div class="border-t border-gray-200 mt-6 pt-6">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-gray-800">Eğitim</h2>
                <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" data-modal-target="addEducationModal">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Eğitim Ekle
                    </span>
                </button>
            </div>
            
            <?php if (empty($education)): ?>
                <div class="py-4 text-center border border-dashed border-gray-300 rounded-lg">
                    <p class="text-gray-500">Henüz eğitim bilgisi eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($education as $edu): ?>
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($edu['degree']) ?> <?= !empty($edu['field_of_study']) ? '- ' . htmlspecialchars($edu['field_of_study']) : '' ?></h3>
                                    <p class="text-gray-600"><?= htmlspecialchars($edu['institution']) ?></p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= date('Y', strtotime($edu['start_date'])) ?> - 
                                    <?= !empty($edu['end_date']) ? date('Y', strtotime($edu['end_date'])) : 'Devam Ediyor' ?>
                                </div>
                            </div>
                            <?php if (!empty($edu['description'])): ?>
                                <p class="mt-2 text-gray-600 text-sm"><?= nl2br(htmlspecialchars($edu['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Beceriler -->
        <div class="border-t border-gray-200 mt-6 pt-6">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-gray-800">Beceriler</h2>
                <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800" data-modal-target="editSkillsModal">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Becerileri Düzenle
                    </span>
                </button>
            </div>
            
            <?php if (empty($skills)): ?>
                <div class="py-4 text-center border border-dashed border-gray-300 rounded-lg">
                    <p class="text-gray-500">Henüz beceri eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php 
                    $categoryLabels = [
                        'language' => 'Dil Becerileri',
                        'technical' => 'Teknik Beceriler',
                        'social' => 'Sosyal Beceriler',
                        'other' => 'Diğer'
                    ];
                    
                    $skillsByCategory = [];
                    foreach ($skills as $skill) {
                        $category = $skill['category'] ?? 'other';
                        if (!isset($skillsByCategory[$category])) {
                            $skillsByCategory[$category] = [];
                        }
                        $skillsByCategory[$category][] = $skill;
                    }
                    
                    foreach ($skillsByCategory as $category => $categorySkills): 
                    ?>
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <h3 class="font-medium text-gray-800 mb-3"><?= $categoryLabels[$category] ?? 'Diğer Beceriler' ?></h3>
                            <div class="space-y-2">
                                <?php foreach ($categorySkills as $skill): ?>
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm text-gray-600"><?= htmlspecialchars($skill['name']) ?></span>
                                            <span class="text-xs text-gray-500">
                                                <?= str_repeat('★', $skill['level']) . str_repeat('☆', 5 - $skill['level']) ?>
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: <?= ($skill['level'] / 5) * 100 ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Profil Düzenleme Modal -->
<div id="editProfileModal" class="modal hidden fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-xl shadow-xl max-w-2xl w-full mx-auto">
        <div class="modal-header flex justify-between items-center border-b p-4">
            <h5 class="text-lg font-bold">Profili Düzenle</h5>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6">
            <form id="editProfileForm" method="post" action="">
            <div class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Adı</label>
                            <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['name'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Soyadı</label>
                            <input type="text" id="surname" name="surname" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['surname'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Cinsiyet</label>
                            <div class="mt-2 flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="male" class="form-radio h-4 w-4 text-indigo-600" <?= (($profile['gender'] ?? 'male') === 'male') ? 'checked' : '' ?>>
                                    <span class="ml-2 text-sm text-gray-700">Erkek</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="female" class="form-radio h-4 w-4 text-indigo-600" <?= (($profile['gender'] ?? '') === 'female') ? 'checked' : '' ?>>
                                    <span class="ml-2 text-sm text-gray-700">Kadın</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                            <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                            <input type="text" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">İl</label>
                            <input type="text" id="city" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['city'] ?? '') ?>">
                        </div>
                        <div>
                            <label for="county" class="block text-sm font-medium text-gray-700 mb-2">İlçe</label>
                            <input type="text" id="county" name="county" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['county'] ?? '') ?>">
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                        <input type="text" id="address" name="address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
                    </div>
                    
                    <div>
                        <label for="about" class="block text-sm font-medium text-gray-700 mb-2">Hakkımda</label>
                        <textarea id="about" name="about" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($profile['about'] ?? '') ?></textarea>
                    </div>

                    <input type="hidden" name="action" value="update_profile">
                </div>
            </form>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" form="editProfileForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Kaydet
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm modal-close">
                    İptal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal açma
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal-target');
            document.getElementById(modalId).classList.remove('hidden');
        });
    });
    
    // Modal kapatma
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.add('hidden');
        });
    });
    
    // Modal dışına tıklayarak kapatma
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    });
});
</script> 