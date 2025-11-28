const campoBusca = document.getElementById("campo-busca");
const listaAutocomplete = document.getElementById("autocomplete-list");
const listaResultados = document.getElementById("lista-resultados");
const resultCount = document.getElementById("result-count");
const filtroEstado = document.getElementById("filtro-estado");
const filtroCidade = document.getElementById("filtro-cidade");
const filtroBairro = document.getElementById("filtro-bairro");
const legendContainer = document.getElementById("legend-produtos");
const btnLimparFiltros = document.getElementById("btn-limpar-filtros");
const btnAplicarFiltros = document.getElementById("btn-aplicar-filtros");
const btnPaginaAnterior = document.getElementById("btn-pagina-anterior");
const btnPaginaProxima = document.getElementById("btn-pagina-proxima");
const paginationInfo = document.getElementById("pagination-info");

let empresasCache = [];
let chartProdutos = null;
let autocompleteAbortController = null;
let paginaAtual = 1;
let totalPaginas = 1;
let totalResultados = 0;
const porPagina = 20;

/* ---------- HELPERS ---------- */

function formatarMoeda(valor) {
  return valor.toLocaleString("pt-BR", {
    style: "currency",
    currency: "BRL",
    maximumFractionDigits: 2
  });
}

/* ---------- CHAMADA API EMPRESAS (lista/filtros) ---------- */

async function buscarEmpresasApi(pagina = 1) {
  const estado = filtroEstado ? filtroEstado.value : "";
  const cidade = filtroCidade ? filtroCidade.value : "";
  const bairro = filtroBairro ? filtroBairro.value : "";

  const params = new URLSearchParams();
  if (estado) params.append("estado", estado);
  if (cidade) params.append("cidade", cidade);
  if (bairro) params.append("bairro", bairro);
  params.append("pagina", pagina.toString());
  params.append("por_pagina", porPagina.toString());

  const url =
    "buscar_empresas.php" + (params.toString() ? "?" + params.toString() : "");

  try {
    const resp = await fetch(url);
    const data = await resp.json();

    if (!resp.ok) {
      console.error(data);
      alert("Erro ao buscar empresas.");
      return;
    }

    empresasCache = data.empresas || [];
    paginaAtual = data.pagina || 1;
    totalPaginas = data.total_paginas || 1;
    totalResultados = data.total ?? empresasCache.length;

    atualizarResultados(empresasCache, {
      total: totalResultados,
      pagina: paginaAtual,
      totalPaginas
    });
    atualizarGrafico(empresasCache);
    atualizarPaginacao();
  } catch (err) {
    console.error(err);
    alert("Falha na comunicação com o servidor.");
  }
}

/* ---------- RESULTADOS DA LISTA ---------- */

function atualizarResultados(lista, meta = {}) {
  listaResultados.innerHTML = "";

  if (!lista || !lista.length) {
    resultCount.textContent = "0 resultados";
    return;
  }

  const total = meta.total ?? lista.length;
  const pagina = meta.pagina ?? 1;
  const totalPaginas = meta.totalPaginas ?? 1;
  const listaExibir = lista;

  resultCount.textContent = `${total} resultado${total > 1 ? "s" : ""} • Página ${pagina} de ${totalPaginas}`;

  listaExibir.forEach((e) => {
    const card = document.createElement("a");
    card.className = "result-card";
    card.href = `detalhes.php?cnpj=${encodeURIComponent(e.cnpj)}`;

    const chipsHtml = (e.produtos || [])
      .map(
        (p, index) => `
        <span class="chip ${index === 0 ? "" : "secondary"}">
          <span class="chip-dot"></span>
          ${p.nome}
          <span class="chip-value">${formatarMoeda(p.valor)}</span>
        </span>`
      )
      .join("");

    card.innerHTML = `
      <div class="result-top-row">
        <div>
          <div class="result-name">${e.razao_social}</div>
          <div class="result-cnpj">CNPJ: ${e.cnpj}</div>
        </div>
        <div class="result-location">
          ${e.cidade || ""} - ${e.estado || ""}<br/>
          ${e.bairro || ""}
        </div>
      </div>
      <div class="product-chips">
        ${chipsHtml}
      </div>
    `;

    listaResultados.appendChild(card);
  });
}

/* ---------- AUTOCOMPLETE (consulta no banco inteiro) ---------- */

