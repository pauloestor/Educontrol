<?php

header('Content-Type: application/json');

require_once("../phpconexao/conexao.php");

try {

    // ============================
    // RECEBER DADOS
    // ============================

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Ativa');

    // ============================
    // VALIDAÇÕES
    // ============================

    if (empty($nome)) {
        echo json_encode([
            "success" => false,
            "message" => "O nome da disciplina é obrigatório."
        ]);
        exit;
    }

    if (strlen($nome) < 2) {
        echo json_encode([
            "success" => false,
            "message" => "O nome da disciplina deve possuir pelo menos 2 caracteres."
        ]);
        exit;
    }

    // ============================
    // VERIFICAR DUPLICIDADE
    // ============================

    $sql = "SELECT id FROM disciplinas WHERE nome = :nome";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        echo json_encode([
            "success" => false,
            "message" => "Esta disciplina já está cadastrada."
        ]);
        exit;
    }

    // ============================
    // INSERIR DISCIPLINA
    // ============================

    $sql = "INSERT INTO disciplinas
            (nome, descricao, estado)
            VALUES
            (:nome, :descricao, :estado)";

    $stmt = $conexao->prepare($sql);

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':estado', $estado);

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Disciplina cadastrada com sucesso."
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao cadastrar disciplina.",
        "error" => $e->getMessage()
    ]);
}