<?php
// =====================================
// CADASTRO DE USUÁRIO (ADMIN / PROFESSOR)
// =====================================

header("Content-Type: application/json");

require_once("../phpconexao/conexao.php");

// =============================
// RECEBER DADOS DO FRONT
// =============================
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$tipo = $_POST['tipo'] ?? 'professor';

$telefone = $_POST['telefone'] ?? null;
$genero = $_POST['genero'] ?? null;
$data_nascimento = $_POST['data_nascimento'] ?? null;

// dados do professor
$disciplina_id = $_POST['disciplina_id'] ?? null;
$salario_base = $_POST['salario_base'] ?? null;

try {

    // =============================
    // VERIFICAR EMAIL DUPLICADO
    // =============================
    $check = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email já cadastrado"
        ]);
        exit;
    }

    // =============================
    // INSERIR USUÁRIO
    // =============================
    $sql = "INSERT INTO usuarios 
        (nome, email, senha, tipo, telefone, genero, data_nascimento, primeiro_login)
        VALUES 
        (:nome, :email, :senha, :tipo, :telefone, :genero, :data_nascimento, 1)";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":senha" => password_hash($senha, PASSWORD_DEFAULT),
        ":tipo" => $tipo,
        ":telefone" => $telefone,
        ":genero" => $genero,
        ":data_nascimento" => $data_nascimento
    ]);

    $usuario_id = $conexao->lastInsertId();

    // =============================
    // SE FOR PROFESSOR
    // =============================
    if ($tipo === "professor") {

        // gerar número mecanográfico automático
        $count = $conexao->query("SELECT COUNT(*) as total FROM professores")->fetch();
        $numero = "PROF-" . str_pad($count['total'] + 1, 3, "0", STR_PAD_LEFT);

        $sqlProf = "INSERT INTO professores 
            (usuario_id, numero_mecanografico, disciplina_id, salario_base)
            VALUES 
            (:usuario_id, :numero, :disciplina_id, :salario)";

        $stmtProf = $conexao->prepare($sqlProf);

        $stmtProf->execute([
            ":usuario_id" => $usuario_id,
            ":numero" => $numero,
            ":disciplina_id" => $disciplina_id,
            ":salario" => $salario_base
        ]);
    }

    // =============================
    // RESPOSTA FINAL
    // =============================
    echo json_encode([
        "success" => true,
        "message" => "Usuário cadastrado com sucesso"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao cadastrar usuário",
        "error" => $e->getMessage()
    ]);
}