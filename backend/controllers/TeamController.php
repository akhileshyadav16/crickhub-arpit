<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

function list_teams(): void
{
    $pdo = get_pdo();
    $stmt = $pdo->query('SELECT * FROM teams ORDER BY name ASC');
    $teams = $stmt->fetchAll();
    json_response(['data' => $teams]);
}

function get_team(string $id): void
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM teams WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $team = $stmt->fetch();

    if (!$team) {
        json_response(['error' => 'Team not found'], 404);
    }

    json_response(['data' => $team]);
}

function create_team(): void
{
    $payload = get_request_body();

    $errors = require_fields($payload, [
        'name' => 'string',
    ]);

    if ($errors) {
        respond_validation($errors);
    }

    $pdo = get_pdo();

    $stmt = $pdo->prepare('INSERT INTO teams (name, city, coach, captain, founded)
        VALUES (:name, :city, :coach, :captain, :founded)');

    $stmt->execute([
        ':name' => $payload['name'],
        ':city' => $payload['city'] ?? null,
        ':coach' => $payload['coach'] ?? null,
        ':captain' => $payload['captain'] ?? null,
        ':founded' => isset($payload['founded']) ? (int)$payload['founded'] : null,
    ]);

    $teamId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM teams WHERE id = :id');
    $stmt->execute([':id' => $teamId]);
    $team = $stmt->fetch();

    json_response(['data' => $team], 201);
}

function update_team(string $id): void
{
    $payload = get_request_body();
    $pdo = get_pdo();

    $updates = [];
    $params = [':id' => $id];

    $fields = [
        'name' => 'string',
        'city' => 'string',
        'coach' => 'string',
        'captain' => 'string',
        'founded' => 'int',
    ];

    foreach ($fields as $field => $type) {
        if (array_key_exists($field, $payload)) {
            $updates[] = "$field = :$field";
            if ($type === 'int') {
                $params[":$field"] = isset($payload[$field]) && $payload[$field] !== '' ? (int)$payload[$field] : null;
            } else {
                $params[":$field"] = $payload[$field] !== '' ? $payload[$field] : null;
            }
        }
    }

    if (empty($updates)) {
        json_response(['error' => 'No fields to update'], 400);
    }

    $updates[] = 'updated_at = CURRENT_TIMESTAMP';
    $sql = 'UPDATE teams SET ' . implode(', ', $updates) . ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'Team not found'], 404);
    }

    // Fetch updated team
    $stmt = $pdo->prepare('SELECT * FROM teams WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $team = $stmt->fetch();

    json_response(['data' => $team]);
}

function delete_team(string $id): void
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('DELETE FROM teams WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'Team not found'], 404);
    }

    json_response(['success' => true]);
}




