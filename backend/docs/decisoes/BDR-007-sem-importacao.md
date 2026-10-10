# BDR-007 — Sem importação externa/scraping

- **Status:** [APLICADO] (decisão de escopo, vale desde já)
- **Contexto:** edital marca importação/scraping como **diferencial, não obrigatório**, e manda priorizar obrigatórios.
- **Decisão:** não implementar; seeders com dados próprios cobrem "máquina limpa". Se houvesse tempo: comando `products:import` com `upsert` por `name+category` (idempotente), regra explicada no README.
- **Alternativas:** scraping da Chico Rei — descartado (frágil, fora do controle, consome o prazo dos obrigatórios que limitam a nota).
- **Consequências:** zero risco nos 45% de código+boas práticas; item 6 do README registra o "faria diferente".
- **Refs:** edital (fonte de dados · diferenciais) · avaliação: obrigatórios incompletos limitam a nota.
