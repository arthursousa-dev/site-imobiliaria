<?php

use PHPUnit\Framework\TestCase;

final class AutenticacaoTest extends TestCase
{
    private const EMAIL = 'teste.autenticacao@horizonte.local';
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = conectar();
        $this->tearDown();
        $this->pdo->prepare('INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES (:n, :e, :h)')
            ->execute([':n' => 'Teste', ':e' => self::EMAIL, ':h' => password_hash('senha-certa', PASSWORD_DEFAULT)]);
    }

    protected function tearDown(): void
    {
        $this->pdo->prepare('DELETE FROM admin_usuarios WHERE email = :e')->execute([':e' => self::EMAIL]);
    }

    public function test_senha_certa_autentica_e_zera_tentativas(): void
    {
        autenticarAdmin(self::EMAIL, 'errada');
        $admin = autenticarAdmin(self::EMAIL, 'senha-certa');

        $this->assertIsArray($admin);
        $tentativas = $this->pdo->query("SELECT tentativas_login FROM admin_usuarios WHERE email = '" . self::EMAIL . "'")->fetchColumn();
        $this->assertSame(0, (int) $tentativas);
    }

    public function test_conta_bloqueia_apos_cinco_senhas_erradas(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(autenticarAdmin(self::EMAIL, 'errada'));
        }
        $this->assertFalse(autenticarAdmin(self::EMAIL, 'senha-certa'), 'durante o bloqueio nem a senha certa entra');
    }

    public function test_email_inexistente_nao_autentica(): void
    {
        $this->assertFalse(autenticarAdmin('ninguem@horizonte.local', 'qualquer'));
    }
}
