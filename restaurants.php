<?php
// restaurants.php - Restaurant listing and menu items API
require_once 'config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch ($action) {

    // ── LIST ALL RESTAURANTS ───────────────────────────────────
    case 'list':
        $conn   = getDB();
        $search = sanitize($conn, $_GET['search'] ?? '');
        $cuisine= sanitize($conn, $_GET['cuisine'] ?? '');

        $sql = "SELECT * FROM restaurants WHERE is_open = 1";
        if ($search) {
            $sql .= " AND (name LIKE '%$search%' OR cuisine LIKE '%$search%')";
        }
        if ($cuisine) {
            $sql .= " AND cuisine = '$cuisine'";
        }
        $sql .= " ORDER BY rating DESC";

        $result = $conn->query($sql);
        $restaurants = [];
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
        jsonResponse(['success' => true, 'data' => $restaurants]);
        break;

    // ── GET SINGLE RESTAURANT ──────────────────────────────────
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['success' => false, 'message' => 'Restaurant ID required.']);

        $conn   = getDB();
        $result = $conn->query("SELECT * FROM restaurants WHERE id = $id AND is_open = 1");

        if ($result->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'Restaurant not found.']);
        }

        $restaurant = $result->fetch_assoc();

        // Get menu items grouped by category
        $menuResult = $conn->query(
            "SELECT * FROM menu_items WHERE restaurant_id = $id AND is_available = 1 ORDER BY category, name"
        );

        $menu = [];
        while ($item = $menuResult->fetch_assoc()) {
            $cat = $item['category'];
            if (!isset($menu[$cat])) $menu[$cat] = [];
            $menu[$cat][] = $item;
        }

        $restaurant['menu'] = $menu;
        jsonResponse(['success' => true, 'data' => $restaurant]);
        break;

    // ── GET CUISINES (for filter) ──────────────────────────────
    case 'cuisines':
        $conn   = getDB();
        $result = $conn->query("SELECT DISTINCT cuisine FROM restaurants WHERE is_open = 1 ORDER BY cuisine");
        $cuisines = [];
        while ($row = $result->fetch_assoc()) {
            $cuisines[] = $row['cuisine'];
        }
        jsonResponse(['success' => true, 'data' => $cuisines]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
?>
