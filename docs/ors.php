<?php
// Safe CORS include (no output)
$allowed_origin = 'https://manahilibrahim.github.io';
header("Access-Control-Allow-Origin: $allowed_origin");
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// Cross-site cookies
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>true,'httponly'=>true,'samesite'=>'None']);
if (session_status() === PHP_SESSION_NONE) session_start();
