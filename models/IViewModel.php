<?php

/**
 * ViewModel katmanı için temel interface
 * Bu interface, tüm ViewModel sınıfları tarafından uygulanır
 */
interface IViewModel {
    /**
     * View için gerekli verileri hazırla
     * @param array $params View için gerekli parametreler
     * @return array View'a iletilecek veriler
     */
    public function prepareViewData($params = []);
    
    /**
     * Kullanıcı girdilerini işle (form gönderimi, AJAX istekleri vb.)
     * @param array $input Kullanıcı girdileri
     * @return mixed İşlem sonucu
     */
    public function processInput($input = []);
    
    /**
     * Yetkilendirme kontrolü
     * @param array $params Kontrol parametreleri
     * @return bool Yetkili mi?
     */
    public function checkAuthorization($params = []);
} 