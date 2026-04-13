<?php
/**
 * Tüm yönetici panelleri için ana layout dosyası.
 * Bu dosya, sidebar, header ve genel sayfa yapısını içerir.
 *
 * Gerekli Değişkenler:
 * - $pageTitle (string): Sayfa başlığı.
 * - $sidebarLinks (array): Sidebar menü linkleri. Her link ['url', 'icon', 'text', 'action_key'] içermelidir.
 * - $action (string): Mevcut sayfa eylemi.
 * - $mainContentFile (string): Dahil edilecek ana içerik dosyasının yolu.
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Yönetim Paneli'); ?> - Öğretmen Portalı</title>
    <script>
        (function () {
            try {
                if (window.localStorage && localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (error) {
                // localStorage erisimi engellenirse ilk render varsayilan halde devam eder.
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.APP_CSRF_TOKEN = '<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>';
        const nativeFetch = window.fetch.bind(window);

        // Mutasyon isteklerinde CSRF token ekleyen ortak helper.
        window.csrfFetch = function(url, options = {}) {
            const method = String(options.method || 'GET').toUpperCase();
            const headers = new Headers(options.headers || {});

            if (method !== 'GET' && method !== 'HEAD' && !headers.has('X-CSRF-Token')) {
                headers.set('X-CSRF-Token', window.APP_CSRF_TOKEN);
            }

            return nativeFetch(url, { ...options, headers });
        };

        // Geriye donuk uyumluluk: mevcut fetch kullanimlari da otomatik CSRF korumasi alir.
        window.fetch = function(url, options = {}) {
            return window.csrfFetch(url, options);
        };
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e' },
                        secondary: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a' },
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        html {
            font-size: clamp(14px, 0.9vw + 0.6rem, 18px);
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f1f5f9; line-height: 1.5; }
        :root {
            --btn-uniform-height: 42px;
            --btn-uniform-gap: 0.75rem;
        }
        button,
        input[type="button"],
        input[type="submit"],
        input[type="reset"] {
            min-height: var(--btn-uniform-height);
            height: var(--btn-uniform-height);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            vertical-align: middle;
        }
        :where(.flex, .inline-flex):has(> button, > input[type="button"], > input[type="submit"], > input[type="reset"]) {
            align-items: center;
            gap: var(--btn-uniform-gap);
        }
        :where(.flex, .inline-flex):has(> button, > input[type="button"], > input[type="submit"], > input[type="reset"])
            > :where(button, input[type="button"], input[type="submit"], input[type="reset"]) + :where(button, input[type="button"], input[type="submit"], input[type="reset"]) {
            margin-left: 0 !important;
        }
        .sidebar { background-color: #ffffff; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; white-space: nowrap; }
        .sidebar::-webkit-scrollbar { width: 0; height: 0; }
        .sidebar { scrollbar-width: none; -ms-overflow-style: none; }
        .sidebar.collapsed { width: 5rem !important; }
        .sidebar.collapsed .sidebar-title, .sidebar.collapsed span:not(.sidebar-mini), .sidebar.collapsed .dropdown-content, .sidebar.collapsed p, .sidebar.collapsed h4, .sidebar.collapsed .w-24 { display: none; }
        .sidebar.collapsed i { font-size: 1.25rem; margin-right: 0; }
        .sidebar.collapsed #desktopSidebarToggle i { padding-left: 14px; font-size: 24px; }
        .sidebar.collapsed .flex-col.items-center { margin-bottom: 1rem; }
        .sidebar.collapsed .sidebar-link { justify-content: center; padding: 0.5rem 0.75rem; }
        .sidebar.collapsed .sidebar-link i { margin-right: 0; width: auto; }
        #desktopSidebarToggle { transform: translateX(14px); }
        html.sidebar-collapsed #sidebar { width: 5rem !important; }
        html.sidebar-collapsed #sidebar .sidebar-title,
        html.sidebar-collapsed #sidebar span:not(.sidebar-mini),
        html.sidebar-collapsed #sidebar .dropdown-content,
        html.sidebar-collapsed #sidebar p,
        html.sidebar-collapsed #sidebar h4,
        html.sidebar-collapsed #sidebar .w-24 { display: none; }
        html.sidebar-collapsed #sidebar i { font-size: 1.25rem; margin-right: 0; }
        html.sidebar-collapsed #sidebar #desktopSidebarToggle i { padding-left: 14px; font-size: 24px; }
        html.sidebar-collapsed #sidebar .flex-col.items-center { margin-bottom: 1rem; }
        html.sidebar-collapsed #sidebar .sidebar-link { justify-content: center; padding: 0.5rem 0.75rem; }
        html.sidebar-collapsed #sidebar .sidebar-link i { margin-right: 0; width: auto; }
        .sidebar-link { transition: all 0.2s; border-radius: 0.375rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; color: #475569; font-weight: 500; white-space: nowrap; font-size: 0.9rem; }
        .sidebar-link i { width: 24px; text-align: center; margin-right: 12px; }
        .sidebar-link:hover { background-color: #f1f5f9; color: #0ea5e9; }
        .sidebar-link.active { background-color: #0ea5e9; color: white; }
        .sidebar-link.active:hover { background-color: #0284c7; color: white; }
        .sidebar-menu-scroll {
            overflow-y: auto;
            max-height: calc(100vh - 19rem);
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        .content-area { background-color: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); padding: 1.5rem; }
        /* Ayarlar sayfasindaki gibi baslik ust boslugunu standartlastir */
        .content-area > :first-child:has(.header-title) { margin-top: -1.5rem; }
        .header-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; }
        .dropdown-content { display: none; position: absolute; right: 0; min-width: 180px; z-index: 50; background-color: white; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); border-radius: 0.375rem; overflow: hidden; opacity: 0; visibility: hidden; transition: opacity 0.3s, visibility 0.3s; }
        .dropdown:hover .dropdown-content { display: block; opacity: 1; visibility: visible; }

        /* Tum modal'lar icin ortak sabit/ortali davranis */
        [id$="Modal"].fixed {
            position: fixed !important;
            inset: 0 !important;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem !important;
            overflow-y: auto !important;
        }
        [id$="Modal"].fixed:not(.hidden) {
            display: flex !important;
        }
        [id$="Modal"].fixed > div {
            width: 100% !important;
            max-width: min(100%, 64rem) !important;
            max-height: calc(100vh - 2rem) !important;
            overflow: hidden !important;
            margin: auto !important;
        }
        [id$="Modal"].fixed .max-h-\[calc\(100vh-200px\)\] {
            max-height: calc(100vh - 13rem) !important;
            overflow-y: auto !important;
        }
        table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #e2e8f0;
            color: #1f2937 !important;
            font-weight: 700 !important;
        }
        /* Modal yazi puntosunu tablo satirlarina esitle */
        [id$="Modal"].fixed,
        #modal.fixed {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        [id$="Modal"].fixed :is(p, label, span, li, a, button, input, select, textarea, td, th, div),
        #modal.fixed :is(p, label, span, li, a, button, input, select, textarea, td, th, div) {
            font-size: 0.75rem !important;
            line-height: 1rem !important;
        }
        [id$="Modal"].fixed :is(p, li),
        #modal.fixed :is(p, li) {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
    </style>
