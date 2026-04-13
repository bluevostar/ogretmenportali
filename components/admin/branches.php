<?php
// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Get branches with optional search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$branches = $viewModel->getAllBranches($page, $perPage, $sortColumn, $sortDirection, ['search' => $search]);
$totalBranches = is_array($branches) ? count($branches) : 0;
$totalPages = max(1, (int)ceil($totalBranches / $perPage));
$page = max(1, min($page, $totalPages));
$branches = array_slice($branches, ($page - 1) * $perPage, $perPage);

?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">Branş Yönetimi</h1>
        
        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="branches">
                <div class="relative flex w-full">
                    <input type="text" name="search" id="searchInput" placeholder="Branş ara..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm pl-4 pr-10 py-2">
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <button type="button" onclick="clearSearch()" class="absolute right-16 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" title="Aramayı temizle">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php endif; ?>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Excel Yükleme Formu -->
            <form id="excelUploadForm" class="relative flex items-center">
                <input type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" class="hidden" />
                <button type="button" id="excelUploadButton" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs transition">
                    <i class="fas fa-file-excel mr-2"></i> Excel Yükle
                </button>
                <span id="selectedFileName" class="absolute top-full left-0 mt-1 text-xs text-gray-600 whitespace-nowrap"></span>
            </form>
            
            <!-- Excel İndir Butonu -->
            <button type="button" id="excelDownloadButton" onclick="exportToExcel()" class="flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-xs transition">
                <i class="fas fa-download mr-2"></i> Excel İndir
            </button>

            <!-- Branş Ekle Butonu -->
            <button onclick="openCreateBranchModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Branş Ekle
            </button>

            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Sil
            </button>
        </div>
    </div>
   
    <!-- Branşlar Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden flex-1 min-h-0 flex flex-col">
        <div class="overflow-x-auto flex-1 min-h-0">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3">
                        </th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">İD</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[90px]">BRANŞ KODU</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[150px]">BRANŞ ADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($branches && count($branches) > 0): ?>
                    <?php foreach ($branches as $branch): ?>
                    <tr>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            <input type="checkbox" class="branch-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo $branch['id']; ?>">
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo $branch['id']; ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo $branch['code']; ?></td>
                        <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo $branch['name']; ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                            <div class="flex items-center space-x-2">
                                <button onclick="openPreviewBranchModal(<?php echo $branch['id']; ?>)" class="text-green-500 hover:text-green-700" title="Önizleme">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                <button onclick="openEditBranchModal(<?php echo $branch['id']; ?>)" class="text-blue-500 hover:text-blue-700" title="Düzenle">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="openDeleteBranchModal(<?php echo $branch['id']; ?>, '<?php echo htmlspecialchars($branch['name'], ENT_QUOTES); ?>')" class="text-red-500 hover:text-red-700" title="Sil">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-2 py-1.5 text-center text-gray-500 text-xs">
                                Henüz kayıtlı branş bulunmuyor.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
            <p class="text-sm text-gray-600">
                Toplam <span class="font-semibold"><?php echo (int)$totalBranches; ?></span> kayıt,
                Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
            </p>
            <div class="flex items-center gap-1">
                <?php
                    $baseUrl = BASE_URL . '/php/admin_panel.php?action=branches'
                        . '&search=' . urlencode($search)
                        . '&sort=' . urlencode($sortColumn)
                        . '&direction=' . urlencode($sortDirection)
                        . '&per_page=' . (int)$perPage;
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
    </div>
</div>

<!-- Yeni Branş Modal -->
<div id="createBranchModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-plus text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Yeni Branş Ekle</h3>
            <p class="text-sm text-blue-100">Branş bilgilerini doldurun</p>
          </div>
        </div>
        <button onclick="closeCreateBranchModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
      <form id="createBranchForm">
        <input type="hidden" id="branchId" name="branch_id">
        
        <!-- Branş Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-chalkboard-teacher text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Branş Bilgileri</h4>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-hashtag mr-2 text-blue-500"></i>Branş Kodu <span class="text-red-500 ml-1">*</span>
              </label>
              <input id="branchCode" name="code" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="örn. MA" required>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-chalkboard-teacher mr-2 text-blue-500"></i>Branş Adı <span class="text-red-500 ml-1">*</span>
              </label>
              <input id="branchName" name="name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Matematik" required>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-toggle-on mr-2 text-blue-500"></i>Durum
              </label>
              <select id="branchStatus" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <option value="active">Aktif</option>
                <option value="inactive">Pasif</option>
              </select>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeCreateBranchModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitCreateBranch()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Kaydet
      </button>
    </div>
  </div>
</div>

