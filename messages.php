<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';
$userId = $_SESSION['user_id'];

// Get users for recipient list
$usersQ = mysqli_query($conn, "SELECT id, name, username, role FROM users WHERE id != $userId AND status = 'active' ORDER BY name");
$usersList = [];
while ($u = mysqli_fetch_assoc($usersQ)) { $usersList[] = $u; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - FraudShield</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'includes/loader.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include 'includes/dashboard_header.php'; ?>
        <div class="page-content">
            <div class="page-title">
                <h1><i class="ri-mail-line"></i> Messages</h1>
                <p>Internal messaging system</p>
            </div>

            <div class="dashboard-grid">
                <!-- Compose -->
                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-edit-line"></i> Compose Message</h3></div>
                    <div class="dash-card-body">
                        <form id="msgForm" class="dash-form">
                            <div class="form-group">
                                <label>Recipient</label>
                                <select name="recipient_id" required>
                                    <option value="">Select recipient...</option>
                                    <?php foreach ($usersList as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= ucfirst($u['role']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" placeholder="Message subject" required>
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" rows="4" placeholder="Type your message..." required></textarea>
                            </div>
                            <button type="submit" class="btn-primary-full"><i class="ri-send-plane-line"></i> Send Message</button>
                        </form>
                    </div>
                </div>

                <!-- Inbox -->
                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-inbox-line"></i> Inbox</h3></div>
                    <div class="dash-card-body">
                        <div class="message-list" id="messageList">
                            <div class="loading-cell">Loading messages...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        function loadMessages() {
            fetch('fraudBackend.php?action=getMessages')
                .then(r => r.json())
                .then(data => {
                    const myId = <?= $userId ?>;
                    document.getElementById('messageList').innerHTML = data.data.length > 0 ?
                        data.data.map(m => `
                            <div class="message-item ${m.is_read == 0 && m.recipient_id == myId ? 'unread' : ''}" onclick="viewMessage(${m.id}, ${m.recipient_id}, '${escapeHtml(m.sender_name)}', '${escapeHtml(m.subject)}', \`${escapeHtml(m.message)}\`)">
                                <div class="msg-header">
                                    <strong>${m.sender_id == myId ? 'To: ' + escapeHtml(m.recipient_name) : escapeHtml(m.sender_name)}</strong>
                                    <small>${formatDate(m.created_at)}</small>
                                </div>
                                <div class="msg-subject">${escapeHtml(m.subject)}</div>
                                <div class="msg-preview">${escapeHtml(m.message).substring(0, 80)}...</div>
                            </div>
                        `).join('')
                        : '<div class="loading-cell">No messages yet</div>';
                });
        }

        function viewMessage(id, recipientId, sender, subject, message) {
            const myId = <?= $userId ?>;
            if (recipientId == myId) {
                const fd = new FormData();
                fd.append('action', 'markMessageRead');
                fd.append('message_id', id);
                fetch('fraudBackend.php', {method:'POST', body:fd});
            }
            Swal.fire({title: subject, html: `<p><strong>From:</strong> ${sender}</p><hr><p>${message}</p>`, confirmButtonColor: '#D97706'})
                .then(() => loadMessages());
        }

        document.getElementById('msgForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'sendMessage');
            fetch('fraudBackend.php', {method:'POST', body:fd})
                .then(r => r.json())
                .then(data => {
                    Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, timer:1500, showConfirmButton:false})
                        .then(() => { if (data.success) { this.reset(); loadMessages(); } });
                });
        });

        loadMessages();
    </script>
</body>
</html>
