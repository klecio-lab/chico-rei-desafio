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
| `POST` | `/api/logout` | `auth:api` | revoga token atual → `204` |
| `GET` | `/api/me` | `auth:api` | usuário do token |
| `GET` | `/api/products` | — | lista (`q category min_price max_price in_stock sort per_page`) |
| `GET` | `/api/products/{id}` | — | detalhe + `logs` recentes |
| `POST` | `/api/products` | `auth:api` | cria → `201` |
| `PUT` | `/api/products/{id}` | `auth:api` | atualiza total → `200` |
| `DELETE` | `/api/products/{id}` | `auth:api` | exclui → `204` |

Rate limit: `login` 10/min por IP; CUD 30/min por usuário; leitura 120/min por usuário (limiters nomeados, `§4`).
