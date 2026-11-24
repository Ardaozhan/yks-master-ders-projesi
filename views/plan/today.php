<?php
declare(strict_types=1);
use App\Core\{Csrf, Duration};
$labels = ['planned' => 'Planlandı', 'in_progress' => 'Devam ediyor', 'completed' => 'Tamamlandı'];
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
    <?php $current = (int) ($old['id'] ?? 0) === (int) $plan['id']; ?>
    <article class="card" data-plan-id="<?= e($plan['id']) ?>">
        <h2><?= e($plan['subject_name']) ?> — <?= e($plan['topic_name']) ?></h2>
        <p><?= e($plan['exam_type']) ?> · Hedef: <?= e(Duration::formatMinutes((int) $plan['target_minutes'])) ?> ·
            Gerçekleşen: <span data-actual><?= $plan['actual_minutes'] !== null
                ? e(Duration::formatMinutes((int) $plan['actual_minutes'])) : '—' ?></span></p>
        <p>Durum: <span data-badge class="badge badge-<?= e($plan['status']) ?>"><?= e($labels[$plan['status']]) ?></span></p>
        <p data-message aria-live="polite"></p>
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
        <details <?= $current ? 'open' : '' ?>>
        <summary>Durumu güncelle</summary>
        <form method="post" action="<?= e(url('/plan/durum')) ?>" data-status-form>
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="id" value="<?= e($plan['id']) ?>">
            <label for="status-<?= e($plan['id']) ?>">Durum</label>
            <select id="status-<?= e($plan['id']) ?>" name="status">
                <?php foreach ($labels as $value => $label): ?>
                    <option value="<?= e($value) ?>"
                        <?= ($current ? $old['status'] : $plan['status']) === $value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="error"><?= e($current ? ($errors['status'] ?? '') : '') ?></span>
            <label for="actual-<?= e($plan['id']) ?>">Gerçekleşen süre (dakika)</label>
            <input id="actual-<?= e($plan['id']) ?>" name="actual_minutes" type="number" min="1" max="600"
                value="<?= e($current ? $old['actual_minutes'] : ($plan['actual_minutes'] ?: '')) ?>">
            <span class="error"><?= e($current ? ($errors['actual_minutes'] ?? '') : '') ?></span>
            <label for="note-<?= e($plan['id']) ?>">Not</label>
            <textarea id="note-<?= e($plan['id']) ?>" name="note" maxlength="255"><?= e(
                $current ? $old['note'] : $plan['note']
            ) ?></textarea>
            <span class="error"><?= e($current ? ($errors['note'] ?? '') : '') ?></span>
            <button type="submit">Durumu kaydet</button>
        </form>
        </details>
    </article>
<?php endforeach; ?>
