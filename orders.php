<?php
// orders.php - Place and retrieve orders
require_once 'config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

switch ($action) {

    // ── PLACE ORDER ────────────────────────────────────────────
    case 'place':
        requireLogin();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            // fallback to POST
            $data = $_POST;
        }

        $userId         = $_SESSION['user_id'];
        $restaurantId   = (int)($data['restaurant_id'] ?? 0);
        $deliveryAddress= trim($data['delivery_address'] ?? '');
        $paymentMethod  = $data['payment_method'] ?? 'cash';
        $items          = $data['items'] ?? [];

        if (!$restaurantId || empty($items) || !$deliveryAddress) {
            jsonResponse(['success' => false, 'message' => 'Restaurant, items, and delivery address are required.']);
        }

        $validPayments = ['cash', 'card', 'upi'];
        if (!in_array($paymentMethod, $validPayments)) {
            $paymentMethod = 'cash';
        }

        $conn = getDB();

        // Calculate total from DB prices (never trust client-side totals)
        $total = 0;
        $validItems = [];
        foreach ($items as $item) {
            $menuId  = (int)($item['id'] ?? 0);
            $qty     = max(1, (int)($item['qty'] ?? 1));
            $res = $conn->query("SELECT id, price, name FROM menu_items WHERE id = $menuId AND restaurant_id = $restaurantId AND is_available = 1");
            if ($res->num_rows > 0) {
                $mi = $res->fetch_assoc();
                $validItems[] = ['id' => $menuId, 'qty' => $qty, 'price' => $mi['price'], 'name' => $mi['name']];
                $total += $mi['price'] * $qty;
            }
        }

        if (empty($validItems)) {
            jsonResponse(['success' => false, 'message' => 'No valid items in cart.']);
        }

        $addrSafe = sanitize($conn, $deliveryAddress);
        $conn->begin_transaction();

        try {
            // Insert order
            $conn->query("INSERT INTO orders (user_id, restaurant_id, total_amount, delivery_address, payment_method)
                          VALUES ($userId, $restaurantId, $total, '$addrSafe', '$paymentMethod')");
            $orderId = $conn->insert_id;

            // Insert order items
            foreach ($validItems as $vi) {
                $conn->query("INSERT INTO order_items (order_id, menu_item_id, quantity, price)
                              VALUES ($orderId, {$vi['id']}, {$vi['qty']}, {$vi['price']})");
            }

            $conn->commit();
            jsonResponse([
                'success'  => true,
                'message'  => 'Order placed successfully!',
                'order_id' => $orderId,
                'total'    => number_format($total, 2),
                'items'    => $validItems
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(['success' => false, 'message' => 'Order failed. Please try again.']);
        }
        break;

    // ── LIST USER ORDERS ───────────────────────────────────────
    case 'list':
        requireLogin();
        $userId = $_SESSION['user_id'];
        $conn   = getDB();

        $result = $conn->query(
            "SELECT o.*, r.name AS restaurant_name, r.cuisine
             FROM orders o
             JOIN restaurants r ON o.restaurant_id = r.id
             WHERE o.user_id = $userId
             ORDER BY o.created_at DESC"
        );

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Get items for each order
            $oi = $conn->query(
                "SELECT oi.quantity, oi.price, mi.name
                 FROM order_items oi
                 JOIN menu_items mi ON oi.menu_item_id = mi.id
                 WHERE oi.order_id = {$row['id']}"
            );
            $row['items'] = [];
            while ($item = $oi->fetch_assoc()) {
                $row['items'][] = $item;
            }
            $orders[] = $row;
        }

        jsonResponse(['success' => true, 'data' => $orders]);
        break;

    // ── UPDATE STATUS (admin use) ──────────────────────────────
    case 'update_status':
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status  = sanitize(getDB(), $_POST['status'] ?? '');
        $validStatuses = ['pending','confirmed','preparing','out_for_delivery','delivered','cancelled'];
        if (!$orderId || !in_array($status, $validStatuses)) {
            jsonResponse(['success' => false, 'message' => 'Invalid order ID or status.']);
        }
        $conn = getDB();
        $conn->query("UPDATE orders SET status = '$status' WHERE id = $orderId");
        jsonResponse(['success' => true, 'message' => 'Order status updated.']);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
?>
