<?php

header('Content-Type: application/json');

require_once "../phpconexao/conexao.php";

try {

    // =========================
    // RECEBER DADOS
    // =========================
    $id = $_POST['id'] ?? null;

    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'professor';

    $telefone = $_POST['telefone'] ?? null;
    $genero = $_POST['genero'] ?? null;
    $data_nascimento = $_POST['data_nascimento'] ?? null;

    $disciplina_id = $_POST['disciplina_id'] ?? null;
    $salario_base = $_POST['salario_base'] ?? 0;

    // =========================
    // VALIDAÇÕES BÁSICAS
    // =========================
    if (!$id) {
        echo json_encode([
            "success" => false,
            "message" => "ID do utilizador não fornecido"
        ]);
        exit;
    }

    if (empty($nome) || empty($email)) {
        echo json_encode([
            "success" => false,
            "message" => "Nome e email são obrigatórios"
        ]);
        exit;
    }

    // =========================
    // VERIFICAR SE EMAIL JÁ EXISTE (OUTRO USER)
    // =========================
    $check = $conexao->prepare("
        SELECT id FROM usuarios 
        WHERE email = :email AND id != :id
    ");

    $check->bindParam(':email', $email);
    $check->bindParam(':id', $id);
    $check->execute();

    if ($check->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Este email já está em uso"
        ]);
        exit;
    }

    // =========================
    // ATUALIZAR USUÁRIO
    // =========================

    if (!empty($senha)) {

        // com senha
        $sql = "
            UPDATE usuarios SET
                nome = :nome,
                email = :email,
                senha = :senha,
                tipo = :tipo,
                telefone = :telefone,
                genero = :genero,
                data_nascimento = :data_nascimento
            WHERE id = :id
        ";

        $stmt = $conexao->prepare($sql);

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt->bindParam(':senha', $senhaHash);

    } else {

        // sem alterar senha
        $sql = "
            UPDATE usuarios SET
                nome = :nome,
                email = :email,
                tipo = :tipo,
                telefone = :telefone,
                genero = :genero,
                data_nascimento = :data_nascimento
            WHERE id = :id
        ";

        $stmt = $conexao->prepare($sql);
    }

    // =========================
    // BIND COMUM
    // =========================
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':genero', $genero);
    $stmt->bindParam(':data_nascimento', $data_nascimento);
    $stmt->bindParam(':id', $id);

    $stmt->execute();

    // =========================
    // SE FOR PROFESSOR → ATUALIZAR TABELA PROFESSORES
    // =========================
    if ($tipo === "professor") {

        // verificar se já existe registro
        $checkProf = $conexao->prepare("
            SELECT id FROM professores WHERE usuario_id = :id
        ");
        $checkProf->bindParam(':id', $id);
        $checkProf->execute();

        if ($checkProf->rowCount() > 0) {

            // UPDATE
            $sqlProf = "
                UPDATE professores SET
                    disciplina_id = :disciplina_id,
                    salario_base = :salario_base
                WHERE usuario_id = :usuario_id
            ";

        } else {

            // INSERT
            $sqlProf = "
                INSERT INTO professores
                (usuario_id, disciplina_id, salario_base)
                VALUES
                (:usuario_id, :disciplina_id, :salario_base)
            ";
        }

        $stmtProf = $conexao->prepare($sqlProf);

        $stmtProf->bindParam(':usuario_id', $id);
        $stmtProf->bindParam(':disciplina_id', $disciplina_id);
        $stmtProf->bindParam(':salario_base', $salario_base);

        $stmtProf->execute();
    }

    // =========================
    // RESPOSTA FINAL
    // =========================
    echo json_encode([
        "success" => true,
        "message" => "Usuário atualizado com sucesso"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao atualizar usuário",
        "error" => $e->getMessage()
    ]);
}