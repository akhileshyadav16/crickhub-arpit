<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

function list_players(): void
{
    $pdo = get_pdo();

    $search = trim($_GET['search'] ?? '');
    $teamId = $_GET['team_id'] ?? null;

    $sql = 'SELECT p.*, t.name AS team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id';
    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(LOWER(p.name) LIKE :search OR LOWER(t.name) LIKE :search)';
        $params[':search'] = '%' . strtolower($search) . '%';
    }

    if ($teamId) {
        $conditions[] = 'p.team_id = :teamId';
        $params[':teamId'] = $teamId;
    }

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY p.name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $players = $stmt->fetchAll();

    json_response(['data' => $players]);
}

function get_player(string $id): void
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT p.*, t.name AS team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = :id');
    $stmt->execute([':id' => $id]);

    $player = $stmt->fetch();

    if (!$player) {
        json_response(['error' => 'Player not found'], 404);
    }

    json_response(['data' => $player]);
}

function create_player(): void
{
    $payload = get_request_body();

    $errors = require_fields($payload, [
        'name' => 'string',
        'role' => 'string',
    ]);

    if ($errors) {
        respond_validation($errors);
    }

    $pdo = get_pdo();

    $teamId = $payload['team_id'] ?? null;

    if (is_string($teamId) && trim($teamId) === '') {
        $teamId = null;
    }

    if ($teamId) {
        $teamStmt = $pdo->prepare('SELECT id FROM teams WHERE id = :id');
        $teamStmt->execute([':id' => $teamId]);
        if (!$teamStmt->fetchColumn()) {
            respond_validation(['team_id' => 'Team not found.']);
        }
    }

    $stmt = $pdo->prepare('INSERT INTO players (name, role, team_id, matches, runs, average, strike_rate, hundreds, fifties, fours, sixes, bio)
        VALUES (:name, :role, :team_id, :matches, :runs, :average, :strike_rate, :hundreds, :fifties, :fours, :sixes, :bio)');

    $stmt->execute([
        ':name' => $payload['name'],
        ':role' => $payload['role'],
        ':team_id' => $teamId,
        ':matches' => (int)($payload['matches'] ?? 0),
        ':runs' => (int)($payload['runs'] ?? 0),
        ':average' => (float)($payload['average'] ?? 0),
        ':strike_rate' => (float)($payload['strike_rate'] ?? 0),
        ':hundreds' => (int)($payload['hundreds'] ?? 0),
        ':fifties' => (int)($payload['fifties'] ?? 0),
        ':fours' => (int)($payload['fours'] ?? 0),
        ':sixes' => (int)($payload['sixes'] ?? 0),
        ':bio' => $payload['bio'] ?? null,
    ]);

    $playerId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT p.*, t.name AS team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = :id');
    $stmt->execute([':id' => $playerId]);
    $player = $stmt->fetch();

    json_response(['data' => $player], 201);
}

function update_player(string $id): void
{
    $payload = get_request_body();
    $pdo = get_pdo();

    // Validate team_id if provided
    if (array_key_exists('team_id', $payload)) {
        if (is_string($payload['team_id']) && trim($payload['team_id']) === '') {
            $payload['team_id'] = null;
        } elseif ($payload['team_id']) {
            $teamStmt = $pdo->prepare('SELECT id FROM teams WHERE id = :id');
            $teamStmt->execute([':id' => $payload['team_id']]);
            if (!$teamStmt->fetchColumn()) {
                respond_validation(['team_id' => 'Team not found.']);
            }
        }
    }

    // Build dynamic UPDATE query based on provided fields
    $updates = [];
    $params = [':id' => $id];

    $fields = [
        'name' => 'string',
        'role' => 'string',
        'team_id' => 'uuid',
        'matches' => 'int',
        'runs' => 'int',
        'average' => 'float',
        'strike_rate' => 'float',
        'hundreds' => 'int',
        'fifties' => 'int',
        'fours' => 'int',
        'sixes' => 'int',
        'bio' => 'text',
    ];

    foreach ($fields as $field => $type) {
        if (array_key_exists($field, $payload)) {
            $updates[] = "$field = :$field";
            if ($type === 'int') {
                $params[":$field"] = isset($payload[$field]) ? (int)$payload[$field] : null;
            } elseif ($type === 'float') {
                $params[":$field"] = isset($payload[$field]) ? (float)$payload[$field] : null;
            } else {
                $params[":$field"] = $payload[$field];
            }
        }
    }

    if (empty($updates)) {
        json_response(['error' => 'No fields to update'], 400);
    }

    $updates[] = 'updated_at = CURRENT_TIMESTAMP';
    $sql = 'UPDATE players SET ' . implode(', ', $updates) . ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => 'Player not found'], 404);
    }

    // Fetch updated player
    $stmt = $pdo->prepare('SELECT p.*, t.name AS team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id WHERE p.id = :id');
    $stmt->execute([':id' => $id]);
    $player = $stmt->fetch();

    json_response(['data' => $player]);
}

function delete_player(string $id): void
{
    error_log("[CrickHub] delete_player called with ID: {$id}");
    $pdo = get_pdo();
    
    // First check if player exists
    $checkStmt = $pdo->prepare('SELECT id FROM players WHERE id = :id');
    $checkStmt->execute([':id' => $id]);
    $exists = $checkStmt->fetch();
    
    if (!$exists) {
        error_log("[CrickHub] Player not found with ID: {$id}");
        json_response(['error' => 'Player not found'], 404);
    }
    
    $stmt = $pdo->prepare('DELETE FROM players WHERE id = :id');
    $stmt->execute([':id' => $id]);
    
    $deleted = $stmt->rowCount();
    error_log("[CrickHub] Delete executed, rows affected: {$deleted}");

    if ($deleted === 0) {
        json_response(['error' => 'Failed to delete player'], 500);
    }

    json_response(['success' => true, 'message' => 'Player deleted successfully']);
}

