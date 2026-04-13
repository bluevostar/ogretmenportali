<?php

/**
 * Model katmanı için temel interface
 * Bu interface, tüm model sınıfları tarafından uygulanır
 */
interface IModel {
    /**
     * Belirli bir ID'ye sahip veriyi getir
     * @param int $id
     * @return array|bool
     */
    public function getById($id);
    
    /**
     * Belirli koşullara göre tek kayıt getir
     * @param array $conditions
     * @return array|bool
     */
    public function getOne($conditions);
    
    /**
     * Tüm kayıtları getir
     * @param array $conditions İsteğe bağlı koşullar
     * @param array $orderBy Sıralama kriterleri
     * @param int $limit Maksimum kayıt sayısı
     * @param int $offset Başlangıç kaydı
     * @return array
     */
    public function getAll($conditions = [], $orderBy = [], $limit = null, $offset = null);
    
    /**
     * Yeni kayıt ekle
     * @param array $data
     * @return int|bool Eklenen kaydın ID'si veya false
     */
    public function create($data);
    
    /**
     * Kayıt güncelle
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data);
    
    /**
     * Kayıt sil
     * @param int $id
     * @return bool
     */
    public function delete($id);
} 