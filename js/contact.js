document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const privacySwitch = form.querySelector('[role="switch"]');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');

    // Gizlilik politikası switch'inin durumunu takip et
    let privacyAccepted = false;
    privacySwitch.addEventListener('click', function() {
        privacyAccepted = !privacyAccepted;
        this.setAttribute('aria-checked', privacyAccepted);
        this.classList.toggle('bg-indigo-600');
        this.classList.toggle('bg-gray-200');
        const switchButton = this.querySelector('span:not(.sr-only)');
        switchButton.classList.toggle('translate-x-3.5');
        switchButton.classList.toggle('translate-x-0');
        
        // Hidden input'u güncelle
        let hiddenInput = form.querySelector('input[name="privacy-policy"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'privacy-policy';
            form.appendChild(hiddenInput);
        }
        hiddenInput.value = privacyAccepted ? '1' : '0';
    });

    // Form gönderimi öncesi validasyon
    form.addEventListener('submit', function(e) {
        const firstName = form.querySelector('#first-name').value.trim();
        const lastName = form.querySelector('#last-name').value.trim();
        const email = form.querySelector('#email').value.trim();
        const message = form.querySelector('#message').value.trim();
        
        let errors = [];
        
        if (!firstName) errors.push('Ad alanı gereklidir.');
        if (!lastName) errors.push('Soyad alanı gereklidir.');
        if (!email) errors.push('Email alanı gereklidir.');
        if (!isValidEmail(email)) errors.push('Geçerli bir email adresi giriniz.');
        if (!message) errors.push('Mesaj alanı gereklidir.');
        if (!privacyAccepted) errors.push('Gizlilik politikasını kabul etmelisiniz.');

        if (errors.length > 0) {
            e.preventDefault();
            showErrors(errors);
            return false;
        }
    });

    // Email validasyonu
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Hata mesajlarını göster
    function showErrors(errors) {
        const errorDiv = document.createElement('div');
        errorDiv.id = 'error-message';
        errorDiv.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
        
        errors.forEach(error => {
            const p = document.createElement('p');
            p.textContent = error;
            errorDiv.appendChild(p);
        });

        // Varolan hata mesajını kaldır
        const existingError = document.getElementById('error-message');
        if (existingError) existingError.remove();
        
        document.body.appendChild(errorDiv);
        
        // 5 saniye sonra hata mesajını kaldır
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    // Başarı mesajını 5 saniye sonra kaldır
    if (successMessage) {
        setTimeout(() => {
            successMessage.remove();
        }, 5000);
    }

    // Telefon numarası formatlaması
    const phoneInput = form.querySelector('#phone-number');
    phoneInput.addEventListener('input', function(e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : !x[3] ? x[1] + '-' + x[2] : x[1] + '-' + x[2] + '-' + x[3];
    });
});