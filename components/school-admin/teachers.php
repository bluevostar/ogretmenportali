<?php
// Filtre parametrelerini al
$branch = isset($_GET['branch']) ? clean_input($_GET['branch']) : '';
$status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Öğretmenleri ve branşları getir
$teachers = $viewModel->getTeachers($branch, $status, $search);
$branches = $viewModel->getBranches();
?>

<!-- Filtreler -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="teachers">
            
            <div>
                <label for="branch" class="block text-sm font-medium text-gray-700">Branş</label>
                <select id="branch" name="branch" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Tümü</option>
                    <?php foreach ($branches as $b): ?>
                    <option value="<?php echo htmlspecialchars($b['id']); ?>" <?php echo $branch == $b['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($b['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Durum</label>
                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                    <option value="">Tümü</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Beklemede</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                </select>
            </div>
            
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Arama</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                       placeholder="İsim veya e-posta ara...">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-xs font-medium text-white bg-primary-600 hover:bg-primary-700">
                    <i class="fas fa-search mr-2"></i>
                    Filtrele
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Öğretmen Listesi -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Öğretmenler</h3>
        <a href="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=export_teachers" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-download mr-2"></i>
            Excel'e Aktar
        </a>
    </div>
    
    <?php if (empty($teachers)): ?>
    <div class="p-6 text-center text-gray-500">
        <i class="fas fa-users text-4xl mb-4"></i>
        <p>Henüz öğretmen bulunmuyor.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Öğretmen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branş</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deneyim</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <?php if ($teacher['profile_image']): ?>
                                <img class="h-10 w-10 rounded-full" src="<?php echo BASE_URL . '/' . $teacher['profile_image']; ?>" alt="">
                                <?php else: ?>
                                <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($teacher['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['branch']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($teacher['experience']); ?> Yıl</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClass = '';
                        $statusText = '';
                        switch ($teacher['status']) {
                            case 'active':
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'Aktif';
                                break;
                            case 'pending':
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'Beklemede';
                                break;
                            case 'inactive':
                                $statusClass = 'bg-red-100 text-red-800';
                                $statusText = 'Pasif';
                                break;
                        }
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=teachers&teacher_id=<?php echo $teacher['id']; ?>" 
                           class="text-primary-600 hover:text-primary-900">Detay</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Öğretmen Detay Modal -->
<div id="teacherDetailModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div id="teacherDetailContent">
                    <!-- AJAX ile doldurulacak -->
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeTeacherDetail()" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Kapat
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewTeacherDetail(teacherId) {
    // AJAX ile öğretmen detayını getir
    csrfFetch(`<?php echo BASE_URL; ?>/php/school-admin-panel.php?action=get_teacher_detail&id=${teacherId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('teacherDetailContent').innerHTML = data.html;
                document.getElementById('teacherDetailModal').classList.remove('hidden');
            } else {
                alert('Öğretmen bilgileri alınırken bir hata oluştu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu. Lütfen tekrar deneyin.');
        });
}

function closeTeacherDetail() {
    document.getElementById('teacherDetailModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const teacherId = new URLSearchParams(window.location.search).get('teacher_id');
    if (teacherId) {
        viewTeacherDetail(teacherId);
    }
});
</script>