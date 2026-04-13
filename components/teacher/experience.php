<?php
// Öğretmenin deneyim bilgilerini viewmodel üzerinden çek
try {
    $experiences = $teacherViewModel->getTeacherJobExperiences($_SESSION['user_id']);
} catch(Exception $e) {
    error_log("Experience fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Deneyim bilgileri yüklenirken bir hata oluştu.";
    $experiences = [];
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
    
    
    <!-- Deneyim Bilgileri Kartı -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">İş Deneyimleri</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Mesleki deneyimleriniz ve iş geçmişiniz.</p>
            </div>
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-plus mr-2"></i>
                Deneyim Ekle
            </button>
        </div>

        <!-- Deneyim Listesi -->
        <div class="border-t border-gray-200">
            <?php if (empty($experiences)): ?>
                <div class="px-4 py-5 text-center text-gray-500">
                    <i class="fas fa-briefcase text-4xl mb-3"></i>
                    <p>Henüz deneyim bilgisi eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($experiences as $experience): ?>
                        <div class="px-4 py-5 sm:px-6 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($experience['position']); ?></h4>
                                    <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($experience['company_name']); ?></p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <?php 
                                        echo date('M Y', strtotime($experience['start_date'])) . ' - ';
                                        echo $experience['is_current'] ? 'Devam Ediyor' : date('M Y', strtotime($experience['end_date']));
                                        ?>
                                    </p>
                                    <?php if (!empty($experience['description'])): ?>
                                        <p class="mt-2 text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($experience['description'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editExperience(<?php echo $experience['id']; ?>)" class="text-primary-600 hover:text-primary-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteExperience(<?php echo $experience['id']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Deneyim Ekleme/Düzenleme Modal -->
<div id="experienceModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 transition-all duration-300">
    <div class="relative top-10 mx-auto p-0 w-11/12 max-w-3xl shadow-2xl rounded-xl bg-white overflow-hidden transform transition-all duration-300">
        <!-- Modal header with gradient background -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold" id="modalTitle">Deneyim Bilgisi Ekle</h3>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <p class="text-indigo-100 text-sm mt-1">Mesleki deneyimlerinizi güncelleyin</p>
        </div>
        
        <form id="experienceForm" action="<?php echo BASE_URL; ?>/php/save_experience.php" method="POST" class="p-6">
            <input type="hidden" name="experience_id" id="experience_id">
            
            <!-- Form alanları -->
            <div class="space-y-6">
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700">Pozisyon *</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-briefcase text-gray-400"></i>
                        </div>
                        <input type="text" name="position" id="position" required
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>

                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Kurum/Şirket Adı *</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-gray-400"></i>
                        </div>
                        <input type="text" name="company_name" id="company_name" required
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Başlangıç Tarihi *</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                            <input type="month" name="start_date" id="start_date" required
                                   class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                        </div>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Bitiş Tarihi</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-check text-gray-400"></i>
                            </div>
                            <input type="month" name="end_date" id="end_date"
                                   class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_current" id="is_current"
                           class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_current" class="ml-2 block text-sm text-gray-700">
                        Halen devam ediyorum
                    </label>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Açıklama</label>
                    <div class="relative mt-1">
                        <div class="absolute top-2 left-0 pl-3 pointer-events-none">
                            <i class="fas fa-align-left text-gray-400"></i>
                        </div>
                        <textarea name="description" id="description" rows="3"
                                  class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all"></textarea>
                    </div>
                </div>
            </div>

            <!-- Form Butonları -->
            <div class="flex justify-end mt-8 space-x-3">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                    <i class="fas fa-times mr-2"></i>İptal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal işlemleri
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Deneyim Bilgisi Ekle';
    document.getElementById('experienceForm').reset();
    document.getElementById('experience_id').value = '';
    
    const modal = document.getElementById('experienceModal');
    modal.classList.remove('hidden');
    
    // Animasyon için
    const modalContent = modal.querySelector('div');
    modalContent.classList.add('scale-100');
    modalContent.classList.remove('scale-95');
}

function closeModal() {
    const modal = document.getElementById('experienceModal');
    const modalContent = modal.querySelector('div');
    
    // Animasyon için
    modalContent.classList.add('scale-95');
    modalContent.classList.remove('scale-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Deneyimi düzenleme modalı
function editExperience(id) {
    // AJAX ile deneyim bilgisini çek
    fetch(`${BASE_URL}/php/get_experience.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const experience = data.data;
                document.getElementById('experience_id').value = experience.id;
                document.getElementById('position').value = experience.position;
                document.getElementById('company_name').value = experience.company_name;
                
                const isCurrent = experience.is_current == 1;
                document.getElementById('is_current').checked = isCurrent;
                
                const endDateInput = document.getElementById('end_date');
                endDateInput.disabled = isCurrent;
                endDateInput.value = isCurrent ? '' : experience.end_date;
                
                document.getElementById('description').value = experience.description || '';
                
                document.getElementById('modalTitle').textContent = 'Deneyim Bilgisi Düzenle';
                
                const modal = document.getElementById('experienceModal');
                modal.classList.remove('hidden');
                
                // Animasyon için
                const modalContent = modal.querySelector('div');
                modalContent.classList.add('scale-100');
                modalContent.classList.remove('scale-95');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message || 'Deneyim bilgisi yüklenirken bir hata oluştu.',
                    confirmButtonColor: '#4f46e5'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Deneyim bilgisi yüklenirken bir hata oluştu.',
                confirmButtonColor: '#4f46e5'
            });
        });
}

// Deneyim silme fonksiyonu
function deleteExperience(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu deneyim bilgisi kalıcı olarak silinecektir!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX ile deneyim bilgisini sil
            fetch(`${BASE_URL}/php/delete_experience.php?id=${id}`, {
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
                        text: data.message || 'Deneyim bilgisi silinirken bir hata oluştu.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Deneyim bilgisi silinirken bir hata oluştu.'
                });
            });
        }
    });
}

// Devam ediyor checkbox kontrolü
document.getElementById('is_current').addEventListener('change', function() {
    const endDateInput = document.getElementById('end_date');
    endDateInput.disabled = this.checked;
    if (this.checked) {
        endDateInput.value = '';
    }
});

// Form doğrulama
document.getElementById('experienceForm').addEventListener('submit', function(e) {
    const position = document.getElementById('position').value.trim();
    const companyName = document.getElementById('company_name').value.trim();
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const isCurrent = document.getElementById('is_current').checked;
    
    if (!position || !companyName || !startDate) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen zorunlu alanları doldurunuz.',
        });
        return;
    }
    
    if (!isCurrent && !endDate) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen bitiş tarihini girin veya "Halen devam ediyorum" seçeneğini işaretleyin.',
        });
        return;
    }
    
    // Başlangıç tarihi bitiş tarihinden büyük olamaz
    if (!isCurrent && endDate && new Date(startDate) > new Date(endDate)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Başlangıç tarihi, bitiş tarihinden sonra olamaz.',
        });
        return;
    }
});
</script> 