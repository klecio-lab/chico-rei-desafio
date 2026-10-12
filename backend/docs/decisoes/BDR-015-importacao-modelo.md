# BDR-015 — Importar por URL explícita + filtro de modelagem

- **Status:** [APLICADO] · **Prioridade:** P1 · **Chat:** back · **Estende:** BDR-009 (`--url/--modelo/--categoria` + `models` no parser; cobertura em `ImportCommandTest` + `ChicoReiParserTest`)
- **Contexto:** "feminina" não é tema da loja — é modelagem (`babylook`, gênero F) dentro dos produtos; e a listagem base `/camiseta/` não cabe em `--tema`.
- **Decisão:** `products:import` ganha `--url=` (listagem explícita, ignora `--tema`), `--modelo=` (filtra por modelagem ou gênero, case-insensitive) e `--categoria=` (default: `--tema`). Parser extrai `models` (url + gender) de cada item.
- **Testes:** fixture com models; `--modelo=F` importa só Alfa; `--url` busca na URL dada.
- **Consequências:** BDR-009 intacta (só estendida); `api.md` inalterado (operação, não endpoint).
- **Refs:** BDR-009.
