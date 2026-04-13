<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/models/viewmodels/AdminViewModel.php';

// Kullanıcı giriş yapmamışsa veya admin değilse admin giriş sayfasına yönlendir
if (!is_logged_in() || $_SESSION['role'] != ROLE_COUNTRY_ADMIN) {
    redirect(BASE_URL . '/php/login.php');
}

// ViewModel'i başlat
$viewModel = new AdminViewModel($db);

// Sadece öğretmenleri getir
$users = $viewModel->filterUsers('teacher');

// CSV başlıkları
$csvData = "ID,Ad Soyad,E-posta,Durum,Kayıt Tarihi\n";

foreach ($users as $user) {
    // Durumu Türkçeleştir
    $status = $user['status'] == 'active' ? 'Aktif' : 'Pasif';
    
    // CSV satırını oluştur
    $csvData .= "{$user['id']},{$user['name']},{$user['email']},{$status}," . date('d.m.Y H:i', strtotime($user['created_at'])) . "\n";
}

// CSV dosyasını indirme
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ogretmenler.csv');
echo $csvData;
exit; 