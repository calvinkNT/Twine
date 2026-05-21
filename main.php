<?php

require_once "_config.php";

if (!$acceptance) {
    echo "Please accept the terms in _config.php and write your valid DMCA contact email.";
    exit;
}

define('BASE',       __DIR__);
define('UPLOAD_DIR', BASE . '/dynamic');
define('DB_PATH',    BASE . '/vine.db');

header('Content-Type: application/json');

set_exception_handler(function (Throwable $e) {
    //http_response_code(500);
    echo json_encode(['code' => 420, 'data' => (object)[], 'success' => false,
                      'error' => 'Internal server error']);
});

$method = $_SERVER['REQUEST_METHOD'];
$path   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts  = explode('/', $path);
$body   = file_get_contents('php://input');
$ct     = strtolower($_SERVER['CONTENT_TYPE'] ?? '');

$input = [];
if ($method === 'POST') {
    $input = strpos($ct, 'application/json') !== false
        ? (json_decode($body, true) ?? [])
        : $_POST;
} elseif ($method === 'PUT' || $method === 'DELETE') {
    if (strpos($ct, 'application/x-www-form-urlencoded') !== false) {
        parse_str($body, $input);
    } elseif (strpos($ct, 'application/json') !== false) {
        $input = json_decode($body, true) ?? [];
    }
}

require_once "db.php";
require_once "helpers.php";
require_once "serialisers.php";
require_once "routes.php";