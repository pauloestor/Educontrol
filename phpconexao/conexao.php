<?php
// ===============================
// CONEXÃO PDO COM BANCO DE DADOS
// ===============================

$host = "localhost";
$dbname = "educontrol";
$user = "root";
$pass = "";

try {
    $conexao = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );

    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die(json_encode([
        "success" => false,
        "message" => "Erro na conexão: " . $e->getMessage()
    ]));
}
?>