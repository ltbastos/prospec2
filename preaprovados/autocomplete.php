<?php
// autocomplete.php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'PreAprovados';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com o banco.']);
    exit;
}
$mysqli->set_charset($charset);

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '' || mb_strlen($q) < 2) {
    echo json_encode(['empresas' => []]);
    exit;
}

// para nome: usamos exatamente o que o usuário digitou
$nomePrefixo = $q;

// para CNPJ: deixamos só dígitos
$cnpjDigits = preg_replace('/\D/', '', $q);
if ($cnpjDigits === '') {
    $cnpjDigits = $q; // fallback, caso ele tenha digitado algo estranho
}

$sql = "
    SELECT
      cnpj,
      razao_social,
      cidade,
      estado
    FROM d_empresas
    WHERE razao_social LIKE CONCAT(?, '%')
       OR REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') LIKE CONCAT(?, '%')
    ORDER BY razao_social
    LIMIT 10
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao preparar consulta.']);
    exit;
}

$stmt->bind_param('ss', $nomePrefixo, $cnpjDigits);
$stmt->execute();
$res = $stmt->get_result();

$empresas = [];
while ($row = $res->fetch_assoc()) {
    $empresas[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode(['empresas' => $empresas]);
