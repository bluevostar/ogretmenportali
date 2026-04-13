<?php
// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sortDirection = isset($_GET['direction']) ? $_GET['direction'] : 'asc';

// Get branches with pagination and sorting
$branches = $viewModel->getAllBranches($page, $perPage, $sortColumn, $sortDirection);
$totalBranches = $viewModel->getTotalBranchesCount();
$totalPages = ceil($totalBranches / $perPage);

?>

<div class="content-area">
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title mb-4 md:mb-0">Branş Yönetimi</h1>

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-3">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="branches">
                <div class="flex w-full">
                    <input type="text" name="search" placeholder="Branş ara..." 
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
              
            <!-- Branş Ekle Butonu -->
            <button id="addBranchButton" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Branş Ekle
            </button>
            
            <!-- Excel Yükleme Butonu -->
            <form id="excelUploadForm" class="flex items-center">
                <input type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" class="hidden" />
                <button type="button" id="excelUploadButton" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs transition">
                    <i class="fas fa-file-excel mr-2"></i>
                    Excel Yükle
                </button>
            </form>

            <!-- Toplu Silme Butonu -->
            <button id="bulkDeleteBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash-alt mr-2"></i>
                Seçilenleri Sil
            </button>
        </div>
    </div>

    <!-- Branşlar Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BRANŞ KODU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BRANŞ ADI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DURUM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KAYIT TARİHİ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">İŞLEMLER</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($branches && count($branches) > 0): ?>
                    <?php foreach ($branches as $branch): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="branch-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-id="<?php echo $branch['id']; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($branch['id']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($branch['code']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($branch['name']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $branch['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $branch['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d.m.Y', strtotime($branch['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <button onclick="openEditBranchModal(<?php echo $branch['id']; ?>)" class="text-blue-600 hover:text-blue-900" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="showDeleteModal(<?php echo $branch['id']; ?>, '<?php echo htmlspecialchars($branch['name']); ?>')" class="text-red-600 hover:text-red-900" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Henüz kayıtlı branş bulunmuyor.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-700">
                        Toplam <span class="font-medium"><?php echo $totalBranches; ?></span> branş,
                        Sayfa <span class="font-medium"><?php echo $page; ?></span> / <span class="font-medium"><?php echo $totalPages; ?></span>
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?action=branches&page=<?php echo ($page - 1); ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-xs font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="?action=branches&page=<?php echo $i; ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-xs font-medium <?php echo $i === $page ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?action=branches&page=<?php echo ($page + 1); ?>&sort=<?php echo $sortColumn; ?>&direction=<?php echo $sortDirection; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-xs font-medium text-gray-500 hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Branş Ekleme/Düzenleme Modal -->
<div id="branchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border shadow-lg rounded-xl bg-white max-w-5xl">
        <div class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden">
            <!-- Sol taraf - Form bölümü -->
            <div class="w-full p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900" id="modalTitle">Yeni Branş Ekle</h3>
                    <button type="button" onclick="closeBranchModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="branchForm" class="space-y-4" onsubmit="handleBranchFormSubmit(event)">
                    <input type="hidden" id="branchId" name="branch_id">
                    
                    <div class="mb-4">
                        <label for="branchCode" class="block text-sm font-medium text-gray-700 mb-2">Branş Kodu</label>
                        <input type="text" id="branchCode" name="code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="branchName" class="block text-sm font-medium text-gray-700 mb-2">Branş Adı</label>
                        <input type="text" id="branchName" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i> Kaydet
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Excel Önizleme Modal -->
<div id="excelPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="previewModalTitle">Excel Önizleme</h3>
                <button type="button" onclick="closeExcelPreviewModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded-md text-yellow-800">
                <p class="text-sm"><i class="fas fa-info-circle mr-2"></i> Lütfen verileri kontrol edin. Sadece geçerli veriler eklenecektir.</p>
                <p class="text-sm mt-1">BRANŞ KODU ve BRANŞ ADI alanları zaten sistemde varsa, bu kayıtlar atlanacaktır.</p>
            </div>
            
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr id="excelPreviewTableHead">
                            <!-- Excel sütun başlıkları burada dinamik olarak oluşturulacak -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="excelPreviewTableBody">
                        <!-- Excel verileri burada dinamik olarak oluşturulacak -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mt-4">
                <button id="cancelExcelUploadBtn" onclick="closeExcelPreviewModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors mr-3">
                    <i class="fas fa-times mr-2"></i> İptal
                </button>
                <button id="submitExcelDataBtn" class="bg-primary-500 text-white px-4 py-2 rounded-lg hover:bg-primary-600 transition-colors">
                    <i class="fas fa-upload mr-2"></i> Verileri Aktar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı - Tekli Silme -->
<div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal içeriği -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Branşı Sil
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-content">
                                Bu branşı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form method="post">
                    <input type="hidden" name="branch_id" id="deleteBranchId">
                    <button type="submit" name="delete_branch" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Sil
                    </button>
                </form>
                <button type="button" onclick="hideDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    İptal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Çoklu Silme Onay Modalı -->
<div id="bulkDeleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal içeriği -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Seçilen Branşları Sil
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="bulk-modal-content">
                                Seçilen branşları silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button id="confirmBulkDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Sil
                </button>
                <button type="button" onclick="hideBulkDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    İptal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SheetJS Kütüphanesi -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<!-- SweetAlert2 Kütüphanesi -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global değişkenler
let excelData = [];
let excelHeaders = [];

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    const excelUploadForm = document.getElementById('excelUploadForm');
    const excelFile = document.getElementById('excelFile');
    const excelUploadButton = document.getElementById('excelUploadButton');
    const selectedFileName = document.getElementById('selectedFileName');

    // Excel seçme butonu
    excelUploadButton.addEventListener('click', function() {
        excelFile.click();
    });

    // Dosya seçildiğinde
    excelFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) {
            selectedFileName.textContent = '';
            return;
        }

        // Dosya uzantısını kontrol et
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(fileExt)) {
            Swal.fire({
                icon: 'error',
                title: 'Geçersiz Dosya',
                text: 'Lütfen sadece .xlsx veya .xls uzantılı Excel dosyaları yükleyin.',
                confirmButtonColor: '#6C4FFE'
            });
            this.value = '';
            selectedFileName.textContent = '';
            return;
        }

        // Dosya adını göster
        selectedFileName.textContent = file.name;

        // Excel dosyasını oku
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                // Loading göster
                Swal.fire({
                    title: 'İşleniyor...',
                    text: 'Excel dosyası okunuyor',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

                // Veri kontrolü
                if (jsonData.length < 2) {
                    throw new Error('Excel dosyası en az bir başlık satırı ve bir veri satırı içermelidir.');
                }

                // Başlıkları kontrol et
                const headers = jsonData[0].map(h => String(h).trim().toUpperCase());
                if (!headers.includes('BRANŞ KODU') || !headers.includes('BRANŞ ADI')) {
                    throw new Error('Excel dosyası gerekli sütunları içermiyor. Gerekli sütunlar: BRANŞ KODU, BRANŞ ADI');
                }

                // Verileri formatla
                const kodIndex = headers.indexOf('BRANŞ KODU');
                const adIndex = headers.indexOf('BRANŞ ADI');

                const formattedData = jsonData.slice(1)
                    .filter(row => row.length > 0) // Boş satırları filtrele
                    .map(row => ({
                        branch_code: row[kodIndex]?.toString().trim() || '',
                        name: row[adIndex]?.toString().trim() || ''
                    }))
                    .filter(branch => branch.branch_code && branch.name);

                if (formattedData.length === 0) {
                    throw new Error('Excel dosyasında geçerli veri bulunamadı.');
                }

                // Verileri sunucuya gönder
                uploadExcelData(formattedData);
            } catch (error) {
                console.error('Excel processing error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: error.message,
                    confirmButtonColor: '#6C4FFE'
                });
                this.value = '';
                selectedFileName.textContent = '';
            }
        };

        reader.onerror = function() {
            console.error('FileReader error');
            Swal.fire({
                icon: 'error',
                title: 'Dosya Okuma Hatası',
                text: 'Excel dosyası okunurken bir hata oluştu.',
                confirmButtonColor: '#6C4FFE'
            });
            this.value = '';
            selectedFileName.textContent = '';
        };

        // Dosyayı oku
        reader.readAsArrayBuffer(file);
    });

    // Yeni branş ekleme butonu
    const addBranchButton = document.getElementById('addBranchButton');
    if (addBranchButton) {
        addBranchButton.addEventListener('click', function() {
            document.getElementById('branchModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Yeni Branş Ekle';
            document.getElementById('branchForm').reset();
            document.getElementById('branchId').value = '';
        });
    }

    // Seçili branşları silme butonu
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', handleDeleteSelected);
    }

    // Tümünü seç checkbox'ı
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.branch-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Tekil checkbox'lar
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

    // Form submit olayını dinle
    document.getElementById('branchForm').addEventListener('submit', handleBranchFormSubmit);

    // Tekli silme formunu AJAX ile gönder
    const deleteModalForm = document.querySelector('#deleteModal form');
    if (deleteModalForm) {
        deleteModalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const branchId = document.getElementById('deleteBranchId').value;
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `delete_branch=1&branch_id=${encodeURIComponent(branchId)}`
            })
            .then(response => response.json())
            .then(data => {
                hideDeleteModal();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: data.message,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: data.message || 'Branş silinirken bir hata oluştu.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            })
            .catch(error => {
                hideDeleteModal();
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Bir iletişim hatası oluştu.',
                    confirmButtonColor: '#3085d6'
                });
            });
        });
    }
});

