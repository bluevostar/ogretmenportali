<?php
// Filtreleme parametrelerini al
$role_filter = isset($_GET['role']) ? clean_input($_GET['role']) : null;
if (empty($role_filter)) { $role_filter = 'teacher'; }
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Kullanıcıları getir
$users = $viewModel->filterUsers($role_filter, $search);
if ($users === false) {
    $users = [];
}
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$totalUsers = count($users);
$totalPages = max(1, (int)ceil($totalUsers / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$users = array_slice($users, ($page - 1) * $perPage, $perPage);

// Branş listesini getir
$branches = $viewModel->getBranches();
?>

<div class="content-area h-full min-h-full flex flex-col">
    <div class="flex justify-between items-center mb-3">
        <h1 class="header-title mb-4 md:mb-0">Öğretmenler</h1>

        <!-- Filtreler ve İşlemler -->
        <div class="flex flex-col md:flex-row gap-2">
            <!-- Arama -->
            <form action="" method="get" class="flex w-full md:w-auto">
                <input type="hidden" name="action" value="teachers">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role_filter); ?>">
                <div class="flex w-full gap-2">
                    <input type="text" name="search" placeholder="Kullanıcı ara..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-l-md shadow-sm px-4 py-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Excel İndir Butonu -->
            <button type="button" onclick="exportTeachersToExcel()" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg flex items-center text-xs transition-all duration-300">
                <i class="fas fa-download mr-2"></i> Excel İndir
            </button>

            <!-- Kullanıcı Ekle Butonu -->
            <button type="button" onclick="openCreateTeacherModal()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs transition">
                <i class="fas fa-plus mr-2"></i>
                Yeni Öğretmen Ekle
            </button>


            <!-- Toplu Silme Butonu -->
            <button id="deleteSelectedBtn" disabled class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs transition opacity-50 cursor-not-allowed">
                <i class="fas fa-trash mr-2"></i> Sil
            </button>
        </div>
    </div>

    <!-- Öğretmenler Tablosu -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden flex-1 min-h-0 flex flex-col">
        <div class="overflow-x-auto flex-1 min-h-0">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3">
                        </th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">İD</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">T.C. NO</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">ADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">SOYADI</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[60px]">İL</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[70px]">İLÇE</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="px-2 py-1.5 text-center text-gray-500 text-xs">
                                Kullanıcı bulunamadı.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <input type="checkbox" class="user-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500 w-3 h-3" data-id="<?php echo $user['id']; ?>">
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo $user['id']; ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($user['tc_kimlik_no'] ?? ''); ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php 
                                // name alanından sadece adı al (eğer birleşikse)
                                $name = $user['name'] ?? '';
                                $surnameFromDb = $user['surname'] ?? '';
                                // Eğer name alanında ad ve soyad birleşikse, ayır
                                if (!empty($name) && empty($surnameFromDb) && strpos($name, ' ') !== false) {
                                    $parts = explode(' ', $name, 2);
                                    $name = $parts[0];
                                    $surnameFromDb = $parts[1] ?? '';
                                }
                                echo htmlspecialchars($name);
                            ?></td>
                            <td class="px-2 py-1.5 text-xs text-gray-900"><?php 
                                // surname alanını kullan, yoksa name'den ayır
                                $surname = $user['surname'] ?? '';
                                if (empty($surname) && !empty($user['name']) && strpos($user['name'], ' ') !== false) {
                                    $parts = explode(' ', $user['name'], 2);
                                    $surname = $parts[1] ?? '';
                                }
                                echo htmlspecialchars($surname);
                            ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($user['city'] ?? ''); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900"><?php echo htmlspecialchars($user['county'] ?? ''); ?></td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openPreviewTeacherModal(<?php echo $user['id']; ?>)" 
                                            class="text-green-500 hover:text-green-700" title="Önizleme">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button onclick="openEditTeacherModal(<?php echo $user['id']; ?>)" 
                                       class="text-blue-500 hover:text-blue-700" title="Düzenle">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>')" 
                                            class="text-red-500 hover:text-red-700" title="Sil">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-2 border-t border-gray-200 bg-white flex items-center justify-between mt-auto">
            <p class="text-sm text-gray-600">
                Toplam <span class="font-semibold"><?php echo (int)$totalUsers; ?></span> kayıt,
                Sayfa <span class="font-semibold"><?php echo (int)$page; ?></span> / <span class="font-semibold"><?php echo (int)$totalPages; ?></span>
            </p>
            <div class="flex items-center gap-1">
                <?php
                    $baseUrl = BASE_URL . '/php/admin_panel.php?action=teachers'
                        . '&role=' . urlencode((string)$role_filter)
                        . '&search=' . urlencode((string)$search);
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

<!-- Önizleme Modal -->
<div id="previewTeacherModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm overflow-hidden py-2">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden my-auto max-h-[95vh] flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-user text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Öğretmen Önizleme</h3>
            <p class="text-xs text-blue-100">Öğretmen detayları ve iletişim bilgileri</p>
          </div>
        </div>
        <button onclick="closePreviewTeacherModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-3 space-y-3 bg-gray-50">
      <!-- Öğretmen Başlık Kartı -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h2 id="previewTeacherName" class="text-xl font-bold text-gray-900 mb-1">-</h2>
            <p class="text-sm text-gray-600">
              <span class="font-medium">T.C. Kimlik No:</span>
              <span id="previewTeacherTcKimlik" class="font-semibold text-gray-900">-</span>
            </p>
          </div>
          <span id="previewTeacherStatus" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 flex items-center space-x-1">
            <i class="fas fa-circle text-xs"></i>
            <span>-</span>
          </span>
        </div>
      </div>

      <!-- Temel Bilgiler + Eğitim Bilgileri -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Temel Bilgiler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-user text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-4">
            <dl class="space-y-2">
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-24 flex-shrink-0">
                  <i class="fas fa-envelope mr-2 text-blue-500"></i>E-Posta
                </dt>
                <dd class="flex-1 min-w-0 overflow-x-auto">
                  <a id="previewTeacherEmailLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline block whitespace-nowrap" title="">
                    <span id="previewTeacherEmail">-</span>
                  </a>
                </dd>
              </div>
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-24 flex-shrink-0">
                  <i class="fas fa-phone mr-2 text-blue-500"></i>Telefon
                </dt>
                <dd class="flex-1">
                  <a id="previewTeacherPhoneLink" href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center space-x-1">
                    <span id="previewTeacherPhone">-</span>
                  </a>
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Eğitim Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-graduation-cap text-purple-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Eğitim Bilgileri</h4>
            </div>
          </div>
          <div class="p-4">
            <dl class="space-y-2">
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-24 flex-shrink-0">
                  <i class="fas fa-university mr-2 text-purple-500"></i>Üniversite
                </dt>
                <dd id="previewTeacherUniversity" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
              <div class="flex items-start gap-3">
                  <dt class="flex items-center text-sm font-medium text-gray-500 w-32 flex-shrink-0">
                    <i class="fas fa-building mr-2 text-purple-500"></i>Fakülte/MYO
                </dt>
                <dd id="previewTeacherDepartment" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-24 flex-shrink-0">
                  <i class="fas fa-certificate mr-2 text-purple-500"></i>Mezuniyet
                </dt>
                <dd id="previewTeacherGraduation" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <!-- Sertifika -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-50 to-sky-100 px-4 py-2 border-b border-gray-200">
          <div class="flex items-center space-x-2">
            <i class="fas fa-award text-cyan-600"></i>
            <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Sertifika</h4>
          </div>
        </div>
        <div class="p-4">
          <p id="previewTeacherCertificate" class="text-sm font-semibold text-gray-900">-</p>
        </div>
      </div>

      <!-- Deneyim -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-50 to-gray-100 px-4 py-2 border-b border-gray-200">
          <div class="flex items-center space-x-2">
            <i class="fas fa-briefcase text-slate-600"></i>
            <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Deneyim</h4>
          </div>
        </div>
        <div class="p-4">
          <dl class="space-y-2">
            <div class="flex items-start space-x-3">
              <dt class="flex items-center text-sm font-medium text-gray-500 w-24 flex-shrink-0">
                <i class="fas fa-briefcase mr-2 text-slate-500"></i>Süre
              </dt>
              <dd id="previewTeacherExperience" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Konum -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Konum -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-green-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Konum</h4>
            </div>
          </div>
          <div class="p-4">
            <dl class="space-y-2">
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-16 flex-shrink-0">
                  <i class="fas fa-city mr-2 text-green-500"></i>İl
                </dt>
                <dd id="previewTeacherCity" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-16 flex-shrink-0">
                  <i class="fas fa-map-pin mr-2 text-green-500"></i>İlçe
                </dt>
                <dd id="previewTeacherCounty" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Görevlendirme -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-school text-amber-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Görevlendirme</h4>
            </div>
          </div>
          <div class="p-4">
            <dl class="space-y-2">
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-16 flex-shrink-0">
                  <i class="fas fa-building mr-2 text-amber-500"></i>Kurum
                </dt>
                <dd id="previewTeacherSchool" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
              <div class="flex items-start space-x-3">
                <dt class="flex items-center text-sm font-medium text-gray-500 w-16 flex-shrink-0">
                  <i class="fas fa-book mr-2 text-amber-500"></i>Branş
                </dt>
                <dd id="previewTeacherBranch" class="text-sm font-semibold text-gray-900 flex-1">-</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closePreviewTeacherModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
        <i class="fas fa-times mr-2"></i>Kapat
      </button>
    </div>
  </div>
</div>

<!-- Yeni Öğretmen Modal -->
<div id="createTeacherModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm overflow-hidden py-4">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden my-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-6 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-plus text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Yeni Öğretmen Ekle</h3>
            <p class="text-xs text-green-100">Öğretmen bilgilerini doldurun</p>
          </div>
        </div>
        <button onclick="closeCreateTeacherModal()" class="w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white flex items-center justify-center transition-colors">
          <i class="fas fa-times text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-3 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
      <form id="createTeacherForm">
        <!-- Temel Bilgiler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-3">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-1.5 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-user text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Ad <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Adını girin" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Soyad <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="surname" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Soyadını girin" required>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-envelope mr-2 text-blue-500"></i>E-Posta <span class="text-red-500 ml-1">*</span>
              </label>
                <input name="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="ornek@email.com" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-phone mr-2 text-blue-500"></i>Telefon <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Telefon numarası" required>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-id-card mr-2 text-blue-500"></i>T.C. Kimlik No <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="tc_kimlik_no" type="text" maxlength="11" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="TC Kimlik No (11 haneli)" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-lock mr-2 text-blue-500"></i>Şifre <span class="text-red-500 ml-1">*</span>
                </label>
                <input name="password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="En az 6 karakter" required>
              </div>
            </div>
          </div>
        </div>

        <!-- Eğitim Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-4 py-1.5 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-graduation-cap text-purple-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Eğitim Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-university mr-2 text-purple-500"></i>Üniversite Adı <span class="text-red-500 ml-1">*</span>
              </label>
              <input name="university_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" placeholder="Üniversite adını girin" required>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-building mr-2 text-purple-500"></i>Fakülte/MYO <span class="text-red-500 ml-1">*</span>
              </label>
              <input name="department_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" placeholder="Fakülte/MYO adını girin" required>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-book mr-2 text-purple-500"></i>Branş <span class="text-red-500 ml-1">*</span>
              </label>
              <select name="branch" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" required>
                <option value="">Branş seçiniz</option>
                <?php foreach ($branches as $branch): ?>
                  <option value="<?php echo htmlspecialchars($branch['name']); ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-certificate mr-2 text-purple-500"></i>Mezuniyet <span class="text-red-500 ml-1">*</span>
              </label>
              <select name="graduation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" required>
              <option value="">Mezuniyet seçiniz</option>
                <option value="Doktora">Doktora</option>
                <option value="Emekli Öğretmen">Emekli Öğretmen</option>
                <option value="Önlisans">Önlisans</option>
                <option value="Lisans">Lisans</option>
                <option value="Sanatta Yeterlilik">Sanatta Yeterlilik</option>
                <option value="Yüksek Lisans">Yüksek Lisans</option>
                <option value="Yurtdışı Denklik">Yurtdışı Denklik</option>
              </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-award mr-2 text-purple-500"></i>Sertifika
                </label>
                <textarea name="certificates" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" rows="3" placeholder="Sertifika bilgileri (isteğe bağlı)"></textarea>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-briefcase mr-2 text-purple-500"></i>Deneyim
                </label>
                <textarea name="experience" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" rows="3" placeholder="Deneyim bilgileri (isteğe bağlı)"></textarea>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>İl <span class="text-red-500 ml-1">*</span>
                </label>
                <select id="createTeacherCity" name="city" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" required>
                  <option value="">İl seçiniz</option>
                </select>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-map-pin mr-2 text-purple-500"></i>İlçe <span class="text-red-500 ml-1">*</span>
                </label>
                <select id="createTeacherCounty" name="county" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" disabled required>
                  <option value="">Önce il seçiniz</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-3 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeCreateTeacherModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors text-sm">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitCreateTeacher()" class="px-4 py-2 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium hover:from-green-700 hover:to-emerald-700 shadow-md hover:shadow-lg transition-all text-sm">
        <i class="fas fa-save mr-2"></i>Kaydet
      </button>
    </div>
  </div>
</div>

<!-- Düzenleme Modal -->
<div id="editTeacherModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm overflow-hidden py-4">
  <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden my-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white">
            <i class="fas fa-edit text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Öğretmen Düzenle</h3>
            <p class="text-xs text-blue-100">Öğretmen bilgilerini güncelleyin</p>
          </div>
        </div>
        <div class="flex items-center">
          <label class="inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" id="editTeacherStatusToggle" class="sr-only peer" checked aria-label="Durumu değiştir">
            <span class="relative inline-flex w-11 h-6 items-center">
              <span id="editTeacherStatusTrack" class="absolute inset-0 rounded-full transition-colors duration-200 bg-red-500"></span>
              <span id="editTeacherStatusThumb" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200" style="transform: translateX(0);"></span>
            </span>
            <span id="editTeacherStatusLabel" class="ml-3 text-xs font-semibold transition-colors duration-200 text-red-200">Aktif</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-3 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
      <form id="editTeacherForm">
        <input type="hidden" id="editTeacherId" name="user_id" value="">
        <input type="hidden" id="editTeacherStatus" name="status" value="active">
        
        <!-- Temel Bilgiler -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-3">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-1.5 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-user text-blue-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Temel Bilgiler</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Ad
                </label>
                <input id="editTeacherName" name="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Adını girin" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-user mr-2 text-blue-500"></i>Soyad
                </label>
                <input id="editTeacherSurname" name="surname" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Soyadını girin" required>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-envelope mr-2 text-blue-500"></i>E-Posta
                </label>
                <input id="editTeacherEmail" name="email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="ornek@email.com" required>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-phone mr-2 text-blue-500"></i>Telefon
                </label>
                <input id="editTeacherPhone" name="phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Telefon numarası">
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-id-card mr-2 text-blue-500"></i>T.C. Kimlik No
                </label>
                <input id="editTeacherTc" name="tc_kimlik_no" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="T.C. Kimlik Numarası">
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-lock mr-2 text-blue-500"></i>Şifre
                </label>
                <input id="editTeacherPassword" name="password" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Değiştirmek için yeni şifre girin">
              </div>
            </div>
          </div>
        </div>

        <!-- Eğitim Bilgileri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-4 py-1.5 border-b border-gray-200">
            <div class="flex items-center space-x-2">
              <i class="fas fa-graduation-cap text-purple-600"></i>
              <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Eğitim Bilgileri</h4>
            </div>
          </div>
          <div class="p-3 space-y-2">
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-university mr-2 text-purple-500"></i>Üniversite Adı
              </label>
              <input id="editTeacherUniversity" name="university_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" placeholder="Üniversite adını girin">
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-building mr-2 text-purple-500"></i>Fakülte/MYO
              </label>
              <input id="editTeacherDepartment" name="department_name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" placeholder="Fakülte/MYO adını girin">
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-book mr-2 text-purple-500"></i>Branş
              </label>
              <select id="editTeacherBranch" name="branch" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" required>
                <option value="">Branş seçiniz</option>
                <?php foreach ($branches as $branch): ?>
                  <option value="<?php echo htmlspecialchars($branch['id']); ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-certificate mr-2 text-purple-500"></i>Mezuniyet
              </label>
              <select id="editTeacherGraduation" name="graduation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                <option value="">Mezuniyet seçiniz</option>
                <option value="Doktora">Doktora</option>
                <option value="Emekli Öğretmen">Emekli Öğretmen</option>
                <option value="Önlisans">Önlisans</option>
                <option value="Lisans">Lisans</option>
                <option value="Sanatta Yeterlilik">Sanatta Yeterlilik</option>
                <option value="Yüksek Lisans">Yüksek Lisans</option>
                <option value="Yurtdışı Denklik">Yurtdışı Denklik</option>
              </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-award mr-2 text-purple-500"></i>Sertifika
              </label>
              <textarea id="editTeacherCertificates" name="certificates" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" rows="3" placeholder="Sertifika bilgileri (isteğe bağlı)"></textarea>
              </div>
              <div>
              <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-briefcase mr-2 text-purple-500"></i>Deneyim
              </label>
              <textarea id="editTeacherExperience" name="experience" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" rows="3" placeholder="Deneyim bilgileri (isteğe bağlı)"></textarea>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>İl
                </label>
                <select id="editTeacherCity" name="city" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                  <option value="">İl seçiniz</option>
                </select>
              </div>
              <div>
                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                  <i class="fas fa-map-pin mr-2 text-purple-500"></i>İlçe
                </label>
                <select id="editTeacherCounty" name="county" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors" disabled>
                  <option value="">Önce il seçiniz</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="bg-white px-6 py-3 border-t border-gray-200 flex justify-end space-x-3">
      <button onclick="closeEditTeacherModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors text-sm">
        <i class="fas fa-times mr-2"></i>İptal
      </button>
      <button onclick="submitEditTeacher()" class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all text-sm">
        <i class="fas fa-save mr-2"></i>Güncelle
      </button>
    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SheetJS Library for Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- İl-İlçe verileri -->
<script src="<?php echo BASE_URL; ?>/assets/js/city-county-data.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/city-county.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // city-county-data.js yüklendiğinde illeri doldur
    if (typeof cityCountyData !== 'undefined') {
        // Yeni öğretmen ekle modalı için
        initCityCountyDropdowns('createTeacherCity', 'createTeacherCounty');
        
        // İl seçildiğinde ilçe dropdown'ını zorunlu yap
        const citySelect = document.getElementById('createTeacherCity');
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                const countySelect = document.getElementById('createTeacherCounty');
                if (countySelect && this.value) {
                    countySelect.required = true;
                } else if (countySelect) {
                    countySelect.required = false;
                }
            });
        }
        
        // Düzenle modalı için illeri yükle (ilçeler modal açıldığında yüklenecek)
        populateCities('editTeacherCity');
    }
    
    // Edit modal için il dropdown'ı değiştiğinde ilçeleri güncelle
    const editCitySelect = document.getElementById('editTeacherCity');
    if (editCitySelect) {
        editCitySelect.addEventListener('change', function() {
            populateCounties('editTeacherCounty', this.value);
        });
    }

    const editStatusToggle = document.getElementById('editTeacherStatusToggle');
    if (editStatusToggle) {
        editStatusToggle.addEventListener('change', function() {
            updateEditTeacherStatusUI(this.checked ? 'active' : 'inactive');
        });
        updateEditTeacherStatusUI(editStatusToggle.checked ? 'active' : 'inactive');
    }
});

