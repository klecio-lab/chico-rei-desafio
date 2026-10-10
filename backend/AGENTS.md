# AGENTS.md — Template Laravel genérico

> Como usar: copie este arquivo como `AGENTS.md` na raiz de qualquer projeto
> Laravel novo e preencha os `<placeholders>`. O núcleo (§§0–11) vale para
> **qualquer** projeto — não cita nenhum domínio.
>
> O **Apêndice A** traz exemplos reais de onde cada regra nasceu (projeto
> Fiscali). Leia como inspiração e **apague o apêndice** se ele não fizer
> sentido para o seu domínio — o template continua completo sem ele.

## 0. Regras inegociáveis (evitam dor de cabeça)

1. **Nunca `env()` fora de `config/*.php`** — crie um arquivo de config por
   grupo (`config/pedidos.php`, `config/integracao.php`) e leia com
   `config('grupo.chave')`. `env()` fora do config retorna `null` com
   `config:cache` e espalha string mágica pelo código.
2. **Toda validação em FormRequest, nunca `$request->validate()` no
   controller** — um FormRequest por operação em `app/Http/Requests/Api/`
   (`StoreOrderRequest`, `UpdateOrderRequest`), com `rules()` + `messages()`
   em PT-BR.
3. **Toda resposta é `return response()->json([...], $status)`** com envelope
   `{ message?, data?, meta? }` — ver seção 4.
4. **Performance: prefira `create` / `update` e irmãos** (`updateOrCreate`,
   `firstOrCreate`, `upsert`, `createMany`) ao `new Model()` + atribuições +
   `save()` em loop, ou query crua quando o model resolve — 1 query por
   operação, mass assignment auditado pelo `$fillable`, casts/eventos
   consistentes.

## 1. Ambiente

- Se o dev for Windows + WSL2 com agente em PowerShell 5.1 e CWD em UNC
  (`\\wsl.localhost\...`): `sail`/`artisan`/`npm` **falham se invocados direto**.
  Padrão que funciona:
  - `workdir: C:\temp_opencode` + `wsl bash -c '...'` com **aspas simples
    externas**, `;` como separador (nunca `&&`), aspas simples **dobradas**
    (`''`) para aninhar.
  - Ex.: `wsl bash -c 'cd /home/<voce>/<projeto>; ./vendor/bin/sail artisan test 2>&1 | tail -5'`
  - O que quebra: `&&`, `\"`, parênteses fora do esquema de aspas, paths UNC
    como workdir.
  - Prefira `Read`/`Write`/`Edit`/`Grep`/`Glob` e reserve o `bash` para
    **executar** (testes, artisan, sail, psql).
- Se o projeto **não** for WSL, apague esta seção e use `sail`/`artisan` direto.

## 2. Stack e comandos (tudo via Sail)

- Stack sugerida (API-only): **Laravel (PHP ^8.3+) + PostgreSQL via Sail**,
  **Passport (OAuth2)** para auth, Pint, PHPUnit. Octane/FrankenPHP só se precisar.
  Sanctum só para casos simples (SPA própria sem OAuth/scopes) — o padrão aqui
  é Passport.

### 2.1 Criar o projeto do zero (API + Passport + Sail)

```bash
# 1. Novo projeto já com Sail + pgsql
curl -s "https://laravel.build/<app>?with=pgsql" | bash; cd <app>
# …ou num projeto existente:
composer require laravel/sail --dev
php artisan sail:install          # escolha pgsql
./vendor/bin/sail up -d

# 2. API + Passport (OAuth2) — um comando resolve
php artisan install:api --passport
# → publica e RODA as migrations do Passport (clients/tokens) e gera as
#   encryption keys. Pergunta sobre UUID no Client (aceite o default).

# 3. Amarre o User ao Passport (obrigatório após o passo 2)
# app/Models/User.php:
#   use Laravel\Passport\HasApiTokens, Laravel\Passport\Contracts\OAuthenticatable;
#   class User extends Authenticatable implements OAuthenticatable { use HasApiTokens, ...; }

# 4. Base do template
./vendor/bin/sail artisan migrate --force
```

- Deploy/primeira vez no servidor: `php artisan passport:keys` (chaves NÃO vão
  para o git) + `php artisan passport:client --personal` (personal access).
  Limpeza: agende `passport:purge` no scheduler (`withoutOverlapping()`).
