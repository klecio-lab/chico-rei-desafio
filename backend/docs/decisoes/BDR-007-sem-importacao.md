# BDR-007 — Sem importação externa/scraping

- **Status:** [APLICADO] (decisão de escopo, vale desde já)
- **Contexto:** edital marca importação/scraping como **diferencial, não obrigatório**, e manda priorizar obrigatórios. Site real auditado (https://chicorei.com/): listagem por tema (`/camiseta/<tema>/` com `?per_page=&sort=`), detalhe em `/camisa/<slug>-<id>.html`, HTML server-rendered (parseável com HTTP puro + DOM, sem browser).
- **Decisão:** não implementar; seeders com dados próprios cobrem "máquina limpa". Se houvesse tempo: comando `products:import` lendo as listagens por tema com `upsert` por slug (idempotente, sem duplicar), respeitando throttle + `robots`, regra explicada no README.
- **Alternativas:** scraping da Chico Rei — descartado (frágil, fora do controle, consome o prazo dos obrigatórios que limitam a nota).
- **Consequências:** zero risco nos 45% de código+boas práticas; item 6 do README registra o "faria diferente".
- **Refs:** edital (fonte de dados · diferenciais) · avaliação: obrigatórios incompletos limitam a nota.
