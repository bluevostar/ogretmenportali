<?php
// Filtre parametreleri
$role_filter = isset($_GET['role_filter']) ? clean_input($_GET['role_filter']) : '';
$search_term = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Okul yöneticilerini getir (arama desteği ile)
$filters = [];
if ($search_term) {
    $filters['search'] = $search_term;
}
$schoolAdmins = $viewModel->getSchoolAdmins($filters);
$schoolAdmins = is_array($schoolAdmins) ? $schoolAdmins : [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$totalSchoolAdmins = count($schoolAdmins);
$totalPages = max(1, (int)ceil($totalSchoolAdmins / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$schoolAdmins = array_slice($schoolAdmins, ($page - 1) * $perPage, $perPage);

// Okul listesi (aktif okullar)
$schools = $viewModel->getSchools(['status' => 'active']);
?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">Okul Yöneticileri</h1>

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="school_admins">
                <div class="relative flex w-full">
                    <input type="text" name="search" id="searchInput" placeholder="Yönetici ara..." 
                           value="<?php echo htmlspecialchars($search_term); ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm pl-4 pr-10 py-2">
                    <?php if ($search_term): ?>
                    <button type="button" onclick="clearSearch()" class="absolute right-16 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" title="Aramayı temizle">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php endif; ?>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Excel İndir Butonu -->
            <button type="button" onclick="exportSchoolAdminsToExcel()" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg flex items-center text-xs transition-all duration-300">
                <i class="fas fa-download mr-2"></i> Excel İndir
            </button>

            <!-- Yeni Yönetici Ekle Butonu (Modal açar) -->
            <button type="button" onclick="openCreateSchoolAdminModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Yönetici
            </button>

            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Sil
            </button>
        </div>
    </div>

    <!-- Yöneticiler Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden flex-1 min-h-0 flex flex-col">
        <div class="overflow-x-auto flex-1 min-h-0">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3">
                        </th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">İD</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">ADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">SOYADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[90px]">KURUM KODU</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[120px]">OKUL ADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[60px]">İL</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[70px]">İLÇE</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($schoolAdmins && count($schoolAdmins) > 0): ?>
                        <?php foreach ($schoolAdmins as $admin): ?>
                        <tr>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <input type="checkbox" class="admin-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo $admin['id']; ?>">
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['id']); ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php 
                                $firstName = '';
                                $surnameTmp = $admin['surname'] ?? null;
                                if (!empty($admin['name'])) {
                                    $parts = preg_split('/\s+/', trim($admin['name']));
                                    if (count($parts) > 1) {
                                        $surnameTmp = $surnameTmp ?: array_pop($parts);
                                        $firstName = implode(' ', $parts);
                                    } else {
                                        $firstName = $admin['name'];
                                    }
                                }
                                echo htmlspecialchars($firstName);
                            ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo htmlspecialchars($admin['surname'] ?? ($surnameTmp ?? '')); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['school_code'] ?? '-'); ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo htmlspecialchars($admin['school_name'] ?? '-'); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['school_city'] ?? 'Belirtilmemiş'); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['school_county'] ?? 'Belirtilmemiş'); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openPreviewSchoolAdmin(<?php echo $admin['id']; ?>)" 
                                            class="text-green-500 hover:text-green-700" title="Önizleme">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button onclick="openEditSchoolAdminModal(<?php echo $admin['id']; ?>)" class="text-blue-500 hover:text-blue-700" title="Düzenle">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    
                                    <button onclick="deleteSchoolAdmin(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['name'], ENT_QUOTES); ?>')" 
                                            class="text-red-500 hover:text-red-700" title="Sil">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-2 py-1.5 text-center text-gray-500 text-xs">
                                Henüz kayıtlı okul yöneticisi bulunmuyor.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
            <p class="text-sm text-gray-600">
                Toplam <span class="font-semibold"><?php echo (int)$totalSchoolAdmins; ?></span> kayıt,
                Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
            </p>
            <div class="flex items-center gap-1">
                <?php
                    $baseUrl = BASE_URL . '/php/admin_panel.php?action=school_admins&search=' . urlencode((string)$search_term);
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

