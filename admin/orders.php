<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

global $conn;
$orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC");
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Замовлення - Адмін-панель</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-new { background: #E3F2FD; color: #1565C0; }
        .status-processing { background: #FFF3E0; color: #E65100; }
        .status-shipped { background: #E8F5E9; color: #2E7D32; }
        .status-completed { background: #E0F7F5; color: #0ABAB5; }
        .status-cancelled { background: #FFE0E0; color: #D32F2F; }
        select.status-select { padding: 5px; border-radius: 8px; border: 1px solid #ddd; font-size: 13px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>
        <main class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-truck"></i> Замовлення</h1>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Клієнт</th><th>Тел.</th><th>Сума</th><th>Дата</th><th>Статус</th><th>Дії</th></tr>
                    </thead>
                    <tbody>
                        <?php while($o = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $o['id']; ?></td>
                            <td><?php echo htmlspecialchars($o['full_name']); ?> (<?php echo htmlspecialchars($o['username']); ?>)</td>
                            <td><?php echo htmlspecialchars($o['phone']); ?></td>
                            <td><?php echo number_format($o['total_price'], 0, ',', ' '); ?> грн</td>
                            <td><?php echo date('d.m.Y H:i', strtotime($o['order_date'])); ?></td>
                            <td>
                                <select class="status-select" onchange="updateStatus(<?php echo $o['id']; ?>, this.value)">
                                    <option value="new" <?php echo $o['status'] == 'new' ? 'selected' : ''; ?>>Нове</option>
                                    <option value="processing" <?php echo $o['status'] == 'processing' ? 'selected' : ''; ?>>В обробці</option>
                                    <option value="shipped" <?php echo $o['status'] == 'shipped' ? 'selected' : ''; ?>>Відправлено</option>
                                    <option value="completed" <?php echo $o['status'] == 'completed' ? 'selected' : ''; ?>>Виконано</option>
                                    <option value="cancelled" <?php echo $o['status'] == 'cancelled' ? 'selected' : ''; ?>>Скасовано</option>
                                </select>
                            </td>
                            <td>
                                <button onclick="viewOrder(<?php echo $o['id']; ?>)" class="btn-edit"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
    function updateStatus(id, status) {
        fetch('../ajax/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&status=' + status
        }).then(r => r.json()).then(d => {
            if(d.status === 'success') alert('Статус оновлено!');
        });
    }
    function viewOrder(id) { alert('Деталі замовлення #' + id + ' (в розробці)'); }
    </script>
</body>
</html>