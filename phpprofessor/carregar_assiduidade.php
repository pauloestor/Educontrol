<?php
session_start();
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

$professor_id = $_SESSION['user_id'] ?? null;

if (!$professor_id) {
    echo json_encode([
        "success" => false,
        "message" => "Não autenticado"
    ]);
    exit;
}

try {

    // =========================
    // RESUMO GERAL
    // =========================
    $stmt = $conexao->prepare("
        SELECT 
            COUNT(*) as total_dias,
            SUM(CASE WHEN estado = 'Presente' THEN 1 ELSE 0 END) as presencas,
            SUM(CASE WHEN estado = 'Falta' THEN 1 ELSE 0 END) as faltas,
            SUM(CASE WHEN estado = 'Atraso' THEN 1 ELSE 0 END) as atrasos
        FROM presencas
        WHERE professor_id = ?
    ");

    $stmt->execute([$professor_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    $dias_trabalhados = (int)($dados['total_dias'] ?? 0);
    $presencas = (int)($dados['presencas'] ?? 0);
    $faltas = (int)($dados['faltas'] ?? 0);
    $atrasos = (int)($dados['atrasos'] ?? 0);

    $percentagem = $dias_trabalhados > 0
        ? round(($presencas / $dias_trabalhados) * 100, 1)
        : 0;

    // =========================
    // RESUMO MENSAL
    // =========================
    $stmt2 = $conexao->prepare("
        SELECT 
            DATE_FORMAT(data_presenca, '%M %Y') as mes,
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'Presente' THEN 1 ELSE 0 END) as presencas,
            SUM(CASE WHEN estado = 'Falta' THEN 1 ELSE 0 END) as faltas,
            SUM(CASE WHEN estado = 'Atraso' THEN 1 ELSE 0 END) as atrasos,
            ROUND((SUM(CASE WHEN estado = 'Presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as percentagem
        FROM presencas
        WHERE professor_id = ?
        GROUP BY YEAR(data_presenca), MONTH(data_presenca)
        ORDER BY data_presenca DESC
        LIMIT 6
    ");

    $stmt2->execute([$professor_id]);
    $mensal = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "dias_trabalhados" => $dias_trabalhados,
            "presencas" => $presencas,
            "faltas" => $faltas,
            "atrasos" => $atrasos,
            "percentagem" => $percentagem,
            "mensal" => $mensal
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao carregar assiduidade",
        "erro" => $e->getMessage()
    ]);
}