<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Like;

final class SubjectRepository
{
    /** Find active subjects matching the exam type and query. */
    public function search(string $type, string $query): array
    {
        $sql = 'SELECT s.* FROM subjects s WHERE s.is_active = 1 AND s.exam_type = ? '
            . 'AND (s.name LIKE ? OR EXISTS (SELECT 1 FROM topics t '
            . 'WHERE t.subject_id=s.id AND t.is_active=1 AND t.name LIKE ?)) ORDER BY s.sort_order, s.id';
        $stmt = Database::connection()->prepare($sql);
        $pattern = '%' . Like::escape($query) . '%';
        $stmt->execute([$type, $pattern, $pattern]);
        return $stmt->fetchAll();
    }
}
