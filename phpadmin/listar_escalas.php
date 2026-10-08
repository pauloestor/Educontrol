<?php
// ===============================
// LISTAR ESCALAS
// ===============================

header("Content-Type: application/json; charset=UTF-8");

require_once "../phpconexao/conexao.php";

try {

    $sql = "
        SELECT 

            e.id,

            e.professor_id,
            e.disciplina_id,

            u.nome AS professor,
            p.numero_mecanografico,

            d.nome AS disciplina,

            e.dias_semana,
            e.hora_entrada,
            e.hora_saida,
            e.tolerancia_min,
            e.periodo,

            e.created_at,
            e.updated_at

        FROM escalas e

        INNER JOIN professores p 
            ON p.id = e.professor_id

        INNER JOIN usuarios u 
            ON u.id = p.usuario_id

        INNER JOIN disciplinas d 
            ON d.id = e.disciplina_id

        ORDER BY e.created_at DESC
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute();

    $escalas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "dados" => $escalas
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao listar escalas.",
        "error" => $e->getMessage()
    ]);

}