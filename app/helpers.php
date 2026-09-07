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

function formatarDataBr(?string $data): string
{
    if (!$data) return '—';
    $dt = date_create($data);
    return $dt ? date_format($dt, 'd/m/Y') : $data;
}

/** Autentica um admin contra admin_usuarios (senha em hash bcrypt). */
function autenticarAdmin(string $email, string $senha): array|false
{
    $stmt = conectar()->prepare('SELECT * FROM admin_usuarios WHERE email = :email');
    $stmt->execute([':email' => strtolower(trim($email))]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($senha, $admin['senha_hash'])) {
        return $admin;
    }
    return false;
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
