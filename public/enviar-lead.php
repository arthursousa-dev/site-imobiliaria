<?php
session_start();
require __DIR__ . '/../app/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    $_SESSION['lead_erro'] = 'Sessão expirada. Recarregue a página e tente novamente.';
    header('Location: index.php#contato');
    exit;
}

$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$imovelId = (int) ($_POST['imovel_id'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');
$dataVisita = $_POST['data_visita_preferida'] ?? '';

$erros = [];
if ($nome === '') $erros[] = 'nome';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'e-mail';
if ($telefone === '') $erros[] = 'telefone';

if (!empty($erros)) {
    $_SESSION['lead_erro'] = 'Verifique os dados informados (' . implode(', ', $erros) . ') e tente novamente.';
    header('Location: index.php#contato');
    exit;
}

$pdo = conectar();

// Se um imóvel foi indicado, confirma que ele existe de fato.
$imovelIdFinal = null;
if ($imovelId > 0) {
    $stmt = $pdo->prepare('SELECT id FROM imoveis WHERE id = :id');
    $stmt->execute([':id' => $imovelId]);
    if ($stmt->fetch()) {
        $imovelIdFinal = $imovelId;
    }
}

$insere = $pdo->prepare(
    'INSERT INTO leads (nome, email, telefone, imovel_id, mensagem, data_visita_preferida, status)
     VALUES (:nome, :email, :telefone, :imovel_id, :mensagem, :data_visita, :status)'
);
$insere->execute([
    ':nome'        => $nome,
    ':email'       => $email,
    ':telefone'    => $telefone,
    ':imovel_id'   => $imovelIdFinal,
    ':mensagem'    => $mensagem !== '' ? $mensagem : null,
    ':data_visita' => $dataVisita !== '' ? $dataVisita : null,
    ':status'      => $dataVisita !== '' ? 'visita_agendada' : 'novo',
]);

$_SESSION['lead_ok'] = true;
header('Location: index.php#contato');
exit;
