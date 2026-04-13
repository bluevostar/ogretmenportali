<?php
// Öğretmenin yetenek bilgilerini viewmodel üzerinden çek
try {
    $skillsData = $teacherViewModel->getTeacherSkillsByCategory($_SESSION['user_id']);
    $skills = $skillsData['skills'];
    $groupedSkills = $skillsData['groupedSkills'];
    $categories = $skillsData['categories'];
} catch(Exception $e) {
    error_log("Skills fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Yetenek bilgileri yüklenirken bir hata oluştu.";
    $skills = [];
    $groupedSkills = [];
    $categories = [];
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
    
    <!-- Yetenek Bilgileri Kartı -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Yetenekler ve Beceriler</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Sahip olduğunuz yetenekler ve beceriler.</p>
            </div>
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-plus mr-2"></i>
                Yetenek Ekle
            </button>
        </div>

        <!-- Yetenek Listesi -->
        <div class="border-t border-gray-200">
            <?php if (empty($skills)): ?>
                <div class="px-4 py-5 text-center text-gray-500">
                    <i class="fas fa-star text-4xl mb-3"></i>
                    <p>Henüz yetenek bilgisi eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="p-4">
                    <?php foreach ($categories as $categoryKey => $categoryName): ?>
                        <?php if (!empty($groupedSkills[$categoryKey])): ?>
                            <div class="mb-6">
                                <h4 class="text-md font-semibold text-gray-700 mb-3"><?php echo $categoryName; ?></h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($groupedSkills[$categoryKey] as $skill): ?>
                                        <div class="bg-gray-50 p-4 rounded-lg hover:shadow-md transition-shadow">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h5 class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($skill['skill_name']); ?></h5>
                                                    
                                                    <!-- Seviye göstergesi -->
                                                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-primary-500 h-2 rounded-full" style="width: <?php echo $skill['level'] * 20; ?>%"></div>
                                                    </div>
                                                    <div class="flex justify-between mt-1">
                                                        <span class="text-xs text-gray-500">Başlangıç</span>
                                                        <span class="text-xs text-gray-500">Uzman</span>
                                                    </div>
                                                    
                                                    <?php if (!empty($skill['description'])): ?>
                                                        <p class="mt-2 text-xs text-gray-600"><?php echo nl2br(htmlspecialchars($skill['description'])); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex space-x-2 ml-2">
                                                    <button onclick="editSkill(<?php echo $skill['id']; ?>)" class="text-primary-600 hover:text-primary-800">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="deleteSkill(<?php echo $skill['id']; ?>)" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Yetenek Ekleme/Düzenleme Modal -->
<div id="skillModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <form id="skillForm" action="<?php echo BASE_URL; ?>/php/save_skill.php" method="POST" class="p-6">
                <input type="hidden" name="skill_id" id="skill_id">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Yetenek Bilgisi Ekle</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="skill_name" class="block text-sm font-medium text-gray-700">Yetenek Adı *</label>
                        <input type="text" name="skill_name" id="skill_name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Kategori *</label>
                        <select name="category" id="category" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Seçiniz</option>
                            <option value="language">Dil Becerileri</option>
                            <option value="technical">Teknik Beceriler</option>
                            <option value="social">Sosyal Beceriler</option>
                            <option value="other">Diğer Beceriler</option>
                        </select>
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700">Seviye *</label>
                        <div class="mt-1">
                            <input type="range" name="level" id="level" min="1" max="5" value="3" 
                                  class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Başlangıç</span>
                                <span>Orta</span>
                                <span>Uzman</span>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <span id="levelDisplay" class="text-sm font-medium">3</span>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Açıklama</label>
                        <textarea name="description" id="description" rows="3"
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
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal işlemleri
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Yetenek Bilgisi Ekle';
    document.getElementById('skillForm').reset();
    document.getElementById('skill_id').value = '';
    document.getElementById('levelDisplay').textContent = '3';
    document.getElementById('skillModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('skillModal').classList.add('hidden');
}

// Seviye göstergesini güncelle
document.getElementById('level').addEventListener('input', function() {
    document.getElementById('levelDisplay').textContent = this.value;
});

// Yeteneği düzenleme modalı
function editSkill(id) {
    // AJAX ile yetenek bilgisini çek
    fetch(`${BASE_URL}/php/get_skill.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const skill = data.data;
                document.getElementById('skill_id').value = skill.id;
                document.getElementById('skill_name').value = skill.skill_name;
                document.getElementById('category').value = skill.category;
                document.getElementById('level').value = skill.level;
                document.getElementById('levelDisplay').textContent = skill.level;
                document.getElementById('description').value = skill.description || '';
                
                document.getElementById('modalTitle').textContent = 'Yetenek Bilgisi Düzenle';
                document.getElementById('skillModal').classList.remove('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message || 'Yetenek bilgisi yüklenirken bir hata oluştu.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Yetenek bilgisi yüklenirken bir hata oluştu.'
            });
        });
}

// Yetenek silme fonksiyonu
function deleteSkill(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu yetenek bilgisi kalıcı olarak silinecektir!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX ile yetenek bilgisini sil
            fetch(`${BASE_URL}/php/delete_skill.php?id=${id}`, {
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
                        text: data.message || 'Yetenek bilgisi silinirken bir hata oluştu.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Yetenek bilgisi silinirken bir hata oluştu.'
                });
            });
        }
    });
}

// Form doğrulama
document.getElementById('skillForm').addEventListener('submit', function(e) {
    const skillName = document.getElementById('skill_name').value.trim();
    const category = document.getElementById('category').value;
    
    if (!skillName || !category) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen zorunlu alanları doldurunuz.',
        });
        return;
    }
});
</script> 