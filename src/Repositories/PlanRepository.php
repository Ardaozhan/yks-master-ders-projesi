<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use Throwable;

final class PlanRepository
{
    /** Find plans owned by a student within a date range. */
    public function forRange(int $userId, string $start, string $end): array
    {
        $stmt = Database::connection()->prepare($this->select()
            . ' WHERE p.user_id = ? AND p.plan_date BETWEEN ? AND ? ORDER BY p.plan_date, p.id');
        $stmt->execute([$userId, $start, $end]);
        return $stmt->fetchAll();
    }
    /** Find plans owned by a student for a date. */
    public function forDate(int $userId, string $date): array
    {
        $sql = $this->select() . ' WHERE p.user_id = ? AND p.plan_date = ? ORDER BY p.id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    }

    /** Find one plan only when owned by the student. */
    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare($this->select() . ' WHERE p.id = ? AND p.user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    /** Check whether an active topic belongs to an active subject and exam type. */
    public function activeTopic(int $topicId, int $subjectId, string $type): bool
    {
        $sql = 'SELECT t.id FROM topics t JOIN subjects s ON s.id = t.subject_id '
            . 'WHERE t.id = ? AND s.id = ? AND s.exam_type = ? AND t.is_active = 1 AND s.is_active = 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$topicId, $subjectId, $type]);
        return (bool) $stmt->fetch();
    }

    /** Create a student's plan and optional note atomically. */
    public function create(int $userId, array $data): int
    {
        $db = Database::connection();
        $nested = $this->begin();
        try {
            $sql = 'INSERT INTO study_plans (user_id, topic_id, plan_date, target_minutes, note) VALUES (?, ?, ?, ?, ?)';
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $data['topic_id'], $data['date'], $data['target_minutes'], $data['note']]);
            $id = (int) $db->lastInsertId();
            $this->commit($nested);
            return $id;
        } catch (Throwable $error) {
            $this->rollback($nested);
            throw $error;
        }
    }

    /** Update an owned plan and its note atomically. */
    public function update(int $id, int $userId, array $data): int
    {
        $db = Database::connection();
        $nested = $this->begin();
        try {
            $plan = $this->lock($id, $userId);
            if (!$plan) {
                $this->rollback($nested);
                return 0;
            }
            $sql = 'UPDATE study_plans SET topic_id=?, plan_date=?, target_minutes=?, note=?, '
                . 'updated_at=CURRENT_TIMESTAMP WHERE id=? AND user_id=?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$data['topic_id'], $data['date'], $data['target_minutes'], $data['note'], $id, $userId]);
            if ($plan['status'] !== 'planned') {
                $this->record($id, $userId, $plan['actual_minutes'], $data['note']);
            }
            $this->commit($nested);
            return 1;
        } catch (Throwable $error) {
            $this->rollback($nested);
            throw $error;
        }
    }

    /** Update an owned plan's status and record atomically. */
    public function changeStatus(int $id, int $userId, array $data): int
    {
        $db = Database::connection();
        $nested = $this->begin();
        try {
            if (!$this->lock($id, $userId)) {
                $this->rollback($nested);
                return 0;
            }
            $sql = 'UPDATE study_plans SET status=?, note=?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND user_id=?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$data['status'], $data['note'], $id, $userId]);
            if ($data['status'] === 'planned') {
                $sql = 'DELETE r FROM study_records r JOIN study_plans p ON p.id=r.plan_id WHERE p.id=? AND p.user_id=?';
                $db->prepare($sql)->execute([$id, $userId]);
            } else {
                $actual = $data['actual_minutes'] === '' ? null : (int) $data['actual_minutes'];
                $this->record($id, $userId, $actual, $data['note']);
            }
            $this->commit($nested);
            return 1;
        } catch (Throwable $error) {
            $this->rollback($nested);
            throw $error;
        }
    }

    /** Delete an owned plan and its record atomically. */
    public function delete(int $id, int $userId): int
    {
        $db = Database::connection();
        $nested = $this->begin();
        try {
            if (!$this->lock($id, $userId)) {
                $this->rollback($nested);
                return 0;
            }
            $sql = 'DELETE r FROM study_records r JOIN study_plans p ON p.id=r.plan_id WHERE p.id=? AND p.user_id=?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$id, $userId]);
            $stmt = $db->prepare('DELETE FROM study_plans WHERE id=? AND user_id=?');
            $stmt->execute([$id, $userId]);
            $count = $stmt->rowCount();
            $this->commit($nested);
            return $count;
        } catch (Throwable $error) {
            $this->rollback($nested);
            throw $error;
        }
    }

    private function select(): string
    {
        return 'SELECT p.*, t.name AS topic_name, s.name AS subject_name, s.exam_type, s.id AS subject_id, '
            . 'r.actual_minutes, COALESCE(r.note, p.note) AS note FROM study_plans p JOIN topics t ON t.id=p.topic_id '
            . 'JOIN subjects s ON s.id=t.subject_id LEFT JOIN study_records r ON r.plan_id=p.id';
    }

    private function lock(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare($this->select() . ' WHERE p.id=? AND p.user_id=? FOR UPDATE');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    private function record(int $id, int $userId, ?int $actual, string $note): void
    {
        $sql = 'INSERT INTO study_records (plan_id, actual_minutes, studied_on, note) '
            . 'SELECT p.id, ?, p.plan_date, ? FROM study_plans p WHERE p.id=? AND p.user_id=? '
            . 'ON DUPLICATE KEY UPDATE actual_minutes=VALUES(actual_minutes), studied_on=VALUES(studied_on), '
            . 'note=VALUES(note), updated_at=CURRENT_TIMESTAMP';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$actual, $note === '' ? null : $note, $id, $userId]);
    }

    private function begin(): bool
    {
        $db = Database::connection();
        if ($db->inTransaction()) {
            $db->exec('SAVEPOINT plan_write');
            return true;
        }
        $db->beginTransaction();
        return false;
    }

    private function commit(bool $nested): void
    {
        $db = Database::connection();
        if ($nested) {
            $db->exec('RELEASE SAVEPOINT plan_write');
        } else {
            $db->commit();
        }
    }

    private function rollback(bool $nested): void
    {
        $db = Database::connection();
        if ($nested) {
            $db->exec('ROLLBACK TO SAVEPOINT plan_write');
        } else {
            $db->rollBack();
        }
    }
}
