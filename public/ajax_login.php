<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if ($email === '' || $password === '') {
  echo json_encode(['ok'=>false,'message'=>'Email and password are required.']);
  exit;
}

if (login($email, $password)) {
  echo json_encode(['ok'=>true]);
} else {
  echo json_encode(['ok'=>false,'message'=>'Invalid credentials or account not allowed.']);
}
