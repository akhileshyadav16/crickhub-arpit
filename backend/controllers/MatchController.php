<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

function list_matches(): void
{
    $pdo = get_pdo();

    $search = trim($_GET['search'] ?? '');

    $sql = 'SELECT m.*, ht.name AS home_team_name, at.name AS away_team_name
            FROM matches m
            LEFT JOIN teams ht ON m.home_team_id = ht.id
            LEFT JOIN teams at ON m.away_team_id = at.id';

    $params = [];
    if ($search !== '') {
        $sql .= ' WHERE LOWER(m.title) LIKE :search OR LOWER(ht.name) LIKE :search OR LOWER(at.name) LIKE :search';
        $params[':search'] = '%' . strtolower($search) . '%';
    }

    $sql .= ' ORDER BY m.match_date DESC, m.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $matches = $stmt->fetchAll();

    json_response(['data' => $matches]);
}

function get_match(string $id): void
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT m.*, ht.name AS home_team_name, at.name AS away_team_name FROM matches m
        LEFT JOIN teams ht ON m.home_team_id = ht.id
        LEFT JOIN teams at ON m.away_team_id = at.id
        WHERE m.id = :id');
    $stmt->execute([':id' => $id]);

    $match = $stmt->fetch();

    if (!$match) {
        json_response(['error' => 'Match not found'], 404);
    }

    json_response(['data' => $match]);
}

function create_match(): void
{
    $payload = get_request_body();

    $errors = require_fields($payload, [
        'title' => 'string',
        'status' => 'string',
    ]);

    if ($errors) {
        respond_validation($errors);
    }

    $pdo = get_pdo();

    $stmt = $pdo->prepare('INSERT INTO matches (title, home_team_id, away_team_id, venue, match_date, status, result, summary)
        VALUES (:title, :home_team_id, :away_team_id, :venue, :match_date, :status, :result, :summary)');

    $stmt->execute([
        ':title' => $payload['title'],
        ':home_team_id' => $payload['home_team_id'] ?? null,
        ':away_team_id' => $payload['away_team_id'] ?? null,
        ':venue' => $payload['venue'] ?? null,
        ':match_date' => $payload['match_date'] ?? null,
        ':status' => $payload['status'],
        ':result' => $payload['result'] ?? null,
        ':summary' => $payload['summary'] ?? null,
    ]);

    $matchId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT m.*, ht.name AS home_team_name, at.name AS away_team_name FROM matches m
        LEFT JOIN teams ht ON m.home_team_id = ht.id
        LEFT JOIN teams at ON m.away_team_id = at.id
        WHERE m.id = :id');
    $stmt->execute([':id' => $matchId]);
    $match = $stmt->fetch();

    json_response(['data' => $match], 201);
}

function update_match(string $id): void
{
    $payload = get_request_body();
    $pdo = get_pdo();

    $updates = [];
    $params = [':id' => $id];

    $fields = [
        'title' => 'string',
        'home_team_id' => 'uuid',
        'away_team_id' => 'uuid',
        'venue' => 'string',
        'match_date' => 'date',
        'status' => 'string',
        'result' => 'string',
        'summary' => 'text',
    ];

    foreach ($fields as $field => $type) {
        if (array_key_exists($field, $payload)) {
            $updates[] = "$field = :$field";
            if ($type === 'uuid') {
                $params[":$field"] = ($payload[$field] !== '' && $payload[$field] !== null) ? $payload[$field] : null;
            } else {
                $params[":$field"] = ($payload[$field] !== '' && $payload[$field] !== null) ? $payload[$field] : null;
            }
        }
    }

    if (empty($updates)) {
        json_response(['error' => 'No fields to update'], 400);
    }

    $updates[] = 'updated_at = CURRENT_TIMESTAMP';
    $sql = 'UPDATE matches SET ' . implode(', ', $updates) . ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'Match not found'], 404);
    }

    // Fetch updated match
    $stmt = $pdo->prepare('SELECT m.*, ht.name AS home_team_name, at.name AS away_team_name FROM matches m
        LEFT JOIN teams ht ON m.home_team_id = ht.id
        LEFT JOIN teams at ON m.away_team_id = at.id
        WHERE m.id = :id');
    $stmt->execute([':id' => $id]);
    $match = $stmt->fetch();

    json_response(['data' => $match]);
}

function delete_match(string $id): void
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('DELETE FROM matches WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'Match not found'], 404);
    }

    json_response(['success' => true]);
}




