<?php
$schools = [];
try {
    $stmt = $db->prepare("SELECT id, name, school_code FROM schools WHERE country_admin_id = ? ORDER BY name ASC");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $schools = [];
}
?>

<div class="content-area max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="header-title">Yeni Okul Yöneticisi</h1>
        <a href="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=school_admins"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
            Geri Dön
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="POST" action="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=add_school_admin" class="space-y-5">
            <?php echo csrf_input(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ad</label>
                    <input name="name" required class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Soyad</label>
                    <input name="surname" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-posta</label>
                    <input type="email" name="email" required class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefon</label>
                    <input name="phone" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Şifre</label>
                    <input type="password" name="password" required class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bağlı Okul</label>
                    <select name="school_id" required class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seçiniz</option>
                        <?php foreach ($schools as $school): ?>
                            <option value="<?php echo (int)$school['id']; ?>">
                                <?php echo htmlspecialchars(($school['school_code'] ?? '-') . ' - ' . ($school['name'] ?? '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

