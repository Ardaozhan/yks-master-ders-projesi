<?php declare(strict_types=1); ?>
<?php if ($user = \App\Core\Auth::user()): ?>
<section>
    <h1>Hoş geldin, <?= e($user['full_name']) ?>.</h1>
    <p>Çalışma planı özellikleri yakında eklenecek.</p>
</section>
<?php else: ?>
<section>
    <h1>YKS MASTER</h1>
    <p>Çalışmalarınızı düzenlemek için başlayın.</p>
    <a class="button" href="<?= e(url('/kayit')) ?>">Kayıt ol</a>
    <a href="<?= e(url('/giris')) ?>">Giriş yap</a>
</section>
<?php endif; ?>
