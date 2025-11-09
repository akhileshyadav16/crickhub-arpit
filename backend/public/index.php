<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PlayerController.php';
require_once __DIR__ . '/../controllers/TeamController.php';
require_once __DIR__ . '/../controllers/MatchController.php';

$config = require __DIR__ . '/../config.php';

// Log API request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'none';
error_log("[CrickHub API] {$method} {$uri} (Origin: {$origin})");

// Debug: Log full request details
if ($method === 'DELETE' || $method === 'PUT' || $method === 'PATCH') {
    error_log("[CrickHub API Debug] Method: {$method}, URI: {$uri}, Full REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
}

allow_cors($config['app']['allowed_origins']);

$routes = [];

function route(string $method, string $pattern, callable $handler): void
{
    global $routes;
    $routes[] = [
        'method' => $method,
        'pattern' => $pattern,
        'handler' => $handler,
    ];
}

route('GET', '#^/api/health$#', function ($matches) {
    $status = ['status' => 'ok', 'timestamp' => date('c')];
    
    // Test database connection
    try {
        $pdo = get_pdo();
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        $status['database'] = 'connected';
        $status['database_test'] = $result['test'] === 1 ? 'ok' : 'error';
    } catch (Exception $e) {
        $status['database'] = 'error';
        $status['database_error'] = $e->getMessage();
    }
    
    json_response($status);
});

route('GET', '#^/api/auth/me$#', fn ($matches) => me());
route('POST', '#^/api/auth/login$#', fn ($matches) => login());
route('POST', '#^/api/auth/logout$#', fn ($matches) => logout());

route('GET', '#^/api/players$#', fn ($matches) => list_players());
route('POST', '#^/api/players$#', function ($matches) {
    require_role('admin');
    create_player();
});
route('GET', '#^/api/players/([^/]+)$#', fn ($matches) => get_player($matches[1]));
route('PUT', '#^/api/players/([^/]+)$#', function ($matches) {
    require_role('admin');
    update_player($matches[1]);
});
route('PATCH', '#^/api/players/([^/]+)$#', function ($matches) {
    require_role('admin');
    update_player($matches[1]);
});
route('DELETE', '#^/api/players/([^/]+)$#', function ($matches) {
    error_log("[CrickHub] DELETE route matched, ID: " . $matches[1] . " (length: " . strlen($matches[1]) . ")");
    require_role('admin');
    delete_player($matches[1]);
});

route('GET', '#^/api/teams$#', fn ($matches) => list_teams());
route('POST', '#^/api/teams$#', function ($matches) {
    require_role('admin');
    create_team();
});
route('GET', '#^/api/teams/([^/]+)$#', fn ($matches) => get_team($matches[1]));
route('PUT', '#^/api/teams/([^/]+)$#', function ($matches) {
    require_role('admin');
    update_team($matches[1]);
});
route('PATCH', '#^/api/teams/([^/]+)$#', function ($matches) {
    require_role('admin');
    update_team($matches[1]);
});
route('DELETE', '#^/api/teams/([^/]+)$#', function ($matches) {
    require_role('admin');
    delete_team($matches[1]);
});

route('GET', '#^/api/matches$#', fn ($matches) => list_matches());
route('POST', '#^/api/matches$#', function ($matches) {
    require_role('admin');
    create_match();
});
route('GET', '#^/api/matches/([^/]+)$#', fn ($matches) => get_match($matches[1]));
route('PUT', '#^/api/matches/([^/]+)$#', function ($matches) {
    require_role('admin');
    update_match($matches[1]);
});
route('PATCH', '#^/api/matches/([^/]+)$#', function ($matches) {
    require_role('admin');
    update_match($matches[1]);
});
route('DELETE', '#^/api/matches/([^/]+)$#', function ($matches) {
    require_role('admin');
    delete_match($matches[1]);
});

// $method and $uri already defined above for logging

foreach ($routes as $route) {
    if (strcasecmp($method, $route['method']) !== 0) {
        continue;
    }

    if (preg_match($route['pattern'], $uri, $matches)) {
        error_log("[CrickHub API] Route matched: {$route['method']} {$route['pattern']} -> {$uri}");
        $handler = $route['handler'];
        $handler($matches);
        exit;
    }
}

// Log all attempted route matches for debugging
error_log("[CrickHub API] No route matched for {$method} {$uri}");
error_log("[CrickHub API] Available routes for {$method}: " . json_encode(array_map(function($r) {
    return $r['pattern'];
}, array_filter($routes, function($r) use ($method) {
    return strcasecmp($method, $r['method']) === 0;
}))));

json_response([
    'error' => 'Not Found',
    'path' => $uri,
    'method' => $method,
    'message' => "No route found for {$method} {$uri}"
], 404);

