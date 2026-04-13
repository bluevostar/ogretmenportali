<?php
// İstatistikleri al
$stats = $viewModel->getDashboardStats();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Toplam Başvuru -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                <i class="fas fa-file-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Toplam Başvuru</h3>
                <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_applications']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Bekleyen Başvuru -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Bekleyen</h3>
                <p class="text-3xl font-bold text-gray-900"><?php echo $stats['pending_applications']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Onaylanan Başvuru -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Onaylanan</h3>
                <p class="text-3xl font-bold text-gray-900"><?php echo $stats['approved_applications']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Reddedilen Başvuru -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-500">
                <i class="fas fa-times-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Reddedilen</h3>
                <p class="text-3xl font-bold text-gray-900"><?php echo $stats['rejected_applications']; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Son Başvurular -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900">Son Başvurular</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Öğretmen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branş</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başvuru Tarihi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($viewModel->getRecentApplications(5) as $application): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <?php if ($application['profile_image']): ?>
                                <img class="h-10 w-10 rounded-full" src="<?php echo BASE_URL . '/' . $application['profile_image']; ?>" alt="">
                                <?php else: ?>
                                <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($application['teacher_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($application['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($application['branch']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d.m.Y', strtotime($application['application_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        switch ($application['status']) {
                            case 'pending':
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'Bekliyor';
                                break;
                            case 'approved':
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'Onaylandı';
                                break;
                            case 'rejected':
                                $statusClass = 'bg-red-100 text-red-800';
                                $statusText = 'Reddedildi';
                                break;
                        }
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=applications&view=<?php echo $application['id']; ?>" 
                           class="text-primary-600 hover:text-primary-900">Detay</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Branşlara Göre Başvurular -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900">Branşlara Göre Başvurular</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($viewModel->getApplicationsByBranch() as $branch): ?>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($branch['name']); ?></h4>
                        <p class="text-lg font-semibold text-gray-700"><?php echo $branch['count']; ?> Başvuru</p>
                    </div>
                    <div class="text-primary-500">
                        <i class="fas fa-chart-bar text-2xl"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>