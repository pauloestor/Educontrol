<?php
// ===============================
// UPLOAD DE FOTO DE PERFIL
// phpadmin/upload_foto.php
// ===============================

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
        "message" => "Erro de conexão com BD"
    ]);
    exit;
}

// ===============================
// VERIFICAR UTILIZADOR (ID)
// ===============================
session_start();

// Se tiveres login com sessão, usa isto:
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado"
    ]);
    exit;
}

// ===============================
// VERIFICAR FICHEIRO
// ===============================
if (!isset($_FILES['foto'])) {
    echo json_encode([
        "success" => false,
        "message" => "Nenhuma imagem enviada"
    ]);
    exit;
}

$file = $_FILES['foto'];

$allowed = ["image/jpeg", "image/png", "image/jpg", "image/webp"];

if (!in_array($file['type'], $allowed)) {
    echo json_encode([
        "success" => false,
        "message" => "Formato de imagem inválido"
    ]);
    exit;
}

// ===============================
// CRIAR PASTA SE NÃO EXISTIR
// ===============================
$uploadDir = "../uploads/perfis/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ===============================
// DEFINIR NOME DO FICHEIRO
// ===============================
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

// nome único baseado no ID do utilizador
$fileName = "user_" . $user_id . "_" . time() . "." . $ext;

$targetFile = $uploadDir . $fileName;

// ===============================
// MOVER FICHEIRO
// ===============================
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao guardar imagem"
    ]);
    exit;
}

// ===============================
// CAMINHO PARA BD
// ===============================
$pathBD = "uploads/perfis/" . $fileName;

// ===============================
// ATUALIZAR BASE DE DADOS
// ===============================
$stmt = $pdo->prepare("
    UPDATE usuarios
    SET foto = :foto
    WHERE id = :id
");

$stmt->execute([
    ":foto" => $pathBD,
    ":id" => $user_id
]);

// ===============================
// RESPOSTA
// ===============================
echo json_encode([
    "success" => true,
    "message" => "Foto atualizada com sucesso",
    "foto" => $pathBD
]);