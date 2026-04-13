<?php
include_once '../includes/config.php';
include_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-16">
    <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-md text-center">
        <div class="text-red-500 mb-4">
            <i class="fas fa-exclamation-triangle text-6xl"></i>
        </div>
        
        <h1 class="text-2xl font-bold text-red-800 mb-4">Yetkisiz Erişim</h1>
        
        <p class="text-gray-600 mb-6">Bu sayfaya erişmek için gerekli yetkiniz bulunmamaktadır.</p>
        
        <div class="flex justify-center space-x-4">
            <a href="<?php echo BASE_URL; ?>" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">Ana Sayfa</a>
            
            <?php if (is_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>/php/logout.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400 transition">Çıkış Yap</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/php/login.php" class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400 transition">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 