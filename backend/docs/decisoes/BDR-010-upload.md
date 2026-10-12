# BDR-010 — Upload real de imagem (compatível com URL)

- **Status:** [APLICADO] · **Prioridade:** P2 · **Chat:** back, depois front (mesma fatia vertical) (`image_file` nos FormRequests + `ProductService` em disco `public`; cobertura em `Feature/ProductTest.php`; contrato em `tecnica/api.md`)
- **Contexto:** BDR-006 escolheu URL por prazo; com folga até terça, upload entra.
- **Decisão:** `POST/PUT products` aceitam `image_file` (multipart, `image|max:2048`) **ou** `image` URL (BDR-006 mantida); se ambos, file vence. Service salva em disco `public/products`; coluna guarda a URL pública. `storage:link` entra em `ambiente.md` + README.
- **Contrato:** atualizar `tecnica/api.md` (param `image_file`) **antes** do front (convenção do README de docs).
- **Front (mesmo dia):** input file + preview + fallback atual mantido.
- **Testes:** `UploadedFile::fake()->image()`; URL segue válida; ambos enviados → file vence.
- **Refs:** edital §produtos (`image`) · BDR-006 · AGENTS.md `§0.2`, `§4`.
