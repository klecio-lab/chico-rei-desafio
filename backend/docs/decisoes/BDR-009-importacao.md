# BDR-009 — Importação via scraping Chico Rei (idempotente)

- **Status:** [APLICADO] · **Prioridade:** P1 · **Chat:** back · **Sucede:** BDR-007 (`products:import` + `ChicoReiFetcher`/`ChicoReiParser` + migration `source_url`; cobertura em `Feature/ImportCommandTest.php` + `Unit/ChicoReiParserTest.php`)
- **Contexto:** diferencial com exemplo nominal da empresa; site auditado (HTML server-rendered com JSON embutido; listagem `/camiseta/<tema>/` com `?per_page=&sort=`; detalhe `/camiseta/<slug>-<id>.html`; `robots.txt` permite `/camiseta/`, 1 request por import respeita o crawl-delay).
- **Decisão:** comando `products:import [--tema=] [--limit=] [--dry-run]`; parse do JSON embutido na listagem (nome/preço/imagem/link — o detalhe só seria N requests a mais pelo mesmo dado); migration nova (`source_url` nullable unique em `products`); `upsert` por `source_url` (atualiza `price`/`image`; `stock` default 10, `category` = tema); throttle 1 req/s + UA próprio; `--dry-run` só loga; regra explicada no README item 3 (exigência do edital).
- **Testes:** fixture `tests/Fixtures/chicorei_tema.html` (HTML real anonimizado) + `Http::fake`; rodar 2× não duplica; dry-run não persiste.
- **Consequências:** `api.md` inalterado (import é operação, não endpoint); Scheduler NÃO agenda (manual).
- **Refs:** edital (diferencial importação · README item 3) · BDR-007 (sucedida por esta).
