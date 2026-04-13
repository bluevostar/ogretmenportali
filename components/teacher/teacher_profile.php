<?php
// Öğretmen bilgilerini viewmodel üzerinden çek
try {
    // Session bilgilerini kontrol et ve log dosyasına yaz
    error_log("SESSION içeriği: " . print_r($_SESSION, true));
    error_log("Session teacher_id: " . (isset($_SESSION['teacher_id']) ? $_SESSION['teacher_id'] : 'yok'));
    
    $teacher = $teacherViewModel->getTeacherDetailedProfile($_SESSION['teacher_id']);
    
    // Öğretmen bilgilerini log dosyasına yaz
    error_log("Öğretmen profil verisi: " . print_r($teacher, true));
    
    if (empty($teacher)) {
        error_log("Teacher profile not found for teacher ID: " . $_SESSION['teacher_id']);
        $_SESSION['error'] = "Profil bilgileri bulunamadı.";
    }
} catch(Exception $e) {
    error_log("Teacher profile error: " . $e->getMessage());
    $_SESSION['error'] = "Profil bilgileri yüklenirken bir hata oluştu.";
}
?>

<!-- Başarı Mesajı -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Hata Mesajı -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="px-4 sm:px-0">
    <!-- Profil Kartı -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Profil Bilgileri</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Kişisel ve iletişim bilgileriniz.</p>
            </div>
            <button onclick="openEditModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-edit mr-2"></i>
                Düzenle
            </button>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <!-- Kişisel Bilgiler Başlığı -->
                <div class="bg-indigo-50 px-4 py-3 sm:px-6">
                    <h4 class="text-sm font-semibold text-indigo-800 uppercase tracking-wide flex items-center">
                        <i class="fas fa-user-circle mr-2 text-indigo-600"></i>
                        Kişisel Bilgiler
                    </h4>
                </div>
                
                <!-- Kişisel Bilgiler -->
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Ad</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['name']) ? htmlspecialchars($teacher['name']) : 'Bilgi yok'; ?></dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Soyad</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['surname']) ? htmlspecialchars($teacher['surname']) : 'Bilgi yok'; ?></dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">TC Kimlik No</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['tc_kimlik_no']) ? htmlspecialchars($teacher['tc_kimlik_no']) : 'Bilgi yok'; ?></dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Doğum Tarihi</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['birth_date']) ? date('d.m.Y', strtotime($teacher['birth_date'])) : 'Bilgi yok'; ?></dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Cinsiyet</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <?php 
                            if (isset($teacher['gender'])) {
                                echo $teacher['gender'] == 'male' ? 'Erkek' : 'Kadın';
                            } else {
                                echo 'Bilgi yok';
                            }
                        ?>
                    </dd>
                </div>
                
                <!-- İletişim Bilgileri Başlığı -->
                <div class="bg-indigo-50 px-4 py-3 sm:px-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-indigo-800 uppercase tracking-wide flex items-center">
                        <i class="fas fa-address-card mr-2 text-indigo-600"></i>
                        İletişim Bilgileri
                    </h4>
                </div>
                
                <!-- İletişim Bilgileri -->
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['email']) ? htmlspecialchars($teacher['email']) : 'Bilgi yok'; ?></dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['phone']) ? htmlspecialchars($teacher['phone']) : 'Bilgi yok'; ?></dd>
                </div>
                
                <!-- Adres Bilgileri Başlığı -->
                <div class="bg-indigo-50 px-4 py-3 sm:px-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-indigo-800 uppercase tracking-wide flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                        Adres Bilgileri
                    </h4>
                </div>
                
                <!-- Adres Bilgileri -->
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">İl</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['city']) && !empty($teacher['city']) ? htmlspecialchars($teacher['city']) : 'Bilgi yok'; ?></dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">İlçe</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo isset($teacher['county']) && !empty($teacher['county']) ? htmlspecialchars($teacher['county']) : 'Bilgi yok'; ?></dd>
                </div>
                
                
                <!-- Branş ve Deneyim Bilgileri -->
                <?php if (isset($teacher['profile'])): ?>
                <!-- Mesleki Bilgiler Başlığı -->
                <div class="bg-indigo-50 px-4 py-3 sm:px-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-indigo-800 uppercase tracking-wide flex items-center">
                        <i class="fas fa-graduation-cap mr-2 text-indigo-600"></i>
                        Mesleki Bilgiler
                    </h4>
                </div>
                
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Branş</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <?php 
                            if (isset($teacher['profile']['branch_id'])) {
                                $branch = $teacherViewModel->getBranchName($teacher['profile']['branch_id']);
                                echo htmlspecialchars($branch);
                            } else {
                                echo 'Bilgi yok';
                            }
                        ?>
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Deneyim</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <?php echo isset($teacher['profile']['experience']) ? htmlspecialchars($teacher['profile']['experience']) . ' yıl' : 'Bilgi yok'; ?>
                    </dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <!-- Session verileri - Hata ayıklama için -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
        <h4 class="font-medium text-yellow-800">Debugging Bilgisi</h4>
        <div class="mt-2 text-sm text-yellow-700">
            <p>Kullanıcı ID: <?php echo $_SESSION['user_id']; ?></p>
            <p>Rol: <?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Belirtilmemiş'; ?></p>
            <p>Veritabanı: ogretmenpro</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Profil Düzenleme Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 transition-all duration-300">
    <div class="relative top-10 mx-auto p-0 w-11/12 max-w-3xl shadow-2xl rounded-xl bg-white overflow-hidden transform transition-all duration-300">
        <!-- Modal header with gradient background -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold">Profil Bilgilerini Düzenle</h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-200 transition-colors focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <p class="text-indigo-100 text-sm mt-1">Kişisel bilgilerinizi güncelleyin</p>
        </div>
        
        <form id="editForm" action="<?php echo BASE_URL; ?>/php/update_profile.php" method="post" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kişisel Bilgiler Bölümü -->
                <div class="md:col-span-2">
                    <h4 class="text-gray-600 font-medium mb-3 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-indigo-500"></i>
                        Kişisel Bilgiler
                    </h4>
                    <div class="w-full h-0.5 bg-gray-100 mb-4"></div>
                </div>
                
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Ad</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="name" id="name" value="<?php echo isset($teacher['name']) ? htmlspecialchars($teacher['name']) : ''; ?>" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="surname" class="block text-sm font-medium text-gray-700">Soyad</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="surname" id="surname" value="<?php echo isset($teacher['surname']) ? htmlspecialchars($teacher['surname']) : ''; ?>" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="tc_kimlik_no" class="block text-sm font-medium text-gray-700">TC Kimlik No</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" name="tc_kimlik_no" id="tc_kimlik_no" maxlength="11" value="<?php echo isset($teacher['tc_kimlik_no']) ? htmlspecialchars($teacher['tc_kimlik_no']) : ''; ?>" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">Doğum Tarihi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input type="date" name="birth_date" id="birth_date" value="<?php echo isset($teacher['birth_date']) ? $teacher['birth_date'] : ''; ?>" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="gender" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-venus-mars text-gray-400"></i>
                        </div>
                        <select name="gender" id="gender" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                            <option value="">Seçiniz</option>
                            <option value="male" <?php echo (isset($teacher['gender']) && $teacher['gender'] == 'male') ? 'selected' : ''; ?>>Erkek</option>
                            <option value="female" <?php echo (isset($teacher['gender']) && $teacher['gender'] == 'female') ? 'selected' : ''; ?>>Kadın</option>
                        </select>
                    </div>
                </div>
                
                <!-- İletişim Bilgileri Bölümü -->
                <div class="md:col-span-2 mt-4">
                    <h4 class="text-gray-600 font-medium mb-3 flex items-center">
                        <i class="fas fa-address-card mr-2 text-indigo-500"></i>
                        İletişim Bilgileri
                    </h4>
                    <div class="w-full h-0.5 bg-gray-100 mb-4"></div>
                </div>
                
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" value="<?php echo isset($teacher['email']) ? htmlspecialchars($teacher['email']) : ''; ?>" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone-alt text-gray-400"></i>
                        </div>
                        <input type="text" name="phone" id="phone" value="<?php echo isset($teacher['phone']) ? htmlspecialchars($teacher['phone']) : ''; ?>" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <!-- Adres Bilgileri Bölümü -->
                <div class="md:col-span-2 mt-4">
                    <h4 class="text-gray-600 font-medium mb-3 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-indigo-500"></i>
                        Adres Bilgileri
                    </h4>
                    <div class="w-full h-0.5 bg-gray-100 mb-4"></div>
                </div>
                
                <div class="space-y-2">
                    <label for="city" class="block text-sm font-medium text-gray-700">İl</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-city text-gray-400"></i>
                        </div>
                        <select name="city" id="city" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                            <option value="">İl seçiniz</option>
                        </select>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="county" class="block text-sm font-medium text-gray-700">İlçe</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-gray-400"></i>
                        </div>
                        <select name="county" id="county" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                            <option value="">Önce il seçiniz</option>
                        </select>
                    </div>
                </div>
                
                <div class="md:col-span-2 space-y-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                            <i class="fas fa-home text-gray-400"></i>
                        </div>
                        <textarea name="address" id="address" rows="3" class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all"><?php echo isset($teacher['address']) ? htmlspecialchars($teacher['address']) : ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Form Butonları -->
            <div class="flex justify-end mt-8 space-x-3">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-white text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                    <i class="fas fa-times mr-2"></i>İptal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Profil düzenleme başarılı olduğunda gösterilen bildirim -->
