# BDR-002 — MySQL via Sail (porta 3307)

- **Status:** [APLICADO] (containers `backend-laravel.test-1` + `backend-mysql-1`, app `:8006`, Vite `:5174`)
- **Contexto:** edital **exige MySQL** (template sugeria pgsql); host tem MySQL na 3306 e serviço na 80.
- **Decisão:** `laravel.build?with=mysql` + `FORWARD_DB_PORT=3307`, `APP_PORT=8006`, `VITE_PORT=5174`.
- **Alternativas:** usar a 3306 do host — descartado (banco do avaliador não pode depender da máquina dele; `migrate --seed` tem que funcionar limpo).
- **Consequências:** diferencial "Docker" atendido de graça; `.env` com portas não-padrão documentadas.
- **Refs:** edital (não negociável: MySQL) · AGENTS.md `§2`.
