/**
 * Öğretmen Portalı - Ana JavaScript Dosyası
 */

// Form doğrulama fonksiyonu
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    
    if (!form) return false;
    
    let isValid = true;
    const errorMessages = {};
    
    // Form elemanlarını kontrol et
    for (const field in rules) {
        const element = form.elements[field];
        const value = element.value.trim();
        const fieldRules = rules[field];
        
        // Alan kurallarını kontrol et
        if (fieldRules.required && value === '') {
            isValid = false;
            errorMessages[field] = 'Bu alan zorunludur.';
            continue;
        }
        
        if (fieldRules.minLength && value.length < fieldRules.minLength) {
            isValid = false;
            errorMessages[field] = `En az ${fieldRules.minLength} karakter olmalıdır.`;
            continue;
        }
        
        if (fieldRules.email && !validateEmail(value)) {
            isValid = false;
            errorMessages[field] = 'Geçerli bir e-posta adresi giriniz.';
            continue;
        }
        
        if (fieldRules.match && form.elements[fieldRules.match].value !== value) {
            isValid = false;
            errorMessages[field] = 'Alanlar eşleşmiyor.';
            continue;
        }
    }
    
    // Hata mesajlarını göster
    displayErrorMessages(formId, errorMessages);
    
    return isValid;
}

// E-posta doğrulama
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Hata mesajlarını gösterme
function displayErrorMessages(formId, errorMessages) {
    const form = document.getElementById(formId);
    
    // Önceki hata mesajlarını temizle
    const previousErrors = form.querySelectorAll('.error-message');
    previousErrors.forEach(error => error.remove());
    
    // Hata sınıflarını temizle
    const formInputs = form.querySelectorAll('.form-input');
    formInputs.forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Yeni hata mesajları ekle
    for (const field in errorMessages) {
        const element = form.elements[field];
        const errorMessage = errorMessages[field];
        
        // Input'u işaretle
        element.classList.add('border-red-500');
        
        // Hata mesajını ekle
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
        errorDiv.innerText = errorMessage;
        
        element.parentNode.appendChild(errorDiv);
    }
}

// AJAX form gönderimi
function submitFormAjax(formId, url, successCallback, errorCallback) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successCallback(data);
        } else {
            errorCallback(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorCallback({message: 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.'});
    });
}

// Dosya yükleme önizleme
function previewFile(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    input.addEventListener('change', function() {
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.addEventListener('load', function() {
                preview.innerHTML = `
                    <div class="flex items-center p-2 bg-blue-50 rounded mt-2">
                        <i class="fas fa-file-pdf text-blue-500 mr-2"></i>
                        <span class="text-sm">${file.name}</span>
                    </div>
                `;
            });
            
            reader.readAsDataURL(file);
        }
    });
}

// Dinamik form alanları ekleme
function addDynamicField(containerId, templateId) {
    const container = document.getElementById(containerId);
    const template = document.getElementById(templateId);
    
    if (!container || !template) return;
    
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm';
    addButton.innerText = 'Alan Ekle';
    
    addButton.addEventListener('click', function() {
        const newField = template.content.cloneNode(true);
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'ml-2 text-red-500 text-sm';
        removeButton.innerHTML = '<i class="fas fa-times"></i>';
        
        removeButton.addEventListener('click', function() {
            this.parentNode.remove();
        });
        
        newField.querySelector('.dynamic-field').appendChild(removeButton);
        container.appendChild(newField);
    });
    
    container.parentNode.insertBefore(addButton, container.nextSibling);
}

// Tablo sıralama
function setupTableSorting(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const headers = table.querySelectorAll('th');
    
    headers.forEach((header, index) => {
        if (header.classList.contains('sortable')) {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        }
    });
}

function sortTable(table, columnIndex) {
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const header = table.querySelectorAll('th')[columnIndex];
    const isAscending = header.classList.contains('asc');
    
    // Sıralama sınıflarını temizle
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('asc', 'desc');
    });
    
    // Yeni sıralama sınıfını ekle
    header.classList.add(isAscending ? 'desc' : 'asc');
    
    // Tabloyu sırala
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent;
        const bValue = b.cells[columnIndex].textContent;
        
        return isAscending 
            ? bValue.localeCompare(aValue, 'tr') 
            : aValue.localeCompare(bValue, 'tr');
    });
    
    // DOM'u güncelle
    const tbody = table.querySelector('tbody');
    rows.forEach(row => tbody.appendChild(row));
}

