# BDR-013 — CI no push

- **Status:** [PROPOSTO] · **Prioridade:** P1 · **Chat:** back
- **Contexto:** sem CI, cada push arrisca quebrar a suite verde sem ninguém notar antes do avaliador.
- **Decisão:** `.github/workflows/tests.yml` com service MySQL 8 + setup PHP + `composer install` + `php artisan test` (env de testing, DB `testing`); badge no README.
- **Consequências:** ~30–45 min de trabalho, protege os 20% de boas práticas; avaliador vê maturidade.
- **Refs:** AGENTS.md `§9`, `§10`.
