<?php
declare(strict_types=1);
use App\Core\{Csrf, Duration};
?>
<h1>Bugün — <?= e($day->format('d.m.Y')) ?></h1>
<div class="date-row">
    <form action="<?= e(url('/bugun')) ?>" method="get" class="date-row">
        <label for="date">Tarih</label>
        <input id="date" type="date" name="tarih" value="<?= e($date) ?>" required>
        <button type="submit">Göster</button>
    </form>
    <a href="<?= e(url('/bugun?tarih=' . $day->modify('-1 day')->format('Y-m-d'))) ?>">Önceki gün</a>
    <a href="<?= e(url('/bugun?tarih=' . $day->modify('+1 day')->format('Y-m-d'))) ?>">Sonraki gün</a>
</div>
<p><a class="button" href="<?= e(url('/plan/yeni?tarih=' . $date)) ?>">Yeni plan ekle</a></p>
<?php if (!$plans): ?>
    <p>Bu gün için plan yok. <a href="<?= e(url('/plan/yeni?tarih=' . $date)) ?>">İlk planını ekle</a></p>
<?php endif; ?>
<?php foreach ($plans as $plan): ?>
    <article class="card" data-plan-id="<?= e($plan['id']) ?>">
        <h2><?= e($plan['subject_name']) ?> — <?= e($plan['topic_name']) ?></h2>
        <p><?= e($plan['exam_type']) ?> · Hedef: <?= e(Duration::formatMinutes((int) $plan['target_minutes'])) ?></p>
        <?php if ($plan['note']): ?>
            <p>Not: <?= e($plan['note']) ?></p>
        <?php endif; ?>
        <div class="actions">
        <a class="button secondary" href="<?= e(url('/plan/duzenle?id=' . $plan['id'])) ?>">Düzenle</a>
        <form method="post" action="<?= e(url('/plan/sil')) ?>" data-delete-plan
            onsubmit="return confirm(this.dataset.confirm)"
            data-confirm="<?= e($plan['topic_name'] . ' planı silinsin mi?') ?>">
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="id" value="<?= e($plan['id']) ?>">
            <button class="danger" type="submit">Sil</button>
        </form>
        </div>
    </article>
<?php endforeach; ?>
