<?php
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

$professor_id = 1; // depois trocar por SESSION

try {

    // =========================
    // RESUMO DO MÊS
    // =========================
    $stmt = $conexao->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN estado = 'Presente' THEN 1 ELSE 0 END),0) AS presencas,
            COALESCE(SUM(CASE WHEN estado = 'Atrasado' THEN 1 ELSE 0 END),0) AS atrasos,
            COALESCE(SUM(CASE WHEN estado = 'Falta' THEN 1 ELSE 0 END),0) AS faltas
        FROM presencas
        WHERE professor_id = ?
        AND MONTH(data_presenca) = MONTH(CURDATE())
        AND YEAR(data_presenca) = YEAR(CURDATE())
    ");
    $stmt->execute([$professor_id]);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);

    // =========================
    // ÚLTIMA PRESENÇA
    // =========================
    $stmt = $conexao->prepare("
        SELECT data_presenca, hora_entrada, hora_saida
        FROM presencas
        WHERE professor_id = ?
        ORDER BY data_presenca DESC
        LIMIT 1
    ");
    $stmt->execute([$professor_id]);
    $ultima = $stmt->fetch(PDO::FETCH_ASSOC);

    // =========================
    // ATIVIDADES RECENTES
    // =========================
    $stmt = $conexao->prepare("
        SELECT data_presenca, hora_entrada, estado
        FROM presencas
        WHERE professor_id = ?
        ORDER BY data_presenca DESC
        LIMIT 5
    ");
    $stmt->execute([$professor_id]);
    $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "resumo" => $resumo,
            "ultima" => $ultima,
            "atividades" => $atividades
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}