- WSL: rode tudo acima pelo padrão da §1
  (`wsl bash -c 'cd /home/<voce>/<projeto>; ...'` com `;`).
- Comandos canônicos (adapte o path):
  - `./vendor/bin/sail up -d`
  - `./vendor/bin/sail artisan test --filter=NomeDoTeste`
  - `./vendor/bin/sail artisan migrate --force`
  - `./vendor/bin/sail exec pgsql psql -U sail -d <banco> -c 'SELECT 1'`
- Documente aqui a `FORWARD_DB_PORT` do projeto e o porquê (ex.: `5433`
  porque a `5432` do host está ocupada). Sem isso o `sail up -d` falha (bind).
- `.env.example` é documentação: toda chave nova em `config/*.php` ganha linha
  comentada lá com default e explicação.
- **Proibido `env()` fora de `config/`**: controllers, services, jobs, models
  e tests leem via `config('grupo.chave')` (ou `Config::set` nos testes).
  Verificação: `env\(` só pode aparecer em `config/*.php`.

## 3. Estrutura e convenções de código

```
app/
├── Http/Controllers/Api/   # um controller por recurso, só HTTP (sem regra)
├── Http/Requests/Api/      # FormRequest por operação (regras + mensagens)
├── Models/                 # fillable + casts() + relations; eventos só p/ invariantes
├── Policies/               # UMA policy por model com dono (view/update/delete)
├── Jobs/                   # um job = uma unidade retryável
├── Services/               # regra de negócio; orquestrador fino + DI no construtor
├── Console/Commands/       # commands idempotentes (--dry-run quando destrutivo)
└── Providers/              # bindings de interfaces, limiters, nada de new espalhado
config/*.php                # todo número mágico vem de env() com default funcional
routes/api.php              # API-only: grupo auth:api + throttle; rotas fixas antes das {id}
routes/web.php              # só health (/ → ok), nada de regra aqui
```

- **Controllers finos + FormRequest obrigatório**: o controller recebe o
  FormRequest já validado, chama Service/Job e retorna envelope JSON.
  Proibido `$request->validate()`, `Http::*`, SQL ou cálculo no controller.
  ```php
  public function store(StoreOrderRequest $request): JsonResponse
  {
      $order = $this->orders->create($request->user()->id, $request->validated());
      return response()->json(['message' => '...', 'data' => ['id' => $order->id]], 201);
  }
  ```
- **Services orquestram, detalhes executam**: Service recebe dependências no
  construtor (DI), decide o fluxo e persiste em `DB::transaction`. Detalhe de
  formato/provedor mora em classe dedicada com interface + factory. Trocar
  provedor = implementar a interface + trocar `config/`, nunca `if` espalhado.
- **Interfaces + binding no provider**: todo serviço externo (pagamento,
  geocoder, gateway, fetcher) tem `XInterface` + implementação default +
  `bind()` com `match (config(...))`. Isso dá kill-switch por env e mock limpo.
- **Config conta história**: cada `config/*.php` tem comentário explicando o
  porquê (limite da API, fila dedicada, retenção). Env sem default funcional
  é bug — o app deve subir com `.env.example` puro em dev.

## 4. API JSON (contrato)

- **Sempre `return response()->json([...], $status)`** — nunca `return [...]`,
  nunca `response()` sem `->json`. Padrão:
  ```php
  return response()->json([
      'message' => 'Pedido recebido para processamento. Você será notificado ao concluir.',
      'data' => [
          'id' => $order->id,
          'status' => $order->status,
          'total' => $order->total,
      ],
  ], 202);
  ```
- Envelope — só mude atualizando o front e `docs/tecnica/api.md`:
  - Sucesso: `{ "data": ..., "meta": {...} }`
  - Criação: `201` (síncrono) ou `202` (async com `id` para acompanhar)
  - Erro de regra/validação: `422` com `{ "message": "..." }` em PT-BR
  - Sem token → `401` JSON (não redirect). Sem dono → `403`. Fora do escopo
    → `404` (não vazar existência).
- `bootstrap/app.php` (API-only, sem views): `Authenticate::redirectUsing(fn () => null)` +
  `shouldRenderJsonWhen(fn ($req) => $req->is('api/*') || $req->expectsJson())`.
  `routes/web.php` fica só com health (`/` → `ok`); todo o resto mora em
  `routes/api.php`, stateless, sem sessão.