<!-- Branş Önizleme Modal -->
<div id="previewBranchModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-chalkboard-teacher text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Branş Önizleme</h3>
            <p class="text-sm text-blue-100">Branş detayları</p>
          </div>
        </div>
        <button onclick="closePreviewBranchModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6 bg-gray-50">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h2 id="previewBranchName" class="text-2xl font-bold text-gray-900 mb-2">-</h2>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <div class="flex items-center space-x-1">
                <i class="fas fa-hashtag text-blue-500"></i>
                <span class="font-medium">Branş Kodu:</span>
                <span id="previewBranchCode" class="text-gray-900 font-semibold">-</span>
              </div>
            </div>
          </div>
          <span id="previewBranchStatus" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 flex items-center space-x-1">
            <i class="fas fa-circle text-xs"></i>
            <span>-</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closePreviewBranchModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>Kapat
      </button>
    </div>
  </div>
</div>

<!-- Branş Düzenleme Modal -->
<div id="editBranchModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-edit text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Branş Düzenle</h3>
            <p class="text-sm text-blue-100">Branş bilgilerini güncelleyin</p>
          </div>
        </div>
        <button onclick="closeEditBranchModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
      <form id="editBranchForm">
        <input type="hidden" id="editBranchId" name="branch_id">
        
        <!-- Branş Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-chalkboard-teacher text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Branş Bilgileri</h4>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-hashtag mr-2 text-blue-500"></i>Branş Kodu
              </label>
              <input id="editBranchCode" name="code" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="örn. MA">
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-chalkboard-teacher mr-2 text-blue-500"></i>Branş Adı <span class="text-red-500 ml-1">*</span>
              </label>
              <input id="editBranchName" name="name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Matematik" required>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-toggle-on mr-2 text-blue-500"></i>Durum
              </label>
              <select id="editBranchStatus" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <option value="active">Aktif</option>
                <option value="inactive">Pasif</option>
              </select>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeEditBranchModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitEditBranch()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Güncelle
      </button>
    </div>
  </div>
</div>

<!-- SheetJS ve SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function clearSearch() {
    window.location.href = '?action=branches';
}

