# Checklist do edital

Legenda: `[x]` feito · `[ ]` pendente. Atualize conforme entrega.

## Não negociáveis (sem estes, não é avaliado)

- [x] Back-end Laravel + MySQL (Sail/Docker)
- [x] FormRequests em toda entrada
- [x] Respostas padronizadas (`tecnica/api.md`)
- [x] Erros consistentes (422/401/403/404 em PT-BR)
- [ ] Front Vue com componentização (chat front)
- [x] Git com histórico (`27a39b3` scaffold; continuar `feat:`/`docs:` em PT-BR)
- [ ] README com os 6 itens (rascunho na raiz; expandir na entrega)

## Frente 1 — Autenticação (`regras-de-negocio/autenticacao.md`)

- [x] Base Passport (tabelas + client `chico-rei-app`)
- [x] `POST /api/login` · `POST /api/logout` · `GET /api/me`
- [x] Só autenticado cria/edita/exclui; público só lista/visualiza

## Frente 2 — Produtos (`regras-de-negocio/produtos.md`, `logs.md`)

- [x] CRUD completo + campos do edital (`name description image price category stock`)
- [x] Listagem: `q` busca + filtros (categoria, preço, estoque) + `sort` + `per_page`
- [x] `GET /api/products/{id}` detalhe completo
- [x] `product_logs` via Observer + Job async

## Frente 3 — Web (chat front)

- [ ] Login · listagem com filtros/busca/paginação/ordenação · detalhe · CUD protegido · componentes

## Dados

- [x] Migrations + seeders (~20 produtos + usuário demo) no repo

## Diferenciais (só após obrigatórios completos)

- [x] Docker (Sail = orquestrado)
- [x] Logs em job assíncrono (BDR-005; worker: `sail artisan queue:work`)
- [x] Testes automatizados (padrão Fiscali, `§9` do AGENTS.md)
- [ ] Importação externa — **não**: ver BDR-007
- [ ] Sentry / Elasticsearch — **não**: ver `visao/fora-do-escopo.md`
