<?php
session_start();
header("Content-Type: application/json");

require_once "../phpconexao/conexao.php";

// ===============================
// LOGIN
// ===============================
$usuario_id = $_SESSION['user_id'] ?? null;

if (!$usuario_id) {
    echo json_encode([
        "success" => false,
        "message" => "Utilizador não autenticado"
    ]);
    exit;
}

try {

    // ===============================
    // PROFESSOR
    // ===============================
    $stmt = $conexao->prepare("
        SELECT id
        FROM professores
        WHERE usuario_id = ?
        LIMIT 1
    ");

    $stmt->execute([$usuario_id]);
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$professor) {
        echo json_encode([
            "success" => false,
            "message" => "Professor não encontrado"
        ]);
        exit;
    }

    $professor_id = $professor["id"];

    // ===============================
    // FILTROS
    // ===============================
    $mes = $_GET['mes'] ?? null;
    $ano = $_GET['ano'] ?? null;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $where = "WHERE professor_id = ?";
    $params = [$professor_id];

    if ($mes) {
        $where .= " AND MONTH(data_presenca) = ?";
        $params[] = $mes;
    }

    if ($ano) {
        $where .= " AND YEAR(data_presenca) = ?";
        $params[] = $ano;
    }

    // ===============================
    // TOTAL
    // ===============================
    $count = $conexao->prepare("
        SELECT COUNT(*) as total
        FROM presencas
        $where
    ");

    $count->execute($params);
    $total = $count->fetch(PDO::FETCH_ASSOC)['total'];

    // ===============================
    // DADOS
    // ===============================
    $sql = "
        SELECT 
            data_presenca,
            hora_entrada,
            hora_saida,
            estado
        FROM presencas
        $where
        ORDER BY data_presenca DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $conexao->prepare($sql);
    $stmt->execute($params);

    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===============================
    // RESPOSTA
    // ===============================
    echo json_encode([
        "success" => true,
        "data" => $dados,
        "pagination" => [
            "total" => $total,
            "page" => $page,
            "pages" => ceil($total / $limit)
        ]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}