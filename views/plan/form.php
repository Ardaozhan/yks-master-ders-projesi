<?php
declare(strict_types=1);
use App\Core\Csrf;
?>
<section class="card">
    <h1><?= $plan ? 'Planı düzenle' : 'Yeni plan' ?></h1>
    <?php $formPath = $plan ? '/plan/duzenle?id=' . $plan['id'] . '&' : '/plan/yeni?'; ?>
    <p>Sınav türü: <a href="<?= e(url($formPath . 'tur=TYT&tarih=' . $date)) ?>">TYT</a> ·
        <a href="<?= e(url($formPath . 'tur=AYT&tarih=' . $date)) ?>">AYT</a></p>
    <form method="post" action="<?= e(url($plan ? '/plan/guncelle' : '/plan')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <?php if ($plan): ?>
            <input type="hidden" name="id" value="<?= e($plan['id']) ?>">
        <?php endif; ?>
        <label for="date">Tarih</label>
        <input id="date" type="date" name="date" value="<?= e($date) ?>" required>
        <span class="error"><?= e($errors['date'] ?? '') ?></span>
        <label for="type">Sınav türü</label>
        <select id="type" name="type" data-exam-type>
            <option value="TYT" <?= $type === 'TYT' ? 'selected' : '' ?>>TYT</option>
            <option value="AYT" <?= $type === 'AYT' ? 'selected' : '' ?>>AYT</option>
        </select>
        <label for="subject">Ders</label>
        <select id="subject" name="subject_id" data-topic-select="topic" required>
            <option value="">Ders seçin</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= e($subject['id']) ?>" <?= $subjectId === (int) $subject['id'] ? 'selected' : '' ?>>
                    <?= e($subject['name']) ?>
                </option>
            <?php endforeach; ?>
            <?php if ($plan && $subjectId === (int) $plan['subject_id'] &&
                !in_array($subjectId, array_map('intval', array_column($subjects, 'id')), true)): ?>
                <option value="<?= e($subjectId) ?>" selected><?= e($plan['subject_name']) ?> (pasif)</option>
            <?php endif; ?>
        </select>
        <span class="error"></span>
        <label for="topic">Konu</label>
        <select id="topic" name="topic_id" required>
            <option value="">Konu seçin</option>
            <?php $selectedTopic = (int) ($old['topic_id'] ?? (($plan && $plan['exam_type'] === $type)
                ? $plan['topic_id'] : 0)); ?>
            <?php foreach ($topics as $topic): ?>
                <option value="<?= e($topic['id']) ?>" <?= $selectedTopic === (int) $topic['id'] ? 'selected' : '' ?>>
                    <?= e($topic['name']) ?>
                </option>
            <?php endforeach; ?>
            <?php if ($plan && $selectedTopic === (int) $plan['topic_id'] &&
                !in_array($selectedTopic, array_map('intval', array_column($topics, 'id')), true)): ?>
                <option value="<?= e($selectedTopic) ?>" selected><?= e($plan['topic_name']) ?> (pasif)</option>
            <?php endif; ?>
        </select>
        <span class="error"><?= e($errors['topic_id'] ?? '') ?></span>
        <label for="target">Hedef süre (dakika)</label>
        <input id="target" type="number" name="target_minutes" min="5" max="600" required
            value="<?= e($old['target_minutes'] ?? ($plan['target_minutes'] ?? '')) ?>">
        <span class="error"><?= e($errors['target_minutes'] ?? '') ?></span>
        <label for="note">Not</label>
        <textarea id="note" name="note" maxlength="255"><?= e($old['note'] ?? ($plan['note'] ?? '')) ?></textarea>
        <span class="error"><?= e($errors['note'] ?? '') ?></span>
        <button type="submit">Kaydet</button>
    </form>
</section>
