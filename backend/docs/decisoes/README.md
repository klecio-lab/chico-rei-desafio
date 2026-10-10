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
| 007 | Sem importação externa/scraping | [APLICADO] |
| 008 | Monorepo `backend/` + `frontend/` | [APLICADO] |
