<?php
require_once dirname(__DIR__) . '/includes/config.php';

$schools = [];
try {
    $stmt = $db->query("SELECT id, name, city, county, status FROM schools ORDER BY name ASC");
    $schools = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('schools.php list error: ' . $e->getMessage());
}

include dirname(__DIR__) . '/includes/header.php';
?>

<main class="pt-28 pb-16 bg-slate-50 min-h-screen">
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900">Okullar</h1>
            <p class="mt-2 text-slate-600">Sistemde kayıtlı okulları buradan görüntüleyebilirsiniz.</p>
        </header>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Okul</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">İl / İlçe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($schools)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-500">Henüz okul kaydı bulunamadı.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schools as $school): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-slate-800"><?php echo htmlspecialchars($school['name']); ?></td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <?php echo htmlspecialchars(trim(($school['city'] ?? '') . ' / ' . ($school['county'] ?? ''), ' /')); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php $active = ($school['status'] ?? '') === 'active'; ?>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium <?php echo $active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                            <?php echo $active ? 'Aktif' : 'Pasif'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
