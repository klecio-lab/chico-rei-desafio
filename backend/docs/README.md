# docs — chico-rei-desafio (back-end)

Fonte da verdade do back-end. O front-end consome `tecnica/api.md` como contrato.

## Mapa

| Pasta/arquivo | O que tem |
|---|---|
| `edital-checklist.md` | todos os itens do edital com status |
| `regras-de-negocio/` | comportamento (o **quê**) — auth, produtos, logs |
| `tecnica/` | implementação (o **como**) — API, arquitetura, banco, testes, ambiente |
| `decisoes/` (BDRs) | **porquê** de cada decisão + trade-offs (base do item 5 do README) |
| `visao/` | escopo e fora-do-escopo (base do item 6 do README) |
| `../AGENTS.md` | regras do Laravel (template genérico) — BDRs referenciam os `§` dele |

## Convenção

Mudou comportamento → atualize o doc correspondente **no mesmo commit**.
Mudou endpoint → atualize `tecnica/api.md` (o front depende dele).
Nova decisão → nova BDR em `decisoes/` (não edite BDR aplicada; crie outra que a sucede).
