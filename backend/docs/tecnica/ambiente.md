# Ambiente (reprodução em máquina limpa — base do item 2 do README)

Pré-requisitos: Docker + portas livres. O projeto usa portas não-padrão (BDR-002) porque host e Fiscali ocupam as padrões:

| Serviço | Porta host | Container |
|---|---|---|
| API | `8006` | 80 |
| Vite (front) | `5174` | 5173 |
| MySQL | `3307` | 3306 |

## Back-end (a partir de `backend/`)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed        # tabelas + ~20 produtos + usuário demo
./vendor/bin/sail artisan storage:link          # /storage/* para uploads (BDR-010)
./vendor/bin/sail artisan passport:keys         # chaves NÃO vão ao git (1ª vez na máquina)
./vendor/bin/sail artisan passport:client --personal --provider=users --name=chico-rei-app
./vendor/bin/sail artisan test                  # suite verde
```

Worker dos logs (fila `database` em dev): a request nunca espera o log, mas sem
worker ele fica na fila e o detalhe mostra "Sem movimentações". Em produção é um
worker dedicado; em dev/teste:

```bash
./vendor/bin/sail artisan queue:work            # processa LogProductActivity
```

## Busca ES (opcional, BDR-012)

Desligada por padrão (`ELASTIC_ENABLED=false`). Para ligar em dev:

```bash
./vendor/bin/sail up -d elasticsearch           # sobe o serviço (8.18, 512 MB)
# ELASTIC_ENABLED=true no .env
./vendor/bin/sail artisan products:reindex      # indexa o catálogo
```

O `q` passa a usar full-text com fallback para `LIKE`; CUD sincroniza o índice
(best-effort, nunca quebra a escrita). CI e máquina limpa não usam ES.


- Demo: `demo@chicorei.test` / `password` (ver `tecnica/banco-de-dados.md`).
- DB de teste: criar `testing` uma vez (ver `tecnica/testes.md`).
- WSL + PowerShell: rodar tudo via `wsl bash -c '...'` com `;` (AGENTS.md `§1`).

## Front-end (a partir de `frontend/`)

```bash
npm install
npm run dev
```

`VITE_API_URL=http://localhost:8006` (`.env` do front; exemplo em `.env.example`).
