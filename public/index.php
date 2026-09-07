<?php
session_start();
require __DIR__ . '/../app/helpers.php';

$imoveis = conectar()->query(
    "SELECT * FROM imoveis WHERE ativo = 1 AND status = 'disponivel' ORDER BY criado_em DESC"
)->fetchAll();

$erro = $_SESSION['lead_erro'] ?? null;
unset($_SESSION['lead_erro']);
$sucesso = $_SESSION['lead_ok'] ?? null;
unset($_SESSION['lead_ok']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Horizonte Imobiliária</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<section id="home">
  <header>
    <a href="#home" class="logo"><span>H</span> Horizonte</a>
    <nav>
      <a href="#home">INÍCIO</a>
      <a href="#imoveis">IMÓVEIS</a>
      <a href="#contato">CONTATO</a>
      <a href="admin/login.php" class="btn-entrar">ADMIN</a>
    </nav>
  </header>

  <div class="hero">
    <div class="hero-content">
      <h1>Seu novo lar<br><span>começa aqui</span></h1>
      <p>Imóveis exclusivos para quem busca qualidade e sofisticação.</p>
      <a href="#imoveis" class="btn-destaque">Ver Imóveis</a>
    </div>
  </div>

  <div class="imoveis" id="imoveis">
    <h2>Imóveis em Destaque</h2>
    <p>Selecionados especialmente para você</p>
    <div class="grid">

      <?php foreach ($imoveis as $imovel): ?>
        <div class="card">
          <img src="<?= limpar($imovel['imagem_url']) ?>" alt="<?= limpar($imovel['titulo']) ?>">
          <div class="card-content">
            <h3><?= limpar($imovel['titulo']) ?></h3>
            <p>
              <?= (int) $imovel['quartos'] ?> quartos ·
              <?= (int) $imovel['suites'] ?> suíte<?= $imovel['suites'] != 1 ? 's' : '' ?> ·
              <?= (int) $imovel['vagas'] ?> vaga<?= $imovel['vagas'] != 1 ? 's' : '' ?> ·
              <?= (int) $imovel['area_m2'] ?>m²
            </p>
            <p class="preco"><?= formatarPreco($imovel['preco']) ?></p>
            <a href="#contato" onclick="document.getElementById('lead_imovel').value='<?= (int) $imovel['id'] ?>'; document.getElementById('lead_imovel_nome').textContent='<?= limpar($imovel['titulo']) ?>';" class="btn-destaque" style="display:inline-block;margin-top:10px;font-size:14px;padding:8px 18px">Tenho interesse</a>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($imoveis)): ?>
        <p>Nenhum imóvel disponível no momento.</p>
      <?php endif; ?>

    </div>
  </div>

  <footer>
    © <?= date('Y') ?> <span>Horizonte Imobiliária</span> — Todos os direitos reservados<br>
    Arthur Sousa — ADS 1º Projeto HTML · Kleber — Programação e Tecnologias Web Full Stack
  </footer>
</section>

<section id="contato">
  <header>
    <a href="#home" class="logo"><span>H</span> Horizonte</a>
    <nav>
      <a href="#home">INÍCIO</a>
      <a href="#imoveis">IMÓVEIS</a>
      <a href="#contato">CONTATO</a>
      <a href="admin/login.php" class="btn-entrar">ADMIN</a>
    </nav>
  </header>

  <div class="contato">
    <h2>Fale com a gente</h2>
    <p>Estamos prontos para te atender</p>
    <div class="contato-box">

      <div class="contato-item">
        <div class="contato-icone">👤</div>
        <div>
          <strong>Corretor</strong>
          <span>Arthur Sousa</span>
        </div>
      </div>

      <div class="contato-item">
        <div class="contato-icone">📸</div>
        <div>
          <strong>Instagram</strong>
          <a href="https://instagram.com/arthurs.dev" target="_blank">@arthur</a>
        </div>
      </div>

      <div class="contato-item">
        <div class="contato-icone">💬</div>
        <div>
          <strong>WhatsApp</strong>
          <a href="https://wa.me/5567992928122" target="_blank">(67) 99292-8122</a>
        </div>
      </div>

    </div>

    <div class="lead-box">
      <h3>Solicitar contato <span id="lead_imovel_nome" style="color:var(--dourado)"></span></h3>

      <?php if ($erro): ?>
        <p style="color:#c0392b;margin-bottom:14px"><?= limpar($erro) ?></p>
      <?php endif; ?>
      <?php if ($sucesso): ?>
        <p style="color:#2e7d32;margin-bottom:14px">Recebemos seu contato! Em breve falaremos com você.</p>
      <?php endif; ?>

      <form action="enviar-lead.php" method="post" class="lead-form">
        <?= csrfCampo() ?>
        <input type="hidden" name="imovel_id" id="lead_imovel">

        <input type="text" name="nome" placeholder="Seu nome" required>
        <input type="email" name="email" placeholder="Seu e-mail" required>
        <input type="text" name="telefone" placeholder="Seu telefone / WhatsApp" required>
        <input type="date" name="data_visita_preferida" placeholder="Data preferida para visita">
        <textarea name="mensagem" placeholder="Mensagem (opcional)"></textarea>

        <button type="submit" class="btn-destaque">Enviar</button>
      </form>
    </div>
  </div>
</section>

</body>
</html>
