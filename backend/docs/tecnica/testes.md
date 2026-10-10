# Testes (plano deste projeto — padrão Fiscali, `§9`)

## `phpunit.xml`

`APP_ENV=testing`, `BCRYPT_ROUNDS=4`, `CACHE_STORE=array`, `DB_DATABASE=testing`,
`DB_URL=""`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`.
Banco `testing` no MySQL do Sail (criar uma vez: `sail exec mysql mysql -u sail -ppassword -e 'CREATE DATABASE testing;'` — sem este DB a suite falha). Sail no ar.

`sync` + `array` = `LogProductActivity` roda inline: o teste asserta a linha de log sem worker.

## `tests/TestCase.php` (helpers obrigatórios)

- `authUser()`: `User::factory()->create()` + `Passport::actingAs($user)` — todo teste de API autenticada começa aqui.
- Sem helper de HTTP externo: o projeto **não chama API externa** (BDR-007), então `Http::fake` é dispensado.
- Throttles: se limiters atrapalharem, `Config::set(...)` + teste dedicado de 429.

## Matriz mínima (nada entra sem estes verdes)

| Arquivo | Casos |
|---|---|
| `Feature/AuthTest.php` | login `200` + token; credencial ruim `422`; `/me` sem token `401`; logout revoga (token seguinte dá `401`) |
| `Feature/ProductTest.php` | `index/show` públicos; CUD sem token `401`; `store/update` inválido `422` (por FormRequest); CRUD feliz (`201/200/204`); filtros `q/category/min/max_price/in_stock` + `sort` + `per_page`; `show` inexistente `404` |
| `Feature/ProductLogTest.php` | criar/editar/excluir gera 1 linha em `product_logs` com `action/user_id/changes`; delete mantém o log (`product_id` null) |
| `Unit/*` | regras que mereçam (ex.: ordenação permitida, normalização de filtros) |

## Execução

`--filter=` focado no dia a dia; suite cheia em segundos. Cada bug ganha teste de regressão com o payload que quebrou.
