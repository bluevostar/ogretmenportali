<?php
// Mesajları getir
$messages = [];
try {
    $query = "
        SELECT m.*, 
               u.name as sender_name, 
               u.surname as sender_surname,
               u.email as sender_email
        FROM notifications m
        LEFT JOIN users u ON m.sender_id = u.id
        WHERE m.recipient_id = ?
        ORDER BY m.created_at DESC
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['teacher_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Mesaj getirme hatası: " . $e->getMessage());
}
?>

<div class="space-y-6">
    <!-- Başlık -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mesajlarım</h1>
            <p class="text-gray-500 mt-1">Tüm bildirimlerinizi ve mesajlarınızı buradan görüntüleyebilirsiniz</p>
        </div>
    </div>

    <!-- Mesaj Listesi -->
    <div class="bg-white rounded-lg shadow-sm">
        <?php if (empty($messages)): ?>
            <div class="p-12 text-center">
                <i class="fas fa-envelope-open-text text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Mesajınız Yok</h3>
                <p class="text-gray-500">Henüz mesajınız bulunmuyor.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($messages as $message): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors <?php echo !empty($message['is_read']) ? '' : 'bg-blue-50'; ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <?php if (empty($message['is_read'])): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Yeni
                                        </span>
                                    <?php endif; ?>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($message['title'] ?? 'Bildirim'); ?>
                                    </h3>
                                </div>
                                
                                <?php if (!empty($message['sender_name'])): ?>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-user text-gray-400 mr-2"></i>
                                        <?php echo htmlspecialchars($message['sender_name'] . ' ' . $message['sender_surname']); ?>
                                        <span class="text-gray-400 mx-2">•</span>
                                        <?php echo htmlspecialchars($message['sender_email']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="text-gray-700 mb-3">
                                    <?php echo nl2br(htmlspecialchars($message['message'] ?? '')); ?>
                                </p>
                                
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span>
                                        <i class="far fa-clock mr-1"></i>
                                        <?php
                                        $createdAt = new DateTime($message['created_at']);
                                        echo $createdAt->format('d.m.Y H:i');
                                        ?>
                                    </span>
                                    <?php if (!empty($message['type'])): ?>
                                        <span class="px-2 py-1 bg-gray-100 rounded">
                                            <?php echo htmlspecialchars($message['type']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="ml-4 flex items-start gap-2">
                                <?php if (empty($message['is_read'])): ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=messages">
                                        <input type="hidden" name="mark_read" value="<?php echo $message['id']; ?>">
                                        <button type="submit" class="p-2 text-gray-500 hover:text-blue-600 rounded-lg hover:bg-gray-100" title="Okundu olarak işaretle">
                                            <i class="far fa-check-circle"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <button onclick="deleteMessage(<?php echo $message['id']; ?>)" class="p-2 text-gray-500 hover:text-red-600 rounded-lg hover:bg-gray-100" title="Sil">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteMessage(messageId) {
    if (confirm('Bu mesajı silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>/php/teacher_panel.php?action=messages';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_message';
        input.value = messageId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
// Mesaj işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        try {
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_id = ?");
            $stmt->execute([$_POST['mark_read'], $_SESSION['teacher_id']]);
            header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=messages');
            exit;
        } catch (PDOException $e) {
            error_log("Mesaj okundu işaretleme hatası: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['delete_message'])) {
        try {
            $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND recipient_id = ?");
            $stmt->execute([$_POST['delete_message'], $_SESSION['teacher_id']]);
            header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=messages');
            exit;
        } catch (PDOException $e) {
            error_log("Mesaj silme hatası: " . $e->getMessage());
        }
    }
}
?>
