<?php

header('Content-Type: application/json');

require_once "../phpconexao/conexao.php";

try {

    $sql = "
        SELECT
            id,
            nome,
            descricao,
            estado,
            criado_em
        FROM disciplinas
        ORDER BY criado_em DESC
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute();

    $disciplinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "dados" => $disciplinas
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao listar disciplinas.",
        "error" => $e->getMessage()
    ]);
}