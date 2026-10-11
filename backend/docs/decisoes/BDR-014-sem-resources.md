# BDR-014 — Não refatorar para Resources/scopes/refresh/cursor

- **Status:** [APLICADO] (decisão de escopo)
- **Contexto:** com prazo até terça, todo refator sem nota é risco.
- **Decisão:** manter envelope manual + token simples + `paginate`; API Resources/DTOs, scopes por recurso, refresh-token e cursor ficam pós-desafio.
- **Motivo:** padronização já atende os 20% de boas práticas; o ganho seria estético.
- **Refs:** AGENTS.md `§4` · `visao/fora-do-escopo.md`.
