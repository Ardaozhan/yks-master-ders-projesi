<?php
declare(strict_types=1);
use App\Core\{Auth, Csrf, Session};
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = rtrim($GLOBALS['config']['base_path'], '/');
$path = $base !== '' && str_starts_with($path, $base . '/') ? substr($path, strlen($base)) : $path;
?>
<!doctype html>
<html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>YKS MASTER</title>
        <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
        <script defer src="<?= e(url('/assets/js/app.js')) ?>"></script>
    </head>
    <body data-base-path="<?= e($GLOBALS['config']['base_path']) ?>">
        <header>
            <div class="container header">
                <a class="brand" href="<?= e(url('/')) ?>">YKS MASTER</a>
                <nav aria-label="Ana menü">
                    <?php if (Auth::user()): ?>
                        <?php $links = ['/' => 'Ana sayfa']; ?>
                        <?php foreach ($links as $href => $label): ?>
                            <a href="<?= e(url($href)) ?>" <?= $path === $href
                                || str_starts_with($path, $href . '/') ? 'aria-current="page"' : '' ?>>
                                <?= e($label) ?>
                            </a>
                        <?php endforeach; ?>
                        <form method="post" action="<?= e(url('/cikis')) ?>">
                            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                            <button type="submit">Çıkış</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= e(url('/giris')) ?>">Giriş</a>
                        <a href="<?= e(url('/kayit')) ?>">Kayıt</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>
        <main class="container">
            <?php if ($message = Session::pull('message')): ?>
                <p class="notice" role="status"><?= e($message) ?></p>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </body>
</html>
