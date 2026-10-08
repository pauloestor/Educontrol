<?php

require_once("../phpconexao/conexao.php");

if (isset($_POST['telefone'])) {

    $telefone = trim($_POST['telefone']);

    $sql = "SELECT id
            FROM usuarios
            WHERE telefone = :telefone";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(":telefone", $telefone);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        echo json_encode([
            "existe" => true,
            "mensagem" => "Este telefone já está cadastrado."
        ]);

    } else {

        echo json_encode([
            "existe" => false
        ]);
    }
}
?>