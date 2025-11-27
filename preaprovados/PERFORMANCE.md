# Desempenho esperado em bases grandes

## Tamanho citado
O código atual foi pensado para trabalhar com:
- `d_empresas` com ~800.000 linhas.
- `f_preaprovados` com ~5.000.000 linhas.

## Por que deve escalar razoavelmente
- A busca de lista e a contagem usam `EXISTS` correlacionado por `cnpj`, o que permite ao MySQL resolver as correspondências via índice de `f_preaprovados` em vez de gerar um `JOIN ... DISTINCT` custoso.
- Apenas 20 registros são retornados por página (`LIMIT ? OFFSET ?`), e os produtos só são carregados para os CNPJs da página atual, reduzindo transferência e CPU no PHP.

## Pontos de atenção
- A consulta de contagem ainda percorre as chaves que satisfazem os filtros. Em estados com milhões de CNPJs, um índice eficiente em `f_preaprovados (cnpj, id_produto)` e nos campos de filtro de `d_empresas` (`estado`, `cidade`, `bairro`, `cod_cnae`) é essencial para manter tempo de resposta.
- Se a coluna `cod_cnae` for usada com frequência, um índice dedicado ou composto (`estado`, `cod_cnae`) pode ajudar a filtrar mais cedo.
- Para bases com alta concorrência, considere mover a aplicação para um pool de conexões persistentes ou aumentar os recursos do servidor MySQL para evitar filas de execução.

## Sugestão de verificação
Antes de colocar em produção, rode `EXPLAIN` nas duas consultas principais de `buscar_empresas.php` (contagem e lista) com filtros típicos do uso real e confirme que o plano utiliza índices em `f_preaprovados.cnpj` e nos filtros da dimensão de empresas.
