<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\Like;

final class TopicRepository
{
    /** Find active topics belonging to a subject. */
    public function forSubject(int $id, string $query = ''): array
    {
        $sql = 'SELECT t.id, t.subject_id, t.name, t.sort_order FROM topics t '
            . 'JOIN subjects s ON s.id=t.subject_id WHERE t.subject_id=? '
            . 'AND t.is_active=1 AND s.is_active=1 AND (t.name LIKE ? OR s.name LIKE ?) ORDER BY t.sort_order,t.id';
        $stmt = Database::connection()->prepare($sql);
        $pattern = '%' . Like::escape($query) . '%';
        $stmt->execute([$id, $pattern, $pattern]);
        return $stmt->fetchAll();
    }
}