function updateEditTeacherStatusUI(status) {
    const statusInput = document.getElementById('editTeacherStatus');
    const statusToggle = document.getElementById('editTeacherStatusToggle');
    const statusLabel = document.getElementById('editTeacherStatusLabel');
    const statusTrack = document.getElementById('editTeacherStatusTrack');
    const statusThumb = document.getElementById('editTeacherStatusThumb');
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

function updateCounties(cityName) {
    const countySelect = document.getElementById('createTeacherCounty');
    if (!countySelect || typeof cityCountyData === 'undefined') return;
    
    // Mevcut option'ları temizle
    countySelect.innerHTML = '<option value="">İlçe seçiniz</option>';
    
    if (!cityName) {
        countySelect.disabled = true;
        countySelect.required = false;
        return;
    }
    
    // Seçilen ile ait ilçeleri bul
    const counties = cityCountyData[cityName];
    if (counties && Array.isArray(counties) && counties.length > 0) {
        countySelect.disabled = false;
        countySelect.required = true;
        counties.forEach(countyName => {
            const option = document.createElement('option');
            option.value = countyName;
            option.textContent = countyName;
            countySelect.appendChild(option);
        });
    } else {
        countySelect.disabled = true;
        countySelect.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.dataset.id);
            if (selectedIds.length === 0) {
                Swal.fire('Uyarı', 'Lütfen silmek istediğiniz kullanıcıları seçin.', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Emin misiniz?',
                text: `${selectedIds.length} kullanıcıyı silmek istediğinizden emin misiniz?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, sil!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // API çağrısı yap
                    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_selected_users', user_ids: selectedIds })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            Swal.fire('Başarılı!', res.message || 'Seçili kullanıcılar silindi.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Hata', res.message || 'Kullanıcılar silinemedi.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Delete users error:', err);
                        Swal.fire('Hata', 'Kullanıcılar silinirken bir hata oluştu.', 'error');
                    });
                }
            });
        });
    }
});

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (selectedCount === 0) {
        deleteBtn.disabled = true;
        deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        deleteBtn.disabled = false;
        deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        deleteBtn.innerHTML = `<i class="fas fa-trash mr-2"></i> Sil`;
    }
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


function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: `${userName} adlı kullanıcıyı silmek istediğinizden emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!'
    }).then((result) => {
        if (result.isConfirmed) {
            // API çağrısı yap
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_user', user_id: userId })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire('Başarılı!', res.message || 'Kullanıcı başarıyla silindi.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Hata', res.message || 'Kullanıcı silinemedi.', 'error');
                }
            })
            .catch(err => {
                console.error('Delete user error:', err);
                Swal.fire('Hata', 'Kullanıcı silinirken bir hata oluştu.', 'error');
            });
        }
    });
}

