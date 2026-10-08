<?php
header("Content-Type: application/json");

session_start();
require_once "../phpconexao/conexao.php";

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado"
    ]);
    exit;
}

try {

    $stmt = $conexao->prepare("
        SELECT 
    u.id,
    u.nome,
    u.email,
    u.telefone,
    u.genero,
    u.data_nascimento,
    u.foto,
    u.tipo,
    u.created_at,

    p.numero_mecanografico,

    d.id AS disciplina_id,
    d.nome AS disciplina

FROM usuarios u

LEFT JOIN professores p
    ON p.usuario_id = u.id

LEFT JOIN disciplinas d
    ON d.id = p.disciplina_id

WHERE u.id = :id

LIMIT 1
    ");

    $stmt->execute([":id" => $user_id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "Utilizador não encontrado"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $user
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}