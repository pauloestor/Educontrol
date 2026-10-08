<?php

require_once("../phpconexao/conexao.php");

if (isset($_POST['nome'])) {

    $nome = trim($_POST['nome']);

    $sql = "SELECT id
            FROM disciplinas
            WHERE nome = :nome";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(":nome", $nome);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        echo json_encode([
            "existe" => true,
            "mensagem" => "Esta disciplina já está cadastrada."
        ]);

    } else {

        echo json_encode([
            "existe" => false
        ]);
    }
}
?>