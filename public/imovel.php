<?php
require_once __DIR__ . '/bootstrap_sessao.php';
require __DIR__ . '/../app/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = conectar()->prepare('SELECT * FROM imoveis WHERE id = :id AND ativo');
$stmt->execute([':id' => $id]);
$imovel = $stmt->fetch();

if (!$imovel) {
    header('Location: index.php#imoveis');
    exit;
}

$vendido = $imovel['status'] === 'vendido';
$erro = $_SESSION['lead_erro'] ?? null;
unset($_SESSION['lead_erro']);
$sucesso = $_SESSION['lead_ok'] ?? null;
unset($_SESSION['lead_ok']);

$caracteristicas = [
    'Tipo'    => $imovel['tipo'],
    'Área'    => (int) $imovel['area_m2'] . ' m²',
    'Quartos' => (int) $imovel['quartos'],
    'Suítes'  => (int) $imovel['suites'],
    'Vagas'   => (int) $imovel['vagas'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= limpar($imovel['titulo']) ?> — Horizonte Imobiliária</title>
  <meta name="description" content="<?= limpar(preg_replace('/^(.{0,155}).*$/su', '$1', $imovel['descricao'] ?? $imovel['titulo'])) ?>">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
  <a href="index.php" class="logo"><span>H</span> Horizonte</a>
  <nav>
    <a href="index.php">INÍCIO</a>
    <a href="index.php#imoveis">IMÓVEIS</a>
    <a href="#contato">CONTATO</a>
    <a href="admin/login.php" class="btn-entrar">ADMIN</a>
  </nav>
</header>

<main class="detalhe">
  <a href="index.php#imoveis" class="link-voltar-claro">← Voltar aos imóveis</a>

  <div class="detalhe-grade">
    <figure class="detalhe-foto">
      <img src="<?= limpar($imovel['imagem_url']) ?>" alt="<?= limpar($imovel['titulo']) ?>">
      <?php if ($vendido): ?><figcaption class="selo-vendido">Vendido</figcaption><?php endif; ?>
    </figure>

    <div class="detalhe-info">
      <h1><?= limpar($imovel['titulo']) ?></h1>
      <p class="preco detalhe-preco"><?= formatarPreco($imovel['preco']) ?></p>

      <dl class="detalhe-caracteristicas">
        <?php foreach ($caracteristicas as $rotulo => $valor): ?>
          <div><dt><?= $rotulo ?></dt><dd><?= limpar((string) $valor) ?></dd></div>
        <?php endforeach; ?>
      </dl>

      <?php if (!empty($imovel['descricao'])): ?>
        <p class="detalhe-descricao"><?= nl2br(limpar($imovel['descricao'])) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="lead-box" id="contato">
    <?php if ($vendido): ?>
      <h3>Este imóvel já foi vendido</h3>
      <p>Veja outras opções disponíveis no <a href="index.php#imoveis">nosso catálogo</a>.</p>
    <?php else: ?>
      <h3>Tenho interesse neste imóvel</h3>

      <?php if ($erro): ?>
        <p class="aviso aviso-erro" role="alert"><?= limpar($erro) ?></p>
      <?php endif; ?>
      <?php if ($sucesso): ?>
        <p class="aviso aviso-ok" role="status">Recebemos seu contato! Em breve falaremos com você.</p>
      <?php endif; ?>

      <form action="enviar-lead.php" method="post" class="lead-form">
        <?= csrfCampo() ?>
        <input type="hidden" name="imovel_id" value="<?= (int) $imovel['id'] ?>">
        <input type="hidden" name="origem" value="imovel">

        <input type="text" name="nome" placeholder="Seu nome" maxlength="150" required aria-label="Seu nome">
        <input type="email" name="email" placeholder="Seu e-mail" maxlength="150" required aria-label="Seu e-mail">
        <input type="tel" name="telefone" placeholder="Seu telefone / WhatsApp" maxlength="30" required aria-label="Seu telefone">
        <label class="rotulo-campo" for="data_visita">Data preferida para visita (opcional)</label>
        <input type="date" id="data_visita" name="data_visita_preferida" min="<?= date('Y-m-d') ?>">
        <textarea name="mensagem" placeholder="Mensagem (opcional)" maxlength="2000" aria-label="Mensagem"></textarea>

        <button type="submit" class="btn-destaque">Quero agendar uma visita</button>
      </form>
    <?php endif; ?>
  </div>
</main>

<footer>
  © <?= date('Y') ?> <span>Horizonte Imobiliária</span> — Todos os direitos reservados
</footer>

</body>
</html>
