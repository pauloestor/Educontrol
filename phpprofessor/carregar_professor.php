<?php
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

try {

    $stmt = $conexao->prepare("
        SELECT 
            u.nome,
            u.email,
            u.genero,
            p.numero_mecanografico,
            p.disciplina_id,
            d.nome AS disciplina
        FROM usuarios u
        INNER JOIN professores p ON p.usuario_id = u.id
        LEFT JOIN disciplinas d ON p.disciplina_id = d.id
        WHERE u.id = ?
    ");

    $stmt->execute([$user_id]);

    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $professor
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}