<div id="successNotification" class="hidden fixed bottom-5 right-5 bg-green-600 text-white px-6 py-4 rounded-lg shadow-lg z-50">
    <div class="flex items-center space-x-3">
        <i class="fas fa-check-circle text-2xl"></i>
        <div>
            <h4 class="font-medium">İşlem Başarılı</h4>
            <p class="text-sm">Profil bilgileriniz başarıyla güncellendi.</p>
        </div>
    </div>
</div>

<script>
// Sayfa yüklendiğinde çalışacak
document.addEventListener('DOMContentLoaded', function() {
    console.log("Sayfa yüklendi");
    
    // Türkiye'deki tüm illeri yükle
    fetch('/ogretmenpro/assets/js/tam_iller.json')
        .then(response => {
            console.log('JSON Yanıt:', response);
            if (!response.ok) {
                throw new Error('JSON dosyası yüklenemedi');
            }
            return response.json();
        })
        .then(data => {
            console.log('Tüm İl Verileri yüklendi');
            // İlleri dropdown'a ekle
            populateCities(data);
        })
        .catch(error => {
            console.error('İller yüklenirken hata oluştu:', error);
            // Hata durumunda detaylı il listesini deneyelim
            tryDetailedCityList();
        });
    
    // Başarı mesajını kontrol et
    if (localStorage.getItem('profileUpdated') === 'true') {
        const notification = document.getElementById('successNotification');
        notification.classList.remove('hidden');
        
        // 5 saniye sonra bildirim kaybolsun
        setTimeout(() => {
            notification.classList.add('hidden');
            localStorage.removeItem('profileUpdated');
        }, 5000);
    }
});