function preencherAutocomplete(lista) {
  listaAutocomplete.innerHTML = "";

  if (!campoBusca.value.trim() || !lista.length) {
    listaAutocomplete.style.display = "none";
    return;
  }

  listaAutocomplete.style.display = "block";

  lista.forEach((e) => {
    const item = document.createElement("div");
    item.className = "autocomplete-item";
    item.innerHTML = `
      <span>${e.razao_social}</span>
      <span>${e.cnpj} • ${e.cidade || ""} - ${e.estado || ""}</span>
    `;
    item.addEventListener("click", () => {
      // clique na sugestão vai DIRETO para detalhes
      window.location.href = `detalhes.php?cnpj=${encodeURIComponent(e.cnpj)}`;
    });
    listaAutocomplete.appendChild(item);
  });
}

async function buscarSugestoes(query) {
  const q = query.trim();

  if (!q || q.length < 2) {
    listaAutocomplete.style.display = "none";
    return;
  }

  // cancela requisição anterior se o usuário continuar digitando
  if (autocompleteAbortController) {
    autocompleteAbortController.abort();
  }
  autocompleteAbortController = new AbortController();

  try {
    const resp = await fetch(
      "autocomplete.php?q=" + encodeURIComponent(q),
      { signal: autocompleteAbortController.signal }
    );
    const data = await resp.json();

    if (!resp.ok) {
      console.error(data);
      return;
    }

    preencherAutocomplete(data.empresas || []);
  } catch (err) {
    if (err.name === "AbortError") {
      return;
    }
    console.error("Erro no autocomplete", err);
  }
}

if (campoBusca) {
  campoBusca.addEventListener("input", (e) => {
    buscarSugestoes(e.target.value);
  });
}

// Clique fora fecha autocomplete
document.addEventListener("click", (e) => {
  if (!e.target.closest(".search-input-wrapper")) {
    listaAutocomplete.style.display = "none";
  }
});

/* ---------- GRÁFICO DE ROSCA ---------- */

function calcularQuantidadePorProduto(lista) {
  const contagem = {};

  (lista || []).forEach((empresa) => {
    (empresa.produtos || []).forEach((p) => {
      if (p.valor > 0) {
        contagem[p.nome] = (contagem[p.nome] || 0) + 1;
      }
    });
  });

  return contagem;
}

function atualizarGrafico(lista) {
  const canvas = document.getElementById("chart-produtos");
  if (!canvas || typeof Chart === "undefined") return;

  // limpa legenda anterior
  if (legendContainer) {
    legendContainer.innerHTML = "";
  }

  // calcula contagem por produto
  const contagem = calcularQuantidadePorProduto(lista);
  const labels = Object.keys(contagem);
  const valores = Object.values(contagem);

  // se não tiver nada, destrói gráfico existente e sai
  if (!labels.length) {
    if (chartProdutos) {
      chartProdutos.destroy();
      chartProdutos = null;
    }
    return;
  }

  const cores = [
    "#cc092f",
    "#1b2563",
    "#10b981",
    "#fbbf24",
    "#6366f1",
    "#ec4899",
    "#14b8a6"
  ];

  // destrói gráfico antigo se existir
  if (chartProdutos) {
    chartProdutos.destroy();
  }

  // cria o donut
  chartProdutos = new Chart(canvas, {
    type: "doughnut",
    data: {
      labels,
      datasets: [
        {
          data: valores,
          backgroundColor: cores.slice(0, labels.length),
          borderWidth: 1,
          borderColor: "#ffffff"
        }
      ]
    },
    options: {
      plugins: {
        legend: { display: false } // esconde legenda nativa do Chart.js
      },
      cutout: "72%" // rosca mais fina
    }
  });

  // monta a legenda HTML com N de empresas por produto
  if (legendContainer) {
    labels.forEach((label, idx) => {
      const qtd = valores[idx]; // quantidade de empresas naquele produto

      const item = document.createElement("div");
      item.className = "chart-legend-item";
      item.innerHTML = `
        <span class="chart-legend-dot" style="background-color:${cores[idx]}"></span>
        <span class="chart-legend-label">
          ${label} (${qtd})
        </span>
      `;
      legendContainer.appendChild(item);
    });
  }
}


/* ---------- FILTROS DO BANCO ---------- */

