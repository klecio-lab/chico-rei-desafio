# BDR-005 — Logs via Observer + Job async

- **Status:** [PROPOSTO]
- **Contexto:** edital exige logs de criação/atualização/exclusão **e** dá extra para "job assíncrono".
- **Decisão:** `ProductObserver` (created/updated/deleted) despacha `LogProductActivity` (`tries = 1`, nunca lança); tabela `product_logs` append-only.
- **Alternativas:** escrever o log inline no controller/service — descartado (acopla, atrasa a request, perde o diferencial).
- **Consequências:** request nunca espera o log; com `QUEUE_CONNECTION=database` em prod é async real; em `sync` (local/testes) roda inline com resultado idêntico.
- **Refs:** edital (obrigatório: logs · extra: job async) · AGENTS.md `§6` · `regras-de-negocio/logs.md`.
