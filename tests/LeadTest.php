<?php

use PHPUnit\Framework\TestCase;

final class LeadTest extends TestCase
{
    private const HOJE = '2026-10-29';

    private function lead(array $sobrescreve = []): array
    {
        return validarLead($sobrescreve + [
            'nome' => 'Maria D\'Ávila',
            'email' => 'maria@exemplo.com',
            'telefone' => '(67) 99999-0000',
            'imovel_id' => '3',
            'mensagem' => '',
            'data_visita_preferida' => '',
        ], self::HOJE);
    }

    public function test_lead_valido_sai_limpo_e_sem_erros(): void
    {
        [$dados, $erros] = $this->lead(['nome' => '  Maria D\'Ávila  ', 'data_visita_preferida' => '2026-11-02']);

        $this->assertSame([], $erros);
        $this->assertSame('Maria D\'Ávila', $dados['nome']);
        $this->assertSame(3, $dados['imovel_id']);
        $this->assertSame('2026-11-02', $dados['data_visita']);
    }

    public function test_campos_obrigatorios_e_email_invalido(): void
    {
        [, $erros] = $this->lead(['nome' => '', 'email' => 'nao-e-email', 'telefone' => '']);
        $this->assertSame(['nome', 'e-mail', 'telefone'], $erros);
    }

    public function test_textos_acima_do_tamanho_das_colunas(): void
    {
        [, $erros] = $this->lead(['nome' => str_repeat('á', 151), 'mensagem' => str_repeat('x', 2001)]);
        $this->assertSame(['nome', 'mensagem'], $erros);

        // 150 caracteres acentuados ocupam 300 bytes, mas cabem no VARCHAR(150).
        [, $erros] = $this->lead(['nome' => str_repeat('á', 150)]);
        $this->assertSame([], $erros);
    }

    public function test_data_da_visita_inexistente_ou_no_passado(): void
    {
        foreach (['2026-02-30', '29/10/2026', 'amanhã', '2026-10-28'] as $data) {
            [, $erros] = $this->lead(['data_visita_preferida' => $data]);
            $this->assertSame(['data da visita'], $erros, "data $data deveria ser recusada");
        }
        [, $erros] = $this->lead(['data_visita_preferida' => self::HOJE]);
        $this->assertSame([], $erros);
    }

    public function test_destino_volta_para_o_imovel_ou_para_a_home(): void
    {
        $this->assertSame('imovel.php?id=7#contato', destinoLead('imovel', 7));
        $this->assertSame('index.php#contato', destinoLead('imovel', 0));
        $this->assertSame('index.php#contato', destinoLead('https://evil.com', 7));
    }

    public function test_sem_corretor_configurado_nao_envia_email(): void
    {
        putenv('CORRETOR_EMAIL');
        [$dados] = $this->lead();
        $this->assertFalse(notificarCorretor($dados, 'Casa de Alto Padrão'));
    }

    public function test_preco_digitado_ou_vindo_do_banco(): void
    {
        $this->assertSame(350000.0, lerPreco('350000'));
        $this->assertSame(350000.0, lerPreco('350.000'));
        $this->assertSame(350000.5, lerPreco('350.000,50'));
        $this->assertSame(3500000.0, lerPreco('3500000.00'));
        $this->assertSame(1850000.0, lerPreco('R$ 1.850.000,00'));
    }
}
