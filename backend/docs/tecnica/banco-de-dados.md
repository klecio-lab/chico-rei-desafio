# Banco de dados

MySQL 8.4 (Sail, `FORWARD_DB_PORT=3307` — a 3306 do host está ocupada). Migrations com prefixo de data após a última existente.

## `products`

| Campo | Tipo | Notas |
|---|---|---|
| `id` | bigint unsigned | PK |
| `name` | varchar(255) | index (busca `q`) |
| `description` | text, nullable | |
| `image` | varchar(2048), nullable | URL (BDR-006) |
| `price` | decimal(10,2) | index (filtro/ordenação) |
| `category` | varchar(100) | index (filtro) |
| `stock` | unsigned int, default 0 | index (filtro `in_stock`) |
| `created_at`/`updated_at` | timestamps | ordenação default `-created_at` |

## `product_logs`

Append-only (`created_at`, sem `updated_at`). `product_id` e `user_id` nullable com `nullOnDelete` — o rastro sobrevive ao delete. Índice `(product_id, created_at)`. Ver `regras-de-negocio/logs.md`.

## `oauth_*` (Passport)

Tabelas do `install:api --passport` (clients/tokens com UUID). Client pessoal `chico-rei-app` criado via `passport:client --personal`. Chaves **fora** do git (`passport:keys` no deploy).

## Seeds

`ProductSeeder` (~20 produtos com a cara da loja: camisetas nos temas reais do site — musica, literatura, filmes, geek, brasilidade, series, games, humor — mais moletons e básicos; preços BRL realistas; estoques variados incluindo zerados para testar `in_stock=false`; imagens via placeholder estável) + `UserSeeder` demo (`demo@chicorei.test` / `password`). `migrate --seed` em máquina limpa.
