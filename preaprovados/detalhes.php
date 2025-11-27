<?php
// detalhes.php
$host = 'localhost';
$db   = 'PreAprovados';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die('Erro de conexão com o banco.');
}
$mysqli->set_charset($charset);

// CNPJ vindo da URL
$cnpjParam = isset($_GET['cnpj']) ? $_GET['cnpj'] : '';
$cnpjLimpo = preg_replace('/\D/', '', $cnpjParam);

if ($cnpjLimpo === '') {
    die('CNPJ não informado.');
}

// Dados da empresa
$sqlEmpresa = "
    SELECT
      cnpj,
      razao_social,
      porte,
      estado,
      cidade,
      bairro,
      rua,
      numero,
      complemento,
      ddd,
      celular,
      telefone,
      cod_cnae,
      cnae,
      latitude,
      longitude
    FROM d_empresas
    WHERE REPLACE(cnpj, '.', '') = ? OR cnpj = ?
    LIMIT 1
";
$stmtEmp = $mysqli->prepare($sqlEmpresa);
$stmtEmp->bind_param('ss', $cnpjLimpo, $cnpjLimpo);
$stmtEmp->execute();
$resEmp = $stmtEmp->get_result();
$empresa = $resEmp->fetch_assoc();
$stmtEmp->close();

if (!$empresa) {
    $mysqli->close();
    die('Empresa não encontrada.');
}

// Produtos pré-aprovados
$sqlProd = "
    SELECT
      p.nome AS produto_nome,
      f.valor_pre_aprovado,
      f.data_referencia AS data_pre_aprovado
    FROM f_preAprovados f
    JOIN d_produtos p ON p.id = f.id_produto
    WHERE f.cnpj = ?
    ORDER BY p.ordem, p.nome
";
$stmtProd = $mysqli->prepare($sqlProd);
$stmtProd->bind_param('s', $empresa['cnpj']);
$stmtProd->execute();
$resProd = $stmtProd->get_result();

$produtos = [];
while ($row = $resProd->fetch_assoc()) {
    $produtos[] = $row;
}

$stmtProd->close();
$mysqli->close();

// Coordenadas
$latitude  = isset($empresa['latitude']) ? trim($empresa['latitude']) : '';
$longitude = isset($empresa['longitude']) ? trim($empresa['longitude']) : '';
$temCoordenadas = ($latitude !== '' && $longitude !== '');

function formatarCnpj($cnpj) {
    $n = preg_replace('/\D/', '', $cnpj);
    if (strlen($n) === 14) {
        return substr($n,0,2).'.'.substr($n,2,3).'.'.substr($n,5,3).'/'.substr($n,8,4).'-'.substr($n,12,2);
    }
    return $cnpj;
}

function formatarTelefone($ddd, $tel) {
    $ddd = trim($ddd);
    $tel = preg_replace('/\D/', '', $tel);
    if ($tel === '') return '';
    if ($ddd !== '') {
        return "($ddd) " . $tel;
    }
    return $tel;
}

function formatarData($data) {
    if (!$data) return '';
    $ts = strtotime($data);
    if (!$ts) return $data;
    return date('d/m/Y', $ts);
}

