<?php
header("Content-Type: application/json");
require_once "../phpconexao/conexao.php";

try {

    // =========================
    // VALIDAR ID
    // =========================
   $professor_id = $_GET['professor_id'] ?? null;

    if (!$professor_id) {
        echo json_encode([
            "success" => false,
            "message" => "ID inválido"
        ]);
        exit;
    }

    $professor_id = (int)$professor_id;

    // =========================
    // PROFESSOR + USUÁRIO + DISCIPLINA
    // =========================
    $stmt = $conexao->prepare("
        SELECT 
            p.id,
            p.numero_mecanografico,
            p.salario_base,

            d.nome AS disciplina,

            u.nome,
            u.email,
            u.telefone,
            u.foto,
            u.genero,
            u.data_nascimento,
            u.created_at,
            u.ultimo_login

        FROM professores p
        INNER JOIN usuarios u ON u.id = p.usuario_id
        INNER JOIN disciplinas d ON d.id = p.disciplina_id
        WHERE p.id = ?
        LIMIT 1
    ");

    $stmt->execute([$professor_id]);
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$professor) {
        echo json_encode([
            "success" => false,
            "message" => "Professor não encontrado"
        ]);
        exit;
    }

    // =========================
    // ASSIDUIDADE
    // =========================
    $stmt = $conexao->prepare("
        SELECT 
            COUNT(CASE WHEN estado = 'Presente' THEN 1 END) AS presencas,
            COUNT(CASE WHEN estado = 'Atraso' THEN 1 END) AS atrasos,
            COUNT(CASE WHEN estado = 'Falta' THEN 1 END) AS faltas,
            COALESCE(SUM(desconto),0) AS total_descontos
        FROM presencas
        WHERE professor_id = ?
    ");

    $stmt->execute([$professor_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // =========================
    // CÁLCULO SALARIAL
    // =========================
    $salario_base = (float)$professor['salario_base'];
    $total_descontos = (float)$stats['total_descontos'];
    $salario_final = $salario_base - $total_descontos;

    // =========================
    // RESPOSTA FINAL
    // =========================
    echo json_encode([
        "success" => true,

        "professor" => $professor,

        "assiduidade" => [
            "presentes" => (int)$stats['presencas'],
            "atrasos" => (int)$stats['atrasos'],
            "faltas" => (int)$stats['faltas'],
            "total_descontos" => $total_descontos,
            "salario_final" => $salario_final
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}