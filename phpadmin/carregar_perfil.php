<?php
session_start();
header("Content-Type: application/json");

require_once "../phpconexao/conexao.php";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erro de conexão"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado"
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT nome, genero, email, telefone, tipo, DATE(created_at) AS created_at, foto FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode([
        "success" => true,
        "data" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não encontrado"
    ]);
}