<?php
require_once "conexao.php";

try {

    $data = date("Y-m-d");
    $total_faltas = 0;

    // pegar todos professores da escala hoje
    $stmt = $conexao->query("
        SELECT DISTINCT professor_id
        FROM escalas
    ");

    $professores = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($professores as $professor_id) {

        // verificar se já marcou presença
        $check = $conexao->prepare("
            SELECT id
            FROM presencas
            WHERE professor_id = ?
            AND data_presenca = ?
        ");

        $check->execute([$professor_id, $data]);

        if (!$check->fetch()) {

            // criar falta automática
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
                VALUES (?, NULL, NULL, ?, NULL, 'Falta', 0, 2500)
            ");

            $insert->execute([
                $professor_id,
                $data
            ]);

            $total_faltas++;
        }
    }

    // =========================
    // LOG DE PROCESSAMENTO
    // =========================
    $log = $conexao->prepare("
        INSERT INTO logs_processamento (data_execucao, total_faltas, mensagem)
        VALUES (NOW(), ?, ?)
    ");

    $log->execute([
        $total_faltas,
        "Processamento concluído com sucesso"
    ]);

    echo json_encode([
        "success" => true,
        "total_faltas" => $total_faltas,
        "message" => "Faltas processadas com sucesso"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}