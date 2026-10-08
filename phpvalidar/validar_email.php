<?php

require_once("../phpconexao/conexao.php");

if (isset($_POST['email'])) {

    $email = trim($_POST['email']);

    $sql = "SELECT id
            FROM usuarios
            WHERE email = :email";

    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        echo json_encode([
            "existe" => true,
            "mensagem" => "Este email já está cadastrado."
        ]);

    } else {

        echo json_encode([
            "existe" => false
        ]);
    }
}
?>