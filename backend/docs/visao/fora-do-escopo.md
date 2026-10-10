# Fora do escopo (com mais tempo)

Base do **item 6 do README**. Nada abaixo é obrigatório; entra só se tudo do `escopo.md` estiver pronto.

- Importação/scraping (BDR-007) — faria `products:import` idempotente.
- Upload real de imagem (BDR-006) — `storage/` público + validação de arquivo.
- Sentry e Elasticsearch — overkill para CRUD de 3 dias; log + `LIKE` com índice bastam.
- API Resources/DTOs, scopes Passport por recurso, refresh-token no front, paginação cursor.
- CI de testes no push (diferencial barato se sobrar tempo: `artisan test` no GitHub Actions).
