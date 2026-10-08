<?php
session_start();
header("Content-Type: application/json");

require "../phpconexao/conexao.php";

// =======================================
// VERIFICAR LOGIN
// =======================================
$usuario_id = $_SESSION['user_id'] ?? null;

if (!$usuario_id) {
    echo json_encode([
        "success" => false,
        "message" => "Não autenticado"
    ]);
    exit;
}

try {

    // =======================================
    // OBTER PROFESSOR
    // =======================================
    $stmt = $conexao->prepare("
        SELECT id
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

    // =======================================
    // DIA DA SEMANA
    // =======================================
    $dias = [
        "Monday"    => "Segunda",
        "Tuesday"   => "Terça",
        "Wednesday" => "Quarta",
        "Thursday"  => "Quinta",
        "Friday"    => "Sexta"
    ];

    $diaSemana = $dias[date("l")] ?? "";

    // =======================================
    // ESCALA DO DIA
    // =======================================
    $stmt = $conexao->prepare("
        SELECT *
        FROM escalas
        WHERE professor_id = ?
        AND FIND_IN_SET(?, dias_semana)
        LIMIT 1
    ");

    $stmt->execute([
        $professor_id,
        $diaSemana
    ]);

    $escala = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$escala) {

        echo json_encode([
            "success" => true,
            "data" => [
                "estado" => "Sem escala",
                "hora_entrada" => null,
                "hora_saida" => null,
                "entrada_marcada" => false,
                "saida_marcada" => false,
                "pode_marcar_entrada" => false,
                "pode_marcar_saida" => false
            ]
        ]);

        exit;
    }

    // =======================================
    // PRESENÇA DE HOJE
    // =======================================
    $stmt = $conexao->prepare("
        SELECT *
        FROM presencas
        WHERE professor_id = ?
        AND data_presenca = CURDATE()
        LIMIT 1
    ");

    $stmt->execute([$professor_id]);

    $presenca = $stmt->fetch(PDO::FETCH_ASSOC);

    $horaAtual = strtotime(date("H:i:s"));

    $horaEntradaEscala = strtotime($escala["hora_entrada"]);

    $horaSaidaEscala = strtotime($escala["hora_saida"]);

    $tolerancia = $escala["tolerancia_min"] * 60;

    // =======================================
    // ESTADO
    // =======================================
    $estado = "Não iniciado";

    $entradaMarcada = false;
    $saidaMarcada = false;

    $podeEntrada = true;
    $podeSaida = false;

    $horaEntrada = null;
    $horaSaida = null;

    if ($presenca) {

        $entradaMarcada = !empty($presenca["hora_entrada"]);
        $saidaMarcada = !empty($presenca["hora_saida"]);

        $horaEntrada = $presenca["hora_entrada"];
        $horaSaida = $presenca["hora_saida"];

        if ($saidaMarcada) {

            $estado = "Registado";

            $podeEntrada = false;
            $podeSaida = false;

        } else {

            $estado = $presenca["estado"];

            $podeEntrada = false;
            $podeSaida = true;
        }

    } else {

        if ($horaAtual < $horaEntradaEscala) {

            $estado = "Aguardando horário";

        } elseif ($horaAtual <= ($horaEntradaEscala + $tolerancia)) {

            $estado = "Presente";

        } elseif ($horaAtual < $horaSaidaEscala) {

            $estado = "Atrasado";

        } else {

            $estado = "Falta";
        }

        $podeEntrada = true;
        $podeSaida = false;
    }

    echo json_encode([
        "success" => true,
        "data" => [

            "estado" => $estado,

            "hora_entrada" => $horaEntrada,

            "hora_saida" => $horaSaida,

            "entrada_marcada" => $entradaMarcada,

            "saida_marcada" => $saidaMarcada,

            "pode_marcar_entrada" => $podeEntrada,

            "pode_marcar_saida" => $podeSaida,

            "escala" => [
                "hora_entrada" => $escala["hora_entrada"],
                "hora_saida" => $escala["hora_saida"],
                "tolerancia" => $escala["tolerancia_min"],
                "periodo" => $escala["periodo"]
            ]
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}