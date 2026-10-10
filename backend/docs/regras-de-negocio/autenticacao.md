# Autenticação e acesso

## Regras

1. Login com `email` + `password` (nativa do Laravel: `Auth::attempt`).
2. Sucesso devolve **personal access token** Passport (`$user->createToken('api')->accessToken`) + expiração.
3. Logout revoga o token atual (`$request->user()->token()->revoke()`).
4. `GET /api/me` devolve o usuário do token.
5. **Só autenticado** (`auth:api`) pode `POST`/`PUT`/`PATCH`/`DELETE` produtos.
6. **Público** (sem token): `GET /api/products` e `GET /api/products/{id}`.
7. Sem token em rota protegida → `401` JSON (nunca redirect). Token válido sem permissão → `403`.

## Validação (FormRequest — `§0.2` do AGENTS.md)

- `LoginRequest`: `email` required/email, `password` required/string. Credencial inválida → `422` com mensagem genérica (nunca dizer se o e-mail existe).
