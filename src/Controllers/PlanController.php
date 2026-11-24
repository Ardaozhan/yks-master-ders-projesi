<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, PlanValidator, Session, View};
use App\Repositories\{PlanRepository, SubjectRepository, TopicRepository};
use DateTimeImmutable;

final class PlanController
{
    /** Display plans for the requested day. */
    public function today(): void
    {
        $date = is_string($_GET['tarih'] ?? null) ? $_GET['tarih'] : date('Y-m-d');
        $validator = new PlanValidator();
        if (isset($validator->plan(['date' => $date, 'target_minutes' => 5])['date'])) {
            $date = date('Y-m-d');
        }
        $day = new DateTimeImmutable($date);
        $plans = (new PlanRepository())->forDate($this->userId(), $date);
        View::render('plan/today', compact('date', 'day', 'plans'));
    }

    /** Display the new plan form. */
    public function create(): void
    {
        $this->form(null);
    }

    /** Create a plan for the current student. */
    public function store(): void
    {
        $this->save(null);
    }

    /** Display an owned plan for editing. */
    public function edit(): void
    {
        $plan = $this->owned($this->id($_GET));
        $this->form($plan);
    }

    /** Update an owned plan. */
    public function update(): void
    {
        $this->save($this->owned($this->id($_POST)));
    }

    /** Delete an owned plan. */
    public function delete(): void
    {
        $plan = $this->owned($this->id($_POST));
        if (!(new PlanRepository())->delete((int) $plan['id'], $this->userId())) {
            $this->notFound();
        }
        Session::flash('message', 'Plan silindi.');
        redirect('/bugun?tarih=' . rawurlencode($plan['plan_date']));
    }

    private function save(?array $plan): void
    {
        $data = [
            'date' => input('date'), 'type' => input('type'), 'subject_id' => input('subject_id'),
            'topic_id' => input('topic_id'), 'target_minutes' => input('target_minutes'), 'note' => input('note'),
        ];
        $errors = (new PlanValidator())->plan($data);
        $repo = new PlanRepository();
        $sameTopic = $plan && (int) $plan['topic_id'] === (int) $data['topic_id']
            && (int) $plan['subject_id'] === (int) $data['subject_id'] && $plan['exam_type'] === $data['type'];
        if (!$sameTopic && !$repo->activeTopic((int) $data['topic_id'], (int) $data['subject_id'], $data['type'])) {
            $errors['topic_id'] = 'Geçerli bir ders ve konu seçin.';
        }
        if ($errors) {
            Session::flash('plan_errors', $errors);
            Session::flash('plan_old', $data);
            redirect($plan ? '/plan/duzenle?id=' . $plan['id'] : '/plan/yeni');
        }
        if ($plan && !$repo->update((int) $plan['id'], $this->userId(), $data)) {
            $this->notFound();
        }
        if (!$plan) {
            $repo->create($this->userId(), $data);
        }
        Session::flash('message', $plan ? 'Plan güncellendi.' : 'Plan oluşturuldu.');
        redirect('/bugun?tarih=' . rawurlencode($data['date']));
    }

    private function form(?array $plan): void
    {
        $old = Session::pull('plan_old', []);
        $errors = Session::pull('plan_errors', []);
        $type = $old['type'] ?? (is_string($_GET['tur'] ?? null) ? $_GET['tur'] : ($plan['exam_type'] ?? 'TYT'));
        $type = in_array($type, ['TYT', 'AYT'], true) ? $type : 'TYT';
        $subjectId = (int) ($old['subject_id'] ?? (($plan && $plan['exam_type'] === $type) ? $plan['subject_id'] : 0));
        $subjects = (new SubjectRepository())->search($type, '');
        $topics = $subjectId ? (new TopicRepository())->forSubject($subjectId) : [];
        $requestedDate = is_string($_GET['tarih'] ?? null) ? $_GET['tarih'] : date('Y-m-d');
        $date = $old['date'] ?? ($plan['plan_date'] ?? $requestedDate);
        View::render('plan/form', compact('plan', 'old', 'errors', 'type', 'subjects', 'topics', 'subjectId', 'date'));
    }

    private function id(array $source): int
    {
        return filter_var($source['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
    }

    private function userId(): int
    {
        return (int) Auth::user()['id'];
    }

    private function owned(int $id): array
    {
        $plan = (new PlanRepository())->findForUser($id, $this->userId());
        if (!$plan) {
            $this->notFound();
        }
        return $plan;
    }

    private function notFound(): never
    {
        View::render('errors/404', [], 404);
        exit;
    }
}