function openPreviewTeacherModal(id) {
    // Öğretmen bilgilerini yükle
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_user&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                const user = data.user;
                const profile = data.teacher_profile || {};
                
                // Ad ve soyadı birleştir
                let firstName = '';
                let surname = user.surname || '';
                if (user.name) {
                    const parts = user.name.split(/\s+/);
                    if (parts.length > 1) {
                        surname = surname || parts.pop();
                        firstName = parts.join(' ');
                    } else {
                        firstName = user.name;
                    }
                }
                const fullName = [firstName, surname].filter(Boolean).join(' ') || '-';
                
                // Önizleme modalını doldur ve aç
                document.getElementById('previewTeacherName').textContent = fullName;
                document.getElementById('previewTeacherTcKimlik').textContent = user.tc_kimlik_no || '-';
                const previewTeacherFullNameEl = document.getElementById('previewTeacherFullName');
                if (previewTeacherFullNameEl) {
                    previewTeacherFullNameEl.textContent = fullName;
                }
                const isDistrictApproved = !!user.is_district_approved;
                const assignmentSchoolName = user.district_approved_school_name || '-';
                const assignmentBranchName = user.district_approved_branch_name || '-';
                document.getElementById('previewTeacherBranch').textContent = isDistrictApproved ? assignmentBranchName : '-';
                document.getElementById('previewTeacherUniversity').textContent = user.university_name || '-';
                document.getElementById('previewTeacherDepartment').textContent = user.department_name || '-';
                document.getElementById('previewTeacherGraduation').textContent = user.graduation || '-';
                document.getElementById('previewTeacherCertificate').textContent = profile.certificates || '-';
                const profileMeta = data.profile || {};
                document.getElementById('previewTeacherExperience').textContent = profile.experience || profileMeta.experience || user.experience || '-';
                document.getElementById('previewTeacherCity').textContent = user.city || '-';
                document.getElementById('previewTeacherCounty').textContent = user.county || '-';
                document.getElementById('previewTeacherSchool').textContent = isDistrictApproved ? assignmentSchoolName : '-';
                
                const email = user.email || '-';
                document.getElementById('previewTeacherEmail').textContent = email;
                const emailLink = document.getElementById('previewTeacherEmailLink');
                if (emailLink) {
                    emailLink.href = user.email ? ('mailto:' + user.email) : '#';
                    emailLink.title = email;
                }
                
                const phone = user.phone || '-';
                document.getElementById('previewTeacherPhone').textContent = phone;
                const phoneLink = document.getElementById('previewTeacherPhoneLink');
                if (phoneLink) { phoneLink.href = user.phone ? ('tel:' + user.phone) : '#'; }
                
                // Status chip: active/pasif
                const statusEl = document.getElementById('previewTeacherStatus');
                const isActive = (user.status ?? (user.active ? 'active' : 'inactive')) === 'active';
                if (statusEl) {
                    statusEl.innerHTML = isActive
                        ? '<i class="fas fa-circle text-xs text-green-500"></i><span>Aktif</span>'
                        : '<i class="fas fa-circle text-xs text-red-500"></i><span>Pasif</span>';
                    statusEl.className = isActive
                        ? 'px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 flex items-center space-x-1'
                        : 'px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center space-x-1';
                }
                
                // Modalı aç
                const modal = document.getElementById('previewTeacherModal');
                if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else {
                Swal.fire('Hata', 'Öğretmen bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Load teacher error:', err);
            Swal.fire('Hata', 'Öğretmen bilgileri yüklenirken bir hata oluştu.', 'error');
        });
}