document.addEventListener('DOMContentLoaded', function() {
    const excelFile = document.getElementById('excelFile');
    const excelUploadButton = document.getElementById('excelUploadButton');
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    excelUploadButton.addEventListener('click', () => excelFile.click());
    
    // Arama inputuna Enter tuşu desteği
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
    
    // Excel dosyası seçildiğinde oku ve yükle
    excelFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(fileExt)) {
            Swal.fire('Geçersiz dosya', 'Lütfen sadece .xlsx veya .xls dosyaları yükleyin.', 'error');
            this.value = '';
            return;
        }
        
        // Loading
        Swal.fire({
            title: 'İşleniyor...',
            text: 'Excel dosyası okunuyor',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        const reader = new FileReader();
        // Yardımcı: Başlık normalize edici (boşluk, aksan ve büyük harf)
        const normalizeHeader = (h) => {
            if (h === undefined || h === null) return '';
            // NBSP -> space, fazla boşlukları tek boşluğa indir
            let s = String(h).replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
            // Büyük harfe çevir
            s = s.toUpperCase();
            // Aksan/kombine işaretlerini kaldır
            s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            return s;
        };

        reader.onload = function(evt) {
            try {
                const data = new Uint8Array(evt.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
                
                if (jsonData.length < 2) {
                    throw new Error('Excel en az bir başlık ve bir veri satırı içermelidir.');
                }
                
                const headersRaw = jsonData[0].map(h => (h ?? '').toString());
                const headersNorm = headersRaw.map(normalizeHeader);

                // Beklenen normalize başlıklar
                const EXPECT_CODE = 'BRANS KODU';
                const EXPECT_NAME = 'BRANS ADI';

                // Esnek eşleştirme: tam eşleşme ya da içerme
                const codeIdx = headersNorm.findIndex(h => h === EXPECT_CODE || /(^|\s)KOD(U)?(\s|$)/.test(h));
                const nameIdx = headersNorm.findIndex(h => h === EXPECT_NAME || /(^|\s)AD(I)?(\s|$)/.test(h));

                if (codeIdx === -1 || nameIdx === -1) {
                    // Kullanıcıya tespit edilen başlıkları göster
                    const detected = headersRaw.join(', ');
                    throw new Error('Gerekli sütunlar eksik: BRANŞ KODU, BRANŞ ADI. Tespit edilen başlıklar: ' + detected);
                }
                
                const formatted = jsonData
                    .slice(1)
                    .filter(row => row && row.length > 0)
                    .map(row => ({
                        branch_code: (row[codeIdx] ?? '').toString().trim(),
                        name: (row[nameIdx] ?? '').toString().trim()
                    }))
                    .filter(item => item.name && item.branch_code);
                
                if (formatted.length === 0) {
                    throw new Error('Excel dosyasında geçerli veri bulunamadı.');
                }
                
                // Sunucuya gönder
                csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: 'bulk_add_branches', branches: formatted })
                })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(res => {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'İşlem Tamamlandı!',
                            text: res.message || 'Branşlar eklendi',
                            confirmButtonColor: '#3085d6'
                        }).then(() => { 
                            document.getElementById('excelFile').value = '';
                            location.reload(); 
                        });
                    } else {
                        let html = '<div style="text-align:left">';
                        html += `<p style="color:#dc2626;font-weight:bold;">${res.message || 'İşlem başarısız'}</p>`;
                        if (res.errors && res.errors.length) {
                            html += '<hr style="margin:12px 0;" /><strong>Hatalar:</strong><ul style="margin-top:8px; padding-left:18px;">';
                            res.errors.forEach(err => html += `<li style="margin:4px 0;color:#dc2626;">${err}</li>`);
                            html += '</ul>';
                        }
                        html += '</div>';
                        Swal.fire({ icon: 'error', title: 'Hata', html, width: '620px' });
                    }
                })
                .catch(err => {
                    console.error('Upload error:', err);
                    Swal.fire('Hata', 'İstek sırasında hata: ' + err.message, 'error');
                });
            } catch (e) {
                console.error('Excel parse error:', e);
                Swal.fire('Hata', e.message, 'error');
            }
        };
        reader.onerror = function() {
            Swal.fire('Hata', 'Dosya okunamadı', 'error');
        };
        reader.readAsArrayBuffer(file);
    });

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.branch-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Her checkbox için change event listener ekle
    document.querySelectorAll('.branch-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            // Tüm checkbox'lar seçili mi kontrol et
            const allCheckboxes = document.querySelectorAll('.branch-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.branch-checkbox:checked');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
            }
        });
    });
    
    // Sayfa yüklendiğinde başlangıç durumunu ayarla
    updateSelectedCount();

    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.branch-checkbox:checked')).map(cb => parseInt(cb.dataset.id));
            if (selectedIds.length === 0) {
                Swal.fire('Uyarı', 'Lütfen silmek istediğiniz branşları seçin.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Emin misiniz?',
                text: `${selectedIds.length} branşı silmek istediğinizden emin misiniz?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, sil!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Loading göster
                    Swal.fire({
                        title: 'İşleniyor...',
                        text: 'Branşlar siliniyor',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // AJAX ile silme işlemi
                    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_selected_branches', branch_ids: selectedIds })
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Başarılı!', `${selectedIds.length} branş başarıyla silindi.`, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Hata', data.message || 'Branşlar silinirken bir hata oluştu.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error');
                    });
                }
            });
        });
    }

});

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.branch-checkbox:checked').length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (selectedCount === 0) {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = `<i class="fas fa-trash mr-2"></i> Sil`;
    } else {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = `<i class="fas fa-trash mr-2"></i> Sil`;
    }
}

function openCreateBranchModal() {
    const modal = document.getElementById('createBranchModal');
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
}

function closeCreateBranchModal() {
    const modal = document.getElementById('createBranchModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitCreateBranch() {
    const form = document.getElementById('createBranchForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = {};
    for (let [key, value] of formData.entries()) {
        payload[key] = value.trim();
    }
    console.log('Form payload:', payload);
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'create_branch', ...payload })
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Branş eklendi', 'success').then(() => { closeCreateBranchModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Branş eklenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openEditBranchModal(id) {
    // Önce branş bilgilerini yükle
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_branch&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.branch) {
                const branch = data.branch;
                // Form alanlarını doldur
                document.getElementById('editBranchId').value = branch.id;
                document.getElementById('editBranchCode').value = branch.code || '';
                document.getElementById('editBranchName').value = branch.name || '';
                document.getElementById('editBranchStatus').value = branch.status || 'active';
                // Modalı aç
                const modal = document.getElementById('editBranchModal');
                if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else {
                Swal.fire('Hata', 'Branş bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Load branch error:', err);
            Swal.fire('Hata', 'Branş bilgileri yüklenirken bir hata oluştu.', 'error');
        });
}

function closeEditBranchModal() {
    const modal = document.getElementById('editBranchModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitEditBranch() {
    const form = document.getElementById('editBranchForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    const branchId = payload.branch_id;
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_branch', branch_id: branchId, ...payload })
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Branş güncellendi', 'success').then(() => { closeEditBranchModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Branş güncellenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openDeleteBranchModal(id, name) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${name} adlı branşı silmek istediğinizden emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Loading göster
            Swal.fire({
                title: 'İşleniyor...',
                text: 'Branş siliniyor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Silme isteğini gönder
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'delete_branch',
                    branch_id: id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: data.message || 'Branş başarıyla silindi.',
                        confirmButtonColor: '#6C4FFE'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: data.message || 'Branş silinirken bir hata oluştu.',
                        confirmButtonColor: '#6C4FFE'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Bir iletişim hatası oluştu: ' + error.message,
                    confirmButtonColor: '#6C4FFE'
                });
            });
        }
    });
}

function closeBranchModal() {
    document.getElementById('branchModal').classList.add('hidden');
}

function toggleBranchStatus(toggle, branchId) {
    const newStatus = toggle.checked ? 'active' : 'inactive';
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_branch_status', branch_id: branchId, status: newStatus })
    }).then(r => r.json()).then(res => {
        if (!res.success) {
            toggle.checked = !toggle.checked;
            Swal.fire('Hata', 'Durum güncellenemedi.', 'error');
        }
    }).catch(() => {
        toggle.checked = !toggle.checked;
        Swal.fire('Hata', 'Bir hata oluştu.', 'error');
    });
}

function openPreviewBranchModal(id) {
    // Önce branş bilgilerini yükle
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_branch&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.branch) {
                const branch = data.branch;
                // Önizleme modalını doldur ve aç
                document.getElementById('previewBranchCode').textContent = branch.code || '-';
                document.getElementById('previewBranchName').textContent = branch.name || '-';
                const statusEl = document.getElementById('previewBranchStatus');
                if (statusEl) {
                    statusEl.innerHTML = branch.status === 'active' 
                        ? '<i class="fas fa-circle text-xs text-green-500"></i><span>Aktif</span>'
                        : '<i class="fas fa-circle text-xs text-red-500"></i><span>Pasif</span>';
                    statusEl.className = branch.status === 'active' 
                        ? 'px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 flex items-center space-x-1'
                        : 'px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center space-x-1';
                }
                // Modalı aç
                const modal = document.getElementById('previewBranchModal');
                if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else {
                Swal.fire('Hata', 'Branş bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Load branch error:', err);
            Swal.fire('Hata', 'Branş bilgileri yüklenirken bir hata oluştu.', 'error');
        });
}

function closePreviewBranchModal() {
    const modal = document.getElementById('previewBranchModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function exportToExcel() {
    // Loading göster
    Swal.fire({
        title: 'İndiriliyor...',
        text: 'Excel dosyası hazırlanıyor',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Backend'den tüm branşları al
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=export_branches', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Sunucu yanıt vermedi');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Veri alınamadı');
        }
        
        const branches = data.branches || [];
        
        if (branches.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Dikkat',
                text: 'İndirilecek veri bulunamadı.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Başlıkları oluştur
        const headers = ['BRANŞ KODU', 'BRANŞ ADI', 'DURUM', 'KAYIT TARİHİ'];
        
        // Veri matrisi oluştur
        const excelData = branches.map(branch => [
            branch.code || '',
            branch.name || '',
            branch.status === 'active' ? 'Aktif' : 'Pasif',
            branch.created_at ? new Date(branch.created_at).toLocaleDateString('tr-TR') : ''
        ]);
        
        // Başlıkları ve verileri birleştir
        const worksheetData = [headers, ...excelData];
        
        // SheetJS ile workbook oluştur
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(worksheetData);
        
        // Sütun genişliklerini ayarla
        const maxWidths = headers.map((header, colIndex) => {
            const maxLength = Math.max(
                header.length,
                ...excelData.map(row => row[colIndex] ? row[colIndex].toString().length : 0)
            );
            return { wch: Math.min(Math.max(maxLength, 10), 50) };
        });
        ws['!cols'] = maxWidths;
        
        XLSX.utils.book_append_sheet(wb, ws, 'Branşlar');
        
        // Dosyayı indir
        const today = new Date().toISOString().split('T')[0];
        const filename = `Branslar_Listesi_${today}.xlsx`;
        XLSX.writeFile(wb, filename);
        
        Swal.close();
        
        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: `${branches.length} branş Excel dosyasına aktarıldı.`,
            confirmButtonColor: '#3085d6',
            timer: 2000
        });
        
    })
    .catch(error => {
        console.error('Export error:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: error.message || 'Excel dosyası oluşturulurken bir hata oluştu.',
            confirmButtonColor: '#6C4FFE'
        });
    });
}
</script>