function formatarMoeda($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Detalhes da empresa - Pré-Aprovado PJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="icon" href="img/icone3.png" type="image/png" />
  <link rel="stylesheet" href="style.css" />

  <!-- Leaflet CSS (sem integrity pra não bloquear) -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
</head>
<body>
  <header class="app-header">
    <div class="app-header-inner">
      <div class="brand">
        <div class="brand-logo">
          <img src="img/icone3.png" alt="Pré-Aprovado PJ" />
        </div>
        <div class="brand-text">
          <div class="brand-title">Pré-Aprovado PJ</div>
          <div class="brand-subtitle">Detalhes da empresa</div>
        </div>
      </div>
    </div>
  </header>

  <main class="app-shell">
    <a href="index.php" class="back-link">← Voltar para a busca</a>

    <section class="card">
      <div class="details-header">
        <div class="details-name">
          <?php echo htmlspecialchars($empresa['razao_social']); ?>
        </div>
        <div class="details-cnpj">
          CNPJ: <?php echo htmlspecialchars(formatarCnpj($empresa['cnpj'])); ?>
        </div>
        <div class="details-tags">
          <?php if (!empty($empresa['porte'])): ?>
            <span class="tag-pill">Porte: <?php echo htmlspecialchars($empresa['porte']); ?></span>
          <?php endif; ?>
          <?php if (!empty($empresa['cod_cnae'])): ?>
            <span class="tag-pill">
              CNAE: <?php echo htmlspecialchars($empresa['cod_cnae']); ?>
            </span>
          <?php endif; ?>
          <span class="tag-pill">Não correntista</span>
        </div>
      </div>

      <div class="details-grid">
        <div>
          <div class="details-block-title">Endereço</div>
          <p class="details-text">
            <?php
              $linha1 = trim(($empresa['rua'] ?? '') . ', ' . ($empresa['numero'] ?? ''));
              $linha2 = trim(($empresa['bairro'] ?? '') . ' • ' . ($empresa['cidade'] ?? '') . ' - ' . ($empresa['estado'] ?? ''));
              if ($linha1 !== '') {
                  echo htmlspecialchars($linha1) . '<br />';
              }
              if (!empty($empresa['complemento'])) {
                  echo htmlspecialchars($empresa['complemento']) . '<br />';
              }
              echo htmlspecialchars($linha2);
            ?>
          </p>

          <div style="margin-top:10px;">
            <div class="details-block-title">Contato</div>
            <p class="details-text">
              <?php
                $tel = formatarTelefone($empresa['ddd'], $empresa['telefone']);
                $cel = formatarTelefone($empresa['ddd'], $empresa['celular']);
                if ($tel !== '') {
                    echo 'Tel.: ' . htmlspecialchars($tel) . '<br />';
                }
                if ($cel !== '') {
                    echo 'Cel.: ' . htmlspecialchars($cel);
                }
                if ($tel === '' && $cel === '') {
                    echo 'Sem telefone cadastrado.';
                }
              ?>
            </p>
          </div>
        </div>
      </div>

      <?php if ($temCoordenadas): ?>
        <div id="map" class="map-container"></div>
      <?php endif; ?>
    </section>

    <section class="card" style="margin-top:14px;">
      <div class="side-card-title">Produtos pré-aprovados</div>

      <?php if (empty($produtos)): ?>
        <p class="empty-state">
          Nenhum produto pré-aprovado cadastrado para esta empresa.
        </p>
      <?php else: ?>
        <table class="table-produtos">
          <thead>
            <tr>
              <th>Produto</th>
              <th>Valor pré-aprovado</th>
              <th>Data de referência</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($produtos as $p): ?>
              <tr>
                <td><?php echo htmlspecialchars($p['produto_nome']); ?></td>
                <td><?php echo htmlspecialchars(formatarMoeda($p['valor_pre_aprovado'])); ?></td>
                <td><?php echo htmlspecialchars(formatarData($p['data_pre_aprovado'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>

  <?php if ($temCoordenadas): ?>
  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    window.addEventListener("load", function () {
      // coordenadas vindas do PHP
      var lat = <?php echo json_encode((float)$latitude); ?>;
      var lng = <?php echo json_encode((float)$longitude); ?>;

      var mapEl = document.getElementById("map");
      if (!mapEl || isNaN(lat) || isNaN(lng)) return;

      // inicializa o mapa
      var map = L.map(mapEl).setView([lat, lng], 15);

      // Tile claro, lembrando o estilo do Google Maps
      L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
        {
          attribution:
            '&copy; OpenStreetMap contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
          maxZoom: 19
        }
      ).addTo(map);

      // Marcador da empresa
      L.marker([lat, lng]).addTo(map);
    });
  </script>
  <?php endif; ?>
</body>
</html>
