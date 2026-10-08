<?php
header("Content-Type: application/json");
session_start();

require_once "../phpconexao/conexao.php";

// ===============================
// VERIFICAR LOGIN
// ===============================
if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Acesso negado. Faça login primeiro."
    ]);
    exit;
}

// ===============================
// DADOS
// ===============================
$nova_senha = $_POST['nova_senha'] ?? '';
$user_id = $_SESSION['user_id'];

// ===============================
// VALIDAÇÃO
// ===============================
if (empty($nova_senha)) {

    echo json_encode([
        "success" => false,
        "message" => "A nova palavra-passe é obrigatória."
    ]);
    exit;
}

// ===============================
// FORÇA MÍNIMA (SEGURANÇA)
// ===============================
if (
    strlen($nova_senha) < 8 ||
    !preg_match('/[A-Z]/', $nova_senha) ||
    !preg_match('/[a-z]/', $nova_senha) ||
    !preg_match('/[0-9]/', $nova_senha) ||
    !preg_match('/[^A-Za-z0-9]/', $nova_senha)
) {
    echo json_encode([
        "success" => false,
        "message" => "A palavra-passe não cumpre os requisitos de segurança."
    ]);
    exit;
}

// ===============================
// HASH DA SENHA
// ===============================
$senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

// ===============================
// UPDATE
// ===============================
try {

    $stmt = $conexao->prepare("
        UPDATE usuarios
        SET senha = :senha,
            primeiro_login = 0,
            updated_at = NOW()
        WHERE id = :id
    ");

    $stmt->bindParam(":senha", $senha_hash);
    $stmt->bindParam(":id", $user_id);

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Palavra-passe alterada com sucesso.",
        "redirect" => "login.html"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao atualizar senha.",
        "error" => $e->getMessage()
    ]);
}