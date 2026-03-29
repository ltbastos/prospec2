<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'PreAprovados';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro de conexão com o banco.']);
    exit;
}
$mysqli->set_charset($charset);

function limparCnpj($cnpj)
{
    return preg_replace('/\D/', '', $cnpj ?? '');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $cnpj = limparCnpj($_GET['cnpj'] ?? '');
    if (strlen($cnpj) !== 14) {
        http_response_code(400);
        echo json_encode(['erro' => 'CNPJ inválido.']);
        exit;
    }

    $sql = "SELECT id, nome, juncao, comentario, data FROM f_comentarios WHERE cnpj = ? ORDER BY data DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $cnpj);
    $stmt->execute();
    $res = $stmt->get_result();

    $comentarios = [];
    while ($row = $res->fetch_assoc()) {
        $comentarios[] = $row;
    }

    $stmt->close();
    $mysqli->close();

    echo json_encode(['comentarios' => $comentarios]);
    exit;
}

if ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $cnpj = limparCnpj($payload['cnpj'] ?? '');
    $nome = trim($payload['nome'] ?? '');
    $juncao = trim($payload['juncao'] ?? '');
    $comentario = trim($payload['comentario'] ?? '');

    if (strlen($cnpj) !== 14 || $nome === '' || $comentario === '') {
        http_response_code(400);
        echo json_encode(['erro' => 'CNPJ, nome e comentário são obrigatórios.']);
        exit;
    }

    $sql = "INSERT INTO f_comentarios (cnpj, nome, juncao, comentario) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssss', $cnpj, $nome, $juncao, $comentario);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['erro' => 'Não foi possível salvar o comentário.']);
        $stmt->close();
        $mysqli->close();
        exit;
    }

    $novoId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();

    echo json_encode(['sucesso' => true, 'id' => $novoId]);
    exit;
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $payload);
    $id = isset($payload['id']) ? (int)$payload['id'] : 0;
    $cnpj = limparCnpj($payload['cnpj'] ?? '');

    if ($id <= 0 || strlen($cnpj) !== 14) {
        http_response_code(400);
        echo json_encode(['erro' => 'Parâmetros inválidos para excluir.']);
        exit;
    }

    $sql = "DELETE FROM f_comentarios WHERE id = ? AND cnpj = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('is', $id, $cnpj);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(['erro' => 'Comentário não encontrado.']);
        $stmt->close();
        $mysqli->close();
        exit;
    }

    $stmt->close();
    $mysqli->close();

    echo json_encode(['sucesso' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não suportado.']);
$mysqli->close();
