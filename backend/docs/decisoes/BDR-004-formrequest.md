# BDR-004 — FormRequest em tudo, zero `validate()` no controller

- **Status:** [APLICADO] (`LoginRequest` com `rules()` + `messages()` PT-BR; testes de `422` em `Feature/AuthTest.php`)
- **Contexto:** edital **exige** FormRequests (não negociável); template proíbe `$request->validate()`.
- **Decisão:** `LoginRequest`, `StoreProductRequest`, `UpdateProductRequest` + `IndexProductRequest` (filtros/`sort`/`per_page` também validados, com `messages()` PT-BR).
- **Alternativas:** validar no controller — vetado (`§0.2`).
- **Consequências:** controller só recebe dado válido; testes de `422` por FormRequest (padrão Fiscali, `§9`).
- **Refs:** edital (não negociável: FormRequests) · AGENTS.md `§0.2`, `§3`.
