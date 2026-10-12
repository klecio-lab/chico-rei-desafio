<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Busca full-text (spike BDR-012)
    |--------------------------------------------------------------------------
    |
    | Desligado por padrão: máquina limpa, CI e testes nem notam que o ES
    | existe. Com ELASTIC_ENABLED=true, o `q` da listagem usa o índice com
    | fallback obrigatório para LIKE em qualquer falha (timeout, queda,
    | índice vazio). A busca atual nunca pode quebrar por causa do ES.
    |
    */

    'elastic_enabled' => env('ELASTIC_ENABLED', false),

    'elastic_host' => env('ELASTIC_HOST', 'http://elasticsearch:9200'),

    'elastic_index' => env('ELASTIC_INDEX', 'products'),

    'elastic_timeout' => (int) env('ELASTIC_TIMEOUT', 3),

];
