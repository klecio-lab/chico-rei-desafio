# Arquitetura (back-end)

API-only, stateless, sem sessão (`bootstrap/app.php` com 401-JSON, `web.php` só health).

## Camadas (`§3` do AGENTS.md)

```
Vue (Axios) → routes/api.php → FormRequest → Controller fino → Service → Model
                                                              ↘ Observer → Job (LogProductActivity) → product_logs
```

- `Http/Controllers/Api/`: `AuthController`, `ProductController` — só HTTP + envelope.
- `Http/Requests/Api/`: `LoginRequest`, `StoreProductRequest`, `UpdateProductRequest`, `IndexProductRequest` (filtros da listagem também são validados!).
- `Services/`: `ProductService` (filtros/ordenacao/persistência em `DB::transaction`).
- `Observers/`: `ProductObserver` → despacha o job, nunca escreve direto.
- `Jobs/`: `LogProductActivity` (`tries = 1`, nunca lança).
- `Providers/`: limiters `login`/`api` + (futuro) bindings.

## Fluxos

- **Leitura pública**: `index/show` — query com filtros → `paginate(per_page)` → envelope.
- **Escrita protegida**: `auth:api` → FormRequest → `create/update` → Observer despacha log → `201/200/204`.
