<?php
header('Content-Type: application/json');

require_once "../phpconexao/conexao.php";

try {

    $sql = "
        SELECT 
            p.id AS professor_id,
            u.nome,
            p.numero_mecanografico,
            d.id AS disciplina_id,
            d.nome AS disciplina
        FROM professores p
        INNER JOIN usuarios u ON u.id = p.usuario_id
        INNER JOIN disciplinas d ON d.id = p.disciplina_id
        ORDER BY u.nome ASC
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute();

    $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "dados" => $professores
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao listar professores",
        "error" => $e->getMessage()
    ]);
}