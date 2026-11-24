<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\{SubjectRepository, TopicRepository};

final class CatalogController
{
    /** Display filtered subjects and topics. */
    public function index(): void
    {
        $type = in_array($_GET['tur'] ?? '', ['TYT', 'AYT'], true) ? $_GET['tur'] : 'TYT';
        $q = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
        $id = filter_var($_GET['ders'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $subjects = (new SubjectRepository())->search($type, $q);
        $ids = array_map('intval', array_column($subjects, 'id'));
        if (!in_array($id, $ids, true)) {
            $id = 0;
        }
        $topics = $id ? (new TopicRepository())->forSubject($id, $q) : [];
        View::render('catalog/index', compact('type', 'q', 'id', 'subjects', 'topics'));
    }

    /** Return topics for the selected subject as JSON. */
    public function topicsJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = filter_var($_GET['ders'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || $id === null) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'data' => null, 'error' => 'Geçersiz ders', 'errors' => null],
                JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(
            ['status' => 'ok', 'data' => (new TopicRepository())->forSubject($id), 'error' => null, 'errors' => null],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    /** Return active subjects for an exam type. */
    public function subjectsJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $type = $_GET['tur'] ?? '';
        if (!in_array($type, ['TYT', 'AYT'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'data' => null, 'error' => 'Geçersiz sınav türü', 'errors' => null]);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => (new SubjectRepository())->search($type, ''),
            'error' => null, 'errors' => null], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
