<?php
// Okulları getir
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filters = [];

if (!empty($search)) {
    $filters['search'] = $search;
}

$schools = $viewModel->getSchools($filters);
$schools = is_array($schools) ? $schools : [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$totalSchools = count($schools);
$totalPages = max(1, (int)ceil($totalSchools / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$schools = array_slice($schools, ($page - 1) * $perPage, $perPage);
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
?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">Okul Yönetimi</h1>
        
        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="schools">
                <div class="relative flex w-full">
                    <input type="text" name="search" id="searchInput" placeholder="Okul ara..." 
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

            <!-- Okul Ekle Butonu -->
            <button onclick="openCreateSchoolModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Okul Ekle
            </button>

            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Sil
            </button>
        </div>
    </div>

    <!-- Okullar Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden flex-1 min-h-0 flex flex-col">
        <div class="overflow-x-auto flex-1 min-h-0">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3">
                        </th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">İD</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[90px]">KURUM KODU</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[150px]">OKUL ADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">OKUL TÜRÜ</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[60px]">İL</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[70px]">İLÇE</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($schools && count($schools) > 0): ?>
                        <?php foreach ($schools as $school): ?>
                        <tr>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <input type="checkbox" class="school-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo $school['id']; ?>">
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo $school['id']; ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo $school['school_code'] ?? ''; ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo $school['name']; ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo empty($school['school_type']) ? 'Belirtilmemiş' : htmlspecialchars($school['school_type']); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($school['city'] ?? ''); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($school['county'] ?? ''); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openPreviewSchoolModal(<?php echo $school['id']; ?>)" class="text-green-500 hover:text-green-700" title="Önizleme">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button onclick="openEditSchoolModal(<?php echo $school['id']; ?>)" class="text-blue-500 hover:text-blue-700" title="Düzenle">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button onclick="openDeleteSchoolModal(<?php echo $school['id']; ?>)" class="text-red-500 hover:text-red-700" title="Sil">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-2 py-1.5 text-center text-gray-500 text-xs">
                                Henüz kayıtlı okul bulunmuyor.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
            <p class="text-sm text-gray-600">
                Toplam <span class="font-semibold"><?php echo (int)$totalSchools; ?></span> kayıt,
                Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
            </p>
            <div class="flex items-center gap-1">
                <?php
                    $baseUrl = BASE_URL . '/php/admin_panel.php?action=schools&search=' . urlencode((string)$search);
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

<!-- Yeni Okul Modal -->
<div id="createSchoolModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4 md:p-6">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-plus text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Yeni Okul Ekle</h3>
            <p class="text-sm text-green-100">Okul bilgilerini doldurun</p>
          </div>
        </div>
        <button onclick="closeCreateSchoolModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-4 bg-gray-50 flex-1 min-h-0 overflow-y-auto">
      <form id="createSchoolForm">
        <!-- Okul Adı ve Kod -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-school text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-3 space-y-3">
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-school mr-2 text-blue-500"></i>Okul Adı <span class="text-red-500 ml-1">*</span>
              </label>
              <input name="name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Okul adını girin" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-hashtag mr-2 text-blue-500"></i>Kurum Kodu
                </label>
                <input name="school_code" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="örn. 9003">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-graduation-cap mr-2 text-blue-500"></i>Okul Türü
                </label>
                <select id="createSchoolType" name="school_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <option value="">Okul türü seçin</option>
                  <?php foreach ($defaultSchoolTypes as $schoolTypeOption): ?>
                    <option value="<?php echo htmlspecialchars($schoolTypeOption); ?>"><?php echo htmlspecialchars($schoolTypeOption); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Konum Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Konum Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-city mr-2 text-blue-500"></i>İl
                </label>
                <select id="createSchoolCity" name="city" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <option value="">İl Seçiniz</option>
                </select>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                </label>
                <select id="createSchoolCounty" name="county" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" disabled>
                  <option value="">İlçe Seçiniz</option>
                </select>
              </div>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-road mr-2 text-blue-500"></i>Adres
              </label>
              <input name="address" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Tam adres bilgisi">
            </div>
          </div>
        </div>

        <!-- İletişim Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-phone-alt text-green-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">İletişim Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-phone mr-2 text-green-500"></i>Telefon
                </label>
                <input name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="Telefon numarası">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-envelope mr-2 text-green-500"></i>E-Posta
                </label>
                <input name="email" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="ornek@okul.meb.k12.tr">
              </div>
            </div>
          </div>
        </div>

        <!-- Açıklama -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-file-alt text-amber-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Açıklama</h4>
            </div>
          </div>
          <div class="p-3">
            <textarea name="description" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" rows="4" placeholder="Okul hakkında açıklama (isteğe bağlı)"></textarea>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeCreateSchoolModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitCreateSchool()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium hover:from-green-700 hover:to-emerald-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Kaydet
      </button>
    </div>
  </div>
</div>

<!-- Önizleme Modal -->
<div id="previewSchoolModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4 md:p-6">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-school text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Okul Önizleme</h3>
            <p class="text-sm text-blue-100">Okul detayları ve iletişim bilgileri</p>
          </div>
        </div>
        <button onclick="closePreviewSchoolModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6 bg-gray-50 flex-1 min-h-0 overflow-y-auto">
      <!-- Okul Başlık Kartı -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h2 id="previewSchoolName" class="text-2xl font-bold text-gray-900 mb-2">-</h2>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <div class="flex items-center space-x-1">
                <i class="fas fa-hashtag text-blue-500"></i>
                <span class="font-medium">Kurum Kodu:</span>
                <span id="previewSchoolCode" class="text-gray-900 font-semibold">-</span>
              </div>
            </div>
          </div>
          <span id="previewStatus" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 flex items-center space-x-1">
            <i class="fas fa-circle text-xs"></i>
            <span>-</span>
          </span>
        </div>
      </div>

      <!-- Grid Layout -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Sol Kolon -->
        <div class="space-y-6">
          <!-- Konum Bilgileri -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
              <div class="flex items-center space-x-2">
                <i class="fas fa-map-marker-alt text-blue-600"></i>
                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Konum Bilgileri</h4>
              </div>
            </div>
            <div class="p-5">
              <dl class="space-y-4">
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-city mr-2 text-blue-500"></i>İl
                  </dt>
                  <dd id="previewCity" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                  </dt>
                  <dd id="previewCounty" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
                <div class="flex items-start space-x-3 pt-3 border-t border-gray-100">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-road mr-2 text-blue-500"></i>Adres
                  </dt>
                  <dd id="previewAddress" class="text-sm text-gray-900 flex-1 leading-relaxed">-</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Okul Bilgileri -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-5 py-3 border-b border-gray-200">
              <div class="flex items-center space-x-2">
                <i class="fas fa-info-circle text-purple-600"></i>
                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Okul Bilgileri</h4>
              </div>
            </div>
            <div class="p-5">
              <dl class="space-y-4">
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-graduation-cap mr-2 text-purple-500"></i>Tür
                  </dt>
                  <dd id="previewSchoolType" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="space-y-6">
          <!-- İletişim -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-3 border-b border-gray-200">
              <div class="flex items-center space-x-2">
                <i class="fas fa-phone-alt text-green-600"></i>
                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">İletişim</h4>
              </div>
            </div>
            <div class="p-5">
              <dl class="space-y-4">
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-phone mr-2 text-green-500"></i>Telefon
                  </dt>
                  <dd class="flex-1">
                    <a id="previewPhoneLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                      <span id="previewPhone">-</span>
                    </a>
                  </dd>
                </div>
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-envelope mr-2 text-green-500"></i>E-Posta
                  </dt>
                  <dd class="flex-1">
                    <a id="previewEmailLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                      <span id="previewEmail">-</span>
                    </a>
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Açıklama -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-5 py-3 border-b border-gray-200">
              <div class="flex items-center space-x-2">
                <i class="fas fa-file-alt text-amber-600"></i>
                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Açıklama</h4>
              </div>
            </div>
            <div class="p-5">
              <p id="previewDescription" class="text-sm text-gray-700 leading-relaxed">-</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closePreviewSchoolModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>Kapat
      </button>
    </div>
  </div>
</div>

<!-- Düzenleme Modal -->
<div id="editSchoolModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm p-4 md:p-6">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-edit text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Okul Düzenle</h3>
            <p class="text-sm text-blue-100">Okul bilgilerini güncelleyin</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <label class="inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" id="editSchoolStatusToggle" class="sr-only peer" checked aria-label="Durumu değiştir">
            <span class="relative inline-flex w-11 h-6 items-center">
              <span id="editSchoolStatusTrack" class="absolute inset-0 rounded-full transition-colors duration-200 bg-red-500"></span>
              <span id="editSchoolStatusThumb" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200" style="transform: translateX(0);"></span>
            </span>
            <span id="editSchoolStatusLabel" class="ml-3 text-xs font-semibold transition-colors duration-200 text-red-200">Aktif</span>
          </label>
          <button onclick="closeEditSchoolModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
            <i class="fas fa-times text-sm"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-4 bg-gray-50 flex-1 min-h-0 overflow-y-auto">
      <form id="editSchoolForm">
        <input type="hidden" id="editSchoolId" name="school_id" value="">
        <input type="hidden" id="editSchoolStatus" name="status" value="active">
        
        <!-- Okul Adı ve Kod -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-school text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-3 space-y-3">
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-school mr-2 text-blue-500"></i>Okul Adı <span class="text-red-500 ml-1">*</span>
              </label>
              <input id="editSchoolName" name="name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Okul adını girin" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-hashtag mr-2 text-blue-500"></i>Kurum Kodu
                </label>
                <input id="editSchoolCode" name="school_code" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="örn. 9003">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-graduation-cap mr-2 text-blue-500"></i>Okul Türü
                </label>
                <select id="editSchoolType" name="school_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <option value="">Okul türü seçin</option>
                  <?php foreach ($defaultSchoolTypes as $schoolTypeOption): ?>
                    <option value="<?php echo htmlspecialchars($schoolTypeOption); ?>"><?php echo htmlspecialchars($schoolTypeOption); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Konum Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Konum Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-city mr-2 text-blue-500"></i>İl
                </label>
                <select id="editCity" name="city" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <option value="">İl Seçiniz</option>
                </select>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                </label>
                <select id="editCounty" name="county" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" disabled>
                  <option value="">İlçe Seçiniz</option>
                </select>
              </div>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-road mr-2 text-blue-500"></i>Adres
              </label>
              <input id="editAddress" name="address" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Tam adres bilgisi">
            </div>
          </div>
        </div>

        <!-- İletişim Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-phone-alt text-green-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">İletişim Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-phone mr-2 text-green-500"></i>Telefon
                </label>
                <input id="editPhone" name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="Telefon numarası">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-envelope mr-2 text-green-500"></i>E-Posta
                </label>
                <input id="editEmail" name="email" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="ornek@okul.meb.k12.tr">
              </div>
            </div>
          </div>
        </div>

        <!-- Açıklama -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-file-alt text-amber-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Açıklama</h4>
            </div>
          </div>
          <div class="p-3">
            <textarea id="editDescription" name="description" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" rows="4" placeholder="Okul hakkında açıklama (isteğe bağlı)"></textarea>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeEditSchoolModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitEditSchool()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Güncelle
      </button>
    </div>
  </div>
</div>

<!-- SheetJS ve SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- İl-İlçe verileri -->
<script src="<?php echo BASE_URL; ?>/assets/js/city-county-data.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/city-county.js"></script>

<script>
const DEFAULT_SCHOOL_TYPES = <?php echo json_encode($defaultSchoolTypes, JSON_UNESCAPED_UNICODE); ?>;

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
    
    excelFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(fileExt)) {
            Swal.fire('Geçersiz dosya', 'Lütfen sadece .xlsx veya .xls dosyaları yükleyin.', 'error');
            return;
        }
        
        // FileReader ile dosyayı oku
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                Swal.fire({
                    title: 'İşleniyor...',
                    text: 'Excel dosyası okunuyor',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
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
                const ilIndex = headers.indexOf('İL');
                const ilceIndex = headers.indexOf('İLÇE');
                const adresIndex = headers.indexOf('ADRES');
                const telefonIndex = headers.indexOf('TELEFON NO');
                const emailIndex = headers.indexOf('E-MAİL');
                const aciklamaIndex = headers.indexOf('AÇIKLAMA');
                
                const formattedData = jsonData.slice(1)
                    .filter(row => row.length > 0) // Boş satırları filtrele
                    .map(row => ({
                        school_code: row[kodIndex]?.toString().trim() || '',
                        name: row[adIndex]?.toString().trim() || '',
                        school_type: row[turIndex]?.toString().trim() || 'Belirtilmemiş',
                        city: row[ilIndex]?.toString().trim() || '',
                        county: row[ilceIndex]?.toString().trim() || '',
                        address: adresIndex !== -1 ? (row[adresIndex]?.toString().trim() || null) : null,
                        phone: telefonIndex !== -1 ? (row[telefonIndex]?.toString().trim() || null) : null,
                        email: emailIndex !== -1 ? (row[emailIndex]?.toString().trim() || null) : null,
                        description: aciklamaIndex !== -1 ? (row[aciklamaIndex]?.toString().trim() || null) : null
                    }))
                    .filter(school => school.school_code && school.name);
                
                if (formattedData.length === 0) {
                    throw new Error('Excel dosyasında geçerli veri bulunamadı.');
                }
                
                // Debug için konsola yazdır
                console.log('Formatted data:', formattedData);
                console.log('Total schools to add:', formattedData.length);
                
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
                excelFile.value = '';
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
            excelFile.value = '';
        };
        
        // Dosyayı oku
        reader.readAsArrayBuffer(file);
    });
    
    // Tümünü seç
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.school-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Her checkbox için change event listener ekle
    document.querySelectorAll('.school-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
        });
    });
    
    // Toplu silme
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.school-checkbox:checked')).map(cb => parseInt(cb.dataset.id));
            if (selectedIds.length === 0) {
                Swal.fire('Uyarı', 'Lütfen silmek istediğiniz okulları seçin.', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Emin misiniz?',
                text: `${selectedIds.length} okulu silmek istediğinizden emin misiniz?`,
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
                        text: 'Okullar siliniyor',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    
                    // AJAX ile silme işlemi
                    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_selected_schools', school_ids: selectedIds })
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Başarılı!', `${selectedIds.length} okul başarıyla silindi.`, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Hata', data.message || 'Okullar silinirken bir hata oluştu.', 'error');
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

    loadDynamicSchoolTypes();
    
    // İl-İlçe dropdown'larını yükle
    if (typeof cityCountyData !== 'undefined') {
        // Yeni okul ekle modalı için
        initCityCountyDropdowns('createSchoolCity', 'createSchoolCounty');
        
        // Düzenleme modalı için illeri yükle
        populateCities('editCity');
        
        // Düzenleme modalı için il değişikliği
        const editCitySelect = document.getElementById('editCity');
        if (editCitySelect) {
            editCitySelect.addEventListener('change', function() {
                populateCounties('editCounty', this.value);
            });
        }
    }
    
    // Durum toggle için event listener
    const editStatusToggle = document.getElementById('editSchoolStatusToggle');
    if (editStatusToggle) {
        editStatusToggle.addEventListener('change', function() {
            updateEditSchoolStatusUI(this.checked ? 'active' : 'inactive');
        });
    }
});