// Excel verilerini sunucuya gönderme işlemi
function uploadExcelData(formattedData) {
    // Loading göster
    Swal.fire({
        title: 'İşleniyor...',
        text: 'Veriler yükleniyor',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    return csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=bulk_add_branches', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            branches: formattedData
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Sunucu yanıt vermedi');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Başarılı!',
                text: data.message,
                confirmButtonColor: '#6C4FFE'
            }).then(() => {
                // Formu temizle ve sayfayı yenile
                document.getElementById('excelFile').value = '';
                document.getElementById('selectedFileName').textContent = '';
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Veri yüklenirken bir hata oluştu.');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: error.message,
            confirmButtonColor: '#6C4FFE'
        });
    });
}

// Branş formu gönderimi
async function handleBranchFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const id = document.getElementById('branchId').value;

    try {
        // Loading göster
        Swal.fire({
            title: 'İşleniyor...',
            text: id ? 'Branş güncelleniyor' : 'Branş ekleniyor',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await csrfFetch(`<?php echo BASE_URL; ?>/php/admin_panel.php?action=${id ? 'update_branch' : 'add_branch'}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Sunucu yanıt vermedi');
        }

        const data = await response.json();
        
        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Başarılı!',
                text: id ? 'Branş başarıyla güncellendi.' : 'Branş başarıyla eklendi.',
                confirmButtonColor: '#6C4FFE'
            });
            location.reload();
        } else {
            throw new Error(data.message || 'Bir hata oluştu');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: error.message,
            confirmButtonColor: '#6C4FFE'
        });
    }
}

// Seçili branşları silme işlemi
function handleDeleteSelected() {
    const selectedBranches = Array.from(document.querySelectorAll('.branch-checkbox:checked')).map(checkbox => checkbox.dataset.id);
    
    if (selectedBranches.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Seçim yapılmadı',
            text: 'Lütfen silmek istediğiniz branşları seçin.',
            confirmButtonColor: '#6C4FFE'
        });
        return;
    }

    const confirmMessage = selectedBranches.length === 1 
        ? 'Bu branşı silmek istediğinizden emin misiniz?' 
        : `${selectedBranches.length} branşı silmek istediğinizden emin misiniz?`;

    Swal.fire({
        title: 'Emin misiniz?',
        text: confirmMessage,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Silme işlemi için loading göster
            Swal.fire({
                title: 'İşleniyor...',
                text: 'Branşlar siliniyor',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Silme isteğini gönder
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=delete_selected_branches', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    branch_ids: selectedBranches
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: selectedBranches.length === 1 ? 'Branş başarıyla silindi.' : 'Seçili branşlar başarıyla silindi.',
                        confirmButtonColor: '#6C4FFE'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Bir hata oluştu');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: error.message,
                    confirmButtonColor: '#6C4FFE'
                });
            });
        }
    });
}

// Seçili branş sayısını güncelleme fonksiyonu
function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.branch-checkbox:checked').length;
    const selectedCountInfo = document.getElementById('selectedCountInfo');
    const deleteButton = document.getElementById('deleteSelectedBtn');
    
    // Sayacı güncelle
    if (selectedCount === 0) {
        selectedCountInfo.textContent = '0 branş seçildi';
        deleteButton.disabled = true;
    } else if (selectedCount === 1) {
        selectedCountInfo.textContent = '1 branş seçildi';
        deleteButton.disabled = false;
    } else {
        selectedCountInfo.textContent = `${selectedCount} branş seçildi`;
        deleteButton.disabled = false;
    }
    
    // Silme butonunu güncelle
    if (selectedCount === 0) {
        deleteButton.innerHTML = '<i class="fas fa-trash mr-2"></i> Seçili Branşları Sil';
        deleteButton.disabled = true;
    } else if (selectedCount === 1) {
        deleteButton.innerHTML = '<i class="fas fa-trash mr-2"></i> Seçilen Branşı Sil';
        deleteButton.disabled = false;
    } else {
        deleteButton.innerHTML = `<i class="fas fa-trash mr-2"></i> ${selectedCount} Branşı Sil`;
        deleteButton.disabled = false;
    }
}

// Modal kapatma fonksiyonu
function closeBranchModal() {
    document.getElementById('branchModal').classList.add('hidden');
}

// Branş düzenleme modalı
function openEditBranchModal(id) {
    document.getElementById('modalTitle').textContent = 'Branş Düzenle';
    document.getElementById('branchId').value = id;
    
    // Branş bilgilerini getir
    csrfFetch(`<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_branch&id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('branchCode').value = data.code;
            document.getElementById('branchName').value = data.name;
            document.getElementById('status').value = data.status;
            document.getElementById('branchModal').classList.remove('hidden');
        });
}

// Tekli silme işlemi için modal
function showDeleteModal(branchId, branchName) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteBranchId').value = branchId;
    document.getElementById('modal-content').innerText = branchName + ' branşını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Toplu işlem için JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tüm öğeleri seçme/seçimi kaldırma
    const selectAllCheckbox = document.getElementById('selectAll');
    const branchCheckboxes = document.querySelectorAll('.branch-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    selectAllCheckbox.addEventListener('change', function() {
        branchCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    branchCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
        });
    });

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.branch-checkbox:checked');
        bulkDeleteBtn.disabled = checkedBoxes.length === 0;
        bulkDeleteBtn.classList.toggle('opacity-50', checkedBoxes.length === 0);
        bulkDeleteBtn.classList.toggle('cursor-not-allowed', checkedBoxes.length === 0);
    }

    // Toplu silme işlemi
    bulkDeleteBtn.addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.branch-checkbox:checked');
        if (checkedBoxes.length > 0) {
            showBulkDeleteModal();
        }
    });

    function showBulkDeleteModal() {
        document.getElementById('bulkDeleteModal').classList.remove('hidden');
    }

    window.hideBulkDeleteModal = function() {
        document.getElementById('bulkDeleteModal').classList.add('hidden');
    }

    document.getElementById('confirmBulkDelete').addEventListener('click', function() {
        const checkedBoxes = document.querySelectorAll('.branch-checkbox:checked');
        const branchIds = Array.from(checkedBoxes).map(checkbox => checkbox.dataset.id);
        
        // AJAX ile silme işlemi
        csrfFetch('<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=delete_branches', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ branch_ids: branchIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Branşlar silinirken bir hata oluştu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu.');
        });
    });
});
</script>