<!-- Okul Yöneticisi Önizleme Modal (İlçe M.E.M. ile aynı stil) -->
<div id="previewSchoolAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-user-tie text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Okul Yöneticisi Önizleme</h3>
          </div>
        </div>
        <button onclick="closePreviewSchoolAdminModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>
    <div class="p-6 space-y-6 bg-gray-50">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h2 id="previewSchoolAdminName" class="text-2xl font-bold text-gray-900 mb-2">-</h2>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <div class="flex items-center space-x-1">
                <i class="fas fa-building text-blue-500"></i>
                <span id="previewSchoolAdminOrg" class="text-gray-900 font-semibold">-</span>
              </div>
            </div>
          </div>
          <span id="previewSchoolAdminStatus" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 flex items-center space-x-1">
            <i class="fas fa-circle text-xs"></i>
            <span>-</span>
          </span>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-6">
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
                  <dd id="previewSchoolAdminCity" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                  </dt>
                  <dd id="previewSchoolAdminCounty" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
                <div class="flex items-start space-x-3 pt-3 border-t border-gray-100">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-road mr-2 text-blue-500"></i>Okul Adresi
                  </dt>
                  <dd id="previewSchoolAdminAddress" class="text-sm text-gray-900 flex-1 leading-relaxed">-</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
        <div class="space-y-6">
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
                    <a id="previewSchoolAdminPhoneLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                      <span id="previewSchoolAdminPhone">-</span>
                    </a>
                  </dd>
                </div>
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-envelope mr-2 text-green-500"></i>E-Posta
                  </dt>
                  <dd class="flex-1">
                    <a id="previewSchoolAdminEmailLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                      <span id="previewSchoolAdminEmail">-</span>
                    </a>
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closePreviewSchoolAdminModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>Kapat
      </button>
    </div>
  </div>
  
</div>

<!-- Yeni Okul Yöneticisi Modal (İlçe M.E.M. ile aynı stil) -->
<div id="createSchoolAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-user-plus text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Yeni Okul Yöneticisi</h3>
            <p class="text-sm text-blue-100">Yönetici bilgilerini doldurun</p>
          </div>
        </div>
        <button onclick="closeCreateSchoolAdminModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>
    <div class="p-6 space-y-6 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
      <form id="createSchoolAdminForm">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-id-card text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <!-- Ad ve Soyad -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Ad <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Ad" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Soyad <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="surname" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Soyad" required>
              </div>
            </div>
            <!-- TC Kimlik No ve E-Posta -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-id-card mr-2 text-blue-500"></i>TC Kimlik No <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="tc_no" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="11 haneli TC kimlik numarası" maxlength="11" pattern="[0-9]{11}" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-envelope mr-2 text-blue-500"></i>E-Posta
                </label>
                <input name="email" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="ornek@okul.meb.k12.tr">
              </div>
            </div>
            <!-- Telefon ve Şifre -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-phone mr-2 text-blue-500"></i>Telefon
                </label>
                <input name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="05xx xxx xx xx">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-lock mr-2 text-blue-500"></i>Şifre <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="password" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="En az 6 karakter" required>
              </div>
            </div>
            <!-- Bağlı Okul -->
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-school mr-2 text-blue-500"></i>Bağlı Okul <span class="text-red-500 ml-1">*</span>
              </label>
              <select name="school_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                <option value="">Okul seçin</option>
                <?php if (!empty($schools)) { foreach ($schools as $s) { ?>
                  <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars(($s['school_code'] ?? '') ? (($s['school_code']).' - '.$s['name']) : $s['name']); ?></option>
                <?php } } ?>
              </select>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeCreateSchoolAdminModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitCreateSchoolAdmin()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Kaydet
      </button>
    </div>
  </div>
</div>

