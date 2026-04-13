/**
 * Ortak il-ilçe dropdown fonksiyonları
 * Tüm modallarda kullanılabilir
 */

/**
 * İl dropdown'ını doldurur
 * @param {string} citySelectId - İl select elementinin ID'si
 * @param {string} selectedCity - Seçili il (opsiyonel)
 */
function populateCities(citySelectId, selectedCity = null) {
    const citySelect = document.getElementById(citySelectId);
    if (!citySelect || typeof cityCountyData === 'undefined') return;
    
    // Mevcut seçenekleri temizle (ilk option hariç)
    citySelect.innerHTML = '<option value="">İl Seçiniz</option>';
    
    // İlleri sıralı olarak ekle
    const cities = Object.keys(cityCountyData).sort();
    cities.forEach(cityName => {
        const option = document.createElement('option');
        option.value = cityName;
        option.textContent = cityName;
        if (selectedCity && cityName === selectedCity) {
            option.selected = true;
        }
        citySelect.appendChild(option);
    });
}

/**
 * İlçe dropdown'ını doldurur
 * @param {string} countySelectId - İlçe select elementinin ID'si
 * @param {string} cityName - Seçilen il adı
 * @param {string} selectedCounty - Seçili ilçe (opsiyonel)
 */
function populateCounties(countySelectId, cityName, selectedCounty = null) {
    const countySelect = document.getElementById(countySelectId);
    if (!countySelect || typeof cityCountyData === 'undefined') return;
    
    // Mevcut seçenekleri temizle
    countySelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
    
    if (!cityName) {
        countySelect.disabled = true;
        return;
    }
    
    // Seçilen ile ait ilçeleri bul
    const counties = cityCountyData[cityName];
    if (counties && Array.isArray(counties) && counties.length > 0) {
        countySelect.disabled = false;
        counties.forEach(countyName => {
            const option = document.createElement('option');
            option.value = countyName;
            option.textContent = countyName;
            if (selectedCounty && countyName === selectedCounty) {
                option.selected = true;
            }
            countySelect.appendChild(option);
        });
    } else {
        countySelect.disabled = true;
    }
}

/**
 * İl ve ilçe dropdown'larını başlatır ve event listener'ları ekler
 * @param {string} citySelectId - İl select elementinin ID'si
 * @param {string} countySelectId - İlçe select elementinin ID'si
 * @param {string} selectedCity - Başlangıçta seçili il (opsiyonel)
 * @param {string} selectedCounty - Başlangıçta seçili ilçe (opsiyonel)
 */
function initCityCountyDropdowns(citySelectId, countySelectId, selectedCity = null, selectedCounty = null) {
    const citySelect = document.getElementById(citySelectId);
    const countySelect = document.getElementById(countySelectId);
    
    if (!citySelect || !countySelect) return;
    
    // İlleri doldur
    populateCities(citySelectId, selectedCity);
    
    // Eğer başlangıçta bir il seçiliyse, ilçeleri de doldur
    if (selectedCity) {
        populateCounties(countySelectId, selectedCity, selectedCounty);
    }
    
    // İl değiştiğinde ilçeleri güncelle
    citySelect.addEventListener('change', function() {
        populateCounties(countySelectId, this.value);
    });
}

// Register.php için varsayılan davranış (geriye dönük uyumluluk)
document.addEventListener('DOMContentLoaded', function() {
    // İl select elementi
    const citySelect = document.getElementById('city');
    // İlçe select elementi
    const countySelect = document.getElementById('county');
    
    if (citySelect && countySelect) {
        initCityCountyDropdowns('city', 'county');
    }
}); 