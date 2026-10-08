<?php
session_start();
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Não autenticado"
    ]);
    exit;
}

$stmt = $conexao->prepare("
    SELECT 
        u.nome,
        u.foto,
        u.tipo
    FROM usuarios u
    WHERE u.id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $user
]);