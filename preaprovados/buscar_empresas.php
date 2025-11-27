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
$cnae   = isset($_GET['cnae'])   ? trim($_GET['cnae'])   : '';
$produto = isset($_GET['produto']) ? (int) $_GET['produto'] : 0;
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int) $_GET['por_pagina'] : 20;

$pagina = $pagina > 0 ? $pagina : 1;
$porPagina = $porPagina > 0 ? $porPagina : 20;


$condicoesEmpresa = "";
$condicoesProdutos = "";
$paramsEmpresa = [];
$paramsProdutos = [];
$typesEmpresa  = "";
$typesProdutos = "";

if ($q !== '') {
    $condicoesEmpresa .= " AND (e.cnpj LIKE ? OR e.razao_social LIKE ?)";
    $like = '%' . $q . '%';
    $paramsEmpresa[] = $like;
    $paramsEmpresa[] = $like;
    $typesEmpresa   .= "ss";
}

if ($estado !== '') {
    $condicoesEmpresa .= " AND e.estado = ?";
    $paramsEmpresa[] = $estado;
    $typesEmpresa   .= "s";
}
if ($cidade !== '') {
    $condicoesEmpresa .= " AND e.cidade = ?";
    $paramsEmpresa[] = $cidade;
    $typesEmpresa   .= "s";
}
if ($bairro !== '') {
    $condicoesEmpresa .= " AND e.bairro = ?";
    $paramsEmpresa[] = $bairro;
    $typesEmpresa   .= "s";
}
if ($cnae !== '') {
    $condicoesEmpresa .= " AND e.cod_cnae = ?";
    $paramsEmpresa[] = $cnae;
    $typesEmpresa   .= "s";
}
if ($produto > 0) {
    $condicoesProdutos .= " AND f.id_produto = ?";
    $paramsProdutos[] = $produto;
    $typesProdutos   .= "i";
}

// total de empresas distintas
$countSql = "SELECT COUNT(*) AS total
             FROM d_empresas e
             WHERE 1=1" . $condicoesEmpresa . "
             AND EXISTS (
                SELECT 1 FROM f_preaprovados f
                WHERE f.cnpj = e.cnpj" . $condicoesProdutos . "
             )";

$stmtCount = $mysqli->prepare($countSql);
if (!$stmtCount) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao preparar consulta de contagem.']);
    exit;
}

$paramsCount = [];
$typesCount  = "";

if (!empty($paramsEmpresa)) {
    $paramsCount = array_merge($paramsCount, $paramsEmpresa);
    $typesCount .= $typesEmpresa;
}
if (!empty($paramsProdutos)) {
    $paramsCount = array_merge($paramsCount, $paramsProdutos);
    $typesCount .= $typesProdutos;
}

if (!empty($paramsCount)) {
    $stmtCount->bind_param($typesCount, ...$paramsCount);
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
$listaSql = "SELECT
                e.cnpj,
                e.razao_social,
                e.estado,
                e.cidade,
                e.bairro,
                e.porte,
                e.cod_cnae,
                e.cnae
              FROM d_empresas e
              WHERE 1=1" . $condicoesEmpresa . "
              AND EXISTS (
                SELECT 1 FROM f_preaprovados f
                WHERE f.cnpj = e.cnpj" . $condicoesProdutos . "
              )
              ORDER BY e.razao_social
              LIMIT ? OFFSET ?";

$paramsLista = $paramsEmpresa;
$typesLista  = $typesEmpresa;

if (!empty($paramsProdutos)) {
    $paramsLista = array_merge($paramsLista, $paramsProdutos);
    $typesLista .= $typesProdutos;
}

$typesLista .= "ii";
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
                FROM f_preaprovados f
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