- Auth: **Passport (OAuth2) como padrão** — `User` com `HasApiTokens`,
  `passport:install` / chaves versionadas, login emitindo
  `$user->createToken('api', $scopes)->accessToken` com expiração curta +
  refresh token; logout revogando (`$request->user()->token()->revoke()`).
  Middleware `auth:api` nas rotas protegidas (não `auth:sanctum`). Scopes por
  recurso quando houver níveis (`orders:read`, `orders:write`). Sanctum é
  exceção, não regra — só se o projeto for SPA simples sem OAuth.
- Rate limit com **limiters nomeados** (um bucket por endpoint): ex.
  `login`/`register`/`password` = 10/min por IP; `POST` sensível = 30/min por
  usuário; resto = 120/min por usuário. Defina no provider, use `throttle:login`.
- Listas paginadas (`paginate(15)` pessoal, `paginate(20)` buscas),
  `per_page` validado (1–100), mais recentes primeiro por default.
- Agregados (`min/max/avg`) do Postgres saem **string** — documente que o
  front aplica `Number()`. Valor calculado vai em coluna dedicada, nunca
  calculado no controller.
- **Rotas fixas antes das com `{id}`** (`/suggest` antes de `/{id}`), senão a
  fixa cai no `show`.

## 5. Banco e migrations

- Migrations em `database/migrations` com **prefixo de data após a última
  existente** (ordem cronológica importa no deploy).
- Trecho específico de um driver (extensão, índice GIN, SQL cru) **sempre
  guardado**: `if (DB::getDriverName() !== 'pgsql') return;` + **fallback em
  PHP puro** quando o recurso for essencial.
- Tipos: dinheiro `decimal(10,2)`, quantidade `decimal(10,3)`, preço unitário
  calculado `decimal(10,4)`. Models com `casts()` para `decimal:N` + `datetime`.
- **Escrita via `create`/`update` e irmãos**: `Model::create($validated)`,
  `$model->update($data)`, `updateOrCreate`, `firstOrCreate`, `upsert`,
  `createMany` — nunca `new` + N atribuições + `save()` em loop. Lotes usam
  `upsert` ou `DB::transaction(fn () => ...)` com `createMany`.
- Idempotência: toda ingestão externa tem **chave natural única** (unique) +
  checagem prévia antes do I/O. Duplicidade → `422` amigável, nunca `500`.
- Defina a política de delete no início e documente: **apagar de verdade**
  (`cascade`) ou **anonimizar** (`nullOnDelete` + evento no model zerando
  `user_id`)? Histórico coletivo, se existir, sobrevive ao delete individual.
- Agregados globais (se houver dados colaborativos): exija **quórum mínimo**
  de contribuintes, nunca exponha quem é quem, e marque dado fora da janela
  como `stale: true`.

## 6. Filas, jobs e scheduler

- `QUEUE_CONNECTION=sync` + `CACHE_STORE=array` nos testes: **jobs rodam
  inline**. Teste que dispara job com HTTP externo precisa de `Http::fake`
  cobrindo o host (senão faz HTTP real).
- Job **nunca lança para fora**: `try/catch` com caminho permanente (`failed` +
  notificação) vs reagendamento com backoff. Se o retry for manual via tabela
  (`status/attempts/max_attempts/next_attempt_at/last_error`), use `tries = 1`
  e reagende com `delay()`.
- Separe o que é **retentável** (tenta de novo) do que é **quarentena**
  (nunca vai dar certo → status final sem retry; `retry` nela dá `422`).
- `routes/console.php` agenda limpezas e vigias com `->withoutOverlapping()`:
  telemetria quente por N dias depois poda, arquivos com `retention_days`,
  vigia destrutiva em `dry-run` no log (aplicar continua manual `--apply`).
- E-mail/notificação **nunca quebra o fluxo principal** — `try/catch` +
  `Log::warning`.

## 7. Integrações externas

- Timeout, User-Agent próprio e throttle por config (ex.: `1100` ms em prod,
  `0` nos testes via `Config::set`). Um default funcional sempre.
- Cacheie o que é estável. Nunca logue segredo/URL crua — logue **hash**.
  Trunque `last_error` (`mb_substr(..., 2000)`).
- Recurso pesado/futuro (browser, worker dedicado) ganha **seam desabilitado
  por padrão** (interface + `Disabled*` com erro permanente) + fila dedicada.
  Nunca trave o fluxo principal esperando por ele.
