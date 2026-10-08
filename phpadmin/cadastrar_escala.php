<?php
header('Content-Type: application/json');

require_once "../phpconexao/conexao.php";

try {

    // =========================
    // RECEBER DADOS
    // =========================
    $professor_id   = $_POST['professor_id'] ?? null;
    $disciplina_id  = $_POST['disciplina_id'] ?? null;
    $dias_semana    = $_POST['dias_semana'] ?? [];
    $hora_entrada   = $_POST['hora_entrada'] ?? null;
    $hora_saida     = $_POST['hora_saida'] ?? null;
    $tolerancia_min = $_POST['tolerancia_min'] ?? 15;
    $periodo        = $_POST['periodo'] ?? null;

    // =========================
    // VALIDAÇÕES BÁSICAS
    // =========================
    if (
        !$professor_id ||
        !$disciplina_id ||
        !$hora_entrada ||
        !$hora_saida ||
        !$periodo ||
        empty($dias_semana)
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Preencha todos os campos obrigatórios."
        ]);
        exit;
    }

    // =========================
    // VALIDAR HORÁRIOS
    // =========================
    if ($hora_entrada >= $hora_saida) {
        echo json_encode([
            "success" => false,
            "message" => "Hora de entrada deve ser menor que hora de saída."
        ]);
        exit;
    }

    // =========================
    // CONVERTER DIAS PARA SET
    // =========================
    // Esperado: ["Segunda","Terça"]
    $dias_formatados = implode(',', $dias_semana);

    // =========================
    // INSERIR NA BD
    // =========================
    $sql = "INSERT INTO escalas (
                professor_id,
                disciplina_id,
                dias_semana,
                hora_entrada,
                hora_saida,
                tolerancia_min,
                periodo
            ) VALUES (
                :professor_id,
                :disciplina_id,
                :dias_semana,
                :hora_entrada,
                :hora_saida,
                :tolerancia_min,
                :periodo
            )";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        ":professor_id"   => $professor_id,
        ":disciplina_id"  => $disciplina_id,
        ":dias_semana"    => $dias_formatados,
        ":hora_entrada"   => $hora_entrada,
        ":hora_saida"     => $hora_saida,
        ":tolerancia_min" => $tolerancia_min,
        ":periodo"        => $periodo
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Escala cadastrada com sucesso!"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao cadastrar escala.",
        "error" => $e->getMessage()
    ]);
}