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
./vendor/bin/sail artisan passport:keys         # chaves NÃO vão ao git (1ª vez na máquina)
./vendor/bin/sail artisan passport:client --personal --name=chico-rei-app
./vendor/bin/sail artisan test                  # suite verde
```

- Demo: `demo@chicorei.test` / `password` (ver `tecnica/banco-de-dados.md`).
- DB de teste: criar `testing` uma vez (ver `tecnica/testes.md`).
- WSL + PowerShell: rodar tudo via `wsl bash -c '...'` com `;` (AGENTS.md `§1`).

## Front-end (a partir de `frontend/`)

```bash
npm install
npm run dev
```

`VITE_API_URL=http://localhost:8006` (`.env` do front; exemplo em `.env.example`).
