<?php

header('Content-Type: application/json');

require_once "../phpconexao/conexao.php";

try {

    $sql = "
        SELECT

            u.id,
            u.nome,
            u.email,
            u.tipo,
            u.telefone,
            u.genero,
            u.data_nascimento,
            u.foto,
            u.created_at,
            u.ultimo_login,

            p.id AS professor_id,
            p.numero_mecanografico,
            p.salario_base,

            d.nome AS disciplina

        FROM usuarios u

        LEFT JOIN professores p
            ON p.usuario_id = u.id

        LEFT JOIN disciplinas d
            ON p.disciplina_id = d.id

        ORDER BY u.nome ASC
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute();

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "dados" => $usuarios
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao listar utilizadores.",
        "error" => $e->getMessage()
    ]);
}