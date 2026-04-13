<?php
require_once dirname(__DIR__) . '/includes/config.php';
if (is_logged_in() && ($_SESSION['role'] ?? null) !== ROLE_TEACHER) {
    switch ($_SESSION['role'] ?? '') {
        case ROLE_ADMIN:
            redirect(BASE_URL . '/php/admin_panel.php');
            break;
        case ROLE_COUNTRY_ADMIN:
            redirect(BASE_URL . '/php/country_admin_panel.php');
            break;
        case ROLE_SCHOOL_ADMIN:
            redirect(BASE_URL . '/php/school-admin-panel.php');
            break;
        default:
            redirect(BASE_URL . '/php/login.php');
    }
}
include dirname(__DIR__) . '/includes/header.php';
?>

<main class="pt-28 pb-16 bg-slate-50 min-h-screen">
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <article class="bg-white rounded-2xl p-8 md:p-10 border border-slate-100">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900">Kullanım Şartları</h1>
            <p class="mt-4 text-slate-700 leading-7">
                Bu platformu kullanan tüm kullanıcılar; doğru bilgi verme, hesap güvenliğini koruma ve
                platform kurallarına uygun hareket etme yükümlülüğünü kabul eder.
            </p>
            <p class="mt-4 text-slate-700 leading-7">
                Öğretmen Portalı, hizmet sürekliliği ve güvenlik kapsamında gerekli teknik değişiklikleri yapma hakkını saklı tutar.
            </p>
            <p class="mt-4 text-slate-700 leading-7">
                Yetkisiz erişim, veri manipülasyonu ve hukuka aykırı kullanım tespitlerinde hesaplar askıya alınabilir.
            </p>
        </article>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
