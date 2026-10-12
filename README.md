# chico-rei-desafio

[![tests](https://github.com/klecio-lab/chico-rei-desafio/actions/workflows/tests.yml/badge.svg)](https://github.com/klecio-lab/chico-rei-desafio/actions/workflows/tests.yml)

Desafio técnico Full-Stack (CHICO REI) — API em Laravel + SPA em Vue.js que a consome, do login ao CRUD de produtos com logs assíncronos.

## Estrutura

```
chico-rei-desafio/
├── backend/    # API Laravel (MySQL + Passport + Sail) — docs em backend/docs
├── frontend/   # SPA Vue 3 + Vite (consome a API)
└── README.md   # este arquivo
```

## 1. Sobre o projeto

Catálogo de produtos com autenticação: leitura pública (listagem com busca,
filtros, ordenação e paginação + detalhe) e gestão protegida por token
(criar/editar/excluir). Toda escrita gera uma linha de auditoria em
`product_logs` via Observer + Job assíncrono. O front consome a API real —
contrato congelado em `backend/docs/tecnica/api.md`.

## 2. Tecnologias

| Camada | Stack |
|---|---|
| Back-end | Laravel 13.35 (PHP 8.5) + MySQL 8.4 via Sail/Docker + Passport (OAuth2) + PHPUnit |
| Front-end | Vue 3.5 + Vite 8 (Node 22) + Vue Router + Pinia + Axios + Tailwind CSS v4 |

## 3. Como rodar

Pré-requisitos: Docker + portas livres (`8006` API, `5174` front, `3307` MySQL).

| Variável | Onde (ver `.env.example`) | Valor local |
|---|---|---|
| `APP_PORT` / `VITE_PORT` / `FORWARD_DB_PORT` | `backend/.env` | `8006` / `5174` / `3307` |
| `VITE_API_URL` | `frontend/.env` | `http://localhost:8006` |
| chaves Passport | geradas via `passport:keys` (fora do git) | — |

```bash
# Back-end (a partir de backend/)
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed   # tabelas + 20 produtos + usuário demo
./vendor/bin/sail artisan passport:keys    # chaves NÃO vão ao git (1ª vez na máquina)
./vendor/bin/sail artisan passport:client --personal --provider=users --name=chico-rei-app
./vendor/bin/sail artisan test             # suite verde (31 testes)

# Worker dos logs (fila database): sem ele, o detalhe mostra
# "Sem movimentações" mesmo após escrever — nos testes é inline.
./vendor/bin/sail artisan queue:work

# Front-end (a partir de frontend/)
npm install
npm run dev                                # http://localhost:5174
```

Login demo: `demo@chicorei.test` / `password`. Banco de teste (`testing`) é
criado automaticamente pelo Sail; detalhes em `backend/docs/tecnica/ambiente.md`.

```bash
# Importação do catálogo real (diferencial, BDR-009 — a partir de backend/)
./vendor/bin/sail artisan products:import --tema=geek --limit=20  # upsert por source_url
./vendor/bin/sail artisan products:import --tema=geek --dry-run   # só mostra, sem persistir
```

Regra: cada execução lê a listagem do tema no site (1 request, throttle 1 req/s)
e faz `upsert` pelo link do detalhe (`source_url` unique) — rodar 2× não duplica;
reimportação atualiza só `price`/`image` (nunca `stock`/`category`, que são gestão
local); `--dry-run` não persiste; `IMPORT_ENABLED=false` desliga.

## 4. Funcionalidades (API)

| Método | Rota | Auth | Descrição |
|---|---|---|---|
| `POST` | `/api/login` | — | `{email, password}` → `{user, token, expires_at}` |
| `POST` | `/api/logout` | token | revoga o token atual |
| `GET` | `/api/me` | token | usuário do token |
| `GET` | `/api/products` | — | lista (`q category min_price max_price in_stock sort per_page`) + `meta` |
| `GET` | `/api/products/{id}` | — | detalhe + últimos 10 `logs` |
| `POST` | `/api/products` | token | cria → `201` |
| `PUT` | `/api/products/{id}` | token | atualização total → `200` |
| `DELETE` | `/api/products/{id}` | token | exclui → `204` |

Toda resposta é `response()->json` com envelope `{ message?, data?, meta? }`;
erros em PT-BR (`422` validação, `401` sem token, `404` inexistente).
`price` serializa string (decimal do MySQL) — o front aplica `Number()`.

## 5. Decisões técnicas (trade-offs)

| Decisão | Por quê | Custo |
|---|---|---|
| Passport em vez de Sanctum | padrão do autor para API (OAuth2) | 1 comando + 1 trait |
| MySQL via Sail, porta 3307 | edital exige MySQL; 3306 do host ocupada | portas não-padrão documentadas |
| Envelope `response()->json` único | 1 formato para o front tratar | sem API Resources (entrariam com mais tempo) |
| FormRequest em tudo | exigência do edital | 1 classe por operação |
| `image` como URL, sem upload | prazo de 3 dias | upload real fica para depois |
| Logs via Observer + Job async | mata o obrigatório + o diferencial de uma vez | worker necessário em dev |
| Sem importação/scraping | diferencial, não obrigatório; prioriza nota | seeders com dados próprios |
| Monorepo `backend/` + `frontend/` | 1 link, 1 clone, 1 README | chats de IA separados por pasta |

Rastro completo: `backend/docs/decisoes/` (BDR-001 a BDR-008) + regras em
`backend/docs/regras-de-negocio/` + `backend/docs/tecnica/`.

## 6. Melhorias futuras (com mais tempo)

- Importação externa idempotente (`products:import` por `name+category`).
- Upload real de imagem (`storage/` público + validação de arquivo).
- API Resources/DTOs, scopes Passport por recurso, refresh-token no front.
- CI rodando a suite no push.
- Sentry/Elasticsearch dispensados: log + `LIKE` com índice bastam nesta escala.

## Status

Entrega completa: 4 fatias fechadas nas duas pontas, 8/8 BDRs aplicadas, suite com 31 testes verdes, verificado em máquina limpa. Declarado como não-feito: importação externa, Sentry/Elasticsearch (ver `backend/docs/visao/fora-do-escopo.md`).