// Modal kontrol fonksiyonları
function showEducationModal() {
    document.getElementById('educationModal').classList.remove('hidden');
}

function hideEducationModal() {
    document.getElementById('educationModal').classList.add('hidden');
    document.getElementById('educationForm').reset();
}

function showExperienceModal() {
    document.getElementById('experienceModal').classList.remove('hidden');
}

function hideExperienceModal() {
    document.getElementById('experienceModal').classList.add('hidden');
    document.getElementById('experienceForm').reset();
}

// Eğitim bilgisi kaydetme
async function saveEducation(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const educationData = {
        degree: formData.get('degree'),
        school: formData.get('school'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        description: formData.get('description')
    };

    try {
        const response = await fetch('php/save_education.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(educationData)
        });

        const result = await response.json();
        
        if (result.success) {
            // Sayfayı yenile
            location.reload();
        } else {
            alert('Eğitim bilgisi kaydedilirken bir hata oluştu: ' + result.error);
        }
    } catch (error) {
        alert('Bir hata oluştu: ' + error.message);
    }
}

// Deneyim bilgisi kaydetme
async function saveExperience(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const experienceData = {
        title: formData.get('title'),
        company: formData.get('company'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date'),
        description: formData.get('description')
    };

    try {
        const response = await fetch('php/save_experience.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(experienceData)
        });

        const result = await response.json();
        
        if (result.success) {
            // Sayfayı yenile
            location.reload();
        } else {
            alert('Deneyim bilgisi kaydedilirken bir hata oluştu: ' + result.error);
        }
    } catch (error) {
        alert('Bir hata oluştu: ' + error.message);
    }
}

// CV yükleme ve silme fonksiyonları
async function uploadCV(input) {
    if (!input.files || !input.files[0]) return;

    const formData = new FormData();
    formData.append('cv', input.files[0]);

    try {
        const response = await fetch('../php/upload_cv.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            window.location.reload();
        } else {
            alert('CV yüklenirken bir hata oluştu.');
        }
    } catch (error) {
        console.error('Hata:', error);
        alert('Bir hata oluştu.');
    }
}

async function deleteCV() {
    if (!confirm('CV\'nizi silmek istediğinizden emin misiniz?')) return;

    try {
        const response = await fetch('../php/delete_cv.php', {
            method: 'POST'
        });

        if (response.ok) {
            window.location.reload();
        } else {
            alert('CV silinirken bir hata oluştu.');
        }
    } catch (error) {
        console.error('Hata:', error);
        alert('Bir hata oluştu.');
    }
}

// Profil resmi güncelleme
document.addEventListener('DOMContentLoaded', function() {
    const profileImageBtn = document.querySelector('.profile-image-upload');
    if (profileImageBtn) {
        profileImageBtn.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('profile_image', file);

            try {
                const response = await fetch('../php/upload_profile_image.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Profil resmi yüklenirken bir hata oluştu.');
                }
            } catch (error) {
                console.error('Hata:', error);
                alert('Bir hata oluştu.');
            }
        });
    }
});

// Sayfa yüklendiğinde çalışacak işlemler
document.addEventListener('DOMContentLoaded', function() {
    // Tablo sıralama
    setupTableSorting('sortable-table');
    
    // Tarih seçici başlat
    const dateInputs = document.querySelectorAll('.date-picker');
    if (dateInputs.length > 0) {
        dateInputs.forEach(input => {
            // Burada bir tarih seçici kütüphanesi kullanılabilir
            // Örnek: flatpickr, pikaday vb.
        });
    }
    
    // Mobil menü kontrolü
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const header = document.querySelector('.sticky-header');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Kaydırma durumunda header'ı güncelle
    window.addEventListener('scroll', function() {
        if (window.scrollY > 0) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Kapatma düğmeleri için olay dinleyicileri
    document.querySelectorAll('.close-alert').forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert-dismissible');
            if (alert) {
                alert.classList.add('opacity-0');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        });
    });
});

// Sayfadan çıkıldığında mobil menüyü kapat
document.addEventListener('click', function(event) {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuButton = document.getElementById('mobile-menu-button');

    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
            mobileMenu.classList.add('hidden');
        }
    }
});