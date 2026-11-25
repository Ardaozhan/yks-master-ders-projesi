<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\{Auth, PlanValidator};
use App\Repositories\PlanRepository;
use DateTimeImmutable;

final class PlanApiController
{
    /** List owned plans for a day or date range. */
    public function index(): void
    {
        $start = $_GET['tarih'] ?? $_GET['baslangic'] ?? date('Y-m-d');
        $end = $_GET['tarih'] ?? $_GET['bitis'] ?? $start;
        if (!$this->validDate($start) || !$this->validDate($end) || $start > $end) {
            $this->respond(400, null, 'Geçersiz tarih aralığı.');
            return;
        }
        $plans = (new PlanRepository())->forRange($this->userId(), $start, $end);
        $this->respond(200, array_map($this->format(...), $plans));
    }

    /** Return an owned plan. */
    public function show(int $id): void
    {
        $plan = $this->owned($id);
        if ($plan) {
            $this->respond(200, $this->format($plan));
        }
    }

    /** Create a plan. */
    public function create(): void
    {
        $this->save(null);
    }

    /** Replace an owned plan. */
    public function update(int $id): void
    {
        $plan = $this->owned($id);
        if ($plan) {
            $this->save($plan);
        }
    }

    /** Update the status of an owned plan. */
    public function status(int $id): void
    {
        $plan = $this->owned($id);
        if (!$plan || ($data = $this->body()) === null) {
            return;
        }
        $data += ['status' => '', 'actual_minutes' => '', 'note' => $plan['note'] ?? ''];
        foreach (['status', 'actual_minutes', 'note'] as $field) {
            if (!is_scalar($data[$field]) && $data[$field] !== null) {
                $this->respond(422, null, 'Alanları kontrol edin.', [$field => 'Geçersiz alan değeri.']);
                return;
            }
        }
        $data['note'] = (string) $data['note'];
        $data['actual_minutes'] = $data['actual_minutes'] ?? '';
        $errors = (new PlanValidator())->status($data);
        if ($errors) {
            $this->respond(422, null, 'Alanları kontrol edin.', $errors);
            return;
        }
        $repo = new PlanRepository();
        if (!$repo->changeStatus($id, $this->userId(), $data)) {
            $this->respond(404, null, 'Plan bulunamadı.');
            return;
        }
        $this->show($id);
    }

    /** Delete an owned plan. */
    public function delete(int $id): void
    {
        if (!(new PlanRepository())->delete($id, $this->userId())) {
            $this->respond(404, null, 'Plan bulunamadı.');
            return;
        }
        http_response_code(204);
    }

    private function save(?array $plan): void
    {
        $data = $this->body();
        if ($data === null) {
            return;
        }
        $data += ['date' => '', 'type' => '', 'subject_id' => '', 'topic_id' => '', 'target_minutes' => '', 'note' => ''];
        foreach ($data as $value) {
            if (!is_scalar($value) && $value !== null) {
                $this->respond(422, null, 'Alanları kontrol edin.', ['body' => 'Geçersiz alan değeri.']);
                return;
            }
        }
        $data['note'] = (string) $data['note'];
        $repo = new PlanRepository();
        $errors = (new PlanValidator())->plan($data);
        $same = $plan && (int) $plan['topic_id'] === (int) $data['topic_id']
            && (int) $plan['subject_id'] === (int) $data['subject_id'] && $plan['exam_type'] === $data['type'];
        if (!$same && !$repo->activeTopic((int) $data['topic_id'], (int) $data['subject_id'], (string) $data['type'])) {
            $errors['topic_id'] = 'Geçerli bir ders ve konu seçin.';
        }
        if ($errors) {
            $this->respond(422, null, 'Alanları kontrol edin.', $errors);
            return;
        }
        $id = $plan ? (int) $plan['id'] : $repo->create($this->userId(), $data);
        if ($plan && !$repo->update($id, $this->userId(), $data)) {
            $this->respond(404, null, 'Plan bulunamadı.');
            return;
        }
        if (!$plan) {
            header('Location: ' . url('/api/plans/' . $id));
        }
        $this->respond($plan ? 200 : 201, $this->format($repo->findForUser($id, $this->userId())));
    }

    private function body(): ?array
    {
        $type = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($type !== 'application/json') {
            $this->respond(415, null, 'JSON içerik türü gerekli.');
            return null;
        }
        try {
            $data = json_decode(file_get_contents('php://input'), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            $this->respond(400, null, 'Geçersiz JSON.');
            return null;
        }
        if (!is_object($data)) {
            $this->respond(400, null, 'JSON nesnesi gerekli.');
            return null;
        }
        return (array) $data;
    }

    private function owned(int $id): ?array
    {
        $plan = (new PlanRepository())->findForUser($id, $this->userId());
        if (!$plan) {
            $this->respond(404, null, 'Plan bulunamadı.');
        }
        return $plan;
    }

    private function userId(): int
    {
        return (int) Auth::user()['id'];
    }

    private function validDate(mixed $date): bool
    {
        if (!is_string($date)) {
            return false;
        }
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function format(array $plan): array
    {
        return ['id' => (int) $plan['id'], 'date' => $plan['plan_date'], 'exam_type' => $plan['exam_type'],
            'subject' => ['id' => (int) $plan['subject_id'], 'name' => $plan['subject_name']],
            'topic' => ['id' => (int) $plan['topic_id'], 'name' => $plan['topic_name']],
            'target_minutes' => (int) $plan['target_minutes'], 'status' => $plan['status'],
            'actual_minutes' => $plan['actual_minutes'] === null ? null : (int) $plan['actual_minutes'],
            'note' => $plan['note'], 'created_at' => $plan['created_at'], 'updated_at' => $plan['updated_at']];
    }

    private function respond(int $status, mixed $data, ?string $error = null, ?array $errors = null): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => $error === null ? 'ok' : 'error', 'data' => $data,
            'error' => $error, 'errors' => $errors], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
