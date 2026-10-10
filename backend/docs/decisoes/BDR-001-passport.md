# BDR-001 — Passport em vez de Sanctum

- **Status:** [APLICADO] (`install:api --passport`, `HasApiTokens` + `OAuthenticatable`, client `chico-rei-app`)
- **Contexto:** edital aceita "autenticação nativa do Laravel"; padrão pessoal do autor é Passport (OAuth2).
- **Decisão:** Passport com personal access tokens no login (`createToken('api')`), revoke no logout.
- **Alternativas:** Sanctum (mais simples, padrão do `install:api`) — descartado porque o autor opera Passport como padrão e o template (`§4`) o adota; custo extra é mínimo (1 comando + 1 trait).
- **Consequências:** tabelas `oauth_*`, `passport:keys` fora do git, `auth:api` nas rotas. Trade-off registrado para o item 5 do README.
- **Refs:** edital §autenticação · AGENTS.md `§4`.
