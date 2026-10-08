<?php
header("Content-Type: application/json");

session_start();

require_once "../phpconexao/conexao.php";

// ===============================
// USUÁRIO LOGADO
// ===============================
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

// validação simples
if (strpos($file['type'], "image/") !== 0) {
    echo json_encode([
        "success" => false,
        "message" => "Ficheiro inválido"
    ]);
    exit;
}

// ===============================
// PASTA UPLOAD
// ===============================
$uploadDir = "../uploads/perfis/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ===============================
// NOME ÚNICO
// ===============================
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

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

try {

    // AQUI ESTÁ A CORREÇÃO PRINCIPAL
    $stmt = $conexao->prepare("
        UPDATE usuarios 
        SET foto = :foto 
        WHERE id = :id
    ");

    $stmt->execute([
        ":foto" => $pathBD,
        ":id" => $user_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Foto atualizada com sucesso",
        "foto" => $pathBD
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}