function normalizeSchoolTypeName(name) {
    return (name || '').toString().trim();
}

function refreshSchoolTypeSelectOptions(extraTypes = []) {
    const allTypes = [...DEFAULT_SCHOOL_TYPES, ...extraTypes.map(t => t.name || t)];
    const uniqueSortedTypes = [...new Set(allTypes.map(normalizeSchoolTypeName).filter(Boolean))]
        .sort((a, b) => a.localeCompare(b, 'tr'));

    ['createSchoolType', 'editSchoolType'].forEach((selectId) => {
        const select = document.getElementById(selectId);
        if (!select) return;
        const currentValue = select.value;
        select.innerHTML = '<option value="">Okul türü seçin</option>';
        uniqueSortedTypes.forEach(typeName => {
            const option = document.createElement('option');
            option.value = typeName;
            option.textContent = typeName;
            select.appendChild(option);
        });
        select.value = currentValue && uniqueSortedTypes.includes(currentValue) ? currentValue : '';
    });
}

function ensureSchoolTypeOptionExists(typeName) {
    const safeType = normalizeSchoolTypeName(typeName);
    if (!safeType) return;
    ['createSchoolType', 'editSchoolType'].forEach((selectId) => {
        const select = document.getElementById(selectId);
        if (!select) return;
        const exists = Array.from(select.options).some(opt => opt.value === safeType);
        if (!exists) {
            const option = document.createElement('option');
            option.value = safeType;
            option.textContent = safeType;
            select.appendChild(option);
        }
    });
}

