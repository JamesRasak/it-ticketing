<?php
declare(strict_types=1);

namespace App;

use PDO;

function env_load(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k,$v] = array_map('trim', explode('=', $line, 2));
        $v = trim($v, "\"' ");
        if (!array_key_exists($k, $_ENV)) { putenv("$k=$v"); $_ENV[$k]=$v; $_SERVER[$k]=$v; }
    }
}

function db(): PDO {
    static $pdo=null; if ($pdo) return $pdo;
    $dsn = 'mysql:host='.(getenv('DB_HOST')?:'127.0.0.1').';port='.(getenv('DB_PORT')?:3306).';dbname='.(getenv('DB_DATABASE')?:'ticketing').';charset=utf8mb4';
    $pdo = new PDO($dsn, getenv('DB_USERNAME')?:'root', getenv('DB_PASSWORD')?:'', [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
    ]);
    return $pdo;
}

function view(string $template, array $data=[]): void {
    extract($data, EXTR_SKIP);
    $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
    require __DIR__.'/views/layout.php';
}

function redirect(string $path): void { header('Location: '.$path); exit; }

function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }

function csrf_check(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) { http_response_code(419); exit('CSRF token mismatch'); }
    }
}

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function auth_required(): void { if (!current_user()) redirect('/login'); }
function role_in(array $roles): bool { $u=current_user(); return $u && in_array($u['role'],$roles,true); }
function flash(string $type,string $msg): void { $_SESSION['flash']=['type'=>$type,'message'=>$msg]; }

function sanitize_filename(string $name): string { $name=preg_replace('/[^A-Za-z0-9_\-\.]/','_', $name); return substr($name,-200); }
function upload_dir(): string { $dir=getenv('UPLOAD_DIR')?:'uploads'; $path=__DIR__.'/../public/'.$dir; if(!is_dir($path)) @mkdir($path,0775,true); return $path; }
function upload_url_base(): string { $dir=getenv('UPLOAD_DIR')?:'uploads'; return '/'.trim($dir,'/'); }
