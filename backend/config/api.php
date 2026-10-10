<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nome do personal access token (Passport)
    |--------------------------------------------------------------------------
    |
    | Nome gravado na coluna `oauth_access_tokens.name` a cada login. Não tem
    | efeito funcional — serve só para auditar no banco qual token veio do
    | login da API (BDR-001).
    |
    */

    'token_name' => env('API_TOKEN_NAME', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Expiração do token de login (dias)
    |--------------------------------------------------------------------------
    |
    | Passport expira personal tokens em 1 ano por padrão — longo demais para
    | credencial de front. 15 dias equilibra UX (sem refresh flow no escopo
    | do desafio) com segurança; aplicado em AppServiceProvider via
    | `Passport::personalAccessTokensExpireIn()`.
    |
    */

    'token_expires_days' => (int) env('API_TOKEN_EXPIRES_DAYS', 15),

    /*
    |--------------------------------------------------------------------------
    | Rate limit do login (por IP)
    |--------------------------------------------------------------------------
    |
    | Login é a única rota pública com custo de bcrypt + emissão de JWT, por
    | isso tem bucket próprio e conservador (10/min por IP). CUD/leitura
    | ganham limiters próprios na BDR do CRUD de produtos.
    |
    */

    'throttle_login_max' => (int) env('THROTTLE_LOGIN_MAX', 10),

    /*
    |--------------------------------------------------------------------------
    | Paginação e rate limit da leitura pública
    |--------------------------------------------------------------------------
    |
    | `per_page` default da listagem de produtos (o FormRequest limita 1–100).
    | Leitura é pública e barata, mas tem bucket próprio (120/min por IP ou
    | usuário logado) para preservar o banco em scraping agressivo.
    |
    */

    'pagination_default' => (int) env('API_PAGINATION_DEFAULT', 15),

    'throttle_read_max' => (int) env('THROTTLE_READ_MAX', 120),

];