function loadDynamicSchoolTypes() {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_school_types' })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            refreshSchoolTypeSelectOptions(Array.isArray(res.types) ? res.types : []);
        }
    })
    .catch(err => console.error('school types load error:', err));
}

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
    
    return csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=bulk_add_schools', {
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
        // Başarılı olan varsa göster
        if (data.success && data.success_count > 0) {
            let htmlContent = '<div style="text-align: left;">';
            htmlContent += '<p>' + data.message + '</p>';
            
            // Hatalar varsa göster
            if (data.errors && data.errors.length > 0) {
                htmlContent += '<hr style="margin: 15px 0;"><strong>Hatalar:</strong><ul style="margin-top: 10px; padding-left: 20px;">';
                data.errors.forEach(function(error) {
                    htmlContent += '<li style="margin: 5px 0; color: #dc2626;">' + error + '</li>';
                });
                htmlContent += '</ul>';
            }
            
            htmlContent += '</div>';
            
            Swal.fire({
                icon: 'success',
                title: 'İşlem Tamamlandı!',
                html: htmlContent,
                confirmButtonColor: '#3085d6',
                width: '600px'
            }).then(() => {
                // Formu temizle ve sayfayı yenile
                document.getElementById('excelFile').value = '';
                location.reload();
            });
        } else if (data.success_count === 0) {
            // Hiç başarılı olmayan işlem
            let htmlContent = '<div style="text-align: left;">';
            htmlContent += '<p style="color: #dc2626; font-weight: bold;">' + data.message + '</p>';
            
            if (data.errors && data.errors.length > 0) {
                htmlContent += '<hr style="margin: 15px 0;"><strong>Hatalar:</strong><ul style="margin-top: 10px; padding-left: 20px;">';
                data.errors.forEach(function(error) {
                    htmlContent += '<li style="margin: 5px 0; color: #dc2626;">' + error + '</li>';
                });
                htmlContent += '</ul>';
            }
            
            htmlContent += '</div>';
            
            Swal.fire({
                icon: 'error',
                title: 'İşlem Başarısız!',
                html: htmlContent,
                confirmButtonColor: '#dc2626',
                width: '600px'
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
    
    // Backend'den tüm okulları al
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=export_schools', {
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
        
        const schools = data.schools || [];
        
        if (schools.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Dikkat',
                text: 'İndirilecek veri bulunamadı.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Başlıkları oluştur
        const headers = ['KURUM KODU', 'OKUL ADI', 'OKUL TÜRÜ', 'İL', 'İLÇE', 'ADRES', 'TELEFON NO', 'E-MAİL', 'AÇIKLAMA', 'DURUM'];
        
        // Veri matrisi oluştur
        const excelData = schools.map(school => [
            school.school_code || '',
            school.name || '',
            school.school_type || 'Belirtilmemiş',
            school.city || '',
            school.county || '',
            school.address || '',
            school.phone || '',
            school.email || '',
            school.description || '',
            school.status === 'active' ? 'Aktif' : 'Pasif'
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
        
        XLSX.utils.book_append_sheet(wb, ws, 'Okullar');
        
        // Dosyayı indir
        const today = new Date().toISOString().split('T')[0];
        const filename = `Okullar_Listesi_${today}.xlsx`;
        XLSX.writeFile(wb, filename);
        
        Swal.close();
        
        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: `${schools.length} okul Excel dosyasına aktarıldı.`,
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

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.school-checkbox:checked').length;
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

function openCreateSchoolModal() {
    const modal = document.getElementById('createSchoolModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.scrollTop = 0;
        setPageScrollLock(true);
    }
}

function closeCreateSchoolModal() {
    const modal = document.getElementById('createSchoolModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        setPageScrollLock(false);
    }
}

function submitCreateSchool() {
    const form = document.getElementById('createSchoolForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = {};
    for (let [key, value] of formData.entries()) {
        payload[key] = value.trim();
    }
    console.log('Form payload:', payload); // Debug
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'create_school', ...payload })
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Okul eklendi', 'success').then(() => { closeCreateSchoolModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Okul eklenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openEditSchoolModal(id) {
    // Önce okul bilgilerini yükle
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_school&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.school) {
                const school = data.school;
                // Form alanlarını doldur
                document.getElementById('editSchoolId').value = school.id;
                document.getElementById('editSchoolCode').value = school.school_code || '';
                document.getElementById('editSchoolName').value = school.name || '';
                ensureSchoolTypeOptionExists(school.school_type || '');
                document.getElementById('editSchoolType').value = school.school_type || '';
                document.getElementById('editAddress').value = school.address || '';
                document.getElementById('editPhone').value = school.phone || '';
                document.getElementById('editEmail').value = school.email || '';
                document.getElementById('editDescription').value = school.description || '';
                
                // İl dropdown'ını doldur ve seçili değeri set et
                const citySelect = document.getElementById('editCity');
                if (citySelect && school.city) {
                    citySelect.value = school.city;
                    // İl seçildiğinde ilçeleri yükle
                    populateCounties('editCounty', school.city, school.county);
                }
                
                // Durum toggle'ını güncelle
                updateEditSchoolStatusUI(school.status || 'active');
                
                // Modalı aç
                const modal = document.getElementById('editSchoolModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.scrollTop = 0;
                    setPageScrollLock(true);
                }
            } else {
                Swal.fire('Hata', 'Okul bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Load school error:', err);
            Swal.fire('Hata', 'Okul bilgileri yüklenirken bir hata oluştu.', 'error');
        });
}

function closeEditSchoolModal() {
    const modal = document.getElementById('editSchoolModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        setPageScrollLock(false);
    }
}

function submitEditSchool() {
    const form = document.getElementById('editSchoolForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    const schoolId = payload.school_id;
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_school', school_id: schoolId, ...payload })
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Okul güncellendi', 'success').then(() => { closeEditSchoolModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Okul güncellenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openDeleteSchoolModal(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu okulu silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!'
    }).then((result) => {
        if (result.isConfirmed) {
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_school', school_id: id })
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error('HTTP error! status: ' + r.status);
                }
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire('Silindi!', data.message || 'Okul başarıyla silindi.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Hata', data.message || 'Okul silinirken bir hata oluştu.', 'error');
                }
            })
            .catch(error => {
                console.error('Delete school error:', error);
                Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + error.message, 'error');
            });
        }
    });
}

