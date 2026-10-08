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
    // BUSCAR ESCALAS DO DIA
    // ===============================
    $stmt = $conexao->prepare("
        SELECT 
            e.id AS escala_id,
            e.professor_id,
            e.disciplina_id,
            e.hora_entrada,
            e.hora_saida,
            e.tolerancia_min
        FROM escalas e
        WHERE FIND_IN_SET(?, e.dias_semana)
    ");
    $stmt->execute([$diaSemana]);
    $escalas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $horaAtual = date("H:i:s");

    // ===============================
    // CRIAR FALTAS AUTOMÁTICAS
    // ===============================
    foreach ($escalas as $escala) {

        $horaLimite = date(
            "H:i:s",
            strtotime($escala['hora_saida'] . " +{$escala['tolerancia_min']} minutes")
        );

        // Só verifica depois do fim da escala + tolerância
        if ($horaAtual < $horaLimite) {
            continue;
        }

        // Verificar se já existe presença
        $stmt = $conexao->prepare("
            SELECT id 
            FROM presencas 
            WHERE professor_id = ? 
            AND escala_id = ? 
            AND data_presenca = ?
            LIMIT 1
        ");

        $stmt->execute([
            $escala['professor_id'],
            $escala['escala_id'],
            $data
        ]);

        $existe = $stmt->fetchColumn();

        if (!$existe) {

            // INSERIR FALTA AUTOMÁTICA
            $stmt = $conexao->prepare("
                INSERT INTO presencas (
                    professor_id,
                    disciplina_id,
                    escala_id,
                    data_presenca,
                    hora_entrada,
                    hora_saida,
                    estado,
                    minutos_atraso,
                    desconto,
                    observacao
                ) VALUES (
                    ?, ?, ?, ?, NULL, NULL,
                    'Falta',
                    0,
                    2500,
                    'Falta automática'
                )
            ");

            $stmt->execute([
                $escala['professor_id'],
                $escala['disciplina_id'],
                $escala['escala_id'],
                $data
            ]);
        }
    }

    // ===============================
    // AGORA REUTILIZA TEU CÓDIGO ORIGINAL
    // ===============================

    // TOTAL PROFESSORES NA ESCALA HOJE
    $stmt = $conexao->prepare("
        SELECT COUNT(DISTINCT professor_id) as total
        FROM escalas
        WHERE FIND_IN_SET(?, dias_semana)
    ");
    $stmt->execute([$diaSemana]);
    $totalProfessores = (int)$stmt->fetchColumn();

    // PRESENTES
    $stmt = $conexao->prepare("
        SELECT COUNT(*)
        FROM presencas
        WHERE data_presenca = ?
        AND estado = 'Presente'
    ");
    $stmt->execute([$data]);
    $presentes = (int)$stmt->fetchColumn();

    // ATRASOS
    $stmt = $conexao->prepare("
        SELECT COUNT(*)
        FROM presencas
        WHERE data_presenca = ?
        AND estado = 'Atraso'
    ");
    $stmt->execute([$data]);
    $atrasos = (int)$stmt->fetchColumn();

    // TOTAL MARCAÇÕES
    $stmt = $conexao->prepare("
        SELECT COUNT(*)
        FROM presencas
        WHERE data_presenca = ?
    ");
    $stmt->execute([$data]);
    $totalMarcacoes = (int)$stmt->fetchColumn();

    // AUSENTES
    $ausentes = max(0, $totalProfessores - $totalMarcacoes);

    // LISTA
    $stmt = $conexao->prepare("
        SELECT 
            pr.id,
            pr.professor_id,
            pr.data_presenca,
            pr.hora_entrada,
            pr.hora_saida,
            pr.estado,
            pr.desconto,

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
    // RESPONSE FINAL
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