- Falha em passo acessório (arquivar fonte, notificar) vira `warning`, nunca
  quebra a operação principal.

## 8. Privacidade e segurança (LGPD por default)

- Dado sensível (GPS, PII) é **efêmero** por default (parâmetro, nunca coluna).
  Persistir exige opt-in explícito (ex.: `location_consent_at`); opt-out apaga
  tudo (`update` + `destroy`).
- `forgot-password` responde **sempre igual** exista a conta ou não. Link com
  `FRONTEND_URL/...?token&email`, expiração curta (ex.: 60 min).
- Policies em **todas** as rotas com `{id}` + teste `403` com segundo usuário.
- Agregados nunca revelam indivíduos.

## 9. Testes (padrão Fiscali — obrigatório)

> Por que obrigatório: esse formato é o que dá integridade e deixa o agent
> guiado — todo teste segue o mesmo esqueleto, então falha sempre mostra onde
> quebrou (`last_error` / mensagem de asserção) em vez de `500` mudo.

- `phpunit.xml` fixo: `APP_ENV=testing`, `BCRYPT_ROUNDS=4`,
  `CACHE_STORE=array`, `DB_DATABASE=testing`, `DB_URL=""`,
  `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`,
  integrações externas `*_ENABLED=false`. Banco de teste dedicado —
  **Sail precisa estar no ar**. `sync` + `array` = jobs rodam inline.
- Layout fixo:
  ```
  tests/
  ├── TestCase.php        # helpers base (ver abaixo) — todo teste herda daqui
  ├── Feature/            # um arquivo por recurso/fluxo (Auth*, *Ownership*,
  │                       # *RetryQueue*, *Delete*, *Import*)
  ├── Unit/               # um arquivo por classe de regra (Normalizer*,
  │                       # Parser*, Classifier*, Service*)
  └── Fixtures/           # payloads reais anonimizados, um por formato/caso
  ```
  Proibido blob gigante inline no teste — vai para `Fixtures/`.
- `tests/TestCase.php` com 3 helpers padrão (copie e adapte os nomes):
  ```php
  // 1. Usuário autenticado via Passport em todos os testes de API
  protected function authUser(array $attrs = []): User
  {
      $user = User::factory()->create($attrs);
      Passport::actingAs($user, ['*']);
      return $user;
  }

  // 2. Fluxo async padrão: submete (201/202) → roda o Job inline →
  //    devolve o registro em estado terminal, falhando com a mensagem real
  protected function runAsyncJob(string $url, string $jobClass): mixed
  {
      $id = $this->postJson('/api/orders', ['url' => $url])
          ->assertStatus(202)->json('data.id');
      (new $jobClass($id))->handle(app(OrderIngestionService::class));
      $record = OrderImport::findOrFail($id);
      $this->assertSame('completed', $record->status, 'Falhou: '.$record->last_error);
      return $record->fresh('order');
  }

  // 3. Fake de todo HTTP externo — chamar no início de cada teste com I/O
  protected function fakeExternals(): void
  {
      Http::fake(['*provedor-externo*' => Http::response([], 200)]);
      Config::set('integracao.throttle_ms', 0);
  }
  ```
- Regras duras:
  - Todo teste de API começa com `authUser()`; sem token espera-se `401`.
  - Toda rota com `{id}` tem teste com **segundo usuário** esperando `403`.
  - Todo FormRequest tem teste de `422` (campo ausente + inválido).
  - Toda resposta asserta **envelope + status** (`message`/`data`/`meta`).
  - Todo teste com Job/HTTP chama `fakeExternals()` — sem isso o teste faz
    HTTP real (bug clássico).
  - Throttles sempre desligados via `Config::set('grupo.throttle_ms', 0)`.
- Cobertura mínima antes de qualquer feature nova: auth (401), posse (403),
  validação (422), envelope, delete (cascade vs anonimiza), retry/quarentena.
- Cada bug de domínio ganha **teste de regressão** com o payload que quebrou.
- Execução: `--filter=` focado no dia a dia; suite cheia em segundos.

## 10. Estilo, git e deploy

- **Pint** antes de commitar + `.editorconfig` (LF, UTF-8, indent 4, 2 em yml).
- Commits em PT-BR, `feat:`/`fix:`/`infra:`/`docs:`/`style:` descritivo.
  **Nunca amend em commit publicado** (`pull.rebase=true`).
