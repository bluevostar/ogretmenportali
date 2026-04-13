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
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-8">Sıkça Sorulan Sorular</h1>
        <div class="space-y-4">
            <article class="bg-white rounded-xl p-6 border border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900">Başvuru nasıl yapılır?</h2>
                <p class="mt-2 text-slate-700">Öğretmen hesabınızla giriş yaptıktan sonra panelden uygun ilanlara başvuru oluşturabilirsiniz.</p>
            </article>
            <article class="bg-white rounded-xl p-6 border border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900">Başvuru durumumu nereden takip ederim?</h2>
                <p class="mt-2 text-slate-700">Öğretmen panelindeki başvuru ekranından onay, ret ve bekleme durumlarını anlık izleyebilirsiniz.</p>
            </article>
            <article class="bg-white rounded-xl p-6 border border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900">Şifremi unuttum, ne yapmalıyım?</h2>
                <p class="mt-2 text-slate-700">Giriş ekranındaki “Şifremi Unuttum” akışını kullanarak hesabınızı doğrulayıp yeni şifre belirleyebilirsiniz.</p>
            </article>
        </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