function closePreviewTeacherModal() {
    const modal = document.getElementById('previewTeacherModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function openCreateTeacherModal() {
    const modal = document.getElementById('createTeacherModal');
    if (modal) {
        // Formu sıfırla
        const form = document.getElementById('createTeacherForm');
        if (form) {
            form.reset();
        }
        
        // İl ve ilçe dropdown'larını sıfırla
        const citySelect = document.getElementById('createTeacherCity');
        const countySelect = document.getElementById('createTeacherCounty');
        if (citySelect) {
            citySelect.value = '';
        }
        if (countySelect) {
            countySelect.value = '';
            countySelect.disabled = true;
            countySelect.required = false;
            countySelect.innerHTML = '<option value="">Önce il seçiniz</option>';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeCreateTeacherModal() {
    const modal = document.getElementById('createTeacherModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitCreateTeacher() {
    const form = document.getElementById('createTeacherForm');
    if (!form) return;
    
    // Form validasyonu
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // İl seçilmişse ilçe de seçilmeli
    const citySelect = document.getElementById('createTeacherCity');
    const countySelect = document.getElementById('createTeacherCounty');
    if (citySelect && citySelect.value && (!countySelect || !countySelect.value)) {
        Swal.fire('Uyarı', 'Lütfen bir ilçe seçiniz.', 'warning');
        if (countySelect) countySelect.focus();
        return;
    }
    
    const formData = new FormData(form);
    const payload = {
        action: 'create_user',
        role: 'teacher'
    };
    for (let [key, value] of formData.entries()) {
        payload[key] = value.trim();
    }
    
    // Ad ve soyadı birleştirme - backend'de ayrı tutulacak, burada birleştirmeye gerek yok
    // Backend'de name ve surname ayrı ayrı kaydedilecek
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Öğretmen eklendi', 'success').then(() => { closeCreateTeacherModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Öğretmen eklenemedi', 'error');
        }
    })
    .catch(err => Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error'));
}

function openEditTeacherModal(id) {
    // Önce öğretmen bilgilerini yükle
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_user&id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                const user = data.user;
                
                // Ad ve soyadı ayır
                let firstName = '';
                let surname = user.surname || '';
                if (user.name) {
                    const parts = user.name.split(/\s+/);
                    if (parts.length > 1) {
                        surname = surname || parts.pop();
                        firstName = parts.join(' ');
                    } else {
                        firstName = user.name;
                    }
                }
                
                // Form alanlarını doldur
                document.getElementById('editTeacherId').value = user.id;
                document.getElementById('editTeacherName').value = firstName;
                document.getElementById('editTeacherSurname').value = surname;
                document.getElementById('editTeacherEmail').value = user.email || '';
                document.getElementById('editTeacherPhone').value = user.phone || '';
                const tcInput = document.getElementById('editTeacherTc');
                if (tcInput) {
                    tcInput.value = user.tc_kimlik_no || '';
                }
                updateEditTeacherStatusUI(user.status || 'active');
                // Şifre alanı güvenlik nedeniyle boş bırakılır (kullanıcı değiştirmek isterse doldurur)
                const passwordField = document.getElementById('editTeacherPassword');
                if (passwordField) {
                    passwordField.value = '';
                    passwordField.placeholder = 'Değiştirmek için yeni şifre girin';
                }
                document.getElementById('editTeacherUniversity').value = user.university_name || '';
                document.getElementById('editTeacherDepartment').value = user.department_name || '';
                const editTeacherCertificatesEl = document.getElementById('editTeacherCertificates');
                if (editTeacherCertificatesEl) {
                    editTeacherCertificatesEl.value = user.certificates || '';
                }
                
                // Mezuniyet değerini set et
                const graduationValue = user.graduation || '';
                const graduationSelect = document.getElementById('editTeacherGraduation');
                if (graduationSelect) {
                    if (graduationValue) {
                        graduationSelect.value = graduationValue;
                        // Eğer değer bulunamazsa, console'a log yaz
                        if (graduationSelect.value !== graduationValue) {
                            console.warn('Mezuniyet değeri bulunamadı:', graduationValue);
                            console.log('Mevcut option değerleri:', Array.from(graduationSelect.options).map(opt => opt.value));
                        } else {
                            console.log('Mezuniyet değeri başarıyla set edildi:', graduationValue);
                        }
                    } else {
                        graduationSelect.value = '';
                    }
                }
                
                // Debug: Gelen veriyi logla
                console.log('User data:', user);
                console.log('Teacher profile:', data.teacher_profile);
                console.log('Graduation value:', graduationValue);
                
                // Öğretmen profil bilgilerini yükle (get_user'dan gelen teacher_profile'i kullan)
                if (data.teacher_profile) {
                    const profile = data.teacher_profile;
                    document.getElementById('editTeacherBranch').value = profile.application_branch_id || '';
                    document.getElementById('editTeacherExperience').value = profile.experience || '';
                    const editTeacherCertificatesEl = document.getElementById('editTeacherCertificates');
                    if (editTeacherCertificatesEl) {
                        editTeacherCertificatesEl.value = profile.certificates || user.certificates || '';
                    }
                    
                    // Eğer user objesinde university_name ve department_name yoksa, profile'den al
                    if (!user.university_name && profile.institution_name) {
                        user.university_name = profile.institution_name;
                    } else if (!user.university_name && profile.institution) {
                        user.university_name = profile.institution;
                    }
                    if (!user.department_name && profile.department) {
                        user.department_name = profile.department;
                    }
                    if (!user.graduation && profile.graduation_level) {
                        user.graduation = profile.graduation_level;
                    }
                    
                    // Tekrar set et (eğer profile'den geldiyse)
                    document.getElementById('editTeacherUniversity').value = user.university_name || '';
                    document.getElementById('editTeacherDepartment').value = user.department_name || '';
                    
                    // Mezuniyet değerini set et
                    const graduationValue = user.graduation || '';
                    const graduationSelect = document.getElementById('editTeacherGraduation');
                    if (graduationSelect && graduationValue) {
                        graduationSelect.value = graduationValue;
                        // Eğer değer bulunamazsa, console'a log yaz
                        if (graduationSelect.value !== graduationValue) {
                            console.warn('Mezuniyet değeri bulunamadı:', graduationValue);
                        }
                    }
                } else {
                    // Eğer teacher_profile yoksa, get_teacher_profile endpoint'ini dene
                    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=get_teacher_profile&user_id=' + id)
                        .then(r => r.json())
                        .then(profileData => {
                            if (profileData.success && profileData.profile) {
                                const profile = profileData.profile;
                                document.getElementById('editTeacherBranch').value = profile.application_branch_id || '';
                                document.getElementById('editTeacherExperience').value = profile.experience || '';
                                const editTeacherCertificatesEl = document.getElementById('editTeacherCertificates');
                                if (editTeacherCertificatesEl) {
                                    editTeacherCertificatesEl.value = profile.certificates || '';
                                }
                                
                                // university_name ve department_name'i profile'den al
                                if (profile.university_name) {
                                    document.getElementById('editTeacherUniversity').value = profile.university_name;
                                } else if (profile.institution_name) {
                                    document.getElementById('editTeacherUniversity').value = profile.institution_name;
                                } else if (profile.institution) {
                                    document.getElementById('editTeacherUniversity').value = profile.institution;
                                }
                                if (profile.department_name) {
                                    document.getElementById('editTeacherDepartment').value = profile.department_name;
                                } else if (profile.department) {
                                    document.getElementById('editTeacherDepartment').value = profile.department;
                                }
                                // Mezuniyet değerini set et
                                const graduationValue = profile.graduation || profile.graduation_level || '';
                                const graduationSelect = document.getElementById('editTeacherGraduation');
                                if (graduationSelect && graduationValue) {
                                    graduationSelect.value = graduationValue;
                                    // Eğer değer bulunamazsa, console'a log yaz
                                    if (graduationSelect.value !== graduationValue) {
                                        console.warn('Mezuniyet değeri bulunamadı:', graduationValue);
                                    }
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Profile load error:', err);
                        });
                }
                
                // İl ve ilçe bilgilerini set et
                const editCitySelect = document.getElementById('editTeacherCity');
                const editCountySelect = document.getElementById('editTeacherCounty');
                if (editCitySelect && user.city) {
                    // Önce illeri yükle (eğer yüklenmemişse)
                    if (editCitySelect.options.length <= 1 && typeof cityCountyData !== 'undefined') {
                        populateCities('editTeacherCity', user.city);
                    }
                    editCitySelect.value = user.city;
                    // İlçe dropdown'ını güncelle
                    populateCounties('editTeacherCounty', user.city, user.county);
                }
                
                // Modalı aç
                const modal = document.getElementById('editTeacherModal');
                if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            } else {
                Swal.fire('Hata', 'Öğretmen bilgileri yüklenemedi.', 'error');
            }
        })
        .catch(err => {
            console.error('Load teacher error:', err);
            Swal.fire('Hata', 'Öğretmen bilgileri yüklenirken bir hata oluştu.', 'error');
        });
}

function closeEditTeacherModal() {
    const modal = document.getElementById('editTeacherModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function submitEditTeacher() {
    const form = document.getElementById('editTeacherForm');
    if (!form) return;
    const formData = new FormData(form);
    const payload = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'password' && !value.trim()) {
            continue; // Boş şifreyi gönderme
        }
        payload[key] = value.trim();
    }
    
    // Ad ve soyadı birleştirme - backend'de ayrı tutulacak, burada birleştirmeye gerek yok
    // Backend'de name ve surname ayrı ayrı kaydedilecek
    
    const userId = payload.user_id;
    delete payload.user_id;
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_user', user_id: userId, ...payload })
    })
    .then(r => {
        // Önce response'u text olarak al, sonra JSON'a çevir
        return r.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Geçersiz JSON yanıtı: ' + text.substring(0, 100));
            }
        });
    })
    .then(res => {
        if (res.success) {
            Swal.fire('Başarılı', res.message || 'Öğretmen güncellendi', 'success').then(() => { closeEditTeacherModal(); location.reload(); });
        } else {
            Swal.fire('Hata', res.message || 'Öğretmen güncellenemedi', 'error');
        }
    })
    .catch(err => {
        console.error('Update teacher error:', err);
        Swal.fire('Hata', 'İstek sırasında bir hata oluştu: ' + err.message, 'error');
    });
}

