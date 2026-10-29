<?php
/**
 * app/helpers.php
 * Funções compartilhadas: conexão, autenticação, CSRF, formatação.
 */

use App\Config\Database;

require_once __DIR__ . '/Config/Database.php';

function conectar(): PDO
{
    return Database::getConnection();
}

function limpar(string $valor): string
{
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}

function formatarPreco(float $valor): string
{
    return 'R$ ' . number_format($valor, 0, ',', '.');
}

/**
 * Converte o preço digitado em número. Aceita "350000", "350.000", "350.000,50" e o
 * formato do banco ("3500000.00", que o formulário de edição traz preenchido).
 */
function lerPreco(string $valor): float
{
    $valor = str_replace(['R$', ' '], '', trim($valor));
    if (preg_match('/^\d+(\.\d{1,2})?$/', $valor)) {
        return (float) $valor;
    }
    return (float) str_replace(['.', ','], ['', '.'], $valor);
}

/** Quantidade de caracteres UTF-8 sem depender da extensão mbstring; UTF-8 inválido conta como excesso. */
function tamanhoTexto(string $texto): int
{
    $total = preg_match_all('/./su', $texto);
    return $total === false ? PHP_INT_MAX : $total;
}

/**
 * Valida os campos do formulário de contato. Devolve [dados limpos, lista de campos inválidos].
 * Limites iguais às colunas: texto maior que o VARCHAR estourava erro 500 no INSERT.
 */
function validarLead(array $entrada, ?string $hoje = null): array
{
    $hoje ??= date('Y-m-d');
    $dados = [
        'nome'       => trim((string) ($entrada['nome'] ?? '')),
        'email'      => trim((string) ($entrada['email'] ?? '')),
        'telefone'   => trim((string) ($entrada['telefone'] ?? '')),
        'imovel_id'  => (int) ($entrada['imovel_id'] ?? 0),
        'mensagem'   => trim((string) ($entrada['mensagem'] ?? '')),
        'data_visita'=> trim((string) ($entrada['data_visita_preferida'] ?? '')),
    ];

    $erros = [];
    if ($dados['nome'] === '' || tamanhoTexto($dados['nome']) > 150) $erros[] = 'nome';
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || tamanhoTexto($dados['email']) > 150) $erros[] = 'e-mail';
    if ($dados['telefone'] === '' || tamanhoTexto($dados['telefone']) > 30) $erros[] = 'telefone';
    if (tamanhoTexto($dados['mensagem']) > 2000) $erros[] = 'mensagem';
    if ($dados['data_visita'] !== '') {
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $dados['data_visita']);
        if (!$data || $data->format('Y-m-d') !== $dados['data_visita'] || $dados['data_visita'] < $hoje) $erros[] = 'data da visita';
    }

    return [$dados, $erros];
}

/** Para onde voltar depois do envio: a página do imóvel, se o contato veio dela, ou a home. */
function destinoLead(string $origem, int $imovelId): string
{
    return $origem === 'imovel' && $imovelId > 0 ? "imovel.php?id={$imovelId}#contato" : 'index.php#contato';
}

/**
 * Avisa o corretor por e-mail que um lead chegou. Só envia se CORRETOR_EMAIL estiver
 * definido; falha de envio é registrada no log e nunca impede o lead de ser salvo.
 */
function notificarCorretor(array $lead, ?string $imovelTitulo): bool
{
    $destino = getenv('CORRETOR_EMAIL') ?: '';
    if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $linhas = [
        'Novo contato recebido pelo site.',
        '',
        'Nome: ' . $lead['nome'],
        'E-mail: ' . $lead['email'],
        'Telefone: ' . $lead['telefone'],
        'Imóvel: ' . ($imovelTitulo ?? 'não informado'),
        'Visita preferida: ' . ($lead['data_visita'] !== '' ? formatarDataBr($lead['data_visita']) : 'não informada'),
        '',
        'Mensagem:',
        $lead['mensagem'] !== '' ? $lead['mensagem'] : '(sem mensagem)',
    ];
    // E-mail do visitante já passou por FILTER_VALIDATE_EMAIL, então não carrega quebra de linha para o cabeçalho.
    $cabecalhos = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $lead['email'],
    ];

    $assunto = '=?UTF-8?B?' . base64_encode('Novo lead: ' . ($imovelTitulo ?? 'contato pelo site')) . '?=';
    $enviado = @mail($destino, $assunto, implode("\r\n", $linhas), implode("\r\n", $cabecalhos));
    if (!$enviado) {
        error_log('notificarCorretor: mail() falhou para ' . $destino);
    }
    return $enviado;
}

function formatarDataBr(?string $data): string
{
    if (!$data) return '—';
    $dt = date_create($data);
    return $dt ? date_format($dt, 'd/m/Y') : $data;
}

/** Autentica um admin contra admin_usuarios (senha em hash bcrypt). */
function autenticarAdmin(string $email, string $senha): array|false
{
    $pdo = conectar();
    $email = strtolower(trim($email));

    $stmt = $pdo->prepare('SELECT *, CASE WHEN bloqueado_ate > now() THEN 1 ELSE 0 END AS bloqueado FROM admin_usuarios WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }
    // Comparado no banco: bloqueado_ate vem do now() do PostgreSQL, e strtotime() usaria o fuso do PHP.
    if ((int) $admin['bloqueado'] === 1) {
        return false;
    }
    if (!password_verify($senha, $admin['senha_hash'])) {
        $tentativas = (int) $admin['tentativas_login'] + 1;
        $bloqueio = $tentativas >= 5 ? "now() + interval '15 minutes'" : 'NULL';
        $pdo->prepare("UPDATE admin_usuarios SET tentativas_login = :t, bloqueado_ate = {$bloqueio} WHERE id = :id")
            ->execute([':t' => $tentativas, ':id' => $admin['id']]);
        return false;
    }

    $pdo->prepare('UPDATE admin_usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = :id')
        ->execute([':id' => $admin['id']]);

    return $admin;
}

function adminLogado(): bool
{
    return !empty($_SESSION['admin_id']);
}

function exigirAdmin(): void
{
    if (!adminLogado()) {
        header('Location: login.php');
        exit;
    }
}

/** Token CSRF por sessão. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfValido(?string $token): bool
{
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}
