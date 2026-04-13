<?php
require_once dirname(__DIR__) . '/includes/config.php';
$pageTitle = "Sunucu Hatası";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#F5F7FA]">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-lg w-full text-center">
            <div class="mb-8">
                <img src="<?php echo BASE_URL; ?>/assets/images/500-error.png" alt="500 Error" class="w-full max-w-md mx-auto">
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Sunucu Hatası</h1>
            <p class="text-lg text-gray-600 mb-8">Üzgünüz, bir sorun oluştu. Teknik ekibimiz sorunu çözmek için çalışıyor. Lütfen daha sonra tekrar deneyiniz.</p>
            <div class="space-y-4">
                <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150 ease-in-out">
                    <i class="fas fa-home mr-2"></i>
                    Ana Sayfaya Dön
                </a>
                <div class="mt-4">
                    <a href="mailto:support@example.com" class="inline-flex items-center justify-center px-8 py-3 border border-indigo-600 text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 transition duration-150 ease-in-out">
                        <i class="fas fa-envelope mr-2"></i>
                        Destek Ekibine Ulaş
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>