<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Loja de origem (scraping respeitoso)
    |--------------------------------------------------------------------------
    |
    | Base do catálogo público da Chico Rei. O robots.txt permite /camiseta/
    | e o import faz 1 request por execução — throttle e UA próprio por
    | educação, não por necessidade de volume (BDR-009).
    |
    */

    'base_url' => env('IMPORT_BASE_URL', 'https://chicorei.com'),

    'default_tema' => env('IMPORT_TEMA', 'geek'),

    'default_limit' => (int) env('IMPORT_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Ritmo e identidade das requisições
    |--------------------------------------------------------------------------
    */

    'throttle_ms' => (int) env('IMPORT_THROTTLE_MS', 1000),

    'timeout_seconds' => (int) env('IMPORT_TIMEOUT', 15),

    'user_agent' => env('IMPORT_USER_AGENT', 'chico-rei-desafio/1.0 (+https://github.com/klecio-lab/chico-rei-desafio)'),

    /*
    |--------------------------------------------------------------------------
    | Kill-switch
    |--------------------------------------------------------------------------
    |
    | Com IMPORT_ENABLED=false o comando aborta sem nenhuma requisição (§7 do
    | AGENTS.md: recurso externo sempre desligável sem deploy).
    |
    */

    'enabled' => env('IMPORT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Estoque de importados
    |--------------------------------------------------------------------------
    |
    | O site não expõe quantidade — importado entra com este estoque default.
    | Reimportação nunca toca no stock (só price/image via upsert).
    |
    */

    'default_stock' => (int) env('IMPORT_DEFAULT_STOCK', 10),

];
