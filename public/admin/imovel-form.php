<?php
session_start();
require __DIR__ . '/../../app/helpers.php';
exigirAdmin();

$pdo = conectar();
$id = (int) ($_GET['id'] ?? 0);
$imovel = null;

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM imoveis WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $imovel = $stmt->fetch();
    if (!$imovel) {
        header('Location: imoveis.php');
        exit;
    }
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Recarregue a página e tente novamente.';
    } else {
        $titulo     = trim($_POST['titulo'] ?? '');
        $tipo       = $_POST['tipo'] ?? '';
        $quartos    = (int) ($_POST['quartos'] ?? 0);
        $suites     = (int) ($_POST['suites'] ?? 0);
        $vagas      = (int) ($_POST['vagas'] ?? 0);
        $area       = (int) ($_POST['area_m2'] ?? 0);
        $preco      = (float) str_replace(['.', ','], ['', '.'], $_POST['preco'] ?? '0');
        $descricao  = trim($_POST['descricao'] ?? '');
        $imagemUrl  = trim($_POST['imagem_url'] ?? '');

        $tiposValidos = ['Casa', 'Apartamento', 'Cobertura', 'Terreno'];

        if ($titulo === '' || !in_array($tipo, $tiposValidos, true) || $area <= 0 || $preco <= 0 || $imagemUrl === '') {
            $erro = 'Preencha título, tipo, área, preço e imagem corretamente.';
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE imoveis SET titulo=:titulo, tipo=:tipo, quartos=:quartos, suites=:suites, vagas=:vagas,
                     area_m2=:area, preco=:preco, descricao=:descricao, imagem_url=:imagem WHERE id=:id'
                );
                $stmt->execute([
                    ':titulo' => $titulo, ':tipo' => $tipo, ':quartos' => $quartos, ':suites' => $suites,
                    ':vagas' => $vagas, ':area' => $area, ':preco' => $preco,
                    ':descricao' => $descricao, ':imagem' => $imagemUrl, ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO imoveis (titulo, tipo, quartos, suites, vagas, area_m2, preco, descricao, imagem_url)
                     VALUES (:titulo, :tipo, :quartos, :suites, :vagas, :area, :preco, :descricao, :imagem)'
                );
                $stmt->execute([
                    ':titulo' => $titulo, ':tipo' => $tipo, ':quartos' => $quartos, ':suites' => $suites,
                    ':vagas' => $vagas, ':area' => $area, ':preco' => $preco,
                    ':descricao' => $descricao, ':imagem' => $imagemUrl,
                ]);
            }
            header('Location: imoveis.php');
            exit;
        }
    }
}

$v = fn($campo, $default = '') => limpar((string) ($imovel[$campo] ?? $default));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $imovel ? 'Editar' : 'Novo' ?> imóvel — Horizonte</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require __DIR__ . '/nav.php'; ?>

    <h1 class="admin-titulo"><?= $imovel ? 'Editar imóvel' : 'Novo imóvel' ?></h1>

    <div class="admin-card" style="max-width:560px;margin-top:20px">
      <?php if ($erro): ?>
        <p style="color:#c0392b;margin-bottom:14px"><?= limpar($erro) ?></p>
      <?php endif; ?>

      <form method="POST" class="lead-form">
        <?= csrfCampo() ?>

        <input type="text" name="titulo" placeholder="Título" value="<?= $v('titulo') ?>" required>

        <select name="tipo" required style="padding:12px 14px;border:1px solid #ccc;border-radius:8px">
          <?php foreach (['Casa', 'Apartamento', 'Cobertura', 'Terreno'] as $t): ?>
            <option value="<?= $t ?>" <?= ($imovel['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>

        <div style="display:flex;gap:10px">
          <input type="number" name="quartos" placeholder="Quartos" value="<?= $v('quartos', '0') ?>" min="0">
          <input type="number" name="suites" placeholder="Suítes" value="<?= $v('suites', '0') ?>" min="0">
          <input type="number" name="vagas" placeholder="Vagas" value="<?= $v('vagas', '0') ?>" min="0">
        </div>

        <input type="number" name="area_m2" placeholder="Área (m²)" value="<?= $v('area_m2') ?>" required>
        <input type="text" name="preco" placeholder="Preço (ex: 350000)" value="<?= $v('preco') ?>" required>
        <textarea name="descricao" placeholder="Descrição"><?= $v('descricao') ?></textarea>
        <input type="text" name="imagem_url" placeholder="URL da imagem" value="<?= $v('imagem_url') ?>" required>

        <button type="submit" class="btn-destaque"><?= $imovel ? 'Salvar alterações' : 'Cadastrar imóvel' ?></button>
      </form>
    </div>

<?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
