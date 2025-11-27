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

if (strlen($cnpjLimpo) !== 14) {
    die('CNPJ inválido.');
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
      email,
      ddd,
      celular,
      telefone,
      cod_cnae,
      cnae,
      latitude,
      longitude
    FROM d_empresas
    WHERE cnpj = ?
    LIMIT 1
";
$stmtEmp = $mysqli->prepare($sqlEmpresa);

if (!$stmtEmp) {
    error_log('Erro ao preparar consulta de empresa: ' . $mysqli->error);
    die('Não foi possível carregar os dados da empresa.');
}

$stmtEmp->bind_param('s', $cnpjLimpo);
$stmtEmp->execute();
$resEmp = $stmtEmp->get_result();
$empresa = $resEmp ? $resEmp->fetch_assoc() : null;
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
    FROM f_preaprovados f
    JOIN d_produtos p ON p.id = f.id_produto
    WHERE f.cnpj = ?
    ORDER BY p.ordem, p.nome
";
$stmtProd = $mysqli->prepare($sqlProd);

if (!$stmtProd) {
    error_log('Erro ao preparar consulta de produtos: ' . $mysqli->error);
    die('Não foi possível carregar os produtos pré-aprovados.');
}

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
                $email = trim($empresa['email'] ?? '');

                $temAlgumContato = false;

                if ($tel !== '') {
                    echo 'Tel.: ' . htmlspecialchars($tel) . '<br />';
                    $temAlgumContato = true;
                }
                if ($cel !== '') {
                    echo 'Cel.: ' . htmlspecialchars($cel) . '<br />';
                    $temAlgumContato = true;
                }
                if ($email !== '') {
                    echo 'E-mail: ' . htmlspecialchars($email);
                    $temAlgumContato = true;
                }
                if (!$temAlgumContato) {
                    echo 'Sem contato cadastrado.';
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

    <section class="card" style="margin-top:14px;">
      <div class="side-card-title">Comentários sobre a prospecção</div>

      <form id="form-comentario" class="comment-form">
        <div class="form-grid">
          <div class="form-field">
            <label for="comentario-nome">Nome</label>
            <input
              type="text"
              id="comentario-nome"
              name="nome"
              maxlength="200"
              required
              placeholder="Quem está registrando"
            />
          </div>
          <div class="form-field">
            <label for="comentario-juncao">Junção</label>
            <input
              type="text"
              id="comentario-juncao"
              name="juncao"
              maxlength="255"
              placeholder="Ex.: 4160"
            />
            <small class="helper-text">Informe a junção (opcional).</small>
          </div>
        </div>

        <div class="form-field">
          <label for="comentario-texto">Resumo</label>
          <textarea
            id="comentario-texto"
            name="comentario"
            rows="4"
            maxlength="4000"
            required
            placeholder="Ex.: Visitado em 20/01, cliente pediu simulação de capital de giro"
          ></textarea>
        </div>

        <div class="comment-actions">
          <button type="submit" class="btn-primary" id="btn-adicionar-comentario">
            Adicionar comentário
          </button>
        </div>
      </form>

      <div id="comentarios-lista" class="comments-list">
        <p class="helper-text" style="margin-bottom:0;">Carregando comentários...</p>
      </div>
    </section>
  </main>

  <script>
    const cnpjEmpresa = <?php echo json_encode($empresa['cnpj']); ?>;
    const comentariosLista = document.getElementById("comentarios-lista");
    const formComentario = document.getElementById("form-comentario");
    const btnAdicionarComentario = document.getElementById("btn-adicionar-comentario");

    function formatarDataComentario(dataStr) {
      if (!dataStr) return "";
      const data = new Date(dataStr.replace(" ", "T"));
      if (isNaN(data.getTime())) return dataStr;
      return data.toLocaleString("pt-BR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      });
    }

    function renderizarComentarios(lista) {
      if (!comentariosLista) return;
      comentariosLista.innerHTML = "";

      if (!lista || !lista.length) {
        comentariosLista.innerHTML =
          '<p class="empty-state">Nenhum comentário registrado ainda.</p>';
        return;
      }

      lista.forEach((c) => {
        const item = document.createElement("div");
        item.className = "comment-item";

        const header = document.createElement("div");
        header.className = "comment-header";

        const author = document.createElement("div");
        author.className = "comment-author";
        author.textContent = c.nome;

        const meta = document.createElement("div");
        meta.className = "comment-meta";
        const juncaoLabel = c.juncao ? `${c.juncao} • ` : "";
        meta.textContent = `${juncaoLabel}${formatarDataComentario(c.data)}`;

        const btnDelete = document.createElement("button");
        btnDelete.className = "comment-delete";
        btnDelete.type = "button";
        btnDelete.textContent = "Excluir";
        btnDelete.dataset.idComentario = c.id;

        header.appendChild(author);
        header.appendChild(meta);
        header.appendChild(btnDelete);

        const corpo = document.createElement("p");
        corpo.className = "comment-body";
        corpo.textContent = c.comentario;

        item.appendChild(header);
        item.appendChild(corpo);
        comentariosLista.appendChild(item);
      });
    }

    async function carregarComentarios() {
      if (!cnpjEmpresa) return;
      try {
        const resp = await fetch(
          "comentarios.php?cnpj=" + encodeURIComponent(cnpjEmpresa)
        );
        const data = await resp.json();
        if (!resp.ok) {
          console.error(data);
          comentariosLista.innerHTML =
            '<p class="empty-state">Não foi possível carregar os comentários.</p>';
          return;
        }
        renderizarComentarios(data.comentarios || []);
      } catch (err) {
        console.error(err);
        comentariosLista.innerHTML =
          '<p class="empty-state">Erro ao carregar comentários.</p>';
      }
    }

    async function adicionarComentario(event) {
      event.preventDefault();
      if (!formComentario || !btnAdicionarComentario) return;

      const nome = document.getElementById("comentario-nome").value.trim();
      const juncao = document.getElementById("comentario-juncao").value.trim();
      const comentario = document.getElementById("comentario-texto").value.trim();

      if (!nome || !comentario) {
        alert("Preencha nome e comentário.");
        return;
      }

      btnAdicionarComentario.disabled = true;

      try {
        const resp = await fetch("comentarios.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ cnpj: cnpjEmpresa, nome, juncao, comentario })
        });
        const data = await resp.json();
        if (!resp.ok) {
          console.error(data);
          alert(data.mensagem || "Erro ao salvar comentário.");
          return;
        }
        formComentario.reset();
        await carregarComentarios();
      } catch (err) {
        console.error(err);
        alert("Falha na comunicação ao salvar comentário.");
      } finally {
        btnAdicionarComentario.disabled = false;
      }
    }

    async function removerComentario(id) {
      if (!id) return;
      try {
        const body = new URLSearchParams();
        body.append("id", id);
        body.append("cnpj", cnpjEmpresa);
        const resp = await fetch("comentarios.php", {
          method: "DELETE",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body
        });
        const data = await resp.json();
        if (!resp.ok) {
          console.error(data);
          alert(data.mensagem || "Não foi possível excluir o comentário.");
          return;
        }
        await carregarComentarios();
      } catch (err) {
        console.error(err);
        alert("Erro ao excluir comentário.");
      }
    }

    if (formComentario) {
      formComentario.addEventListener("submit", adicionarComentario);
    }

    if (comentariosLista) {
      comentariosLista.addEventListener("click", (event) => {
        const btn = event.target.closest("[data-id-comentario]");
        if (btn) {
          removerComentario(btn.dataset.idComentario);
        }
      });
    }

    carregarComentarios();
  </script>

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

      // corrige tamanho em rolagem ou troca de orientação em mobile
      setTimeout(function () {
        map.invalidateSize();
      }, 200);
      window.addEventListener("resize", function () {
        map.invalidateSize();
      });
    });
  </script>
  <?php endif; ?>
</body>
</html>
