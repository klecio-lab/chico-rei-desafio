# BDR-008 — Monorepo `backend/` + `frontend/`

- **Status:** [APLICADO] (repo `klecio-lab/chico-rei-desafio`, `main`, push feito)
- **Contexto:** edital pede **um** link de repositório + README único cobrindo back e front.
- **Decisão:** monorepo com `backend/`, `frontend/`, `README.md` e `.gitignore` na raiz; regras Laravel em `backend/AGENTS.md` (este template).
- **Alternativas:** 2 repos — descartado (2 links, 2 READMEs, histórico fragmentado; faria sentido com deploys/equipes independentes).
- **Consequências:** 1 clone avalia tudo; chats de IA separados por pasta (back/front) com o contrato `tecnica/api.md` como elo.
- **Refs:** edital (entrega: repositório) · AGENTS.md `§10`.
