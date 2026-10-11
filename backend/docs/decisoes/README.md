# BDRs — Business Decision Records

Registro numerado de cada decisão relevante (base do **item 5 do README**: decisões + trade-offs).

## Formato

Cada `BDR-NNN-*.md`: Status (`[PROPOSTO]`/`[APLICADO]`) · Contexto · Decisão · Alternativas descartadas (e porquê) · Consequências · Refs (edital + `§` do AGENTS.md).

Regra: BDR aplicada **não se edita** — mudança de ideia gera BDR nova que a sucede.

## Índice

| BDR | Decisão | Status |
|---|---|---|
| 001 | Passport em vez de Sanctum | [APLICADO] |
| 002 | MySQL via Sail (porta 3307) | [APLICADO] |
| 003 | Envelope `response()->json` único | [PROPOSTO] |
| 004 | FormRequest em tudo, zero `validate()` no controller | [PROPOSTO] |
| 005 | Logs via Observer + Job async | [PROPOSTO] |
| 006 | `image` como URL (string), sem upload | [PROPOSTO] |
| 007 | Sem importação externa/scraping | [SUCEDIDA por BDR-009] |
| 008 | Monorepo `backend/` + `frontend/` | [APLICADO] |
| 009 | Importação via scraping (sucede 007) | [PROPOSTO] P1 |
| 010 | Upload real de imagem | [PROPOSTO] P2 |
| 011 | Sentry (back) | [PROPOSTO] P2 |
| 012 | Elasticsearch (condicional) | [PROPOSTO] P3 |
| 013 | CI no push | [PROPOSTO] P1 |
| 014 | Sem Resources/scopes/refresh/cursor | [APLICADO] |
