<?php
session_start();
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

$pdo = conectar();
$imoveisCadastrados = (int) $pdo->query("SELECT COUNT(*) FROM imoveis WHERE ativo = 1 AND status = 'disponivel'")->fetchColumn();
$clientesAtivos     = (int) $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$visitasAgendadas   = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE data_visita_preferida IS NOT NULL AND data_visita_preferida >= CURDATE()")->fetchColumn();
$vendasMes          = (int) $pdo->query("SELECT COUNT(*) FROM imoveis WHERE status = 'vendido' AND MONTH(vendido_em) = MONTH(CURDATE()) AND YEAR(vendido_em) = YEAR(CURDATE())")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Administrativo — Horizonte</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

    <h1 class="admin-titulo">Dashboard</h1>
    <p class="admin-subtitulo">Bem-vindo, <?= limpar($_SESSION['admin_nome']) ?>.</p>

    <div class="admin-grade">
      <div class="admin-card-stat">
        <div class="stat-icone">🏡</div>
        <div class="stat-info">
          <strong><?= $imoveisCadastrados ?></strong>
          <span>Imóveis cadastrados</span>
        </div>
      </div>
      <div class="admin-card-stat">
        <div class="stat-icone">👥</div>
        <div class="stat-info">
          <strong><?= $clientesAtivos ?></strong>
          <span>Clientes ativos</span>
        </div>
      </div>
      <div class="admin-card-stat">
        <div class="stat-icone">📋</div>
        <div class="stat-info">
          <strong><?= $visitasAgendadas ?></strong>
          <span>Visitas agendadas</span>
        </div>
      </div>
      <div class="admin-card-stat">
        <div class="stat-icone">✅</div>
        <div class="stat-info">
          <strong><?= $vendasMes ?></strong>
          <span>Vendas este mês</span>
        </div>
      </div>
    </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