function openPreviewSchoolModal(id) {
    // Önce okul bilgilerini yükle
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_school&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.school) {
                const school = data.school;
                // Önizleme modalını doldur ve aç
                document.getElementById('previewSchoolCode').textContent = school.school_code || '-';
                document.getElementById('previewSchoolName').textContent = school.name || '-';
                document.getElementById('previewSchoolType').textContent = school.school_type || '-';
                document.getElementById('previewCity').textContent = school.city || '-';
                document.getElementById('previewCounty').textContent = school.county || '-';
                document.getElementById('previewAddress').textContent = school.address || '-';
                document.getElementById('previewPhone').textContent = school.phone || '-';
                const phoneLink = document.getElementById('previewPhoneLink');
                if (phoneLink) { phoneLink.href = school.phone ? ('tel:' + school.phone) : '#'; }
                document.getElementById('previewEmail').textContent = school.email || '-';
                const emailLink = document.getElementById('previewEmailLink');
                if (emailLink) { emailLink.href = school.email ? ('mailto:' + school.email) : '#'; }
                document.getElementById('previewDescription').textContent = school.description || '-';
                const statusEl = document.getElementById('previewStatus');
                if (statusEl) {
                    statusEl.innerHTML = school.status === 'active' 
                        ? '<i class="fas fa-circle text-xs text-green-500"></i><span>Aktif</span>'
                        : '<i class="fas fa-circle text-xs text-red-500"></i><span>Pasif</span>';
                    statusEl.className = school.status === 'active' 
                        ? 'px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 flex items-center space-x-1'
                        : 'px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center space-x-1';
                }
                // Modalı aç
                const modal = document.getElementById('previewSchoolModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.scrollTop = 0;
                    setPageScrollLock(true);
                }
            } else {
                Swal.fire('Hata', 'Okul bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Load school error:', err);
            Swal.fire('Hata', 'Okul bilgileri yüklenirken bir hata oluştu.', 'error');
        });
}

