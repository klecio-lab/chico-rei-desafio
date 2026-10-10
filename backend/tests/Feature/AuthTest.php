<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // O grant `personal_access` exige um client pessoal no banco (aqui: DB `testing`).
        $this->app->make(ClientRepository::class)->createPersonalAccessGrantClient('test-personal-access', 'users');
    }

    public function test_login_com_credenciais_validas_retorna_token_e_envelope(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Login realizado com sucesso.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'expires_at',
                ],
            ])
            ->assertJsonPath('data.user.email', $user->email);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertNotEmpty($response->json('data.expires_at'));
    }

    public function test_login_com_senha_errada_retorna_422_generico(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(422)->assertJsonPath('message', 'Credenciais inválidas.');
    }

    public function test_login_com_email_inexistente_retorna_mesma_mensagem_422(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'ninguem@exemplo.test',
            'password' => 'qualquer-uma',
        ]);

        // Nunca revela se o e-mail existe (regra 15 de autenticacao.md).
        $response->assertStatus(422)->assertJsonPath('message', 'Credenciais inválidas.');
    }

    public function test_login_sem_email_retorna_422(): void
    {
        $response = $this->postJson('/api/login', ['password' => 'password']);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
        $this->assertSame('O e-mail é obrigatório.', $response->json('errors.email.0'));
    }

    public function test_login_com_email_invalido_retorna_422(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nao-e-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
        $this->assertSame('Informe um e-mail válido.', $response->json('errors.email.0'));
    }

    public function test_login_sem_password_retorna_422(): void
    {
        $response = $this->postJson('/api/login', ['email' => 'alguem@exemplo.test']);

        $response->assertStatus(422)->assertJsonValidationErrors(['password']);
        $this->assertSame('A senha é obrigatória.', $response->json('errors.password.0'));
    }

    public function test_me_sem_token_retorna_401_json(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)->assertJsonPath('message', 'Não autenticado.');
    }

    public function test_me_com_token_retorna_usuario(): void
    {
        $user = $this->authUser();

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email']]])
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_logout_revoga_token(): void
    {
        $user = User::factory()->create();

        $token = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(200)->json('data.token');

        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])->assertStatus(200);

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Logout realizado com sucesso.');

        $this->assertTrue(
            Passport::token()->newQuery()->where('user_id', $user->id)->firstOrFail()->revoked
        );

        // O guard memoiza o usuário por instância da app; entre requests de teste
        // é preciso descartá-lo para revalidar o Bearer (em produção cada request
        // é um processo novo e a revogação vale de imediato).
        Auth::forgetGuards();

        $this->getJson('/api/me', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Não autenticado.');
    }
}
