<?php
$defaultSchoolTypes = [
    'Anaokul',
    'İlkokul',
    'Ortaokul',
    'İmam Hatip Ortaokulu',
    'Anadolu Lisesi',
    'Anadolu İmam Hatip Lisesi',
    'Mesleki ve Teknik Anadolu Lisesi',
    'Özel Eğitim Meslek Okulu',
    'Özel Eğitim'
];

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS school_types (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(150) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_school_types_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $seedStmt = $db->prepare("
        INSERT INTO school_types (name, is_active) VALUES (?, 1)
        ON DUPLICATE KEY UPDATE updated_at = updated_at
    ");
    foreach ($defaultSchoolTypes as $typeName) {
        $seedStmt->execute([$typeName]);
    }
} catch (Throwable $t) {
    error_log('school_types init error: ' . $t->getMessage());
}

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(5, min(50, (int)$_GET['per_page'])) : 20;
$offset = ($page - 1) * $perPage;

$schoolTypes = [];
$totalSchoolTypes = 0;
try {
    $whereSql = " WHERE is_active = 1 ";
    $params = [];
    if ($search !== '') {
        $whereSql .= " AND name LIKE ? ";
        $params[] = '%' . $search . '%';
    }

    $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM school_types" . $whereSql);
    $countStmt->execute($params);
    $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalSchoolTypes = (int)($countRow['total'] ?? 0);

    $listSql = "SELECT id, name, created_at FROM school_types" . $whereSql . " ORDER BY name ASC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
    $listStmt = $db->prepare($listSql);
    $listStmt->execute($params);
    $schoolTypes = $listStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $t) {
    $schoolTypes = [];
    $totalSchoolTypes = 0;
}

$totalPages = max(1, (int)ceil($totalSchoolTypes / $perPage));

$allBranchesForMap = [];
try {
    $allBranchesStmt = $db->query("SELECT id, name FROM branches ORDER BY name ASC");
    $allBranchesForMap = $allBranchesStmt ? $allBranchesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $t) {
    $allBranchesForMap = [];
}

$mappedBranchesBySchoolType = [];
try {
    $mappedStmt = $db->query("
        SELECT m.school_type, m.branch_id, b.name AS branch_name
        FROM school_type_branch_map m
        INNER JOIN branches b ON b.id = m.branch_id
        ORDER BY m.school_type ASC, b.name ASC
    ");
    $mappedRows = $mappedStmt ? $mappedStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($mappedRows as $row) {
        $typeName = (string)($row['school_type'] ?? '');
        $branchId = (int)($row['branch_id'] ?? 0);
        $branchName = (string)($row['branch_name'] ?? '');
        if ($typeName !== '' && $branchId > 0 && $branchName !== '') {
            if (!isset($mappedBranchesBySchoolType[$typeName])) {
                $mappedBranchesBySchoolType[$typeName] = [];
            }
            $mappedBranchesBySchoolType[$typeName][] = [
                'branch_id' => $branchId,
                'branch_name' => $branchName
            ];
        }
    }
} catch (Throwable $t) {
    $mappedBranchesBySchoolType = [];
}
?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">Okul Türleri</h1>

        <div class="flex flex-col md:flex-row gap-2">
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="school_types">
                <input type="hidden" name="per_page" value="<?php echo (int)$perPage; ?>">
                <div class="relative flex w-full">
                    <input type="text" name="search" placeholder="Okul türü ara..."
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm pl-4 pr-10 py-2">
                    <?php if ($search !== ''): ?>
                        <a href="<?php echo BASE_URL; ?>/php/admin_panel.php?action=school_types&per_page=<?php echo (int)$perPage; ?>" class="absolute right-16 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <button type="button" onclick="openBranchSchoolTypeModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg flex items-center text-xs transition-all duration-300">
                <i class="fas fa-link mr-2"></i> Branş-Okul Türü Eşleştirme
            </button>
            <button type="button" onclick="openSchoolTypeModal()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg flex items-center text-xs transition-all duration-300">
                <i class="fas fa-plus mr-2"></i> Yeni Okul Türü Ekle
            </button>
            <button type="button" id="deleteSelectedSchoolTypesBtn" disabled class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center text-xs transition-all duration-300 opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Sil
            </button>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden flex-1 min-h-0 flex flex-col">
        <div class="overflow-x-auto flex-1 min-h-0">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input type="checkbox" id="selectAllSchoolTypes" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3">
                        </th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">ID</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[150px]">OKUL TÜRÜ</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($schoolTypes)): ?>
                        <?php foreach ($schoolTypes as $type): ?>
                            <tr>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900">
                                    <input type="checkbox" class="school-type-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo (int)$type['id']; ?>">
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo (int)$type['id']; ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo htmlspecialchars($type['name']); ?></td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="text-indigo-600 hover:text-indigo-800 inline-flex items-center" title="Branşlar" onclick="openMappedBranchesModal('<?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?>')">
                                            <i class="fas fa-list-ul text-xs"></i>
                                        </button>
                                        <button type="button" class="text-blue-600 hover:text-blue-800 inline-flex items-center" title="Düzenle" onclick="openEditSchoolTypeModal(<?php echo (int)$type['id']; ?>, '<?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?>')">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button type="button" class="text-red-600 hover:text-red-800 inline-flex items-center" title="Sil" onclick="deleteSchoolType(<?php echo (int)$type['id']; ?>, '<?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?>')">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-2 py-1.5 text-center text-gray-500 text-xs">Henüz kayıtlı okul türü bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalSchoolTypes > 0): ?>
            <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
                <p class="text-sm text-gray-600">
                    Toplam <span class="font-semibold"><?php echo (int)$totalSchoolTypes; ?></span> kayıt,
                    Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
                </p>
                <div class="flex items-center gap-1">
                    <?php
                        $baseUrl = BASE_URL . '/php/admin_panel.php?action=school_types&search=' . urlencode($search) . '&per_page=' . (int)$perPage;
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl . '&page=' . ($page - 1); ?>" class="px-3 py-1.5 text-xs border rounded hover:bg-gray-50 text-gray-700">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <a href="<?php echo $baseUrl . '&page=' . $i; ?>" class="px-3 py-1.5 text-xs border rounded <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseUrl . '&page=' . ($page + 1); ?>" class="px-3 py-1.5 text-xs border rounded hover:bg-gray-50 text-gray-700">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="editSchoolTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Okul Türü Düzenle</h3>
                <button type="button" onclick="closeEditSchoolTypeModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-5 bg-gray-50">
            <input type="hidden" id="editSchoolTypeId">
            <label for="editSchoolTypeName" class="block text-sm font-medium text-gray-700 mb-2">Okul Türü Adı</label>
            <input id="editSchoolTypeName" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="bg-white px-5 py-4 border-t border-gray-200 flex justify-end gap-2">
            <button type="button" onclick="closeEditSchoolTypeModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs text-gray-700 hover:bg-gray-50">İptal</button>
            <button type="button" onclick="submitEditSchoolType()" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">Güncelle</button>
        </div>
    </div>
</div>

<div id="mappedBranchesModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white" id="mappedBranchesModalTitle">Branşlar</h3>
                <button type="button" onclick="closeMappedBranchesModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-5 bg-gray-50">
            <div id="mappedBranchesModalContent" class="min-h-[80px] text-sm text-gray-700 max-h-[60vh] overflow-y-auto"></div>
        </div>
        <div class="bg-white px-5 py-4 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeMappedBranchesModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs text-gray-700 hover:bg-gray-50">Kapat</button>
        </div>
    </div>
</div>

<div id="schoolTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Yeni Okul Türü Ekle</h3>
                <button type="button" onclick="closeSchoolTypeModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-5 bg-gray-50">
            <label for="newSchoolTypeName" class="block text-sm font-medium text-gray-700 mb-2">Okul Türü Adı</label>
            <input id="newSchoolTypeName" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Örn. Fen Lisesi">
        </div>
        <div class="bg-white px-5 py-4 border-t border-gray-200 flex justify-end gap-2">
            <button type="button" onclick="closeSchoolTypeModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs text-gray-700 hover:bg-gray-50">İptal</button>
            <button type="button" onclick="submitSchoolType()" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">Kaydet</button>
        </div>
    </div>
</div>

<div id="branchSchoolTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white bg-opacity-20 flex items-center justify-center text-white">
                        <i class="fas fa-link"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Branş - Okul Türü Eşleştirme</h3>
                        <p class="text-xs text-indigo-100">Okul türü seçip branşları eşleştirin</p>
                    </div>
                </div>
                <button type="button" onclick="closeBranchSchoolTypeModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-4 bg-gray-50">
            <form id="branchSchoolTypeForm" action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=school_types" method="post">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Okul Türü</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <select id="branchSchoolTypeSelect" name="school_type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" required>
                            <option value="">Okul türü seçin</option>
                            <?php foreach ($schoolTypes as $st): ?>
                                <option value="<?php echo htmlspecialchars($st['name']); ?>"><?php echo htmlspecialchars($st['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="branchSearchInput" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Branş ara...">
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                    <div class="px-3 py-2 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="selectAllMapBranches" class="rounded border-gray-300 text-indigo-600">
                            <label for="selectAllMapBranches" class="text-sm font-medium text-gray-700">Tümünü Seç</label>
                        </div>
                        <button type="button" onclick="submitSelectedBranchMappings()" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">Seçilileri Ekle</button>
                    </div>
                    <div class="max-h-[380px] overflow-y-auto">
                        <table class="w-full table-auto">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1.5 text-left text-xs font-bold text-gray-700 uppercase w-12">Seç</th>
                                    <th class="px-2 py-1.5 text-left text-xs font-bold text-gray-700 uppercase">Branş</th>
                                    <th class="px-2 py-1.5 text-left text-xs font-bold text-gray-700 uppercase w-24">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($allBranchesForMap as $branch): ?>
                                    <tr>
                                        <td class="px-2 py-1.5">
                                            <input type="checkbox" class="map-branch-checkbox rounded border-gray-300 text-indigo-600" value="<?php echo (int)$branch['id']; ?>">
                                        </td>
                                        <td class="px-2 py-1.5 text-xs text-gray-800"><?php echo htmlspecialchars($branch['name']); ?></td>
                                        <td class="px-2 py-1.5">
                                            <button type="button" onclick="submitSingleBranchMapping(<?php echo (int)$branch['id']; ?>)" class="px-2 py-1 bg-blue-600 text-white rounded text-[11px] hover:bg-blue-700">Ekle</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="branchMappingHiddenFields"></div>
                <input type="hidden" name="save_school_type_branch_map" value="1">
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentMappedSchoolType = '';
const mappedBranchesByType = <?php echo json_encode($mappedBranchesBySchoolType, JSON_UNESCAPED_UNICODE); ?>;
const allBranchesForMap = <?php echo json_encode($allBranchesForMap, JSON_UNESCAPED_UNICODE); ?>;

function openSchoolTypeModal() {
    const modal = document.getElementById('schoolTypeModal');
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
}

function getMappedBranches(typeName) {
    return Array.isArray(mappedBranchesByType[typeName]) ? mappedBranchesByType[typeName] : [];
}

function setMappedBranches(typeName, branches) {
    mappedBranchesByType[typeName] = Array.isArray(branches) ? branches : [];
}

function findBranchNameById(branchId) {
    const found = allBranchesForMap.find(b => parseInt(b.id, 10) === parseInt(branchId, 10));
    return found ? String(found.name) : '';
}

function renderMappedBranchesModalTable(typeName) {
    const titleEl = document.getElementById('mappedBranchesModalTitle');
    const contentEl = document.getElementById('mappedBranchesModalContent');
    if (!titleEl || !contentEl) return;
    const branches = getMappedBranches(typeName);

    currentMappedSchoolType = typeName;
    titleEl.textContent = `${typeName} - Branşlar`;
    if (!Array.isArray(branches) || branches.length === 0) {
        contentEl.innerHTML = '<span class="text-gray-500">Bu okul türüne atanmış branş yok.</span>';
    } else {
        const sortedBranches = [...branches].sort((a, b) => String(a.branch_name).localeCompare(String(b.branch_name), 'tr'));
        contentEl.innerHTML = `
            <div class="mb-3 flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" id="selectAllMappedBranches" class="rounded border-gray-300 text-red-600" onchange="toggleSelectAllMappedBranches(this.checked)">
                    Tümünü Seç
                </label>
                <button type="button" id="deleteSelectedMappedBranchesBtn" onclick="deleteSelectedMappedBranches()" class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-trash mr-1"></i> Sil
                </button>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden max-h-[50vh] overflow-y-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase w-12">Seç</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase w-16">#</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase">Branş Adı</th>
                            <th class="px-3 py-2 text-left text-xs font-bold text-gray-700 uppercase w-20">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${sortedBranches.map((branch, index) => `
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="checkbox" class="mapped-branch-checkbox rounded border-gray-300 text-red-600" data-branch-id="${branch.branch_id}" onchange="updateMappedDeleteButtonState()">
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-500">${index + 1}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">${escapeHtml(branch.branch_name)}</td>
                                <td class="px-3 py-2">
                                    <button type="button" class="text-red-600 hover:text-red-800" title="Sil" onclick="deleteSingleMappedBranch(${branch.branch_id}, '${escapeHtml(branch.branch_name)}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
}

function openMappedBranchesModal(typeName) {
    const modal = document.getElementById('mappedBranchesModal');
    if (!modal) return;
    renderMappedBranchesModalTable(typeName);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeMappedBranchesModal() {
    const modal = document.getElementById('mappedBranchesModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function toggleSelectAllMappedBranches(checked) {
    document.querySelectorAll('.mapped-branch-checkbox').forEach(cb => {
        cb.checked = checked;
    });
    updateMappedDeleteButtonState();
}

function updateMappedDeleteButtonState() {
    const selectedCount = document.querySelectorAll('.mapped-branch-checkbox:checked').length;
    const deleteBtn = document.getElementById('deleteSelectedMappedBranchesBtn');
    if (!deleteBtn) return;
    deleteBtn.disabled = selectedCount === 0;
    deleteBtn.innerHTML = `<i class="fas fa-trash mr-1"></i> Sil`;
}

function deleteSingleMappedBranch(branchId, branchName) {
    if (!currentMappedSchoolType || !branchId) return;
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${branchName} branşı bu okul türünden silinecek.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal'
    }).then(result => {
        if (!result.isConfirmed) return;
        submitMappedBranchDelete([branchId]);
    });
}

function deleteSelectedMappedBranches() {
    if (!currentMappedSchoolType) return;
    const selectedIds = Array.from(document.querySelectorAll('.mapped-branch-checkbox:checked'))
        .map(cb => parseInt(cb.dataset.branchId, 10))
        .filter(id => Number.isInteger(id) && id > 0);
    if (selectedIds.length === 0) {
        Swal.fire('Uyarı', 'En az bir branş seçiniz.', 'warning');
        return;
    }
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${selectedIds.length} branş eşleştirmesi silinecek.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal'
    }).then(result => {
        if (!result.isConfirmed) return;
        submitMappedBranchDelete(selectedIds);
    });
}

function submitMappedBranchDelete(branchIds) {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'delete_school_type_branch_links',
            school_type: currentMappedSchoolType,
            branch_ids: branchIds
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const existing = getMappedBranches(currentMappedSchoolType);
            const next = existing.filter(item => !branchIds.includes(parseInt(item.branch_id, 10)));
            setMappedBranches(currentMappedSchoolType, next);
            renderMappedBranchesModalTable(currentMappedSchoolType);
            Swal.fire('Başarılı', res.message || 'Eşleştirmeler silindi.', 'success');
        } else {
            Swal.fire('Hata', res.message || 'Eşleştirmeler silinemedi.', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function escapeHtml(input) {
    return String(input)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function closeSchoolTypeModal() {
    const modal = document.getElementById('schoolTypeModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    const input = document.getElementById('newSchoolTypeName');
    if (input) input.value = '';
}

function submitSchoolType() {
    const input = document.getElementById('newSchoolTypeName');
    const name = (input ? input.value : '').trim();
    if (!name) {
        Swal.fire('Uyarı', 'Lütfen okul türü adı girin.', 'warning');
        return;
    }

    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add_school_type', name })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', 'Okul türü eklendi.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Hata', res.message || 'Okul türü eklenemedi.', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openEditSchoolTypeModal(id, name) {
    const modal = document.getElementById('editSchoolTypeModal');
    const idInput = document.getElementById('editSchoolTypeId');
    const nameInput = document.getElementById('editSchoolTypeName');
    if (!modal || !idInput || !nameInput) return;
    idInput.value = id;
    nameInput.value = name;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditSchoolTypeModal() {
    const modal = document.getElementById('editSchoolTypeModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function submitEditSchoolType() {
    const id = parseInt(document.getElementById('editSchoolTypeId').value || '0', 10);
    const name = (document.getElementById('editSchoolTypeName').value || '').trim();
    if (!id || !name) {
        Swal.fire('Uyarı', 'Geçerli okul türü adı girin.', 'warning');
        return;
    }

    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_school_type', id, name })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Okul türü güncellendi.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Hata', res.message || 'Okul türü güncellenemedi.', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function deleteSchoolType(id, name) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${name} okul türü silinecek. Bu türe bağlı eşleştirmeler kaldırılacak.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal'
    }).then(result => {
        if (!result.isConfirmed) return;
        csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_school_type', id })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Başarılı', res.message || 'Okul türü silindi.', 'success').then(() => location.reload());
            } else {
                Swal.fire('Hata', res.message || 'Okul türü silinemedi.', 'error');
            }
        })
        .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
    });
}

function getSelectedSchoolTypeCheckboxes() {
    return Array.from(document.querySelectorAll('.school-type-checkbox:checked'));
}

function updateSelectedSchoolTypesDeleteButton() {
    const deleteBtn = document.getElementById('deleteSelectedSchoolTypesBtn');
    if (!deleteBtn) return;
    const selectedCount = getSelectedSchoolTypeCheckboxes().length;
    if (selectedCount === 0) {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

async function deleteSelectedSchoolTypes() {
    const selected = getSelectedSchoolTypeCheckboxes();
    if (selected.length === 0) {
        Swal.fire('Uyarı', 'Lütfen silmek için en az bir okul türü seçin.', 'warning');
        return;
    }

    const result = await Swal.fire({
        title: 'Emin misiniz?',
        text: `${selected.length} okul türü silinecek. Bu türlere bağlı eşleştirmeler kaldırılacak.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal'
    });

    if (!result.isConfirmed) return;

    Swal.fire({
        title: 'Siliniyor...',
        text: 'Seçili okul türleri siliniyor',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    let successCount = 0;
    let failedCount = 0;

    for (const checkbox of selected) {
        const id = parseInt(checkbox.dataset.id || '0', 10);
        if (!id) {
            failedCount++;
            continue;
        }
        try {
            const response = await csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_school_type', id })
            });
            const res = await response.json();
            if (res.success) {
                successCount++;
            } else {
                failedCount++;
            }
        } catch (err) {
            failedCount++;
        }
    }

    if (failedCount === 0) {
        Swal.fire('Başarılı', `${successCount} okul türü silindi.`, 'success').then(() => location.reload());
    } else if (successCount === 0) {
        Swal.fire('Hata', 'Seçili okul türleri silinemedi.', 'error');
    } else {
        Swal.fire('Kısmi Başarı', `${successCount} okul türü silindi, ${failedCount} kayıt silinemedi.`, 'warning').then(() => location.reload());
    }
}

function openBranchSchoolTypeModal() {
    const modal = document.getElementById('branchSchoolTypeModal');
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
}

function closeBranchSchoolTypeModal() {
    const modal = document.getElementById('branchSchoolTypeModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitSingleBranchMapping(branchId) {
    const schoolType = document.getElementById('branchSchoolTypeSelect').value;
    if (!schoolType) {
        Swal.fire('Uyarı', 'Önce okul türü seçiniz.', 'warning');
        return;
    }
    submitMappedBranchAdd(schoolType, [branchId]);
}

function submitSelectedBranchMappings() {
    const schoolType = document.getElementById('branchSchoolTypeSelect').value;
    if (!schoolType) {
        Swal.fire('Uyarı', 'Önce okul türü seçiniz.', 'warning');
        return;
    }

    const selected = Array.from(document.querySelectorAll('.map-branch-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        Swal.fire('Uyarı', 'En az bir branş seçiniz.', 'warning');
        return;
    }
    submitMappedBranchAdd(schoolType, selected.map(id => parseInt(id, 10)));
}

function submitMappedBranchAdd(schoolType, branchIds) {
    const cleanIds = branchIds.map(v => parseInt(v, 10)).filter(v => Number.isInteger(v) && v > 0);
    if (cleanIds.length === 0) return;

    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'add_school_type_branch_links',
            school_type: schoolType,
            branch_ids: cleanIds
        })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            Swal.fire('Hata', res.message || 'Eşleştirme kaydedilemedi.', 'error');
            return;
        }

        const current = getMappedBranches(schoolType);
        const currentIds = new Set(current.map(item => parseInt(item.branch_id, 10)));
        const additions = [];
        cleanIds.forEach(id => {
            if (!currentIds.has(id)) {
                additions.push({ branch_id: id, branch_name: findBranchNameById(id) || `Branş #${id}` });
            }
        });
        setMappedBranches(schoolType, [...current, ...additions]);

        document.querySelectorAll('.map-branch-checkbox:checked').forEach(cb => { cb.checked = false; });
        const selectAll = document.getElementById('selectAllMapBranches');
        if (selectAll) selectAll.checked = false;

        if (currentMappedSchoolType === schoolType) {
            renderMappedBranchesModalTable(schoolType);
        }

        Swal.fire('Başarılı', res.message || 'Eşleştirmeler kaydedildi.', 'success');
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAllMapBranches = document.getElementById('selectAllMapBranches');
    if (!selectAllMapBranches) return;
    selectAllMapBranches.addEventListener('change', function() {
        document.querySelectorAll('.map-branch-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    const branchSearchInput = document.getElementById('branchSearchInput');
    if (branchSearchInput) {
        branchSearchInput.addEventListener('input', function() {
            const q = this.value.toLocaleLowerCase('tr');
            document.querySelectorAll('#branchSchoolTypeModal tbody tr').forEach(row => {
                const text = (row.textContent || '').toLocaleLowerCase('tr');
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const selectAllSchoolTypes = document.getElementById('selectAllSchoolTypes');
    const deleteSelectedSchoolTypesBtn = document.getElementById('deleteSelectedSchoolTypesBtn');
    const schoolTypeCheckboxes = Array.from(document.querySelectorAll('.school-type-checkbox'));

    if (selectAllSchoolTypes) {
        selectAllSchoolTypes.addEventListener('change', function() {
            schoolTypeCheckboxes.forEach(cb => { cb.checked = this.checked; });
            updateSelectedSchoolTypesDeleteButton();
        });
    }

    schoolTypeCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (selectAllSchoolTypes) {
                const allChecked = schoolTypeCheckboxes.length > 0 && schoolTypeCheckboxes.every(item => item.checked);
                selectAllSchoolTypes.checked = allChecked;
            }
            updateSelectedSchoolTypesDeleteButton();
        });
    });

    if (deleteSelectedSchoolTypesBtn) {
        deleteSelectedSchoolTypesBtn.addEventListener('click', deleteSelectedSchoolTypes);
    }

    updateSelectedSchoolTypesDeleteButton();
});
</script>