async function carregarEstados() {
  if (!filtroEstado) return;
  try {
    const resp = await fetch("filtros.php?tipo=estados");
    const data = await resp.json();

    filtroEstado.innerHTML = '<option value="">Todos</option>';

    (data.estados || []).forEach((uf) => {
      const opt = document.createElement("option");
      opt.value = uf;
      opt.textContent = uf;
      filtroEstado.appendChild(opt);
    });
  } catch (err) {
    console.error("Erro ao carregar estados", err);
  }
}

async function carregarCidades(estado) {
  if (!filtroCidade) return;
  filtroCidade.innerHTML = '<option value="">Todas</option>';
  filtroBairro.innerHTML = '<option value="">Todos</option>';

  if (!estado) return;

  try {
    const resp = await fetch(
      "filtros.php?tipo=cidades&estado=" + encodeURIComponent(estado)
    );
    const data = await resp.json();

    (data.cidades || []).forEach((cidade) => {
      const opt = document.createElement("option");
      opt.value = cidade;
      opt.textContent = cidade;
      filtroCidade.appendChild(opt);
    });
  } catch (err) {
    console.error("Erro ao carregar cidades", err);
  }
}

async function carregarBairros(estado, cidade) {
  if (!filtroBairro) return;
  filtroBairro.innerHTML = '<option value="">Todos</option>';

  if (!estado || !cidade) return;

  try {
    const resp = await fetch(
      "filtros.php?tipo=bairros&estado=" +
        encodeURIComponent(estado) +
        "&cidade=" +
        encodeURIComponent(cidade)
    );
    const data = await resp.json();

    (data.bairros || []).forEach((bairro) => {
      const opt = document.createElement("option");
      opt.value = bairro;
      opt.textContent = bairro;
      filtroBairro.appendChild(opt);
    });
  } catch (err) {
    console.error("Erro ao carregar bairros", err);
  }
}

/* Eventos dos filtros: toda mudança já dispara busca */

if (filtroEstado) {
  filtroEstado.addEventListener("change", () => {
    const uf = filtroEstado.value;
    carregarCidades(uf);
  });
}

if (filtroCidade) {
  filtroCidade.addEventListener("change", () => {
    const uf = filtroEstado.value;
    const cidade = filtroCidade.value;
    carregarBairros(uf, cidade);
  });
}

/* ---------- LIMPAR FILTROS ---------- */

if (btnLimparFiltros) {
  btnLimparFiltros.addEventListener("click", () => {
    if (filtroEstado) filtroEstado.value = "";
    if (filtroCidade) filtroCidade.innerHTML = '<option value="">Todas</option>';
    if (filtroBairro) filtroBairro.innerHTML = '<option value="">Todos</option>';
    if (campoBusca) campoBusca.value = "";
    if (listaAutocomplete) {
      listaAutocomplete.innerHTML = "";
      listaAutocomplete.style.display = "none";
    }
    paginaAtual = 1;
    buscarEmpresasApi(paginaAtual);
  });
}

/* ---------- APLICAR FILTROS ---------- */

if (btnAplicarFiltros) {
  btnAplicarFiltros.addEventListener("click", () => {
    paginaAtual = 1;
    buscarEmpresasApi(paginaAtual);
  });
}

/* ---------- PAGINAÇÃO ---------- */

function atualizarPaginacao() {
  if (paginationInfo) {
    paginationInfo.textContent = `Página ${paginaAtual} de ${totalPaginas}`;
  }

  if (btnPaginaAnterior) {
    btnPaginaAnterior.disabled = paginaAtual <= 1;
  }
  if (btnPaginaProxima) {
    btnPaginaProxima.disabled = paginaAtual >= totalPaginas;
  }
}

if (btnPaginaAnterior) {
  btnPaginaAnterior.addEventListener("click", () => {
    if (paginaAtual > 1) {
      buscarEmpresasApi(paginaAtual - 1);
    }
  });
}

if (btnPaginaProxima) {
  btnPaginaProxima.addEventListener("click", () => {
    if (paginaAtual < totalPaginas) {
      buscarEmpresasApi(paginaAtual + 1);
    }
  });
}

/* ---------- INICIAL ---------- */

window.addEventListener("DOMContentLoaded", () => {
  carregarEstados();
  buscarEmpresasApi(); // primeira carga da lista + gráfico
});
