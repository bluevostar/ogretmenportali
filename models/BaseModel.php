<?php
require_once __DIR__ . '/IModel.php';

class BaseModel implements IModel {
    protected $db;
    protected $table;
    
    public function __construct($db, $table = null) {
        $this->db = $db;
        $this->table = $table;
    }
    
    /**
     * Veritabanı bağlantısını döndür
     */
    protected function getDb() {
        return $this->db;
    }
    
    /**
     * SQL sorgusunu hazırla ve çalıştır
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log("SQL hazırlama hatası: " . print_r($errorInfo, true));
                error_log("SQL sorgusu: " . $query);
                return false;
            }
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Sorgu çalıştırılırken hata oluştu: " . $e->getMessage());
            error_log("SQL sorgusu: " . $query);
            error_log("Parametreler: " . print_r($params, true));
            return false;
        }
    }
    
    /**
     * Tek bir kayıt getir
     */
    protected function fetchOne($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    /**
     * Tüm kayıtları getir
     */
    protected function fetchAll($query, $params = []) {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    }
    
    /**
     * IModel interface implementation
     */
    
    /**
     * Belirli bir ID'ye sahip veriyi getir
     * @param int $id
     * @return array|bool
     */
    public function getById($id) {
        return $this->getOne(['id' => $id]);
    }
    
    /**
     * Belirli koşullara göre tek kayıt getir
     * @param array $conditions
     * @return array|bool
     */
    public function getOne($conditions) {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause) . " LIMIT 1";
        return $this->fetchOne($query, $params);
    }
    
    /**
     * Tüm kayıtları getir
     * @param array $conditions İsteğe bağlı koşullar
     * @param array $orderBy Sıralama kriterleri
     * @param int $limit Maksimum kayıt sayısı
     * @param int $offset Başlangıç kaydı
     * @return array
     */
    public function getAll($conditions = [], $orderBy = [], $limit = null, $offset = null) {
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        // Where koşulları
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = ?";
                $params[] = $value;
            }
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        // Sıralama
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $orderClauses[] = "{$field} {$direction}";
            }
            $query .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        // Limit ve offset
        if ($limit !== null) {
            $query .= " LIMIT ?";
            $params[] = (int)$limit;
            
            if ($offset !== null) {
                $query .= " OFFSET ?";
                $params[] = (int)$offset;
            }
        }
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Kayıt ekle
     * @param array $data
     * @return int|bool Eklenen kaydın ID'si veya false
     */
    public function create($data) {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $query = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
            $stmt = $this->db->prepare($query);
            $stmt->execute($values);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Kayıt eklenirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kayıt güncelle
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            if (empty($data)) {
                error_log("Update: Güncellenecek veri boş. Table: {$this->table}, ID: {$id}");
                return false;
            }
            
            $fields = array_keys($data);
            $values = array_values($data);
            $set = implode('=?,', $fields) . '=?';
            
            // Anahtar sütun 'id' olmalı
            $query = "UPDATE {$this->table} SET {$set} WHERE id=?";
            $values[] = $id;
            
            error_log("Update query: {$query}");
            error_log("Update values: " . print_r($values, true));
            
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                $errorInfo = $this->db->errorInfo();
                error_log("Update prepare error: " . print_r($errorInfo, true));
                return false;
            }
            
            $result = $stmt->execute($values);
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Update execute error: " . print_r($errorInfo, true));
            } else {
                error_log("Update successful. Rows affected: " . $stmt->rowCount());
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Kayıt güncellenirken hata oluştu: " . $e->getMessage());
            error_log("Table: {$this->table}, ID: {$id}, Data: " . print_r($data, true));
            return false;
        }
    }
    
    /**
     * Kayıt sil
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id=?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Kayıt silinirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kayıt sayısını getir
     */
    public function getCount($conditions = [], $table = null) {
        try {
            $tableName = $table ?? $this->table;
            $query = "SELECT COUNT(*) as count FROM " . $tableName;
            
            if (!empty($conditions)) {
                $where = [];
                $params = [];
                foreach ($conditions as $field => $value) {
                    $where[] = "$field = ?";
                    $params[] = $value;
                }
                $query .= " WHERE " . implode(" AND ", $where);
                
                $result = $this->fetchOne($query, $params);
            } else {
                $result = $this->fetchOne($query);
            }
            
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Kayıt sayısı alınırken hata: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Filtrelenmiş kayıtları getir
     */
    public function getFiltered($conditions = [], $orderBy = null, $limit = null, $table = null) {
        try {
            $tableName = $table ?? $this->table;
            $query = "SELECT * FROM " . $tableName;
            
            $params = [];
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $field => $value) {
                    $where[] = "$field = ?";
                    $params[] = $value;
                }
                $query .= " WHERE " . implode(" AND ", $where);
            }
            
            if ($orderBy) {
                $query .= " ORDER BY $orderBy";
            }
            
            if ($limit) {
                $query .= " LIMIT " . (int)$limit;
            }
            
            return $this->fetchAll($query, $params);
        } catch (PDOException $e) {
            error_log("Kayıtlar filtrelenirken hata: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Arama yap
     */
    public function search($searchColumns, $searchTerm, $table = null) {
        try {
            $tableName = $table ?? $this->table;
            $query = "SELECT * FROM " . $tableName . " WHERE ";
            
            $conditions = [];
            $params = [];
            foreach ($searchColumns as $column) {
                $conditions[] = "$column LIKE ?";
                $params[] = "%$searchTerm%";
            }
            
            $query .= "(" . implode(" OR ", $conditions) . ")";
            
            return $this->fetchAll($query, $params);
        } catch (PDOException $e) {
            error_log("Arama yapılırken hata: " . $e->getMessage());
            return [];
        }
    }
}