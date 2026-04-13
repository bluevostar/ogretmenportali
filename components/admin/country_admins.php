<?php
// Sadece ilçe yöneticilerini (country_admin) getir
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : null;
$districtAdmins = $viewModel->filterUsers('country_admin', $searchTerm);
$districtAdmins = is_array($districtAdmins) ? $districtAdmins : [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$totalDistrictAdmins = count($districtAdmins);
$totalPages = max(1, (int)ceil($totalDistrictAdmins / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$districtAdmins = array_slice($districtAdmins, ($page - 1) * $perPage, $perPage);
?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">İlçe M.E.M. Yöneticileri</h1>

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="country_admins">
                <div class="relative flex w-full">
                    <input type="text" name="search" id="searchInput" placeholder="Yönetici ara..." 
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

            <!-- Yeni Yönetici Ekle Butonu -->
            <button type="button" onclick="openCreateCountryAdminModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Yönetici Ekle
            </button>

            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i>  Sil
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
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[60px]">İL</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[70px]">İLÇE</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($districtAdmins && count($districtAdmins) > 0): ?>
                        <?php foreach ($districtAdmins as $admin): ?>
                        <tr>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <input type="checkbox" class="admin-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo $admin['id']; ?>">
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['id']); ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php echo htmlspecialchars($admin['name'] ?? ''); ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php 
                                $surname = $admin['surname'] ?? null; 
                                if (!$surname && !empty($admin['name'])) {
                                    $parts = preg_split('/\s+/', trim($admin['name']));
                                    $surname = count($parts) > 1 ? array_pop($parts) : '';
                                }
                                echo htmlspecialchars($surname);
                            ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['city'] ?? 'Belirtilmemiş'); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($admin['county'] ?? 'Belirtilmemiş'); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openPreviewCountryAdmin(<?php echo $admin['id']; ?>)" 
                                            class="text-green-500 hover:text-green-700" title="Önizleme">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button onclick="openViewSchoolsModal('<?php echo htmlspecialchars($admin['county'] ?? '', ENT_QUOTES); ?>', <?php echo $admin['id']; ?>)" 
                                            class="text-purple-500 hover:text-purple-700" title="Okulları Görüntüle">
                                        <i class="fas fa-school text-xs"></i>
                                    </button>
                                    <button onclick="openEditCountryAdminModal(<?php echo $admin['id']; ?>)" class="text-blue-500 hover:text-blue-700" title="Düzenle">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button onclick="deleteUser(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['name'], ENT_QUOTES); ?>')" 
                                            class="text-red-500 hover:text-red-700" title="Sil">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-2 py-1.5 text-center text-gray-500 text-xs">
                                <?php if (!empty($searchTerm)): ?>
                                    "<?php echo htmlspecialchars($searchTerm); ?>" araması için sonuç bulunamadı.
                                <?php else: ?>
                                    Henüz kayıtlı ilçe yöneticisi bulunmuyor.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
            <p class="text-sm text-gray-600">
                Toplam <span class="font-semibold"><?php echo (int)$totalDistrictAdmins; ?></span> kayıt,
                Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
            </p>
            <div class="flex items-center gap-1">
                <?php
                    $baseUrl = BASE_URL . '/php/admin_panel.php?action=country_admins&search=' . urlencode((string)($searchTerm ?? ''));
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

<!-- Yönetici Önizleme Modal (Okullar sayfasıyla aynı stil) -->
<div id="previewCountryAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-user-tie text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">İlçe M.E.M. Yönetici Önizleme</h3>
          </div>
        </div>
        <button onclick="closePreviewCountryAdminModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6 bg-gray-50">
      <!-- Başlık Kartı -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h2 id="previewAdminName" class="text-2xl font-bold text-gray-900 mb-2">-</h2>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <div class="flex items-center space-x-1">
                <i class="fas fa-building text-blue-500"></i>
                <span id="previewAdminOrg" class="text-gray-900 font-semibold">-</span>
              </div>
            </div>
          </div>
          <span id="previewAdminStatus" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 flex items-center space-x-1">
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
                  <dd id="previewAdminCity" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                  </dt>
                  <dd id="previewAdminCounty" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
                </div>
                <div class="flex items-start space-x-3 pt-3 border-t border-gray-100">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-road mr-2 text-blue-500"></i>Adres
                  </dt>
                  <dd id="previewAdminAddress" class="text-sm text-gray-900 flex-1 leading-relaxed">-</dd>
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
                    <a id="previewAdminPhoneLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                      <span id="previewAdminPhone">-</span>
                    </a>
                  </dd>
                </div>
                <div class="flex items-start space-x-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-20 flex-shrink-0">
                    <i class="fas fa-envelope mr-2 text-green-500"></i>E-Posta
                  </dt>
                  <dd class="flex-1">
                    <a id="previewAdminEmailLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                      <span id="previewAdminEmail2">-</span>
                    </a>
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closePreviewCountryAdminModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>Kapat
      </button>
    </div>
  </div>
</div>

<!-- Yeni Yönetici Modal (Okul modal yapısıyla tutarlı) -->
<div id="createCountryAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-user-plus text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Yeni İlçe M.E.M. Yöneticisi</h3>
            <p class="text-sm text-blue-100">Yönetici bilgilerini doldurun</p>
          </div>
        </div>
        <button onclick="closeCreateCountryAdminModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
      <form id="createCountryAdminForm">
        <!-- Temel Bilgiler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-id-card text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Ad Soyad <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Ad Soyad" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-envelope mr-2 text-blue-500"></i>E-Posta <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="email" type="email" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="ornek@mem.gov.tr" required>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-lock mr-2 text-blue-500"></i>Şifre <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="password" type="password" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="En az 6 karakter" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-phone mr-2 text-blue-500"></i>Telefon
                </label>
                <input name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="05xx xxx xx xx">
              </div>
            </div>
          </div>
        </div>

        <!-- Konum Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Konum Bilgileri</h4>
            </div>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-city mr-2 text-blue-500"></i>İl
                </label>
                <select id="createCountryAdminCity" name="city" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <option value="">İl Seçiniz</option>
                </select>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                </label>
                <select id="createCountryAdminCounty" name="county" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" disabled>
                  <option value="">İlçe Seçiniz</option>
                </select>
              </div>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-road mr-2 text-blue-500"></i>Adres
              </label>
              <input name="address" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Adres (isteğe bağlı)">
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeCreateCountryAdminModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitCreateCountryAdmin()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Kaydet
      </button>
    </div>
  </div>
  
</div>

<!-- Düzenleme Modal (Okullar sayfasındaki modal ile aynı yapı) -->
<div id="editCountryAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-edit text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Yönetici Düzenle</h3>
            <p class="text-xs text-blue-100">Yönetici bilgilerini güncelleyin</p>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <label class="inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" id="editCountryAdminStatusToggle" class="sr-only peer" checked aria-label="Durumu değiştir">
            <span class="relative inline-flex w-11 h-6 items-center">
              <span id="editCountryAdminStatusTrack" class="absolute inset-0 rounded-full transition-colors duration-200 bg-red-500"></span>
              <span id="editCountryAdminStatusThumb" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200" style="transform: translateX(0);"></span>
            </span>
            <span id="editCountryAdminStatusLabel" class="ml-3 text-xs font-semibold transition-colors duration-200 text-red-200">Aktif</span>
          </label>
          <button onclick="closeEditCountryAdminModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
            <i class="fas fa-times text-sm"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4 space-y-3 bg-gray-50 flex-1 min-h-0 overflow-y-auto">
      <form id="editCountryAdminForm">
        <input type="hidden" id="editAdminId" name="admin_id" value="">

        <!-- Temel Bilgiler -->
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
                  <i class="fas fa-user mr-2 text-blue-500"></i>Ad
                </label>
                <input id="editAdminName" name="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Ad">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Soyad
                </label>
                <input id="editAdminSurname" name="surname" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Soyad">
              </div>
            </div>
            <input type="hidden" id="editCountryAdminStatus" name="status" value="active">
          </div>
        </div>

        <!-- Konum Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-3">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Konum Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-city mr-2 text-blue-500"></i>İl
                </label>
                <select id="editCountryAdminCity" name="city" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <option value="">İl Seçiniz</option>
                </select>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-map-pin mr-2 text-blue-500"></i>İlçe
                </label>
                <select id="editCountryAdminCounty" name="county" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" disabled>
                  <option value="">İlçe Seçiniz</option>
                </select>
              </div>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-road mr-2 text-blue-500"></i>Adres
              </label>
              <input id="editAdminAddress" name="address" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Tam adres">
            </div>
          </div>
        </div>

        <!-- İletişim Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-3">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-phone-alt text-green-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">İletişim Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-phone mr-2 text-green-500"></i>Telefon
                </label>
                <input id="editAdminPhone" name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="Telefon numarası">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-envelope mr-2 text-green-500"></i>E-Posta
                </label>
                <input id="editAdminEmail" name="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="ornek@mem.gov.tr">
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeEditCountryAdminModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitEditCountryAdmin()" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all">
        <i class="fas fa-save mr-2"></i>Kaydet
      </button>
    </div>
  </div>
</div>

<!-- İlçeye Bağlı Okullar Modal -->
<div id="viewSchoolsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
  <div class="bg-white w-full max-w-6xl rounded-xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-school text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">İlçeye Bağlı Okullar</h3>
            <p class="text-sm text-purple-100" id="viewSchoolsModalCounty">-</p>
          </div>
        </div>
        <button onclick="closeViewSchoolsModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 bg-gray-50 max-h-[calc(100vh-300px)] overflow-y-auto">
      <div id="schoolsListContainer">
        <div class="flex items-center justify-center py-12">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Okullar yükleniyor...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeViewSchoolsModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>Kapat
      </button>
    </div>
  </div>
</div>

<!-- SheetJS ve SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/city-county-data.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/city-county.js"></script>

<script>
function clearSearch() {
    window.location.href = '?action=country_admins';
}

document.addEventListener('DOMContentLoaded', function() {
    // city-county-data.js yüklendiğinde illeri doldur
    if (typeof cityCountyData !== 'undefined') {
        populateCreateCountryAdminCities();
        populateEditCountryAdminCities();
    }
    
    // Yeni yönetici ekle modalı için il dropdown'ı değiştiğinde ilçe dropdown'ını güncelle
    const createCitySelect = document.getElementById('createCountryAdminCity');
    if (createCitySelect) {
        createCitySelect.addEventListener('change', function() {
            updateCreateCountryAdminCounties(this.value);
        });
    }
    
    // Düzenleme modalı için il dropdown'ı değiştiğinde ilçe dropdown'ını güncelle
    const editCitySelect = document.getElementById('editCountryAdminCity');
    if (editCitySelect) {
        editCitySelect.addEventListener('change', function() {
            updateEditCountryAdminCounties(this.value);
        });
    }
    
    // Durum toggle için event listener
    const editStatusToggle = document.getElementById('editCountryAdminStatusToggle');
    if (editStatusToggle) {
        editStatusToggle.addEventListener('change', function() {
            updateEditCountryAdminStatusUI(this.checked ? 'active' : 'inactive');
        });
        updateEditCountryAdminStatusUI(editStatusToggle.checked ? 'active' : 'inactive');
    }
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    // Tümünü seç
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.admin-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Tek tek seçim değişimlerini takip et
    const rowCheckboxes = document.querySelectorAll('.admin-checkbox');
    rowCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));

    // Toplu silme
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
                    Swal.fire('Başarılı!', 'Seçili yöneticiler silindi.', 'success').then(() => location.reload());
                }
            });
        });
    }

    // İlk yüklemede buton durumunu ayarla
    updateSelectedCount();

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

});

