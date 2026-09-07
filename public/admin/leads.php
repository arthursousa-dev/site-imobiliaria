<?php
session_start();
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        die('Sessão expirada. Recarregue a página e tente novamente.');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['novo', 'contatado', 'visita_agendada', 'fechado'], true)) {
        $stmt = $pdo->prepare('UPDATE leads SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
    }
    header('Location: leads.php');
    exit;
}

$leads = $pdo->query(
    'SELECT l.*, i.titulo AS imovel_titulo FROM leads l
     LEFT JOIN imoveis i ON i.id = l.imovel_id
     ORDER BY l.criado_em DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clientes — Horizonte</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

    <h1 class="admin-titulo">Clientes</h1>
    <p class="admin-subtitulo">Contatos recebidos pelo site</p>

    <div class="admin-card" style="margin-top:20px">
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid #ddd">
            <th style="padding:8px">Nome</th>
            <th style="padding:8px">Contato</th>
            <th style="padding:8px">Imóvel</th>
            <th style="padding:8px">Visita</th>
            <th style="padding:8px">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $l): ?>
            <tr style="border-bottom:1px solid #eee">
              <td style="padding:8px"><?= limpar($l['nome']) ?></td>
              <td style="padding:8px">
                <?= limpar($l['email']) ?><br>
                <small><?= limpar($l['telefone']) ?></small>
              </td>
              <td style="padding:8px"><?= $l['imovel_titulo'] ? limpar($l['imovel_titulo']) : '—' ?></td>
              <td style="padding:8px"><?= formatarDataBr($l['data_visita_preferida']) ?></td>
              <td style="padding:8px">
                <form method="POST" style="display:inline">
                  <?= csrfCampo() ?>
                  <input type="hidden" name="id" value="<?= $l['id'] ?>">
                  <select name="status" onchange="this.form.submit()">
                    <?php foreach (['novo', 'contatado', 'visita_agendada', 'fechado'] as $s): ?>
                      <option value="<?= $s ?>" <?= $l['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($leads)): ?>
            <tr><td colspan="5" style="padding:8px">Nenhum contato recebido ainda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
