// Modal işlemleri için yardımcı fonksiyonlar
const toggleModal = (modalId, show = true) => {
    const modal = document.getElementById(modalId);
    if (show) {
        modal.classList.remove('hidden');
    } else {
        modal.classList.add('hidden');
    }
};

// Form doğrulama fonksiyonları
const validateProfileForm = (form) => {
    const name = form.elements.name.value.trim();
    const email = form.elements.email.value.trim();
    const phone = form.elements.phone.value.trim();
    
    if (!name || !email) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ad Soyad ve Email alanları zorunludur.',
        });
        return false;
    }
    
    // Email formatı kontrolü
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Geçerli bir email adresi giriniz.',
        });
        return false;
    }
    
    // Telefon formatı kontrolü (opsiyonel)
    if (phone && !/^[0-9\s\-\+\(\)]+$/.test(phone)) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Geçerli bir telefon numarası giriniz.',
        });
        return false;
    }
    
    return true;
};

const validatePhotoUpload = (file) => {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!file) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen bir dosya seçiniz.',
        });
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Sadece JPEG, PNG ve GIF formatları desteklenmektedir.',
        });
        return false;
    }
    
    if (file.size > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Dosya boyutu 5MB\'dan büyük olamaz.',
        });
        return false;
    }
    
    return true;
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Modal butonları
    const editProfileBtn = document.getElementById('editProfileBtn');
    const editProfilePhotoBtn = document.getElementById('editProfilePhotoBtn');
    const closeProfileModal = document.getElementById('closeProfileModal');
    const closePhotoModal = document.getElementById('closePhotoModal');
    const cancelProfileEdit = document.getElementById('cancelProfileEdit');
    const cancelPhotoUpload = document.getElementById('cancelPhotoUpload');
    
    // Profil düzenleme modalı
    editProfileBtn?.addEventListener('click', () => toggleModal('editProfileModal'));
    [closeProfileModal, cancelProfileEdit].forEach(btn => {
        btn?.addEventListener('click', () => toggleModal('editProfileModal', false));
    });
    
    // Fotoğraf yükleme modalı
    editProfilePhotoBtn?.addEventListener('click', () => toggleModal('photoUploadModal'));
    [closePhotoModal, cancelPhotoUpload].forEach(btn => {
        btn?.addEventListener('click', () => toggleModal('photoUploadModal', false));
    });
    
    // Modal dışına tıklama
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-backdrop')) {
            toggleModal(e.target.dataset.modal, false);
        }
    });
    
    // Form doğrulamaları
    const profileForm = document.getElementById('profileForm');
    profileForm?.addEventListener('submit', (e) => {
        if (!validateProfileForm(e.target)) {
            e.preventDefault();
        }
    });
    
    const photoForm = document.getElementById('photoForm');
    photoForm?.addEventListener('submit', (e) => {
        const file = e.target.elements.profile_photo.files[0];
        if (!validatePhotoUpload(file)) {
            e.preventDefault();
        }
    });
    
    // Profil fotoğrafı önizleme
    const photoInput = document.querySelector('input[name="profile_photo"]');
    photoInput?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && validatePhotoUpload(file)) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.querySelector('.profile-photo').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}); 