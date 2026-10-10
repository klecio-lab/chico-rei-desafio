# API — contrato (fonte da verdade para o front)

Base: `http://localhost:8006`. Tudo em JSON. Auth: `Authorization: Bearer <token>` (Passport).

## Envelope (único formato aceito — `§0.3` do AGENTS.md)

```php
return response()->json(['message' => '...', 'data' => [...]], 200);
```

- Sucesso com lista: `{ "data": [...], "meta": { "current_page", "last_page", "per_page", "total" } }`
- Criação: `201` · Erro de validação/regra: `422` `{ "message" }` PT-BR
- Sem token: `401` · Sem acesso: `403` · Inexistente: `404`

## Endpoints

| Método | Rota | Auth | Descrição |
|---|---|---|---|
| `POST` | `/api/login` | — | `{email, password}` → `{user, token, expires_at}` |
| `POST` | `/api/logout` | `auth:api` | revoga token atual → `200` `{ "message" }` |
| `GET` | `/api/me` | `auth:api` | usuário do token |
| `GET` | `/api/products` | — | lista (`q category min_price max_price in_stock sort per_page`) → `200` `{ "data": [...], "meta": { "current_page", "last_page", "per_page", "total" } }` |
| `GET` | `/api/products/{id}` | — | detalhe → `200` `{ "data": { campos..., "logs": [últimos 10 de product_logs] } }`; inexistente → `404` `{ "message": "Produto não encontrado." }` |
| `POST` | `/api/products` | `auth:api` | cria → `201` |
| `PUT` | `/api/products/{id}` | `auth:api` | atualiza total → `200` |
| `DELETE` | `/api/products/{id}` | `auth:api` | exclui → `204` |

Rate limit: `login` 10/min por IP; CUD 30/min por usuário; leitura 120/min por usuário (limiters nomeados, `§4`).

Notas do catálogo: `price` serializa **string** (decimal do MySQL) — o front aplica
`Number()`; `sort` aceita `name|price|stock|created_at` com prefixo `-` (default
`-created_at`); `per_page` default 15 (1–100); `in_stock=false` = zerados.