<!-- Düzenleme Modal (İlçe M.E.M. ile aynı stil) -->
<div id="editSchoolAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-edit text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Okul Yöneticisi Düzenle</h3>
            <p class="text-xs text-blue-100">Yönetici bilgilerini güncelleyin</p>
          </div>
        </div>
        <div class="flex items-center">
          <label class="inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" id="editSchoolAdminStatusToggle" class="sr-only peer" checked aria-label="Durumu değiştir">
            <span class="relative inline-flex w-11 h-6 items-center">
              <span id="editSchoolAdminStatusTrack" class="absolute inset-0 rounded-full transition-colors duration-200 bg-red-500"></span>
              <span id="editSchoolAdminStatusThumb" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200" style="transform: translateX(0);"></span>
            </span>
            <span id="editSchoolAdminStatusLabel" class="ml-3 text-xs font-semibold transition-colors duration-200 text-red-200">Aktif</span>
          </label>
        </div>
      </div>
    </div>
    <div class="p-4 space-y-3 bg-gray-50">
      <form id="editSchoolAdminForm">
        <input type="hidden" id="editSchoolAdminId" name="admin_id" value="">
        <input type="hidden" id="editSchoolAdminStatus" name="status" value="active">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-3">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-id-card text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Ad <span class="text-red-500 ml-1">*</span>
                </label>
                <input id="editSchoolAdminName" name="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Soyad <span class="text-red-500 ml-1">*</span>
                </label>
                <input id="editSchoolAdminSurname" name="surname" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-envelope mr-2 text-blue-500"></i>E-Posta <span class="text-red-500 ml-1">*</span>
                </label>
                <input id="editSchoolAdminEmail" name="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-phone mr-2 text-blue-500"></i>Telefon
                </label>
                <input id="editSchoolAdminPhone" name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-lock mr-2 text-blue-500"></i>Şifre
                </label>
                <input id="editSchoolAdminPassword" name="password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Yeni şifre (boş bırakılırsa değişmez)">
              </div>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-school mr-2 text-blue-500"></i>Bağlı Okul <span class="text-red-500 ml-1">*</span>
              </label>
              <select id="editSchoolAdminSchool" name="school_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                <option value="">Okul seçin</option>
                <?php if (!empty($schools)) { foreach ($schools as $s) { ?>
                  <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars(($s['school_code'] ?? '') ? (($s['school_code']).' - '.$s['name']) : $s['name']); ?></option>
                <?php } } ?>
              </select>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeEditSchoolAdminModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitEditSchoolAdmin()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Güncelle
      </button>
    </div>
  </div>
