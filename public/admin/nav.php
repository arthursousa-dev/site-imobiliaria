<?php $paginaAtual = basename($_SERVER['SCRIPT_NAME']); ?>
<header class="admin-header">
  <div class="logo"><span>H</span> Horizonte — Admin</div>
  <a href="../index.php">← Voltar ao Site</a>
</header>

<div class="admin-layout">

  <nav class="admin-menu">
    <a href="dashboard.php" class="<?= $paginaAtual === 'dashboard.php' ? 'ativo' : '' ?>">🏠 Dashboard</a>
    <a href="imoveis.php" class="<?= in_array($paginaAtual, ['imoveis.php', 'imovel-form.php']) ? 'ativo' : '' ?>">🏡 Gerenciar Imóveis</a>
    <a href="imovel-form.php">➕ Cadastrar Imóvel</a>
    <a href="leads.php" class="<?= $paginaAtual === 'leads.php' ? 'ativo' : '' ?>">👥 Clientes</a>
    <a href="logout.php">🚪 Sair</a>
  </nav>

  <main class="admin-conteudo">