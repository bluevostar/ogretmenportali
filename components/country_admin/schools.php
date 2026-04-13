<?php
// Okulları getir
$schools = $viewModel->getAllSchools();

// Okul yöneticilerini getir
$schoolAdmins = $viewModel->getSchoolAdmins($_SESSION['user_id']);
error_log("School Admins Result: " . print_r($schoolAdmins, true));
error_log("Session user_id: " . $_SESSION['user_id']);

// Mevcut okul türlerini topla
$schoolTypes = [];
foreach ($schools as $school) {
    if (!empty($school['school_type']) && !in_array($school['school_type'], $schoolTypes)) {
        $schoolTypes[] = $school['school_type'];
    }
}
// Okul türlerini alfabetik olarak sırala
sort($schoolTypes);
?>

<div class="content-area">
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title mb-4 md:mb-0">Okul Yönetimi</h1>
        

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-3">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="schools">
                <div class="flex w-full">
                    <input type="text" name="search" placeholder="Okul ara..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
              
            <!-- Excel Yükleme Formu -->
            <form id="excelUploadForm" class="flex items-center gap-3">
                <input type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" class="hidden" />
                <button type="button" id="excelUploadButton" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs transition">
                    <i class="fas fa-file-excel mr-2"></i> Excel Yükle
                </button>
                <span id="selectedFileName" class="text-sm text-gray-600"></span>
            </form>
            
            <!-- Okul Ekle Butonu -->
            <button onclick="showSchoolModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Okul
            </button>

            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Seçili Okulları Sil
            </button>
        </div>
    </div>

    <!-- Okullar Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KURUM KODU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OKUL ADI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OKUL TÜRÜ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İLÇE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DURUM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($schools && count($schools) > 0): ?>
                        <?php foreach ($schools as $school): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="school-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500" data-id="<?php echo $school['id']; ?>">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $school['school_code']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $school['name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo empty($school['school_type']) ? 'Belirtilmemiş' : htmlspecialchars($school['school_type']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($school['city'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($school['country'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer" title="Durumu Değiştir">
                                    <input type="checkbox" value="" class="sr-only peer" <?php echo $school['status'] === 'active' ? 'checked' : ''; ?> onchange="toggleSchoolStatus(this, <?php echo $school['id']; ?>)">
                                    <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-blue-300 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center space-x-3">
                                    <button onclick="openEditSchoolModal(<?php echo $school['id']; ?>)" class="text-blue-500 hover:text-blue-700" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="openDeleteSchoolModal(<?php echo $school['id']; ?>)" class="text-red-500 hover:text-red-700" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button onclick="openPreviewSchoolModal(<?php echo $school['id']; ?>)" class="text-green-500 hover:text-green-700" title="Önizleme">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Henüz kayıtlı okul bulunmuyor.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>



    <!-- Okul Ekleme/Düzenleme Modal -->
    <div id="schoolModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="schoolForm" method="POST" class="w-full">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">
                                Yeni Okul Ekle
                            </h3>
                        </div>

                        <input type="hidden" id="schoolId" name="school_id">

                        <div class="mb-4">
                            <label for="schoolCode" class="block text-gray-700 text-sm font-bold mb-2">
                                Kurum Kodu
                            </label>
                            <input type="text" name="school_code" id="schoolCode" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label for="schoolName" class="block text-gray-700 text-sm font-bold mb-2">
                                Okul Adı
                            </label>
                            <input type="text" name="name" id="schoolName" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label for="schoolType" class="block text-gray-700 text-sm font-bold mb-2">
                                Okul Türü
                            </label>
                            <select name="school_type" id="schoolType" required 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seçiniz</option>
                                <?php foreach ($schoolTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="schoolCity" class="block text-gray-700 text-sm font-bold mb-2">
                                İl
                            </label>
                            <input type="text" name="city" id="schoolCity"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label for="schoolCountry" class="block text-gray-700 text-sm font-bold mb-2">
                                İlçe
                            </label>
                            <input type="text" name="country" id="schoolCountry"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label for="schoolAddress" class="block text-gray-700 text-sm font-bold mb-2">
                                Adres
                            </label>
                            <textarea name="address" id="schoolAddress" rows="3" 
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>

                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Kaydet
                        </button>
                        <button type="button" onclick="closeSchoolModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Silme Onay Modalı -->
    <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Okulu Sil
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modal-content">
                                    Bu okulu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeDeleteModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        İptal
                    </button>
                    <form id="deleteForm" method="post">
                        <input type="hidden" name="school_id" id="deleteSchoolId">
                        <input type="hidden" name="delete_school" value="1">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Sil
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
                    <p class="text-sm mt-1">KURUM KODU ve OKUL ADI alanları zaten sistemde varsa, bu kayıtlar atlanacaktır.</p>
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

    <!-- Silme Onay Modalı -->
    <div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2 text-center">Okulları Sil</h3>
                <p class="text-sm text-gray-500 mb-6" id="deleteConfirmMessage">Seçilen okulları silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="closeDeleteConfirmModal()" class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        İptal
                    </button>
                    <button id="confirmDeleteBtn" type="button" class="px-4 py-2 text-xs font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
                        Evet, Sil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Okul Önizleme Modal -->
    <div id="previewSchoolModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-6 border shadow-lg rounded-xl bg-white max-w-2xl">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-2xl font-bold text-gray-900" id="previewModalTitle">Okul Detayları</h3>
                <button type="button" onclick="closePreviewSchoolModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
            <div class="mt-4" id="previewSchoolContent">
                <!-- Okul bilgileri buraya dinamik olarak yüklenecek -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary-500"></i>
                    <p class="mt-2 text-gray-600">Veriler yükleniyor...</p>
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
                    if (!headers.includes('KURUM KODU') || !headers.includes('OKUL ADI')) {
                        throw new Error('Excel dosyası gerekli sütunları içermiyor. Gerekli sütunlar: KURUM KODU, OKUL ADI');
                    }

                    // Verileri formatla
                    const kodIndex = headers.indexOf('KURUM KODU');
                    const adIndex = headers.indexOf('OKUL ADI');
                    const turIndex = headers.indexOf('OKUL TÜRÜ');

                    const formattedData = jsonData.slice(1)
                        .filter(row => row.length > 0) // Boş satırları filtrele
                        .map(row => ({
                            okul_kodu: row[kodIndex]?.toString().trim() || '',
                            okul_adi: row[adIndex]?.toString().trim() || '',
                            type: row[turIndex]?.toString().trim() || 'Belirtilmemiş'
                        }))
                        .filter(school => school.okul_kodu && school.okul_adi);

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

        // Seçili okulları silme butonu
        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener('click', handleDeleteSelected);
        }

        // Tümünü seç checkbox'ı
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.school-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });
        }

        // Tekil checkbox'lar
        document.querySelectorAll('.school-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                
                // Tüm checkbox'lar seçili mi kontrol et
                const allCheckboxes = document.querySelectorAll('.school-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.school-checkbox:checked');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length && allCheckboxes.length > 0;
                }
            });
        });

        // Form submit olayını dinle
        document.getElementById('schoolForm').addEventListener('submit', handleSchoolFormSubmit);
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

        return csrfFetch('<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=bulk_add_schools', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                schools: formattedData
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
                    text: data.message || 'Okullar başarıyla yüklendi',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    // Formu temizle ve sayfayı yenile
                    document.getElementById('excelFile').value = '';
                    document.getElementById('selectedFileName').textContent = '';
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Excel verileri yüklenirken bir hata oluştu.');
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

    // Okul formu gönderimi
    function handleSchoolFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('schoolId').value;
        
        csrfFetch(`<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=${id ? 'update_school' : 'add_school'}`, {
            method: 'POST',
            body: formData
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
                    text: id ? 'Okul başarıyla güncellendi.' : 'Okul başarıyla eklendi.',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'İşlem sırasında bir hata oluştu');
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

    // Seçili okulları silme işlemi
    function handleDeleteSelected() {
        const selectedSchools = Array.from(document.querySelectorAll('.school-checkbox:checked')).map(checkbox => checkbox.dataset.id);
        
        if (selectedSchools.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Seçim yapılmadı',
                text: 'Lütfen silmek istediğiniz okulları seçin.',
                confirmButtonColor: '#6C4FFE'
            });
            return;
        }

        const confirmMessage = selectedSchools.length === 1 
            ? 'Bu okulu silmek istediğinizden emin misiniz?' 
            : `${selectedSchools.length} okulu silmek istediğinizden emin misiniz?`;

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
                    text: 'Okullar siliniyor',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Silme isteğini gönder
                csrfFetch('<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=delete_selected_schools', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        school_ids: selectedSchools
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
                            text: selectedSchools.length === 1 ? 'Okul başarıyla silindi.' : 'Seçili okullar başarıyla silindi.',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Silme işlemi sırasında bir hata oluştu');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: error.message || 'Silme işlemi sırasında bir hata oluştu',
                        confirmButtonColor: '#3085d6'
                    });
                });
            }
        });
    }

    // Seçili okul sayısını güncelleme fonksiyonu
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.school-checkbox:checked').length;
        const deleteButton = document.getElementById('deleteSelectedBtn');
        
        // Silme butonunu güncelle
        if (selectedCount === 0) {
            deleteButton.disabled = true;
            deleteButton.classList.add('opacity-50', 'cursor-not-allowed');
            deleteButton.innerHTML = '<i class="fas fa-trash mr-2"></i> Seçili Okulları Sil';
        } else {
            deleteButton.disabled = false;
            deleteButton.classList.remove('opacity-50', 'cursor-not-allowed');
            deleteButton.innerHTML = `<i class="fas fa-trash mr-2"></i> Seçili Okulları Sil (${selectedCount})`;
        }
    }

    // Modal kapatma fonksiyonu
    function closeSchoolModal() {
        document.getElementById('schoolModal').classList.add('hidden');
    }

    // Okul düzenleme modalı
    function openEditSchoolModal(id) {
        document.getElementById('modalTitle').textContent = 'Okul Düzenle';
        document.getElementById('schoolId').value = id;
        
        // Okul bilgilerini getir
        csrfFetch(`<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_school&id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('schoolCode').value = data.school_code;
                document.getElementById('schoolName').value = data.name;
                document.getElementById('schoolCity').value = data.city;
                document.getElementById('schoolCountry').value = data.country;
                document.getElementById('schoolType').value = data.school_type;
                document.getElementById('schoolAddress').value = data.address;
                document.getElementById('schoolAdmin').value = data.admin_id;
                document.getElementById('schoolModal').classList.remove('hidden');
            });
    }

    // Okul silme modalı
    function openDeleteSchoolModal(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu okulu silmek istediğinizden emin misiniz?',
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
                    text: 'Okul siliniyor',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Silme isteğini gönder
                csrfFetch(`<?php echo BASE_URL; ?>/php/admin_panel.php?action=delete_school`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest' // AJAX isteği olduğunu belirt
                    },
                    body: JSON.stringify({
                        school_id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı!',
                            text: 'Okul başarıyla silindi.',
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

    // Direkt yükleme butonu için event listener
    document.getElementById('directUploadButton').addEventListener('click', function() {
        const fileInput = document.getElementById('excelFile');
        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Uyarı',
                text: 'Lütfen önce bir Excel dosyası seçin.',
                confirmButtonColor: '#6C4FFE'
            });
            return;
        }
        
        // Dosya zaten seçili ve işlenmiş olacak, ek bir işlem yapmaya gerek yok
    });

    function openPreviewSchoolModal(schoolId) {
        const modal = document.getElementById('previewSchoolModal');
        const content = document.getElementById('previewSchoolContent');
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin fa-3x text-primary-500"></i><p class="mt-2 text-gray-600">Veriler yükleniyor...</p></div>';

        csrfFetch(`../php/get_school_details.php?id=${schoolId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.school) {
                    const school = data.school;
                    let detailsHtml = '<dl class="divide-y divide-gray-200">';
                    for (const key in school) {
                        detailsHtml += `
                            <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500 capitalize">${key.replace(/_/g, ' ')}</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">${school[key] || 'N/A'}</dd>
                            </div>
                        `;
                    }
                    detailsHtml += '</dl>';
                    content.innerHTML = detailsHtml;
                } else {
                    content.innerHTML = `<p class="text-red-500">${data.message || 'Okul bilgileri alınamadı.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching school details:', error);
                content.innerHTML = '<p class="text-red-500">Bir hata oluştu. Lütfen tekrar deneyin.</p>';
            });
    }

    function closePreviewSchoolModal() {
        document.getElementById('previewSchoolModal').classList.add('hidden');
    }
    </script>