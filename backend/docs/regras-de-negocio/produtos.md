# Produtos

## Campos

| Campo | Tipo | Regra |
|---|---|---|
| `id` | bigint | PK |
| `name` | string(255) | obrigatório, `min:3`, `max:255` |
| `description` | text | opcional |
| `image` | string(2048) | opcional, URL válida (ver BDR-006) |
| `price` | decimal(10,2) | obrigatório, `numeric`, `min:0` |
| `category` | string(100) | obrigatória |
| `stock` | unsigned int | obrigatório, `integer`, `min:0` |
| `created_at`/`updated_at` | timestamps | automáticos |

## CRUD

- Criar/atualizar/excluir: só `auth:api`. Escrita via `Product::create($validated)` / `$product->update($validated)` (`§0.4`).
- `DELETE` é físico (sem soft delete): catálogo de desafio, sem histórico coletivo.
- Inexistente → `404`. Resposta sempre envelope `response()->json` (`§0.3`).

## Listagem `GET /api/products` (pública)

| Param | Regra |
|---|---|
| `q` | busca parcial em `name`, case-insensitive (`LIKE %q%`) |
| `category` | igualdade exata |
| `min_price` / `max_price` | `numeric`, `min:0`; `max >= min` |
| `in_stock` | `boolean`; `true` = `stock > 0` |
| `sort` | `name\|price\|stock\|created_at`, prefixo `-` inverte; default `-created_at` |
| `per_page` | `integer`, 1–100, default 15 |

## Detalhe `GET /api/products/{id}` (público)

Produto completo + `logs` recentes (últimas movimentações de `product_logs`).
