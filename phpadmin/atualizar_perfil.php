<?php
// ===============================
// ATUALIZAR PERFIL
// ===============================

session_start();
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Não autenticado"
    ]);
    exit;
}

// dados recebidos
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';

if (empty($email) || empty($telefone)) {
    echo json_encode([
        "success" => false,
        "message" => "Dados inválidos"
    ]);
    exit;
}

try {

    // ===============================
    // UPDATE PERFIL
    // ===============================
    $stmt = $conexao->prepare("
        UPDATE usuarios 
        SET email = :email,
            telefone = :telefone
        WHERE id = :id
    ");

    $stmt->execute([
        ":email" => $email,
        ":telefone" => $telefone,
        ":id" => $user_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Perfil atualizado com sucesso"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao atualizar perfil",
        "error" => $e->getMessage()
    ]);
}