</head>
<body>
    <div class="flex h-screen bg-secondary-100">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-64 md:block h-screen flex flex-col overflow-hidden transition-all duration-300 ease-in-out">
            <div class="flex items-center justify-between h-14 border-b border-secondary-200 px-4 shrink-0">
                <span class="text-xl font-bold text-secondary-900">
                    <i class="fa-solid fa-graduation-cap fa-xl" style="color: #0ea5e9;"></i>
                    <span class="sidebar-title">Öğretmen Portalı</span>
                </span>
                <button id="desktopSidebarToggle" class="text-secondary-500 hover:text-secondary-700 hidden md:block flex items-center justify-center">
                    <i class="fas fa-bars fa-xl"></i>
                </button>
            </div>
            <div class="mt-4 px-4 pb-6 flex-1 min-h-0 flex flex-col">
                <div class="flex flex-col items-center mb-6">
                    <div class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center mb-3">
                        <i class="fas fa-user-tie text-3xl text-primary-500"></i>
                    </div>
                    <h4 class="text-secondary-900 font-semibold">
                        <?php 
                            $displayName = $_SESSION['name'] ?? 'Kullanıcı';
                            if (isset($_SESSION['surname'])) {
                                $displayName .= ' ' . $_SESSION['surname'];
                            }
                            echo htmlspecialchars($displayName);
                        ?>
                    </h4>
                    <p class="text-secondary-500 text-sm"><?php echo htmlspecialchars($userRoleName ?? 'Yönetici'); ?></p>
                </div>
                <ul class="sidebar-menu-scroll flex-1 min-h-0 pr-1">
                    <?php foreach ($sidebarLinks as $link): ?>
                        <li class="mb-2">
                            <a href="<?php echo $link['url']; ?>" class="sidebar-link <?php echo ($action === $link['action_key'] || (isset($link['aliases']) && in_array($action, $link['aliases']))) ? 'active' : ''; ?>">
                                <i class="<?php echo $link['icon']; ?>"></i>
                                <span><?php echo $link['text']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/logout.php" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')" class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış Yap</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Mobile Sidebar (Yapısı aynı, sadece mobil için) -->
        <div id="mobileSidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden transition duration-200 ease-in-out z-30 w-64 bg-white shadow-lg">
            <div class="flex items-center justify-between h-16 border-b border-secondary-200 px-4">
                <span class="text-xl font-bold text-secondary-900">
                    <i class="fas fa-school text-primary-500 mr-2"></i>
                    Öğretmen Portalı
                </span>
                <button id="closeSidebar" class="text-secondary-500 hover:text-secondary-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4 px-4 pb-6">
                <div class="flex flex-col items-center mb-6">
                    <div class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center mb-3">
                        <i class="fas fa-user-tie text-3xl text-primary-500"></i>
                    </div>
                    <h4 class="text-secondary-900 font-semibold">
                        <?php 
                            $displayName = $_SESSION['name'] ?? 'Kullanıcı';
                            if (isset($_SESSION['surname'])) {
                                $displayName .= ' ' . $_SESSION['surname'];
                            }
                            echo htmlspecialchars($displayName);
                        ?>
                    </h4>
                    <p class="text-secondary-500 text-sm"><?php echo htmlspecialchars($userRoleName ?? 'Yönetici'); ?></p>
                </div>
                <ul class="sidebar-menu-scroll">
                    <?php foreach ($sidebarLinks as $link): ?>
                        <li class="mb-2">
                            <a href="<?php echo $link['url']; ?>" class="sidebar-link <?php echo ($action === $link['action_key'] || (isset($link['aliases']) && in_array($action, $link['aliases']))) ? 'active' : ''; ?>">
                                <i class="<?php echo $link['icon']; ?>"></i>
                                <span><?php echo $link['text']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/logout.php" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')" class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış Yap</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white h-14 border-b border-secondary-200">
                <div class="flex items-center justify-between h-14 px-6">
                    <div class="flex items-center">
                        <button id="menuBtn" class="text-secondary-500 hover:text-secondary-700 md:hidden">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    <div class="flex items-center">
                        <div class="relative dropdown">
                            <button class="flex items-center focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-2">
                                    <i class="fas fa-user-tie text-primary-500"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700 mr-1"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Kullanıcı'); ?></span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                            <div class="dropdown-content mt-1 py-2 bg-white rounded-md shadow-lg">
                                <?php if (isset($settingsLink)): ?>
                                <a href="<?php echo $settingsLink; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i> Ayarlar
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/php/logout.php" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-6">
                <?php
                // Başarı/hata mesajlarını göster
                if (isset($_GET['status']) && isset($_GET['message'])) {
                    $status = $_GET['status'];
                    $message = htmlspecialchars($_GET['message']);
                    $alertClass = $status === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700';
                    echo "<div class='{$alertClass} p-4 mb-6 rounded-md' role='alert'>";
                    echo "<p>{$message}</p></div>";
                }
                ?>
                
                <?php 
                // Ana içeriği dahil et
                if (isset($mainContentFile) && file_exists($mainContentFile)) {
                    include $mainContentFile;
                } else {
                    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>İçerik dosyası bulunamadı: " . htmlspecialchars($mainContentFile ?? 'yok') . "</div>";
                }
                ?>
            </main>
        </div>
    </div>
    
    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
        
        if (desktopSidebarToggle) {
            desktopSidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                document.documentElement.classList.toggle('sidebar-collapsed', isCollapsed);
                if (typeof(Storage) !== "undefined") {
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof(Storage) !== "undefined") {
                const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
                if (collapsed && sidebar) {
                    sidebar.classList.add('collapsed');
                } else if (sidebar) {
                    sidebar.classList.remove('collapsed');
                }
            }
        });
        
        // Mobile sidebar toggle
        const mobileSidebar = document.getElementById('mobileSidebar');
        document.getElementById('menuBtn')?.addEventListener('click', function() {
            if (mobileSidebar) mobileSidebar.classList.remove('-translate-x-full');
        });
        
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            if (mobileSidebar) mobileSidebar.classList.remove('-translate-x-full');
        });
        
        document.getElementById('closeSidebar')?.addEventListener('click', function() {
            if (mobileSidebar) mobileSidebar.classList.add('-translate-x-full');
        });

        // Modal acik/kapali durumuna gore sayfa scroll kilidi
        function syncGlobalModalScrollLock() {
            const anyOpenModal = Array.from(document.querySelectorAll('[id$="Modal"].fixed'))
                .some(modal =>
                    !modal.classList.contains('hidden') &&
                    window.getComputedStyle(modal).display !== 'none'
                );
            document.documentElement.style.overflow = anyOpenModal ? 'hidden' : '';
            document.body.style.overflow = anyOpenModal ? 'hidden' : '';
        }
        const modalObserver = new MutationObserver(syncGlobalModalScrollLock);
        document.querySelectorAll('[id$="Modal"].fixed').forEach((modal) => {
            modalObserver.observe(modal, { attributes: true, attributeFilter: ['class', 'style'] });
        });
        syncGlobalModalScrollLock();
        
        // Close alerts
        document.querySelectorAll('.alert-close').forEach(function(button) {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>