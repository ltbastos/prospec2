<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Consulta Pré-Aprovado PJ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="icon" href="img/icone3.png" type="image/png" />
  <link rel="stylesheet" href="style.css" />
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
          <div class="brand-subtitle">Consulta de pré-aprovados para empresas</div>
        </div>
      </div>
    </div>
  </header>

  <main class="app-shell">
    <div class="main-grid">
      <div class="main-left">
        <section class="card search-card">
          <div class="card-header">
            <div>
              <div class="card-title">Consulta de Pré-Aprovado</div>
              <div class="card-subtitle">
                Busque por CNPJ ou nome da empresa. Clique em uma sugestão para ir aos detalhes.
              </div>
            </div>
          </div>

          <div class="search-main">
            <div class="search-row">
              <div class="search-input-wrapper">
                <input
                  type="text"
                  id="campo-busca"
                  placeholder="Digite CNPJ ou nome da empresa..."
                  autocomplete="off"
                />
                <div class="search-icon">🔍</div>

                <div id="autocomplete-list" class="autocomplete-list"></div>
              </div>

              <div class="select">
                <label for="filtro-estado">Estado</label>
                <select id="filtro-estado">
                  <option value="">Todos</option>
                </select>
              </div>

              <div class="select">
                <label for="filtro-cidade">Cidade</label>
                <select id="filtro-cidade">
                  <option value="">Todas</option>
                </select>
              </div>

              <div class="select">
                <label for="filtro-bairro">Bairro</label>
                <select id="filtro-bairro">
                  <option value="">Todos</option>
                </select>
              </div>
            </div>

            <div class="search-actions">
              <button type="button" id="btn-aplicar-filtros" class="btn-primary">
                Aplicar filtros
              </button>
              <button type="button" id="btn-limpar-filtros" class="btn-link">
                Limpar filtros
              </button>
            </div>
          </div>
        </section>

        <section class="card results-card">
          <div class="result-header-row">
            <div class="card-title">Empresas encontradas</div>
            <div>
              <span class="result-count" id="result-count">0 resultados</span>
              <span class="result-tag">Apenas não correntistas</span>
            </div>
          </div>

          <div id="lista-resultados" class="results-list"></div>

          <div class="pagination-bar">
            <button type="button" id="btn-pagina-anterior" class="btn-secondary">
              Anterior
            </button>
            <span id="pagination-info" class="pagination-info">Página 1 de 1</span>
            <button type="button" id="btn-pagina-proxima" class="btn-secondary">
              Próxima
            </button>
          </div>
        </section>
      </div>

      <aside class="side-column">
        <section class="card">
          <div class="side-card-title">Distribuição por produto</div>
          <p class="helper-text">
            Quantidade de empresas com pré-aprovado &gt; 0 em cada produto.
          </p>
          <div class="chart-wrapper">
            <canvas id="chart-produtos"></canvas>
          </div>
          <div id="legend-produtos" class="chart-legend"></div>
        </section>
      </aside>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
</body>
</html>