// İlleri dropdown'a ekle
function populateCities(data) {
    const citySelect = document.getElementById('city');
    const currentCity = "<?php echo isset($teacher['city']) ? htmlspecialchars($teacher['city']) : ''; ?>";
    
    console.log('Mevcut il değeri:', currentCity);
    
    // Mevcut seçenekleri temizle
    citySelect.innerHTML = '<option value="">İl seçiniz</option>';
    
    // Alfebetik sırayla sırala
    const sortedCities = Object.keys(data).map(key => ({ id: key, name: data[key].name }))
                               .sort((a, b) => a.name.localeCompare(b.name, 'tr'));
    
    // İlleri ekle
    sortedCities.forEach(city => {
        const option = document.createElement('option');
        option.value = city.name;
        option.textContent = city.name;
        
        // Mevcut şehri seç
        if (city.name === currentCity) {
            option.selected = true;
            console.log('İl eşleşti ve seçildi:', city.name);
        }
        
        citySelect.appendChild(option);
    });
    
    // Veritabanında kayıtlı il, yüklenmiş listede yoksa manuel ekle
    let cityFound = false;
    if (currentCity) {
        for (let i = 0; i < citySelect.options.length; i++) {
            if (citySelect.options[i].value === currentCity) {
                citySelect.options[i].selected = true;
                cityFound = true;
                break;
            }
        }
        
        // İl listede yoksa ekle
        if (!cityFound && currentCity.trim() !== '') {
            const option = document.createElement('option');
            option.value = currentCity;
            option.textContent = currentCity;
            option.selected = true;
            citySelect.appendChild(option);
            console.log('İl listede bulunamadı, manuel eklendi:', currentCity);
        }
    }
    
    // Seçili ile ait ilçeleri yükle
    if (currentCity) {
        fetchCountiesForCity(currentCity);
    }
    
    // İl değiştiğinde ilçeleri güncelle
    citySelect.addEventListener('change', function() {
        if (this.value) {
            fetchCountiesForCity(this.value);
        } else {
            // İl seçilmemişse ilçe listesini temizle
            const countySelect = document.getElementById('county');
            countySelect.innerHTML = '<option value="">Önce il seçiniz</option>';
        }
    });
}