</div>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- XLSX Library for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
function clearSearch() {
    window.location.href = '?action=school_admins';
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    // Okul yöneticisi durum toggle event listener
    const editSchoolAdminStatusToggle = document.getElementById('editSchoolAdminStatusToggle');
    if (editSchoolAdminStatusToggle) {
        editSchoolAdminStatusToggle.addEventListener('change', function() {
            updateEditSchoolAdminStatusUI(this.checked ? 'active' : 'inactive');
        });
    }
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.admin-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Satır checkbox değişimlerini dinle
    const rowCheckboxes = document.querySelectorAll('.admin-checkbox');
    rowCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));

    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.admin-checkbox:checked')).map(cb => cb.dataset.id);
            if (selectedIds.length === 0) {
                Swal.fire('Uyarı', 'Lütfen silmek istediğiniz yöneticileri seçin.', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Emin misiniz?',
                text: `${selectedIds.length} yöneticiyi silmek istediğinizden emin misiniz?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, sil!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Toplu silme isteği
                    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_selected_school_admins', admin_ids: selectedIds })
                    })
                    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(res => {
                        if (res.success) {
                            Swal.fire('Silindi!', res.message || 'Seçili yöneticiler silindi.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Hata', res.message || 'Toplu silme sırasında bir hata oluştu.', 'error');
                        }
                    })
                    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
                }
            });
        });
    }

    // İlk yüklemede buton durumunu ayarla
    updateSelectedCount();
});

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.admin-checkbox:checked').length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (selectedCount === 0) {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = '<i class="fas fa-trash mr-2"></i> Sil';
    } else {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = `<i class="fas fa-trash mr-2"></i> Sil`;
    }
}

function exportSchoolAdminsToExcel() {
    Swal.fire({
        title: 'İndiriliyor...',
        text: 'Excel dosyası hazırlanıyor',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=export_school_admins', {
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
        
        const admins = data.admins || [];
        
        if (admins.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Dikkat',
                text: 'İndirilecek veri bulunamadı.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Başlıkları oluştur
        const headers = ['ID', 'ADI', 'SOYADI', 'E-POSTA', 'TELEFON', 'KURUM KODU', 'OKUL ADI', 'İL', 'İLÇE', 'DURUM'];
        
        // Veri matrisi oluştur
        const excelData = admins.map(admin => [
            admin.id || '',
            admin.name || '',
            admin.surname || '',
            admin.email || '',
            admin.phone || '',
            admin.school_code || '',
            admin.school_name || '',
            admin.school_city || admin.city || '',
            admin.school_county || admin.county || '',
            admin.status === 'active' ? 'Aktif' : 'Pasif'
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
        
        XLSX.utils.book_append_sheet(wb, ws, 'Okul Yöneticileri');
        
        // Dosyayı indir
        const today = new Date().toISOString().split('T')[0];
        const filename = `Okul_Yoneticileri_${today}.xlsx`;
        XLSX.writeFile(wb, filename);
        
        Swal.close();
        
        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: `${admins.length} okul yöneticisi Excel dosyasına aktarıldı.`,
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

function toggleUserStatus(toggle, userId) {
    const newStatus = toggle.checked ? 'active' : 'inactive';
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle_user_status', user_id: userId, status: newStatus })
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

function updateEditSchoolAdminStatusUI(status) {
    const statusInput = document.getElementById('editSchoolAdminStatus');
    const statusToggle = document.getElementById('editSchoolAdminStatusToggle');
    const statusLabel = document.getElementById('editSchoolAdminStatusLabel');
    const statusTrack = document.getElementById('editSchoolAdminStatusTrack');
    const statusThumb = document.getElementById('editSchoolAdminStatusThumb');
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

function openPreviewSchoolAdmin(id) {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_user&role=school_admin&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                const u = data.user;
                document.getElementById('previewSchoolAdminName').textContent = [u.name||'', u.surname||''].filter(Boolean).join(' ') || '-';
                const isActive = (u.status ?? (u.active ? 'active' : 'inactive')) === 'active';
                const statusEl = document.getElementById('previewSchoolAdminStatus');
                statusEl.innerHTML = isActive ? '<i class="fas fa-circle text-xs text-green-500"></i><span>Aktif</span>' : '<i class="fas fa-circle text-xs text-red-500"></i><span>Pasif</span>';
                statusEl.className = isActive ? 'px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 flex items-center space-x-1' : 'px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center space-x-1';
                document.getElementById('previewSchoolAdminOrg').textContent = (u.school_name || '-')
                document.getElementById('previewSchoolAdminCity').textContent = u.city || '-';
                document.getElementById('previewSchoolAdminCounty').textContent = u.county || '-';
                document.getElementById('previewSchoolAdminAddress').textContent = u.school_address || '-';
                const phone = u.phone || '-';
                document.getElementById('previewSchoolAdminPhone').textContent = phone;
                const phoneLink = document.getElementById('previewSchoolAdminPhoneLink'); if (phoneLink) phoneLink.href = u.phone ? ('tel:'+u.phone) : '#';
                const email = u.email || '-';
                document.getElementById('previewSchoolAdminEmail').textContent = email;
                const emailLink = document.getElementById('previewSchoolAdminEmailLink'); if (emailLink) emailLink.href = u.email ? ('mailto:'+u.email) : '#';
                const modal = document.getElementById('previewSchoolAdminModal'); if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else { Swal.fire('Hata', 'Yönetici bilgileri yüklenemedi.', 'error'); }
        })
        .catch(() => Swal.fire('Hata', 'Yönetici bilgileri yüklenirken bir hata oluştu.', 'error'));
}
function closePreviewSchoolAdminModal() {
    const modal = document.getElementById('previewSchoolAdminModal'); if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function openCreateSchoolAdminModal() {
    const modal = document.getElementById('createSchoolAdminModal'); if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
}
function closeCreateSchoolAdminModal() {
    const modal = document.getElementById('createSchoolAdminModal'); if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}
function submitCreateSchoolAdmin() {
    const form = document.getElementById('createSchoolAdminForm'); if (!form) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    
    // Zorunlu alan kontrolü: TC kimlik no, Ad, Soyad, Şifre, Bağlı okul
    if (!payload.tc_no || !payload.name || !payload.surname || !payload.password || !payload.school_id) { 
        Swal.fire('Uyarı', 'TC kimlik no, ad, soyad, şifre ve bağlı okul zorunludur', 'warning'); 
        return; 
    }
    
    // TC Kimlik No validasyonu (11 haneli olmalı)
    if (payload.tc_no.length !== 11 || !/^\d+$/.test(payload.tc_no)) {
        Swal.fire('Uyarı', 'TC kimlik numarası 11 haneli olmalı ve sadece rakam içermelidir', 'warning');
        return;
    }
    
    Swal.fire({ title: 'İşleniyor...', text: 'Yönetici ekleniyor', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'create_school_admin', ...payload }) })
      .then(r => { if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(res => { Swal.close(); if (res.success) { Swal.fire('Başarılı', res.message || 'Yönetici eklendi', 'success').then(()=>{ closeCreateSchoolAdminModal(); location.reload(); }); } else { Swal.fire('Hata', res.message || 'Yönetici eklenemedi', 'error'); } })
      .catch(err => { Swal.close(); Swal.fire('Hata', err.message, 'error'); });
}

function openEditSchoolAdminModal(id) {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_user&role=school_admin&id=' + id)
      .then(r => r.json())
      .then(data => {
        if (data.success && data.user) {
          const u = data.user;
          document.getElementById('editSchoolAdminId').value = u.id;
          document.getElementById('editSchoolAdminName').value = u.name || '';
          document.getElementById('editSchoolAdminSurname').value = u.surname || '';
          document.getElementById('editSchoolAdminPhone').value = u.phone || '';
          document.getElementById('editSchoolAdminEmail').value = u.email || '';
          if (u.school_id) {
            const sel = document.getElementById('editSchoolAdminSchool'); if (sel) sel.value = u.school_id;
          }
          const isActive = (u.status ?? (u.active ? 'active' : 'inactive')) === 'active';
          updateEditSchoolAdminStatusUI(isActive ? 'active' : 'inactive');
          const modal = document.getElementById('editSchoolAdminModal'); if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        } else { Swal.fire('Hata', 'Yönetici bilgileri yüklenemedi.', 'error'); }
      })
      .catch(() => Swal.fire('Hata', 'Yönetici bilgileri yüklenirken bir hata oluştu.', 'error'));
}
function closeEditSchoolAdminModal() { const modal = document.getElementById('editSchoolAdminModal'); if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); } }
function submitEditSchoolAdmin() {
    const form = document.getElementById('editSchoolAdminForm'); if (!form) return;
    const payload = Object.fromEntries(new FormData(form).entries());
    if (!payload.name || !payload.surname || !payload.email || !payload.school_id) { Swal.fire('Uyarı', 'Ad, soyad, e-posta ve bağlı okul zorunludur', 'warning'); return; }
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'update_school_admin', ...payload }) })
      .then(r => { if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(res => { if (res.success) { Swal.fire('Başarılı', res.message || 'Yönetici güncellendi', 'success').then(()=>{ closeEditSchoolAdminModal(); location.reload(); }); } else { Swal.fire('Hata', res.message || 'Yönetici güncellenemedi', 'error'); } })
      .catch(err => Swal.fire('Hata', err.message, 'error'));
}

function deleteSchoolAdmin(userId, userName) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${userName} adlı yöneticiyi silmek istediğinizden emin misiniz?`,
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
                body: JSON.stringify({ action: 'delete_school_admin', admin_id: userId })
            })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(res => {
                if (res.success) {
                    Swal.fire('Silindi!', res.message || 'Yönetici başarıyla silindi.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Hata', res.message || 'Yönetici silinirken bir hata oluştu.', 'error');
                }
            })
            .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
        }
    });
}
</script>
