<?php declare(strict_types=1); ?>
<h1>Katalog</h1>
<nav class="tabs" aria-label="Sınav türü">
    <a <?= $type === 'TYT' ? 'aria-current="page"' : '' ?> href="<?= e(url('/katalog?tur=TYT')) ?>">TYT</a>
    <a <?= $type === 'AYT' ? 'aria-current="page"' : '' ?> href="<?= e(url('/katalog?tur=AYT')) ?>">AYT</a>
</nav>
<form method="get" action="<?= e(url('/katalog')) ?>">
    <input type="hidden" name="tur" value="<?= e($type) ?>">
    <label for="q">Ders veya konu ara</label>
    <div class="search">
        <input id="q" name="q" value="<?= e($q) ?>">
        <button>Ara</button>
    </div>
</form>
<div class="catalog">
    <section>
        <h2>Dersler</h2>
        <?php if (!$subjects): ?>
            <p>Sonuç bulunamadı. <a href="<?= e(url('/katalog?tur=' . $type)) ?>">Filtreyi temizle</a></p>
        <?php else: ?>
            <ul>
                <?php foreach ($subjects as $subject): ?>
                    <li>
                        <a <?= $id === (int) $subject['id'] ? 'aria-current="page"' : '' ?>
                            href="<?= e(url('/katalog?' . http_build_query([
                                'tur' => $type, 'ders' => $subject['id'], 'q' => $q
                            ]))) ?>"><?= e($subject['name']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <section>
        <h2>Konular</h2>
        <?php if (!$id): ?>
            <p>Konuları görmek için bir ders seçin.</p>
        <?php elseif (!$topics): ?>
            <p>Sonuç bulunamadı. <a href="<?= e(url('/katalog?tur=' . $type)) ?>">Filtreyi temizle</a></p>
        <?php else: ?>
            <ul>
                <?php foreach ($topics as $topic): ?>
                    <li><?= e($topic['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
