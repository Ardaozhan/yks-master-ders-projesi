<?php
declare(strict_types=1);
use App\Core\Csrf;
?>
<section class="card">
    <h1>Kayıt ol</h1>
    <form method="post" action="<?= e(url('/kayit')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <label for="full_name">Ad soyad</label>
        <input id="full_name" name="full_name" maxlength="100" autocomplete="name"
            value="<?= e($old['full_name'] ?? '') ?>" aria-describedby="name-error" required>
        <span class="error" id="name-error"><?= e($errors['full_name'] ?? '') ?></span>
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" autocomplete="email"
            value="<?= e($old['email'] ?? '') ?>" aria-describedby="email-error" required>
        <span class="error" id="email-error"><?= e($errors['email'] ?? '') ?></span>
        <label for="password">Parola</label>
        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password"
            aria-describedby="password-error" required>
        <span class="error" id="password-error"><?= e($errors['password'] ?? '') ?></span>
        <label for="student_type">Öğrenci durumu</label>
        <select id="student_type" name="student_type" aria-describedby="type-error" required>
            <option value="">Seçin</option>
            <option value="grade12" <?= ($old['student_type'] ?? '') === 'grade12' ? 'selected' : '' ?>>
                12. sınıf
            </option>
            <option value="graduate" <?= ($old['student_type'] ?? '') === 'graduate' ? 'selected' : '' ?>>
                Mezun
            </option>
        </select>
        <span class="error" id="type-error"><?= e($errors['student_type'] ?? '') ?></span>
        <button type="submit">Kayıt ol</button>
    </form>
</section>