function closePreviewSchoolModal() {
    const modal = document.getElementById('previewSchoolModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        setPageScrollLock(false);
    }
}

function setPageScrollLock(isLocked) {
    document.documentElement.style.overflow = isLocked ? 'hidden' : '';
    document.body.style.overflow = isLocked ? 'hidden' : '';
}

function clearSearch() {
    window.location.href = '?action=schools';
}

function toggleSchoolStatus(toggle, id) {
    const newStatus = toggle.checked ? 'active' : 'inactive';
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_school_status', school_id: id, status: newStatus })
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

// Yeni okul ekle modalı için il dropdown'ını doldur (ortak fonksiyon kullanılıyor)
function populateCreateSchoolCities() {
    populateCities('createSchoolCity');
}

// Yeni okul ekle modalı için ilçe dropdown'ını güncelle (ortak fonksiyon kullanılıyor)
function updateCreateSchoolCounties(cityName) {
    populateCounties('createSchoolCounty', cityName);
}

// Düzenleme modalı için il dropdown'ını doldur
function populateEditSchoolCities() {
    const citySelect = document.getElementById('editCity');
    if (!citySelect || typeof cityCountyData === 'undefined') return;
    
    // Mevcut option'ları temizle (ilk option hariç)
    while (citySelect.options.length > 1) {
        citySelect.remove(1);
    }
    
    // İlleri sıralı olarak ekle
    const cities = Object.keys(cityCountyData).sort();
    cities.forEach(cityName => {
        const option = document.createElement('option');
        option.value = cityName;
        option.textContent = cityName;
        citySelect.appendChild(option);
    });
}

// Düzenleme modalı için ilçe dropdown'ını güncelle
// Düzenleme modalı için ilçe dropdown'ını güncelle (ortak fonksiyon kullanılıyor)
function updateEditSchoolCounties(cityName) {
    populateCounties('editCounty', cityName);
}

// Düzenleme modalı için durum toggle UI güncelleme
function updateEditSchoolStatusUI(status) {
    const statusInput = document.getElementById('editSchoolStatus');
    const statusToggle = document.getElementById('editSchoolStatusToggle');
    const statusLabel = document.getElementById('editSchoolStatusLabel');
    const statusTrack = document.getElementById('editSchoolStatusTrack');
    const statusThumb = document.getElementById('editSchoolStatusThumb');
    const normalized = status === 'inactive' ? 'inactive' : 'active';
    const isActive = normalized === 'active';
    
    if (statusInput) {
        statusInput.value = normalized;
    }
    if (statusToggle) {
        statusToggle.checked = isActive;
    }
    if (statusLabel) {
        statusLabel.textContent = isActive ? 'Aktif' : 'Pasif';
        statusLabel.className = 'ml-3 text-xs font-semibold transition-colors duration-200 ' + (isActive ? 'text-green-100' : 'text-red-200');
    }
    if (statusTrack) {
        statusTrack.className = 'absolute inset-0 rounded-full transition-colors duration-200 ' + (isActive ? 'bg-green-500' : 'bg-red-500');
    }
    if (statusThumb) {
        statusThumb.className = 'absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200';
        statusThumb.style.transform = isActive ? 'translateX(20px)' : 'translateX(0)';
    }
}
</script>
