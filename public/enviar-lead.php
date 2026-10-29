<?php
require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . '/../app/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$destino = destinoLead((string) ($_POST['origem'] ?? ''), (int) ($_POST['imovel_id'] ?? 0));

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    $_SESSION['lead_erro'] = 'Sessão expirada. Recarregue a página e tente novamente.';
    header('Location: ' . $destino);
    exit;
}

[$lead, $erros] = validarLead($_POST);

if (!empty($erros)) {
    $_SESSION['lead_erro'] = 'Verifique os dados informados (' . implode(', ', $erros) . ') e tente novamente.';
    header('Location: ' . $destino);
    exit;
}

$pdo = conectar();

// Se um imóvel foi indicado, confirma que ele existe de fato.
$imovelIdFinal = null;
$imovelTitulo = null;
if ($lead['imovel_id'] > 0) {
    $stmt = $pdo->prepare('SELECT id, titulo FROM imoveis WHERE id = :id');
    $stmt->execute([':id' => $lead['imovel_id']]);
    if ($imovel = $stmt->fetch()) {
        $imovelIdFinal = (int) $imovel['id'];
        $imovelTitulo = $imovel['titulo'];
    }
}

$insere = $pdo->prepare(
    'INSERT INTO leads (nome, email, telefone, imovel_id, mensagem, data_visita_preferida, status)
     VALUES (:nome, :email, :telefone, :imovel_id, :mensagem, :data_visita, :status)'
);
$insere->execute([
    ':nome'        => $lead['nome'],
    ':email'       => $lead['email'],
    ':telefone'    => $lead['telefone'],
    ':imovel_id'   => $imovelIdFinal,
    ':mensagem'    => $lead['mensagem'] !== '' ? $lead['mensagem'] : null,
    ':data_visita' => $lead['data_visita'] !== '' ? $lead['data_visita'] : null,
    ':status'      => $lead['data_visita'] !== '' ? 'visita_agendada' : 'novo',
]);

notificarCorretor($lead, $imovelTitulo);

$_SESSION['lead_ok'] = true;
header('Location: ' . $destino);
exit;