// Seçilen il için ilçeleri yükle
function fetchCountiesForCity(cityName) {
    console.log('İlçeler getiriliyor:', cityName);
    
    // Detaylı il-ilçe verileri için iller.json dosyasını kullan
    fetch('/ogretmenpro/assets/js/iller.json')
        .then(response => {
            if (!response.ok) {
                throw new Error('İlçe verileri yüklenemedi');
            }
            return response.json();
        })
        .then(data => {
            // İl koduyla ilgili ilçeleri bul
            let cityCode = null;
            for (const [code, city] of Object.entries(data)) {
                if (city.name === cityName) {
                    cityCode = code;
                    break;
                }
            }
            
            if (cityCode && data[cityCode] && data[cityCode].counties) {
                populateCounties(data[cityCode].counties);
            } else {
                // İl koduna ait ilçe verisi bulunamadıysa manuel olarak ekle
                const countySelect = document.getElementById('county');
                countySelect.innerHTML = '<option value="">İlçe verisi bulunamadı</option>';
                
                // Mevcut ilçeyi manuel olarak ekle
                const currentCounty = "<?php echo isset($teacher['county']) ? htmlspecialchars($teacher['county']) : ''; ?>";
                if (currentCounty && currentCounty.trim() !== '') {
                    const option = document.createElement('option');
                    option.value = currentCounty;
                    option.textContent = currentCounty;
                    option.selected = true;
                    countySelect.appendChild(option);
                    console.log('İlçe verisi bulunamadı, manuel eklendi:', currentCounty);
                }
            }
        })
        .catch(error => {
            console.error('İlçeler yüklenirken hata:', error);
            
            // İlçe verisi yüklenemezse, mevcut ilçeyi manuel olarak ekle
            const countySelect = document.getElementById('county');
            countySelect.innerHTML = '<option value="">İlçe verisi yüklenemedi</option>';
            
            const currentCounty = "<?php echo isset($teacher['county']) ? htmlspecialchars($teacher['county']) : ''; ?>";
            if (currentCounty && currentCounty.trim() !== '') {
                const option = document.createElement('option');
                option.value = currentCounty;
                option.textContent = currentCounty;
                option.selected = true;
                countySelect.appendChild(option);
                console.log('İlçe verisi yüklenemedi, manuel eklendi:', currentCounty);
            }
        });
}

