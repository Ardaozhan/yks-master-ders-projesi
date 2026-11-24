<?php
declare(strict_types=1);
use App\Core\Csrf;
?>
<section class="card">
    <h1>Giriş yap</h1>
    <?php if (isset($errors['general'])): ?>
        <p class="error" role="alert"><?= e($errors['general']) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/giris')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" autocomplete="email"
            value="<?= e($old['email'] ?? '') ?>" required>
        <span class="error"><?= e($errors['email'] ?? '') ?></span>
        <label for="password">Parola</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <span class="error"><?= e($errors['password'] ?? '') ?></span>
        <button type="submit">Giriş yap</button>
    </form>
    <p>Hesabınız yok mu? <a href="<?= e(url('/kayit')) ?>">Kayıt olun</a>.</p>
</section>
