<?php
session_start();
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado."
    ]);
    exit;
}

try {

    $sql = "
        SELECT
            nome,
            tipo,
            foto
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$user_id]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $usuario
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}