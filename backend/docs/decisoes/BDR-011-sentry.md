# BDR-011 — Sentry (back-end)

- **Status:** [APLICADO] · **Prioridade:** P2 · **Chat:** back (`sentry/sentry-laravel`, DSN vazio; `sentry:test` sem DSN avisa e não envia; suite verde com SDK inativo)
- **Contexto:** diferencial "monitoramento" do edital.
- **Decisão:** `sentry/sentry-laravel`; `SENTRY_DSN` em `.env.example` (**vazio**); sem DSN o SDK fica inativo — máquina limpa segura e avaliador sem conta não quebra; sample rates conservadores; validar com evento de teste.
- **Front:** fora (documentado aqui para não gerar dúvida).
- **Refs:** edital (diferencial monitoramento).
