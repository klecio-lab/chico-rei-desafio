# Logs de produtos

## Regras

1. Toda criação, atualização e exclusão de produto gera **1 linha** em `product_logs`.
2. Linha contém: `product_id` (nullable — delete mantém o rastro), `action` (`created|updated|deleted`), `user_id` (ator), `changes` (JSON: antes/depois; no delete, snapshot final), `created_at`.
3. Escrita **assíncrona**: o Observer (`ProductObserver`) despacha `LogProductActivity` para a fila; a request nunca espera o log (mata o obrigatório + o diferencial "job assíncrono" de uma vez).
4. Falha no log **nunca quebra** o CRUD: job com `tries = 1` + `try/catch` + `Log::warning` (`§6` do AGENTS.md).
5. Em `QUEUE_CONNECTION=sync` local/testes, o job roda inline (comportamento idêntico).

## Tabela `product_logs`

| Campo | Tipo | Notas |
|---|---|---|
| `id` | bigint | PK |
| `product_id` | FK → products, nullable, `nullOnDelete` | delete preserva o log |
| `action` | string(20) | `created\|updated\|deleted` |
| `user_id` | FK → users, `nullOnDelete` | quem fez |
| `changes` | json, nullable | diff antes/depois |
| `created_at` | timestamp | quando (sem `updated_at`: append-only) |
