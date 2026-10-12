# BDR-012 — Elasticsearch (condicional)

- **Status:** [APLICADO] · **Prioridade:** P3 · **Chat:** back · **Gate:** só executa se 009/010/011/013 estiverem verdes até seg à noite (spike coube no timebox: serviço + REST sem SDK + fallback testado com e sem servidor)
- **Contexto:** diferencial "busca inteligente"; é o item mais pesado (serviço + driver Scout + reindex).
- **Decisão:** spike com timebox de 2h (serviço no Sail + driver Scout compatível). Passou → `q` usa ES com fallback para `LIKE`; travou → aborta e registra o resultado aqui, `LIKE` com índice permanece.
- **Consequências:** nota preservada em qualquer cenário; ES nunca pode quebrar a busca atual (fallback obrigatório).
- **Refs:** edital (diferencial Elasticsearch).
