<?php
// Öğretmenin eğitim bilgilerini veritabanından çek
try {
    $stmt = $db->prepare("SELECT * FROM teacher_education WHERE user_id = :user_id ORDER BY end_date DESC");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Education fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Eğitim bilgileri yüklenirken bir hata oluştu.";
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
    
    
    <!-- Eğitim Bilgileri Kartı -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Eğitim Bilgileri</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Akademik geçmişiniz ve eğitim bilgileriniz.</p>
            </div>
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <i class="fas fa-plus mr-2"></i>
                Eğitim Ekle
            </button>
        </div>

        <!-- Eğitim Listesi -->
        <div class="border-t border-gray-200">
            <?php if (empty($educations)): ?>
                <div class="px-4 py-5 text-center text-gray-500">
                    <i class="fas fa-graduation-cap text-4xl mb-3"></i>
                    <p>Henüz eğitim bilgisi eklenmemiş.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($educations as $education): ?>
                        <div class="px-4 py-5 sm:px-6 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($education['school_name']); ?></h4>
                                    <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($education['degree']); ?> - <?php echo htmlspecialchars($education['field_of_study']); ?></p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <?php 
                                        echo date('M Y', strtotime($education['start_date'])) . ' - ';
                                        echo $education['is_current'] ? 'Devam Ediyor' : date('M Y', strtotime($education['end_date']));
                                        ?>
                                    </p>
                                    <?php if (!empty($education['description'])): ?>
                                        <p class="mt-2 text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($education['description'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="editEducation(<?php echo $education['id']; ?>)" class="text-primary-600 hover:text-primary-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteEducation(<?php echo $education['id']; ?>)" class="text-red-600 hover:text-red-800">
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

<!-- Eğitim Ekleme/Düzenleme Modal -->
<div id="educationModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 transition-all duration-300">
    <div class="relative top-10 mx-auto p-0 w-11/12 max-w-3xl shadow-2xl rounded-xl bg-white overflow-hidden transform transition-all duration-300">
        <!-- Modal header with gradient background -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold" id="modalTitle">Eğitim Bilgisi Ekle</h3>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <p class="text-indigo-100 text-sm mt-1">Akademik bilgilerinizi güncelleyin</p>
        </div>
        
        <form id="educationForm" action="<?php echo BASE_URL; ?>/php/save_education.php" method="POST" class="p-6">
            <input type="hidden" name="education_id" id="education_id">
            
            <!-- Form alanları -->
            <div class="space-y-6">
                <div>
                    <label for="school_name" class="block text-sm font-medium text-gray-700">Okul Adı *</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-university text-gray-400"></i>
                        </div>
                        <input type="text" name="school_name" id="school_name" required
                               class="pl-10 block w-full py-3 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>

                <div>
                    <label for="degree" class="block text-sm font-medium text-gray-700">Derece *</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-graduation-cap text-gray-400"></i>
                        </div>
                        <select name="degree" id="degree" required
                                class="pl-10 block w-full py-3 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                            <option value="">Seçiniz</option>
                            <option value="Lisans">Lisans</option>
                            <option value="Yüksek Lisans">Yüksek Lisans</option>
                            <option value="Doktora">Doktora</option>
                            <option value="Ön Lisans">Ön Lisans</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="field_of_study" class="block text-sm font-medium text-gray-700">Bölüm *</label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-book text-gray-400"></i>
                        </div>
                        <input type="text" name="field_of_study" id="field_of_study" required
                               class="pl-10 block w-full py-3 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
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
                                   class="pl-10 block w-full py-3 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                        </div>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Bitiş Tarihi</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-check text-gray-400"></i>
                            </div>
                            <input type="month" name="end_date" id="end_date"
                                   class="pl-10 block w-full py-3 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
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
                                class="pl-10 block w-full py-3 rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all"></textarea>
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
    document.getElementById('modalTitle').textContent = 'Eğitim Bilgisi Ekle';
    document.getElementById('educationForm').reset();
    document.getElementById('education_id').value = '';
    
    const modal = document.getElementById('educationModal');
    modal.classList.remove('hidden');
    
    // Animasyon için
    const modalContent = modal.querySelector('div');
    modalContent.classList.add('scale-100');
    modalContent.classList.remove('scale-95');
}

function closeModal() {
    const modal = document.getElementById('educationModal');
    const modalContent = modal.querySelector('div');
    
    // Animasyon için
    modalContent.classList.add('scale-95');
    modalContent.classList.remove('scale-100');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
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
document.getElementById('educationForm').addEventListener('submit', function(e) {
    const schoolName = document.getElementById('school_name').value.trim();
    const degree = document.getElementById('degree').value.trim();
    const fieldOfStudy = document.getElementById('field_of_study').value.trim();
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const isCurrent = document.getElementById('is_current').checked;
    
    if (!schoolName || !degree || !fieldOfStudy || !startDate) {
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
            text: 'Lütfen bitiş tarihini giriniz veya devam ediyor olarak işaretleyiniz.',
        });
        return;
    }
});

// Eğitim düzenleme
function editEducation(id) {
    // AJAX ile eğitim bilgilerini getir
    fetch(`${BASE_URL}/php/get_education.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Eğitim Bilgisi Düzenle';
            document.getElementById('education_id').value = data.id;
            document.getElementById('school_name').value = data.school_name;
            document.getElementById('degree').value = data.degree;
            document.getElementById('field_of_study').value = data.field_of_study;
            document.getElementById('start_date').value = data.start_date.substring(0, 7);
            document.getElementById('is_current').checked = data.is_current;
            document.getElementById('end_date').value = data.end_date ? data.end_date.substring(0, 7) : '';
            document.getElementById('end_date').disabled = data.is_current;
            document.getElementById('description').value = data.description;
            
            const modal = document.getElementById('educationModal');
            modal.classList.remove('hidden');
            
            // Animasyon için
            const modalContent = modal.querySelector('div');
            modalContent.classList.add('scale-100');
            modalContent.classList.remove('scale-95');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Eğitim bilgileri yüklenirken bir hata oluştu.',
                confirmButtonColor: '#4f46e5'
            });
        });
}

// Eğitim silme
function deleteEducation(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu eğitim bilgisi kalıcı olarak silinecektir!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${BASE_URL}/php/delete_education.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Silindi!',
                        'Eğitim bilgisi başarıyla silindi.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Bir hata oluştu');
                }
            })
            .catch(error => {
                Swal.fire(
                    'Hata!',
                    error.message,
                    'error'
                );
            });
        }
    });
}

// Modal dışına tıklandığında kapat
window.addEventListener('click', function(e) {
    const modal = document.getElementById('educationModal');
    if (e.target === modal) {
        closeModal();
    }
});
</script> 