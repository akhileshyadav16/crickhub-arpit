<?php
/**
 * Vercel Serverless Function Wrapper
 * Routes API requests to the backend
 * 
 * Vercel requires serverless functions in the /api directory.
 * This wrapper includes the actual backend code from /backend/public/index.php
 */

// Get the project root (one level up from api/)
$projectRoot = dirname(__DIR__);
$backendPath = $projectRoot . '/backend';

// Change working directory to backend for relative path resolution
chdir($backendPath);

// Include the actual backend index.php
// The backend code uses __DIR__ which will be api/, so we need to adjust paths
require_once $backendPath . '/public/index.php';

