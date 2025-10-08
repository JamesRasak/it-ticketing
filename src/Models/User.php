<?php
declare(strict_types=1);

namespace App\Models;
use function App\db;

class User {
    public static function findByEmail(string $email): ?array { $s=db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $s->execute([$email]); $r=$s->fetch(); return $r?:null; }
    public static function find(int $id): ?array { $s=db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]); $r=$s->fetch(); return $r?:null; }
    public static function create(string $name,string $email,string $password): int { $h=password_hash($password, PASSWORD_DEFAULT); $s=db()->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)'); $s->execute([$name,$email,$h]); return (int)db()->lastInsertId(); }
    public static function agents(): array { $s=db()->query("SELECT id,name,email FROM users WHERE role IN ('agent','admin') ORDER BY name"); return $s->fetchAll(); }
}
