<?php
session_start();
header("Content-Type: application/json");

require_once "../phpconexao/conexao.php";

try {

    $data = date("Y-m-d");

    // ===============================
    // DIA DA SEMANA
    // ===============================
    $dias = [
        "Monday" => "Segunda",
        "Tuesday" => "Terça",
        "Wednesday" => "Quarta",
        "Thursday" => "Quinta",
        "Friday" => "Sexta"
    ];

    $diaSemana = $dias[date("l")] ?? null;

    // ===============================
    // TOTAL PROFESSORES NA ESCALA HOJE
    // ===============================
    $stmt = $conexao->prepare("
        SELECT COUNT(DISTINCT professor_id) as total
        FROM escalas
        WHERE FIND_IN_SET(?, dias_semana)
    ");
    $stmt->execute([$diaSemana]);
    $totalProfessores = (int)$stmt->fetchColumn();

    // ===============================
    // PRESENTES
    // ===============================
    $stmt = $conexao->prepare("
        SELECT COUNT(*)
        FROM presencas
        WHERE data_presenca = ?
        AND estado = 'Presente'
    ");
    $stmt->execute([$data]);
    $presentes = (int)$stmt->fetchColumn();

    // ===============================
    // ATRASOS
    // ===============================
    $stmt = $conexao->prepare("
        SELECT COUNT(*)
        FROM presencas
        WHERE data_presenca = ?
        AND estado = 'Atraso'
    ");
    $stmt->execute([$data]);
    $atrasos = (int)$stmt->fetchColumn();

    // ===============================
    // TOTAL MARCAÇÕES
    // ===============================
    $stmt = $conexao->prepare("
        SELECT COUNT(*)
        FROM presencas
        WHERE data_presenca = ?
    ");
    $stmt->execute([$data]);
    $totalMarcacoes = (int)$stmt->fetchColumn();

    // ===============================
    // AUSENTES (DERIVADO)
    // ===============================
    $ausentes = max(0, $totalProfessores - $totalMarcacoes);

    // ===============================
    // LISTA PARA TABELA
    // ===============================
    $stmt = $conexao->prepare("
    SELECT 
        pr.id,
        pr.professor_id,
        pr.data_presenca,
        pr.hora_entrada,
        pr.hora_saida,
        pr.estado,

        u.nome AS nome,
        u.foto AS foto,
        p.numero_mecanografico AS mecanografico,
        d.nome AS disciplina

    FROM presencas pr
    LEFT JOIN professores p ON pr.professor_id = p.id
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN disciplinas d ON pr.disciplina_id = d.id

    WHERE pr.data_presenca = ?
    ORDER BY pr.hora_entrada ASC
");

    $stmt->execute([$data]);
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===============================
    // RESPOSTA FINAL
    // ===============================
    echo json_encode([
        "success" => true,
        "presentes" => $presentes,
        "atrasos" => $atrasos,
        "ausentes" => $ausentes,
        "total" => $totalMarcacoes,
        "lista" => $lista
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}