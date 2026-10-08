<?php
header("Content-Type: application/json");
session_start();

require_once "../phpconexao/conexao.php";

//==============================
// DADOS
//==============================
$email = $_POST['username'] ?? '';
$senha = $_POST['password'] ?? '';

//==============================
// VALIDAÇÃO
//==============================
if (empty($email) || empty($senha)) {

    echo json_encode([
        "success" => false,
        "message" => "Preencha todos os campos."
    ]);
    exit;
}

//==============================
// BUSCAR USER
//==============================
$sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
$stmt = $conexao->prepare($sql);
$stmt->bindParam(":email", $email);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

//==============================
// EXISTE?
//==============================
if (!$user) {

    echo json_encode([
        "success" => false,
        "message" => "Utilizador não encontrado."
    ]);
    exit;
}

//==============================
// SENHA
//==============================
if (!password_verify($senha, $user['senha'])) {

    echo json_encode([
        "success" => false,
        "message" => "Senha incorreta."
    ]);
    exit;
}

//==============================
// UPDATE LOGIN
//==============================
$update = $conexao->prepare("
    UPDATE usuarios 
    SET ultimo_login = NOW()
    WHERE id = :id
");

$update->bindParam(":id", $user['id']);
$update->execute();

//==============================
// SESSÃO
//==============================
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['nome'];
$_SESSION['user_type'] = $user['tipo'];

//==============================
// REGRAS DE REDIRECIONAMENTO
//==============================

// 1. PRIMEIRO LOGIN
if ($user['primeiro_login'] == 1) {

    echo json_encode([
        "success" => true,
        "redirect" => "alterar_senha.html",
        "message" => "Primeiro acesso"
    ]);
    exit;
}

// 2. ADMIN
if ($user['tipo'] === "admin") {

    echo json_encode([
        "success" => true,
        "redirect" => "admin.php",
        "message" => "Login admin"
    ]);
    exit;
}

// 3. PROFESSOR
if ($user['tipo'] === "professor") {

    echo json_encode([
        "success" => true,
        "redirect" => "professor.php",
        "message" => "Login professor"
    ]);
    exit;
}

// fallback
echo json_encode([
    "success" => false,
    "message" => "Tipo de utilizador inválido."
]);