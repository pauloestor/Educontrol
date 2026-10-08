<?php
session_start();
header("Content-Type: application/json");

require_once "../phpconexao/conexao.php";

// =====================================
// VERIFICAR LOGIN
// =====================================
$usuario_id = $_SESSION['user_id'] ?? null;

if (!$usuario_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado"
    ]);
    exit;
}

try {

    // =====================================
    // OBTER ID DO PROFESSOR
    // =====================================
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

    $data_presenca = date("Y-m-d");
    $hora_saida = date("H:i:s");

    // =====================================
    // VERIFICAR SE EXISTE ENTRADA
    // =====================================
    $check = $conexao->prepare("
        SELECT id, hora_saida
        FROM presencas
        WHERE professor_id = ?
        AND data_presenca = ?
        LIMIT 1
    ");

    $check->execute([
        $professor_id,
        $data_presenca
    ]);

    $presenca = $check->fetch(PDO::FETCH_ASSOC);

    if (!$presenca) {

        echo json_encode([
            "success" => false,
            "message" => "Ainda não marcou a entrada."
        ]);
        exit;
    }

    if (!empty($presenca["hora_saida"])) {

        echo json_encode([
            "success" => false,
            "message" => "A saída já foi registada hoje."
        ]);
        exit;
    }

    // =====================================
    // ATUALIZAR SAÍDA
    // =====================================
    $update = $conexao->prepare("
        UPDATE presencas
        SET hora_saida = ?
        WHERE id = ?
    ");

    $update->execute([
        $hora_saida,
        $presenca["id"]
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Saída registada com sucesso.",
        "hora_saida" => $hora_saida
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao registar saída.",
        "error" => $e->getMessage()
    ]);
}