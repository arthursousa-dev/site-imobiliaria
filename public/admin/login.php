<?php
session_start();
require __DIR__ . '/../../app/helpers.php';

if (adminLogado()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Recarregue a página e tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $admin = autenticarAdmin($email, $senha);
        if ($admin) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header('Location: dashboard.php');
            exit;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Administrativo — Horizonte</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="login-container">
  <div class="login-box">
    <h2>Área Administrativa</h2>
    <p>Acesse o painel de gerenciamento</p>

    <?php if ($erro): ?>
      <p style="color:#ff8a8a;margin-bottom:10px"><?= limpar($erro) ?></p>
    <?php endif; ?>

    <form method="POST">
      <?= csrfCampo() ?>
      <input type="email" name="email" placeholder="E-mail" required autofocus>
      <input type="password" name="senha" placeholder="Senha" required>
      <button type="submit" class="btn-login" style="border:none;cursor:pointer;width:100%">Entrar</button>
    </form>
    <a href="../index.php" class="link-voltar">← Voltar ao site</a>
  </div>
</div>

</body>
</html>
