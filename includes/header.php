<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen İş Başvuru Sistemi</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            100: '#E9E3FF',
                            200: '#D3C8FF',
                            300: '#A594FF',
                            400: '#8A75FF',
                            500: '#6C4FFE',
                            600: '#5736FE',
                            700: '#4020E4',
                            800: '#341BBD',
                            900: '#251494',
                        },
                        secondary: {
                            500: '#FF6584',
                        }
                    },
                    animation: {
                        float: 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Custom CSS -->
    <style>
        html {
            font-size: clamp(14px, 0.9vw + 0.6rem, 18px);
        }
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.5;
        }
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
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Poppins', sans-serif;
        }
        .sticky-header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
            background: linear-gradient(to right, #0f766e, #0d9488);
        }
        .sticky-header.scrolled {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .sticky-header.scrolled .nav-link {
            color: #1f2937;
        }
        .sticky-header.scrolled .nav-link:hover {
            color: #14b8a6;
        }
        .sticky-header.scrolled .brand-name {
            color: #1f2937;
        }
        .sticky-header.scrolled .auth-button-login {
            background-color: rgba(20, 184, 166, 0.1) !important;
            color: #14b8a6 !important;
            border: 1px solid #14b8a6;
        }
        .sticky-header.scrolled .auth-button-login:hover {
            background-color: #14b8a6 !important;
            color: white !important;
        }
        .sticky-header.scrolled .auth-button-register {
            background-color: #14b8a6 !important;
            color: white !important;
        }
        .sticky-header.scrolled .auth-button-register:hover {
            background-color: #0d9488 !important;
        }
        .sticky-header.scrolled #mobile-menu-button {
            color: #1f2937 !important;
        }
        .sticky-header.scrolled #mobile-menu-button:hover {
            color: #14b8a6 !important;
        }
        [x-cloak] {
            display: none !important;
        }
        table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #e2e8f0;
            color: #1f2937 !important;
            font-weight: 700 !important;
        }
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
    <!-- Mesajlar -->
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <div class="fixed top-0 right-0 m-6 z-50">
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-lg mb-4 transform transition-all duration-500 alert-dismissible flex items-center justify-between max-w-md">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"><?php echo $_SESSION['success']; ?></p>
                </div>
            </div>
            <button class="close-alert text-green-600 hover:text-green-800 focus:outline-none">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['success']); endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-lg mb-4 transform transition-all duration-500 alert-dismissible flex items-center justify-between max-w-md">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"><?php echo $_SESSION['error']; ?></p>
                </div>
            </div>
            <button class="close-alert text-red-600 hover:text-red-800 focus:outline-none">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['error']); endif; ?>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="sticky-header py-4 text-white bg-transparent">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <?php if (!strpos($_SERVER['PHP_SELF'], 'login.php')): ?>
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center">
                        <div class="mr-2 h-10 w-10 bg-gradient-to-br from-teal-700 to-teal-800 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                        <span class="font-bold text-xl brand-name">ÖğretmenPro</span>
                    </a>
                </div>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
                
                <!-- Desktop Navigation -->
                <nav class="hidden lg:block">
                    <ul class="flex space-x-8">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="nav-link font-medium hover:text-teal-200 transition py-2 px-1">Ana Sayfa</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/php/schools.php" class="nav-link font-medium hover:text-teal-200 transition py-2 px-1">Okullar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/php/about.php" class="nav-link font-medium hover:text-teal-200 transition py-2 px-1">Hakkımızda</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/php/contact.php" class="nav-link font-medium hover:text-teal-200 transition py-2 px-1">İletişim</a></li>
                    </ul>
                </nav>
                
                <!-- Auth Buttons / User Menu -->
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative" x-data="{ isOpen: false }">
                            <button @click="isOpen = !isOpen" class="flex items-center space-x-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-full py-2 px-4 transition focus:outline-none">
                                <div class="w-8 h-8 bg-teal-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                    <?php echo substr($_SESSION['name'], 0, 1); ?>
                                </div>
                                <span class="hidden sm:inline"><?php echo htmlspecialchars($_SESSION['name'] . (isset($_SESSION['surname']) ? ' ' . $_SESSION['surname'] : '')); ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="isOpen" @click.away="isOpen = false" class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg overflow-hidden z-20 transform origin-top-right transition-all duration-200" x-cloak>
                                <div class="p-4 border-b border-gray-100">
                                    <p class="text-sm text-gray-500">Hoş geldiniz</p>
                                    <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($_SESSION['name'] . (isset($_SESSION['surname']) ? ' ' . $_SESSION['surname'] : '')); ?></p>
                                </div>
                                <div class="py-2">
                                    
                                    
                                    <?php if ($_SESSION['role'] == ROLE_TEACHER): ?>
                                        <a href="<?php echo BASE_URL; ?>/php/applications.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-teal-600">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Başvurularım
                                            </div>
                                        </a>
                                    <?php elseif ($_SESSION['role'] == ROLE_SCHOOL_ADMIN): ?>
                                        <a href="<?php echo BASE_URL; ?>/php/manage_applications.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-teal-600">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Başvuru Yönetimi
                                            </div>
                                        </a>
                                    <?php elseif ($_SESSION['role'] == ROLE_ADMIN): ?>
                                        <a href="<?php echo BASE_URL; ?>/php/admin_panel.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-teal-600">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Yönetim Paneli
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="border-t border-gray-100">
                                    <a href="<?php echo BASE_URL; ?>/php/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-red-600">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Çıkış Yap
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if (!strpos($_SERVER['PHP_SELF'], 'login.php')): ?>
                        <div class="hidden md:flex space-x-3">
                            <a href="<?php echo BASE_URL; ?>/php/login.php" class="auth-button-login px-5 py-2.5 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-full font-medium transition-all">
                                Giriş Yap
                            </a>
                            <a href="<?php echo BASE_URL; ?>/php/register.php" class="auth-button-register px-5 py-2.5 bg-white text-teal-600 hover:bg-gray-100 rounded-full font-medium transition-all shadow-md">
                                Kayıt Ol
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="lg:hidden text-white focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="lg:hidden hidden mt-4 p-4 bg-white rounded-xl shadow-lg absolute left-4 right-4 z-20">
                <nav class="mb-4">
                    <ul class="space-y-3">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="block text-gray-800 font-medium hover:text-teal-600 transition">Ana Sayfa</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/php/schools.php" class="block text-gray-800 font-medium hover:text-teal-600 transition">Okullar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/php/about.php" class="block text-gray-800 font-medium hover:text-teal-600 transition">Hakkımızda</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/php/contact.php" class="block text-gray-800 font-medium hover:text-teal-600 transition">İletişim</a></li>
                    </ul>
                </nav>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4">
                    <a href="<?php echo BASE_URL; ?>/php/login.php" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg font-medium text-center transition-all hover:bg-gray-200">
                        Giriş Yap
                    </a>
                    <a href="<?php echo BASE_URL; ?>/php/register.php" class="px-4 py-2 bg-teal-600 text-white rounded-lg font-medium text-center transition-all hover:bg-teal-700">
                        Kayıt Ol
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
</body>
</html>