<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Sadece süper admin erişebilir
if (!isset($_SESSION['role']) || $_SESSION['role'] !== ROLE_SUPER_ADMIN) {
    header('Location: ' . BASE_URL . '/php/login.php');
    exit;
}

// Veritabanı işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = clean_input($_POST['name']);
                $email = clean_input($_POST['email']);
                $phone = clean_input($_POST['phone']);
                $school_id = clean_input($_POST['school_id']);
                $password = password_hash(clean_input($_POST['password']), PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("INSERT INTO school_admins (name, email, phone, school_id, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $school_id, $password]);
                break;

            case 'edit':
                $id = clean_input($_POST['id']);
                $name = clean_input($_POST['name']);
                $email = clean_input($_POST['email']);
                $phone = clean_input($_POST['phone']);
                $school_id = clean_input($_POST['school_id']);
                
                $sql = "UPDATE school_admins SET name = ?, email = ?, phone = ?, school_id = ? WHERE id = ?";
                if (!empty($_POST['password'])) {
                    $sql = "UPDATE school_admins SET name = ?, email = ?, phone = ?, school_id = ?, password = ? WHERE id = ?";
                    $password = password_hash(clean_input($_POST['password']), PASSWORD_DEFAULT);
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $email, $phone, $school_id, $password, $id]);
                } else {
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $email, $phone, $school_id, $id]);
                }
                break;

            case 'delete':
                $id = clean_input($_POST['id']);
                $stmt = $db->prepare("DELETE FROM school_admins WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
        header('Location: ' . BASE_URL . '/php/school_admins.php');
        exit;
    }
}

// Okul listesini al
$schools = $db->query("SELECT id, name FROM schools ORDER BY name")->fetchAll();

// Okul yöneticilerini listele
$stmt = $db->query("
    SELECT sa.*, s.name as school_name 
    FROM school_admins sa 
    LEFT JOIN schools s ON sa.school_id = s.id 
    ORDER BY sa.name
");
$school_admins = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Okul Yöneticileri - Öğretmen Portalı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #e2e8f0;
            color: #1f2937 !important;
            font-weight: 700 !important;
        }
        #modal.fixed {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        #modal.fixed :is(p, label, span, li, a, button, input, select, textarea, td, th, div) {
            font-size: 0.75rem !important;
            line-height: 1rem !important;
        }
        #modal.fixed :is(p, li) {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Okul Yöneticileri</h1>
        
        <button onclick="showAddModal()" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">
            Yeni Okul Yöneticisi Ekle
        </button>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ad Soyad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-posta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Okul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($school_admins as $admin): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($admin['name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($admin['email']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($admin['phone']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($admin['school_name']) ?></td>
                        <td class="px-6 py-4">
                            <button onclick="showEditModal(<?= htmlspecialchars(json_encode($admin)) ?>)" 
                                    class="text-blue-600 hover:text-blue-800 mr-2">Düzenle</button>
                            <button onclick="deleteAdmin(<?= $admin['id'] ?>)" 
                                    class="text-red-600 hover:text-red-800">Sil</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Template -->
    <div id="modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h2 id="modalTitle" class="text-xl font-bold mb-4">Yeni Okul Yöneticisi</h2>
                <form id="adminForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="adminId">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Ad Soyad</label>
                        <input type="text" name="name" id="adminName" required
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">E-posta</label>
                        <input type="email" name="email" id="adminEmail" required
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Telefon</label>
                        <input type="tel" name="phone" id="adminPhone" required
                               class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Okul</label>
                        <select name="school_id" id="adminSchool" required
                                class="w-full px-3 py-2 border rounded">
                            <option value="">Seçiniz</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?= $school['id'] ?>"><?= htmlspecialchars($school['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Şifre</label>
                        <input type="password" name="password" id="adminPassword"
                               class="w-full px-3 py-2 border rounded">
                        <small class="text-gray-500">Düzenleme yaparken boş bırakılabilir</small>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal()" 
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2">İptal</button>
                        <button type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Yeni Okul Yöneticisi';
            document.getElementById('formAction').value = 'add';
            document.getElementById('adminId').value = '';
            document.getElementById('adminForm').reset();
            document.getElementById('adminPassword').required = true;
            document.getElementById('modal').classList.remove('hidden');
        }

        function showEditModal(admin) {
            document.getElementById('modalTitle').textContent = 'Okul Yöneticisi Düzenle';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('adminId').value = admin.id;
            document.getElementById('adminName').value = admin.name;
            document.getElementById('adminEmail').value = admin.email;
            document.getElementById('adminPhone').value = admin.phone;
            document.getElementById('adminSchool').value = admin.school_id;
            document.getElementById('adminPassword').required = false;
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        function deleteAdmin(id) {
            if (confirm('Bu okul yöneticisini silmek istediğinize emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
