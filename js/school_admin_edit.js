function editSchoolAdmin(id) {
    // URL oluştur
    const url = BASE_URL + '/php/country_admin_panel.php?action=edit_school_admin&id=' + id;
    
    // Sayfayı yönlendir
    window.location.href = url;
}

// Sayfa yüklendiğinde çalışacak
document.addEventListener('DOMContentLoaded', function() {
    console.log("School admin edit JS loaded");
    
    // Düzenleme bağlantılarını bul ve olay dinleyicisi ekle
    document.querySelectorAll('a[href*="edit_school_admin"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const idMatch = url.match(/id=(\d+)/);
            
            if (idMatch && idMatch[1]) {
                const adminId = idMatch[1];
                console.log("Editing admin ID:", adminId);
                
                // Yönlendirmeyi yap
                window.location.href = url;
            }
        });
    });
}); 