function exportTeachersToExcel() {
    Swal.fire({
        title: 'İndiriliyor...',
        text: 'Excel dosyası hazırlanıyor',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=export_teachers', {
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
        
        const teachers = data.teachers || [];
        
        if (teachers.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Dikkat',
                text: 'İndirilecek veri bulunamadı.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Başlıkları oluştur
        const headers = ['ID', 'T.C. KİMLİK NO', 'ADI', 'SOYADI', 'E-POSTA', 'TELEFON', 'MEZUNİYET', 'ÜNİVERSİTE ADI', 'FAKÜLTE/MYO BÖLÜM', 'BRANŞ', 'İL', 'İLÇE', 'DURUM'];
        
        // Veri matrisi oluştur
        const excelData = teachers.map(teacher => {
            // Ad ve soyadı ayır
            let firstName = '';
            let surname = teacher.surname || '';
            if (teacher.name) {
                const parts = teacher.name.split(/\s+/);
                if (parts.length > 1) {
                    surname = surname || parts.pop();
                    firstName = parts.join(' ');
                } else {
                    firstName = teacher.name;
                }
            }
            
            return [
                teacher.id || '',
                teacher.tc_kimlik_no || '',
                firstName,
                surname,
                teacher.email || '',
                teacher.phone || '',
                teacher.graduation || '',
                teacher.university_name || '',
                teacher.department_name || '',
                teacher.branch_name || '',
                teacher.city || '',
                teacher.county || '',
                (teacher.status === 'active' || teacher.active) ? 'Aktif' : 'Pasif'
            ];
        });
        
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
        
        XLSX.utils.book_append_sheet(wb, ws, 'Öğretmenler');
        
        // Dosyayı indir
        const today = new Date().toISOString().split('T')[0];
        const filename = `Ogretmenler_Listesi_${today}.xlsx`;
        XLSX.writeFile(wb, filename);
        
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Başarılı',
            text: `${teachers.length} öğretmen bilgisi Excel dosyasına aktarıldı.`,
            confirmButtonColor: '#3085d6'
        });
    })
    .catch(error => {
        console.error('Export error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata',
            text: 'Excel dosyası oluşturulurken bir hata oluştu: ' + error.message,
            confirmButtonColor: '#3085d6'
        });
    });
}
</script>