// İlçeleri dropdown'a ekle
function populateCounties(counties) {
    const countySelect = document.getElementById('county');
    const currentCounty = "<?php echo isset($teacher['county']) ? htmlspecialchars($teacher['county']) : ''; ?>";
    
    console.log('Mevcut ilçe değeri:', currentCounty);
    
    // Mevcut seçenekleri temizle
    countySelect.innerHTML = '<option value="">İlçe seçiniz</option>';
    
    // Alfebetik sırayla sırala
    const sortedCounties = Object.keys(counties).map(key => ({ id: key, name: counties[key].name }))
                                 .sort((a, b) => a.name.localeCompare(b.name, 'tr'));
    
    // İlçeleri ekle
    sortedCounties.forEach(county => {
        const option = document.createElement('option');
        option.value = county.name;
        option.textContent = county.name;
        
        // Mevcut ilçeyi seç
        if (county.name === currentCounty) {
            option.selected = true;
            console.log('İlçe eşleşti ve seçildi:', county.name);
        }
        
        countySelect.appendChild(option);
    });
    
    // Veritabanında kayıtlı ilçe, yüklenmiş listede yoksa manuel ekle
    let countyFound = false;
    if (currentCounty) {
        for (let i = 0; i < countySelect.options.length; i++) {
            if (countySelect.options[i].value === currentCounty) {
                countySelect.options[i].selected = true;
                countyFound = true;
                break;
            }
        }
        
        // İlçe listede yoksa ekle
        if (!countyFound && currentCounty.trim() !== '') {
            const option = document.createElement('option');
            option.value = currentCounty;
            option.textContent = currentCounty;
            option.selected = true;
            countySelect.appendChild(option);
            console.log('İlçe listede bulunamadı, manuel eklendi:', currentCounty);
        }
    }
}

// Tam il listesi yüklenemediyse, detaylı listeyi dene
function tryDetailedCityList() {
    fetch('/ogretmenpro/assets/js/iller.json')
        .then(response => {
            if (!response.ok) {
                throw new Error('Detaylı il listesi yüklenemedi');
            }
            return response.json();
        })
        .then(data => {
            console.log('Detaylı il listesi yüklendi');
            populateCities(data);
        })
        .catch(error => {
            console.error('Detaylı il listesi yüklenemedi:', error);
            // Her iki kaynak da başarısız olursa yedek veri kullan
            useBackupCityData();
        });
}

// Hiçbir kaynak çalışmazsa yedek veri kullan
function useBackupCityData() {
    console.log('Yedek il verisi kullanılıyor');
    
    // Temel il listesi
    const backupCities = {
        "1": {"name": "Adana"},
        "6": {"name": "Ankara"},
        "7": {"name": "Antalya"},
        "16": {"name": "Bursa"},
        "34": {"name": "İstanbul"},
        "35": {"name": "İzmir"},
        "38": {"name": "Kayseri"},
        "42": {"name": "Konya"},
        "41": {"name": "Kocaeli"},
        "27": {"name": "Gaziantep"},
        "55": {"name": "Samsun"},
        "61": {"name": "Trabzon"},
        "25": {"name": "Erzurum"},
        "44": {"name": "Malatya"},
        "21": {"name": "Diyarbakır"},
        "33": {"name": "Mersin"},
        "22": {"name": "Edirne"},
        "63": {"name": "Şanlıurfa"},
        "52": {"name": "Ordu"},
        "46": {"name": "Kahramanmaraş"}
    };
    
    populateCities(backupCities);
}

function openEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
    
    // Animasyon için
    setTimeout(() => {
        const modalContent = modal.querySelector('div');
        modalContent.classList.add('scale-100');
        modalContent.classList.remove('scale-95');
    }, 10);
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    const modalContent = modal.querySelector('div');
    
    // Animasyon için
    modalContent.classList.add('scale-95');
    modalContent.classList.remove('scale-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Form doğrulama
document.getElementById('editForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const surname = document.getElementById('surname').value.trim();
    const email = document.getElementById('email').value.trim();
    
    if (!name || !surname || !email) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ad, Soyad ve Email alanları zorunludur.',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Tamam'
        });
        return;
    }
    
    // Email formatı kontrolü
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Geçerli bir email adresi giriniz.',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Tamam'
        });
        return;
    }
    
    // TC Kimlik No kontrolü (11 hane)
    const tcKimlikNo = document.getElementById('tc_kimlik_no').value.trim();
    if (tcKimlikNo && tcKimlikNo.length !== 11) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'TC Kimlik Numarası 11 haneli olmalıdır.',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Tamam'
        });
        return;
    }
    
    // Başarılı form gönderimi sonrası
    localStorage.setItem('profileUpdated', 'true');
});

// Modal dışına tıklandığında kapat
window.addEventListener('click', function(e) {
    const modal = document.getElementById('editModal');
    if (e.target === modal) {
        closeEditModal();
    }
});

// Sadece TC Kimlik alanı için sayısal değerler kabul et
document.getElementById('tc_kimlik_no').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
});
</script>