- **Nunca commite dumps/segredos**: `*.dump`/`*.sql`/`.env*` no `.gitignore`.
  Confira `git status` antes de `add -A`.
- Deploy: push na `main` → CI builda → redeploy; **migrations rodam no boot**
  (`MIGRATE_ON_START`). Migration com `CREATE EXTENSION` exige a extensão na
  imagem ou criação manual como superuser — documente.
- Pós-deploy com dado externo: comando artisan dedicado em vez de SQL manual.
- Docs vivas: `docs/tecnica/api.md` é **fonte da verdade do front — atualize
  ao criar/alterar endpoint**. Regra de negócio → `docs/regras-de-negocio/`;
  arquitetura/banco acompanham migrations e services.

## 11. Checklist de projeto novo

- [ ] Projeto criado pela §2.1 (`laravel.build?with=pgsql` + `install:api --passport` + `HasApiTokens`); `FORWARD_DB_PORT` documentada
- [ ] `bootstrap/app.php` com 401-JSON + `shouldRenderJsonWhen(api/*)`; `web.php` só health
- [ ] Passport como auth padrão (`auth:api` + scopes + expiração + revoke no logout)
- [ ] `phpunit.xml` de testes + `TestCase` com `authUser`/`runAsyncJob`/`fakeExternals`
- [ ] Pint + `.editorconfig` + `.gitignore` com `*.dump/*.sql/.env*`
- [ ] `docs/tecnica/api.md` + `docs/README.md` como índice
- [ ] CI + `MIGRATE_ON_START` + comando pós-deploy se houver dado externo
- [ ] Primeira policy + testes 401/403 antes da primeira feature
- [ ] Grep limpo: `env\(` só em `config/`; `->validate(` zero em controllers

---

## Apêndice A — De onde veio cada regra (exemplo Fiscali, pode apagar)

> Este apêndice é só rastreabilidade. Se o seu projeto não tem NFC-e,
> mercados ou geocoding, apague tudo abaixo sem culpa.

- **§2**: `FORWARD_DB_PORT=5433` porque a `5432` do host estava ocupada pelo
  Postgres local; containers `fiscali-laravel.test-1`, `fiscali-pgsql-1`.
- **§3**: `ReceiptController@store` fazia só pré-save síncrono (URL inválida,
  quarentena `tpAmb=2`, duplicidade por `access_key`) e devolvia `202`; o
  `ProcessReceiptImport` processava em background.
- **§4**: `POST /receipts` devolvia `{ message, data: { import_id, status,
  attempts, max_attempts, next_attempt_at } }` em `202`; `min/max` do Postgres
  saiam string e o front aplicava `Number()`; `normalized_unit_price =
  total ÷ quantidade`. Auth do Fiscali era **Sanctum** — o template adotou
  **Passport** como padrão porque o uso típico aqui é API (OAuth2/scopes);
  use Sanctum só em SPA simples.
- **§5**: `price_histories` append-only com `receipt_id/user_id`; delete de
  cupom anonimizava (NULL via evento + FK `nullOnDelete`); agregados exigiam
  `MIN_CONTRIBUTORS = 2` (legado NULL = 1 coletivo); `pg_trgm` guardado por
  `DB::getDriverName()` com fallback em `ProductSimilarityService`; ingestão
  nunca fundia EANs diferentes, prefixo 2 (balança) virava ausente, threshold
  0.6 (0.75 nomes curtos) + `unit_base` compatível.
- **§6**: retry via `receipt_imports`
  (`pending/processing/completed/failed/rejected`, backoff × UF, scheduler a
  cada 15 min); `GeocodeMarketJob` nunca lançava; e-mail via Resend com
  `try/catch`.
- **§7**: `GeocoderInterface` + Nominatim (UA próprio, throttle 1,1s, cache em
  `markets`, `GEOCODER_*` com default); `JsDynamicFetcherInterface` +
  `DisabledJsDynamicFetcher` com kill-switch `SEFAZ_BROWSER_ENABLED`; fonte
  higienizada (sem consumidor, nome por hash); telemetria com SHA-256.
- **§8**: GPS efêmero, endereço só com `location_consent_at`, opt-out apagava.
- **§9**: `'*nominatim*' => Http::fake` obrigatório (senão HTTP real),
  `Config::set('geocoding.throttle_ms', 0)`, fixtures `sefaz_*.xml/html` por UF.
