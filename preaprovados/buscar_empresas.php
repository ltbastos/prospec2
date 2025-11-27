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
    echo json_encode(['erro' => 'Falha na conexão com o banco de dados.']);
    exit;
}

$mysqli->set_charset($charset);

// parâmetros
$q      = isset($_GET['q'])      ? trim($_GET['q'])      : '';
$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$cidade = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$bairro = isset($_GET['bairro']) ? trim($_GET['bairro']) : '';

$sql = "SELECT
          e.cnpj,
          e.razao_social,
          e.estado,
          e.cidade,
          e.bairro,
          e.porte,
          e.cod_cnae,
          e.cnae,
          p.nome AS produto_nome,
          f.valor_pre_aprovado
        FROM d_empresas e
        JOIN f_preAprovados f ON f.cnpj = e.cnpj
        JOIN d_produtos p     ON p.id   = f.id_produto
        WHERE 1=1";

$params = [];
$types  = "";

// filtro por texto
if ($q !== '') {
    $sql .= " AND (e.cnpj LIKE ? OR e.razao_social LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}

if ($estado !== '') {
    $sql .= " AND e.estado = ?";
    $params[] = $estado;
    $types   .= "s";
}
if ($cidade !== '') {
    $sql .= " AND e.cidade = ?";
    $params[] = $cidade;
    $types   .= "s";
}
if ($bairro !== '') {
    $sql .= " AND e.bairro = ?";
    $params[] = $bairro;
    $types   .= "s";
}

$sql .= " ORDER BY e.razao_social LIMIT 10";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao preparar consulta.']);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$empresas = [];

while ($row = $result->fetch_assoc()) {
    $cnpj = $row['cnpj'];

    if (!isset($empresas[$cnpj])) {
        $empresas[$cnpj] = [
            'cnpj'         => $cnpj,
            'razao_social' => $row['razao_social'],
            'estado'       => $row['estado'],
            'cidade'       => $row['cidade'],
            'bairro'       => $row['bairro'],
            'porte'        => $row['porte'],
            'cod_cnae'     => $row['cod_cnae'],
            'cnae'         => $row['cnae'],
            'produtos'     => []
        ];
    }

    $empresas[$cnpj]['produtos'][] = [
        'nome'  => $row['produto_nome'],
        'valor' => (float)$row['valor_pre_aprovado']
    ];
}

$stmt->close();
$mysqli->close();

echo json_encode([
    'empresas' => array_values($empresas)
]);
