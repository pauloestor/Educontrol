<?php
header("Content-Type: application/json");

session_start();
require_once "../phpconexao/conexao.php";

// ============================
// VERIFICAR LOGIN
// ============================

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado."
    ]);
    exit;
}

// ============================
// RECEBER DADOS
// ============================

$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

// ============================
// VALIDAÇÕES
// ============================

if (empty($email) || empty($telefone)) {
    echo json_encode([
        "success" => false,
        "message" => "Preencha todos os campos."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Email inválido."
    ]);
    exit;
}

try {

    // ============================
    // VERIFICAR SE EMAIL JÁ EXISTE
    // ============================

    $stmt = $conexao->prepare("
        SELECT id 
        FROM usuarios 
        WHERE email = :email 
        AND id != :id
    ");

    $stmt->execute([
        ":email" => $email,
        ":id" => $user_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Este email já está em uso."
        ]);
        exit;
    }

    // ============================
    // ATUALIZAR
    // ============================

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
        "message" => "Perfil atualizado com sucesso."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
    ]);
}