// Yeni yönetici ekle modalı için il dropdown'ını doldur (ortak fonksiyon kullanılıyor)
function populateCreateCountryAdminCities() {
    populateCities('createCountryAdminCity');
}

// Yeni yönetici ekle modalı için ilçe dropdown'ını güncelle (ortak fonksiyon kullanılıyor)
function updateCreateCountryAdminCounties(cityName) {
    populateCounties('createCountryAdminCounty', cityName);
}

// Düzenleme modalı için il dropdown'ını doldur (ortak fonksiyon kullanılıyor)
function populateEditCountryAdminCities() {
    populateCities('editCountryAdminCity');
}

// Düzenleme modalı için ilçe dropdown'ını güncelle (ortak fonksiyon kullanılıyor)
function updateEditCountryAdminCounties(cityName) {
    populateCounties('editCountryAdminCounty', cityName);
}

// Durum toggle UI güncelleme fonksiyonu
function updateEditCountryAdminStatusUI(status) {
    const statusInput = document.getElementById('editCountryAdminStatus');
    const statusToggle = document.getElementById('editCountryAdminStatusToggle');
    const statusLabel = document.getElementById('editCountryAdminStatusLabel');
    const statusTrack = document.getElementById('editCountryAdminStatusTrack');
    const statusThumb = document.getElementById('editCountryAdminStatusThumb');
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

function openCreateCountryAdminModal() {
    const modal = document.getElementById('createCountryAdminModal');
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
}

function closeCreateCountryAdminModal() {
    const modal = document.getElementById('createCountryAdminModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitCreateCountryAdmin() {
    const form = document.getElementById('createCountryAdminForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    
    // Basit doğrulama
    if (!payload.name || !payload.email || !payload.password) {
        Swal.fire('Uyarı', 'Lütfen zorunlu alanları doldurun: Ad Soyad, E-posta, Şifre', 'warning');
        return;
    }
    
    Swal.fire({ title: 'İşleniyor...', text: 'Yönetici ekleniyor', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'create_country_admin', ...payload })
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Yönetici eklendi', 'success').then(() => { closeCreateCountryAdminModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Yönetici eklenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
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

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.admin-checkbox:checked').length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (!deleteBtn) return;
    if (selectedCount === 0) {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
        // Sayı yokken varsayılan metne dön
        deleteBtn.innerHTML = '<i class="fas fa-trash mr-2"></i> Sil';
    } else {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = `<i class="fas fa-trash mr-2"></i> Sil`;
    }
}

function openPreviewCountryAdmin(id) {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_user&role=country_admin&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                const u = data.user;
                const email = u.email || '-';
                // Ad ve soyadı birleştir
                const fullName = [u.name || '', u.surname || ''].filter(Boolean).join(' ') || '-';
                const nameEl = document.getElementById('previewAdminName');
                if (nameEl) nameEl.textContent = fullName;
                // Status chip: active/pasif
                const statusEl = document.getElementById('previewAdminStatus');
                const isActive = (u.status ?? (u.active ? 'active' : 'inactive')) === 'active';
                if (statusEl) {
                    statusEl.innerHTML = isActive
                      ? '<i class="fas fa-circle text-xs text-green-500"></i><span>Aktif</span>'
                      : '<i class="fas fa-circle text-xs text-red-500"></i><span>Pasif</span>';
                    statusEl.className = isActive
                      ? 'px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 flex items-center space-x-1'
                      : 'px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center space-x-1';
                }
                // İlçe bilgisini kurum adı formatında göster
                const orgEl = document.getElementById('previewAdminOrg');
                if (orgEl) {
                    const county = u.county || '';
                    if (county) {
                        orgEl.textContent = `${county} İlçe Milli Eğitim Müdürlüğü`;
                    } else {
                        orgEl.textContent = '-';
                    }
                }
                // Location
                const cityEl = document.getElementById('previewAdminCity'); if (cityEl) cityEl.textContent = u.city || '-';
                const countyEl = document.getElementById('previewAdminCounty'); if (countyEl) countyEl.textContent = u.county || '-';
                const addrEl = document.getElementById('previewAdminAddress'); if (addrEl) addrEl.textContent = u.address || '-';
                // Contact
                const phone = u.phone || '-';
                const phoneLink = document.getElementById('previewAdminPhoneLink');
                const phoneSpan = document.getElementById('previewAdminPhone'); if (phoneSpan) phoneSpan.textContent = phone;
                if (phoneLink) { phoneLink.href = u.phone ? ('tel:' + u.phone) : '#'; }
                const emailLink = document.getElementById('previewAdminEmailLink');
                const emailSpan2 = document.getElementById('previewAdminEmail2'); if (emailSpan2) emailSpan2.textContent = email;
                if (emailLink) { emailLink.href = u.email ? ('mailto:' + u.email) : '#'; }
                // Open modal
                const modal = document.getElementById('previewCountryAdminModal');
                if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else {
                Swal.fire('Hata', 'Yönetici bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(() => Swal.fire('Hata', 'Yönetici bilgileri yüklenirken bir hata oluştu.', 'error'));
}

function closePreviewCountryAdminModal() {
    const modal = document.getElementById('previewCountryAdminModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function deleteUser(userId, userName) {
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
            // AJAX ile silme işlemi
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_country_admin', admin_id: userId })
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

// Düzenleme Modali (okullar sayfası düzenleme modalına paralel)
function openEditCountryAdminModal(id) {
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_user&role=country_admin&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                const u = data.user;
                // İsim alanlarını ayır (soyad yoksa tahmin etme, direkt doldur)
                document.getElementById('editAdminId').value = u.id;
                document.getElementById('editAdminName').value = u.name || '';
                document.getElementById('editAdminSurname').value = u.surname || '';
                // Durum
                const isActive = (u.status ?? (u.active ? 'active' : 'inactive')) === 'active';
                updateEditCountryAdminStatusUI(isActive ? 'active' : 'inactive');
                
                // Konum - İl ve ilçe dropdown'larını doldur
                const editCitySelect = document.getElementById('editCountryAdminCity');
                const editCountySelect = document.getElementById('editCountryAdminCounty');
                if (editCitySelect && u.city) {
                    // Önce illeri yükle (eğer yüklenmemişse)
                    if (editCitySelect.options.length <= 1 && typeof cityCountyData !== 'undefined') {
                        populateCities('editCountryAdminCity', u.city);
                    }
                    editCitySelect.value = u.city;
                    // İlçe dropdown'ını güncelle
                    populateCounties('editCountryAdminCounty', u.city, u.county);
                } else if (editCitySelect && typeof cityCountyData !== 'undefined') {
                    // İl seçilmemişse bile dropdown'ları hazırla
                    populateCities('editCountryAdminCity');
                }
                document.getElementById('editAdminAddress').value = u.address || '';
                // İletişim
                document.getElementById('editAdminPhone').value = u.phone || '';
                document.getElementById('editAdminEmail').value = u.email || '';
                // Modalı aç
                const modal = document.getElementById('editCountryAdminModal');
                if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else {
                Swal.fire('Hata', 'Yönetici bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(() => Swal.fire('Hata', 'Yönetici bilgileri yüklenirken bir hata oluştu.', 'error'));
}

function closeEditCountryAdminModal() {
    const modal = document.getElementById('editCountryAdminModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitEditCountryAdmin() {
    const form = document.getElementById('editCountryAdminForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_country_admin', ...payload })
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Yönetici güncellendi', 'success').then(() => { closeEditCountryAdminModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Yönetici güncellenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openViewSchoolsModal(county, adminId) {
    if (!county || county === 'Belirtilmemiş') {
        Swal.fire('Uyarı', 'Bu yöneticinin ilçe bilgisi bulunmamaktadır.', 'warning');
        return;
    }
    
    const modal = document.getElementById('viewSchoolsModal');
    const countyLabel = document.getElementById('viewSchoolsModalCounty');
    if (countyLabel) countyLabel.textContent = county + ' İlçesi';
    
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    // Okulları yükle
    loadSchoolsByCounty(county);
}

function closeViewSchoolsModal() {
    const modal = document.getElementById('viewSchoolsModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function loadSchoolsByCounty(county) {
    const container = document.getElementById('schoolsListContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">Okullar yükleniyor...</p>
            </div>
        </div>
    `;
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_schools_by_county&county=' + encodeURIComponent(county))
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (data.success && data.schools && data.schools.length > 0) {
                renderSchoolsList(data.schools);
            } else {
                container.innerHTML = `
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <i class="fas fa-school text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600">Bu ilçeye bağlı okul bulunmamaktadır.</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Load schools error:', err);
            container.innerHTML = `
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                        <p class="text-red-600">Okullar yüklenirken bir hata oluştu.</p>
                        <p class="text-sm text-gray-500 mt-2">${err.message}</p>
                    </div>
                </div>
            `;
        });
}

function renderSchoolsList(schools) {
    const container = document.getElementById('schoolsListContainer');
    if (!container) return;
    
    if (schools.length === 0) {
        container.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-school text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">Bu ilçeye bağlı okul bulunmamaktadır.</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Kurum Kodu</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Okul Adı</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Okul Türü</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    schools.forEach((school, index) => {
        html += `
            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${school.school_code || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${school.name || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${school.school_type || '-'}</td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-2"></i>
                    Toplam <strong>${schools.length}</strong> okul bulundu.
                </p>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

function exportCountryAdminsToExcel() {
    Swal.fire({
        title: 'İndiriliyor...',
        text: 'Excel dosyası hazırlanıyor',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=export_country_admins', {
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
        const headers = ['ID', 'AD', 'SOYAD', 'E-POSTA', 'TELEFON', 'İL', 'İLÇE', 'DURUM', 'KAYIT TARİHİ'];
        const excelData = admins.map(admin => [
            admin.id || '',
            admin.name || '',
            admin.surname || '',
            admin.email || '',
            admin.phone || '',
            admin.city || '',
            admin.county || '',
            (admin.status ?? (admin.active ? 'active' : 'inactive')) === 'active' ? 'Aktif' : 'Pasif',
            admin.created_at ? new Date(admin.created_at).toLocaleDateString('tr-TR') : ''
        ]);
        const worksheetData = [headers, ...excelData];
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(worksheetData);
        const maxWidths = headers.map((header, colIndex) => {
            const maxLength = Math.max(
                header.length,
                ...excelData.map(row => row[colIndex] ? row[colIndex].toString().length : 0)
            );
            return { wch: Math.min(Math.max(maxLength, 10), 50) };
        });
        ws['!cols'] = maxWidths;
        XLSX.utils.book_append_sheet(wb, ws, 'İlçe M.E.M. Yöneticileri');
        const today = new Date().toISOString().split('T')[0];
        const filename = `Ilce_MEM_Yoneticileri_${today}.xlsx`;
        XLSX.writeFile(wb, filename);
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: `${admins.length} yönetici Excel dosyasına aktarıldı.`,
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
