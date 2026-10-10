# BDR-003 — Envelope `response()->json` único

- **Status:** [PROPOSTO]
- **Contexto:** edital exige padronização de respostas + erros consistentes (20% da nota em boas práticas).
- **Decisão:** toda resposta é `return response()->json(['message'?, 'data'?, 'meta'?], $status)`; listas com `meta` de paginação; `422/401/403/404` em PT-BR.
- **Alternativas:** API Resources por model — descartado para o desafio (envelope manual + ` casts` cobre; Resources entrariam com mais tempo).
- **Consequências:** front trata 1 formato; decimais do MySQL documentados (`Number()` no front).
- **Refs:** edital (não negociável: respostas/erros) · AGENTS.md `§0.3`, `§4`.
