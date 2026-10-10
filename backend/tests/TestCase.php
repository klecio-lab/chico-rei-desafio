<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    /**
     * Usuário autenticado via Passport em todos os testes de API (padrão Fiscali, §9).
     */
    protected function authUser(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);

        Passport::actingAs($user);

        return $user;
    }
}
