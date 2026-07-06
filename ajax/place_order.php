<?php
header('Access-Control-Allow-Origin: *');
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Необхідно увійти в акаунт']);
    exit();
}

global $conn;

// Отримуємо дані з форми
$email = sanitize($_POST['email'] ?? '');
$first_name = sanitize($_POST['first_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$nova_poshta = sanitize($_POST['nova_poshta'] ?? '');
$contact_method = sanitize($_POST['contact_method'] ?? '');
$telegram_nick = sanitize($_POST['telegram_nick'] ?? '');
$comment = sanitize($_POST['comment'] ?? '');
$user_id = $_SESSION['user_id'];

// Формуємо повну адресу для збереження
$full_address = trim($city . ', ' . $nova_poshta);

// Перевіряємо, чи є товари в кошику
$stmt = $conn->prepare("SELECT c.quantity, p.id, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Кошик порожній']);
    exit();
}

// Підраховуємо суму
$total = 0;
$items = [];
while($row = $result->fetch_assoc()) {
    $total += $row['price'] * $row['quantity'];
    $items[] = $row;
}
$stmt->close();

// Створюємо замовлення (зберігаємо всі поля)
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, full_name, phone, address, comment) VALUES (?, ?, ?, ?, ?, ?)");
$full_name = $first_name . ' ' . $last_name;
$stmt->bind_param("idssss", $user_id, $total, $full_name, $phone, $full_address, $comment);
$stmt->execute();
$order_id = $conn->insert_id;
$stmt->close();

// Додаємо товари в order_items
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach($items as $item) {
    $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
    $stmt->execute();
}
$stmt->close();

// Очищуємо кошик
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// --- ТЕЛЕГРАМ-СПОВІЩЕННЯ (якщо потрібно) ---
$telegram_token = 'ВАШ_ТОКЕН_БОТА'; // Змініть на ваш
$chat_id = 'ВАШ_CHAT_ID';           // Змініть на ваш
if ($telegram_token !== 'ВАШ_ТОКЕН_БОТА') {
    $message = "🛍️ НОВЕ ЗАМОВЛЕННЯ #$order_id!\n\n";
    $message .= "👤 Ім'я: $full_name\n";
    $message .= "📞 Телефон: $phone\n";
    $message .= "📧 Email: $email\n";
    $message .= "🏠 Адреса: $full_address\n";
    $message .= "💬 Коментар: $comment\n";
    $message .= "💰 Сума: " . number_format($total, 0, ',', ' ') . " грн\n";
    $message .= "📱 Зв'язок: $contact_method" . ($telegram_nick ? " (@$telegram_nick)" : "");
    file_get_contents("https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$chat_id&text=" . urlencode($message));
}

echo json_encode(['status' => 'success']);
?>