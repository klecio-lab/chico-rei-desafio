# Checklist do edital

Legenda: `[x]` feito · `[ ]` pendente. Atualize conforme entrega.

## Não negociáveis (sem estes, não é avaliado)

- [x] Back-end Laravel + MySQL (Sail/Docker)
- [x] FormRequests em toda entrada
- [x] Respostas padronizadas (`tecnica/api.md`)
- [x] Erros consistentes (422/401/403/404 em PT-BR)
- [x] Front Vue com componentização (fatia 4: Loading/Empty/Error + AppButton/FieldError/ConfirmDialog/ProductCard)
- [x] Git com histórico (`27a39b3` scaffold; continuar `feat:`/`docs:` em PT-BR)
- [x] README com os 6 itens (raiz; verificado em máquina limpa)

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

- [x] Login · listagem com filtros/busca/paginação/ordenação · detalhe · CUD protegido · componentes

## Dados

- [x] Migrations + seeders (~20 produtos + usuário demo) no repo

## Diferenciais (só após obrigatórios completos)

- [x] Docker (Sail = orquestrado)
- [x] Logs em job assíncrono (BDR-005; worker: `sail artisan queue:work`)
- [x] Testes automatizados (padrão Fiscali, `§9` do AGENTS.md)
- [ ] Importação externa — planejado: BDR-009 (P1, sucede BDR-007)
- [ ] Sentry — planejado: BDR-011 (P2)
- [ ] Elasticsearch — condicional: BDR-012 (P3, só se 009/010/011 verdes até seg)
- [ ] CI no push — planejado: BDR-013 (P1, fora do edital, protege a nota)
