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
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int) $_GET['por_pagina'] : 20;

$pagina = $pagina > 0 ? $pagina : 1;
$porPagina = $porPagina > 0 ? $porPagina : 20;

$condicoes = "";
$params = [];
$types  = "";

if ($q !== '') {
    $condicoes .= " AND (e.cnpj LIKE ? OR e.razao_social LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}

if ($estado !== '') {
    $condicoes .= " AND e.estado = ?";
    $params[] = $estado;
    $types   .= "s";
}
if ($cidade !== '') {
    $condicoes .= " AND e.cidade = ?";
    $params[] = $cidade;
    $types   .= "s";
}
if ($bairro !== '') {
    $condicoes .= " AND e.bairro = ?";
    $params[] = $bairro;
    $types   .= "s";
}

// total de empresas distintas
$countSql = "SELECT COUNT(DISTINCT e.cnpj) AS total
             FROM d_empresas e
             JOIN f_preAprovados f ON f.cnpj = e.cnpj
             WHERE 1=1" . $condicoes;

$stmtCount = $mysqli->prepare($countSql);
if (!$stmtCount) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao preparar consulta de contagem.']);
    exit;
}

if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}

$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$total = 0;
if ($row = $resultCount->fetch_assoc()) {
    $total = (int) $row['total'];
}
$stmtCount->close();

$totalPaginas = max(1, (int) ceil($total / $porPagina));
$offset = ($pagina - 1) * $porPagina;

// lista paginada de empresas
$listaSql = "SELECT DISTINCT
                e.cnpj,
                e.razao_social,
                e.estado,
                e.cidade,
                e.bairro,
                e.porte,
                e.cod_cnae,
                e.cnae
              FROM d_empresas e
              JOIN f_preAprovados f ON f.cnpj = e.cnpj
              WHERE 1=1" . $condicoes . "
              ORDER BY e.razao_social
              LIMIT ? OFFSET ?";

$paramsLista = $params;
$typesLista  = $types . "ii";
$paramsLista[] = $porPagina;
$paramsLista[] = $offset;

$stmtLista = $mysqli->prepare($listaSql);
if (!$stmtLista) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao preparar consulta de lista.']);
    exit;
}

$stmtLista->bind_param($typesLista, ...$paramsLista);
$stmtLista->execute();
$resultLista = $stmtLista->get_result();

$empresas = [];
$cnpjs = [];

while ($row = $resultLista->fetch_assoc()) {
    $cnpj = $row['cnpj'];
    $cnpjs[] = $cnpj;

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

$stmtLista->close();

// busca produtos apenas das empresas retornadas na página
if (!empty($cnpjs)) {
    $placeholders = implode(',', array_fill(0, count($cnpjs), '?'));
    $prodSql = "SELECT f.cnpj, p.nome AS produto_nome, f.valor_pre_aprovado
                FROM f_preAprovados f
                JOIN d_produtos p ON p.id = f.id_produto
                WHERE f.cnpj IN ($placeholders)";

    $typesProd = str_repeat('s', count($cnpjs));
    $stmtProd = $mysqli->prepare($prodSql);

    if (!$stmtProd) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao preparar consulta de produtos.']);
        exit;
    }

    $stmtProd->bind_param($typesProd, ...$cnpjs);
    $stmtProd->execute();
    $resultProd = $stmtProd->get_result();

    while ($row = $resultProd->fetch_assoc()) {
        $cnpj = $row['cnpj'];
        if (!isset($empresas[$cnpj])) {
            continue;
        }

        $empresas[$cnpj]['produtos'][] = [
            'nome'  => $row['produto_nome'],
            'valor' => (float) $row['valor_pre_aprovado']
        ];
    }

    $stmtProd->close();
}

$mysqli->close();

echo json_encode([
    'empresas'      => array_values($empresas),
    'total'         => $total,
    'pagina'        => $pagina,
    'por_pagina'    => $porPagina,
    'total_paginas' => $totalPaginas
]);
