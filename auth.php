<?php
// auth.php - Handles login and registration
require_once 'config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ── REGISTER ──────────────────────────────────────────────
    case 'register':
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $password= $_POST['password'] ?? '';
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!$name || !$email || !$password) {
            jsonResponse(['success' => false, 'message' => 'Name, email and password are required.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email address.']);
        }

        if (strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        }

        $conn = getDB();
        $emailSafe = sanitize($conn, $email);

        $check = $conn->query("SELECT id FROM users WHERE email = '$emailSafe'");
        if ($check->num_rows > 0) {
            jsonResponse(['success' => false, 'message' => 'Email already registered. Please login.']);
        }

        $hash    = password_hash($password, PASSWORD_BCRYPT);
        $nameSafe= sanitize($conn, $name);
        $phoneSafe= sanitize($conn, $phone);
        $addrSafe= sanitize($conn, $address);

        $sql = "INSERT INTO users (name, email, password, phone, address)
                VALUES ('$nameSafe', '$emailSafe', '$hash', '$phoneSafe', '$addrSafe')";

        if ($conn->query($sql)) {
            $userId = $conn->insert_id;
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $nameSafe;
            $_SESSION['user_email']= $emailSafe;
            jsonResponse(['success' => true, 'message' => 'Registration successful!', 'user' => ['id' => $userId, 'name' => $nameSafe, 'email' => $emailSafe]]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.']);
        }
        break;

    // ── LOGIN ──────────────────────────────────────────────────
    case 'login':
        $email   = trim($_POST['email'] ?? '');
        $password= $_POST['password'] ?? '';

        if (!$email || !$password) {
            jsonResponse(['success' => false, 'message' => 'Email and password are required.']);
        }

        $conn = getDB();
        $emailSafe = sanitize($conn, $email);
        $result = $conn->query("SELECT id, name, email, password FROM users WHERE email = '$emailSafe'");

        if ($result->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'No account found with this email.']);
        }

        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password'])) {
            jsonResponse(['success' => false, 'message' => 'Incorrect password.']);
        }

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email']= $user['email'];

        jsonResponse(['success' => true, 'message' => 'Login successful!', 'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]]);
        break;

    // ── LOGOUT ────────────────────────────────────────────────
    case 'logout':
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Logged out.']);
        break;

    // ── CHECK SESSION ─────────────────────────────────────────
    case 'check':
        if (isLoggedIn()) {
            jsonResponse(['loggedIn' => true, 'user' => ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name']]]);
        } else {
            jsonResponse(['loggedIn' => false]);
        }
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
?>
