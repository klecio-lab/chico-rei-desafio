# BDR-006 — `image` como URL (string), sem upload

- **Status:** [PROPOSTO]
- **Contexto:** edital sugere campo `image`; performance avalia "imagens otimizadas"; prazo de 3 dias.
- **Decisão:** `image` = string URL validada (`nullable|url|max:2048`); seeder usa placeholders; front exibe com `loading="lazy"` + fallback.
- **Alternativas:** upload para `storage/` + disco público — descartado no prazo (validação de arquivo, `storage:link`, limpeza; entraria com mais tempo).
- **Consequências:** CRUD e filtros 100% funcionais; upload real listado em `visao/fora-do-escopo.md`.
- **Refs:** edital §produtos (campo sugerido) · avaliação: performance 10%.
