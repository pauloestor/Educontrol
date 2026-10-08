<?php
session_start();
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado."
    ]);
    exit;
}

try {

    // ===========================
    // TOTAL PROFESSORES
    // ===========================
    $stmt = $conexao->query("
        SELECT COUNT(*) AS total
        FROM professores
    ");
    $totalProfessores = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // PROFESSORES CADASTRADOS ESTE MÊS
    // ===========================
    $stmt = $conexao->query("
        SELECT COUNT(*) AS total
FROM professores p
INNER JOIN usuarios u
    ON u.id = p.usuario_id
WHERE MONTH(u.created_at) = MONTH(CURDATE())
AND YEAR(u.created_at) = YEAR(CURDATE())
    ");

    $novosProfessores = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // PRESENTES HOJE
    // ===========================
    $stmt = $conexao->prepare("
        SELECT COUNT(*) AS total
        FROM presencas
        WHERE data_presenca = CURDATE()
        AND estado='Presente'
    ");
    $stmt->execute();

    $presentes = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // ATRASOS HOJE
    // ===========================
    $stmt = $conexao->prepare("
        SELECT COUNT(*) AS total
        FROM presencas
        WHERE data_presenca = CURDATE()
        AND estado='Atraso'
    ");
    $stmt->execute();

    $atrasos = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // FALTAS HOJE
    // ===========================
    $stmt = $conexao->prepare("
        SELECT COUNT(*) AS total
        FROM presencas
        WHERE data_presenca = CURDATE()
        AND estado='Falta'
    ");
    $stmt->execute();

    $faltas = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // DISCIPLINAS
    // ===========================
    $stmt = $conexao->query("
        SELECT COUNT(*) AS total
        FROM disciplinas
    ");

    $disciplinas = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // ESCALAS
    // ===========================
    $stmt = $conexao->query("
        SELECT COUNT(*) AS total
        FROM escalas
    ");

    $escalas = (int)$stmt->fetch(PDO::FETCH_ASSOC)["total"];


    // ===========================
    // TAXA DE PRESENÇA
    // ===========================
    $totalHoje = $presentes + $faltas + $atrasos;

    $taxaPresenca = 0;

    if ($totalHoje > 0) {
        $taxaPresenca = round(($presentes / $totalHoje) * 100);
    }


    // ===========================
    // RESPOSTA
    // ===========================
    echo json_encode([

        "success" => true,

        "dashboard" => [

            "professores" => $totalProfessores,

            "novos_professores" => $novosProfessores,

            "presentes" => $presentes,

            "faltas" => $faltas,

            "atrasos" => $atrasos,

            "disciplinas" => $disciplinas,

            "escalas" => $escalas,

            "taxa_presenca" => $taxaPresenca

        ]

    ]);

} catch (Exception $e) {

    echo json_encode([

        "success" => false,

        "message" => $e->getMessage()

    ]);

}