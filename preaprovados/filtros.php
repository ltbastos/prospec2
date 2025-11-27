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

$tipo   = isset($_GET['tipo'])   ? $_GET['tipo']   : 'estados';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$cidade = isset($_GET['cidade']) ? $_GET['cidade'] : '';

if ($tipo === 'estados') {
    $sql = "SELECT DISTINCT estado FROM d_empresas WHERE estado IS NOT NULL AND estado <> '' ORDER BY estado";
    $res = $mysqli->query($sql);
    $estados = [];
    while ($row = $res->fetch_assoc()) {
        $estados[] = $row['estado'];
    }
    echo json_encode(['estados' => $estados]);
} elseif ($tipo === 'cidades') {
    if ($estado === '') {
        echo json_encode(['cidades' => []]);
    } else {
        $stmt = $mysqli->prepare("SELECT DISTINCT cidade FROM d_empresas WHERE estado = ? AND cidade IS NOT NULL AND cidade <> '' ORDER BY cidade");
        $stmt->bind_param('s', $estado);
        $stmt->execute();
        $res = $stmt->get_result();
        $cidades = [];
        while ($row = $res->fetch_assoc()) {
            $cidades[] = $row['cidade'];
        }
        $stmt->close();
        echo json_encode(['cidades' => $cidades]);
    }
} elseif ($tipo === 'bairros') {
    if ($estado === '' || $cidade === '') {
        echo json_encode(['bairros' => []]);
    } else {
        $stmt = $mysqli->prepare("SELECT DISTINCT bairro FROM d_empresas WHERE estado = ? AND cidade = ? AND bairro IS NOT NULL AND bairro <> '' ORDER BY bairro");
        $stmt->bind_param('ss', $estado, $cidade);
        $stmt->execute();
        $res = $stmt->get_result();
        $bairros = [];
        while ($row = $res->fetch_assoc()) {
            $bairros[] = $row['bairro'];
        }
        $stmt->close();
        echo json_encode(['bairros' => $bairros]);
    }
} else {
    echo json_encode(['erro' => 'Tipo de filtro inválido.']);
}

$mysqli->close();
