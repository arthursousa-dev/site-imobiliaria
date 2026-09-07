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
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'excluir') {
        $stmt = $pdo->prepare('UPDATE imoveis SET ativo = 0 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    } elseif ($acao === 'marcar_vendido') {
        $stmt = $pdo->prepare("UPDATE imoveis SET status = 'vendido', vendido_em = CURDATE() WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    header('Location: imoveis.php');
    exit;
}

$imoveis = $pdo->query('SELECT * FROM imoveis WHERE ativo = 1 ORDER BY criado_em DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gerenciar Imóveis — Horizonte</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

    <div style="display:flex;justify-content:space-between;align-items:center">
      <h1 class="admin-titulo">Imóveis</h1>
      <a href="imovel-form.php" class="btn-destaque" style="font-size:14px;padding:10px 20px">+ Novo imóvel</a>
    </div>

    <div class="admin-card" style="margin-top:20px">
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="text-align:left;border-bottom:1px solid #ddd">
            <th style="padding:8px">Título</th>
            <th style="padding:8px">Tipo</th>
            <th style="padding:8px">Preço</th>
            <th style="padding:8px">Status</th>
            <th style="padding:8px"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($imoveis as $im): ?>
            <tr style="border-bottom:1px solid #eee">
              <td style="padding:8px"><?= limpar($im['titulo']) ?></td>
              <td style="padding:8px"><?= limpar($im['tipo']) ?></td>
              <td style="padding:8px"><?= formatarPreco($im['preco']) ?></td>
              <td style="padding:8px"><?= $im['status'] === 'vendido' ? 'Vendido' : 'Disponível' ?></td>
              <td style="padding:8px;white-space:nowrap">
                <a href="imovel-form.php?id=<?= $im['id'] ?>">Editar</a>
                &nbsp;·&nbsp;
                <?php if ($im['status'] !== 'vendido'): ?>
                  <form method="POST" style="display:inline" onsubmit="return confirm('Marcar como vendido?')">
                    <?= csrfCampo() ?>
                    <input type="hidden" name="acao" value="marcar_vendido">
                    <input type="hidden" name="id" value="<?= $im['id'] ?>">
                    <button type="submit" style="background:none;border:none;color:#2e7d32;cursor:pointer;font:inherit;padding:0">Marcar vendido</button>
                  </form>
                  &nbsp;·&nbsp;
                <?php endif; ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Remover este imóvel do catálogo?')">
                  <?= csrfCampo() ?>
                  <input type="hidden" name="acao" value="excluir">
                  <input type="hidden" name="id" value="<?= $im['id'] ?>">
                  <button type="submit" style="background:none;border:none;color:#c0392b;cursor:pointer;font:inherit;padding:0">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($imoveis)): ?>
            <tr><td colspan="5" style="padding:8px">Nenhum imóvel cadastrado.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
