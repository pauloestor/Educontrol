<?php
session_start();
header("Content-Type: application/json");

require_once "../phpconexao/conexao.php";

// ===============================
// LOGIN
// ===============================
$usuario_id = $_SESSION['user_id'] ?? null;

if (!$usuario_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado"
    ]);
    exit;
}

try {

    // ===============================
    // PROFESSOR
    // ===============================
    $stmt = $conexao->prepare("
        SELECT id, disciplina_id
        FROM professores
        WHERE usuario_id = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario_id]);
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$professor) {
        echo json_encode([
            "success" => false,
            "message" => "Professor não encontrado."
        ]);
        exit;
    }

    $professor_id = $professor["id"];
    $disciplina_id = $professor["disciplina_id"];

    // ===============================
    // CONFIGURAÇÕES SALARIAIS
    // ===============================
    $configStmt = $conexao->prepare("
        SELECT desconto_falta, desconto_atraso, tolerancia_min
        FROM configuracoes_salariais
        LIMIT 1
    ");
    $configStmt->execute();
    $config = $configStmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        $config = [
            "desconto_falta" => 2500,
            "desconto_atraso" => 500,
            "tolerancia_min" => 10
        ];
    }

    // ===============================
    // DATA/HORA
    // ===============================
    $data_presenca = date("Y-m-d");
    $hora_entrada = date("H:i:s");

    // ===============================
    // ESCALA
    // ===============================
    $dias = [
        "Monday" => "Segunda",
        "Tuesday" => "Terça",
        "Wednesday" => "Quarta",
        "Thursday" => "Quinta",
        "Friday" => "Sexta"
    ];

    $diaSemana = $dias[date("l")] ?? null;

    $stmt = $conexao->prepare("
        SELECT *
        FROM escalas
        WHERE professor_id = ?
        AND FIND_IN_SET(?, dias_semana)
        LIMIT 1
    ");
    $stmt->execute([$professor_id, $diaSemana]);
    $escala = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$escala) {
        echo json_encode([
            "success" => false,
            "message" => "Sem escala atribuída para hoje."
        ]);
        exit;
    }

    // ===============================
    // DUPLICAÇÃO
    // ===============================
    $check = $conexao->prepare("
        SELECT id
        FROM presencas
        WHERE professor_id = ?
        AND data_presenca = ?
        LIMIT 1
    ");
    $check->execute([$professor_id, $data_presenca]);

    if ($check->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "Já marcou presença hoje."
        ]);
        exit;
    }

    // ===============================
    // ATRASO
    // ===============================
    $hora_limite = strtotime($escala["hora_entrada"]);
    $hora_actual = strtotime($hora_entrada);

    $estado = "Presente";
    $minutos_atraso = 0;
    $desconto = 0;

    if ($hora_actual > $hora_limite) {

        $minutos_atraso = floor(($hora_actual - $hora_limite) / 60);

        if ($minutos_atraso > $config["tolerancia_min"]) {
            $estado = "Atraso";
            $desconto = $config["desconto_atraso"];
        }
    }

    // ===============================
    // INSERT
    // ===============================
    $insert = $conexao->prepare("
        INSERT INTO presencas (
            professor_id,
            disciplina_id,
            escala_id,
            data_presenca,
            hora_entrada,
            estado,
            minutos_atraso,
            desconto
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insert->execute([
        $professor_id,
        $disciplina_id,
        $escala["id"],
        $data_presenca,
        $hora_entrada,
        $estado,
        $minutos_atraso,
        $desconto
    ]);

    echo json_encode([
        "success" => true,
        "estado" => $estado,
        "desconto" => $desconto
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}