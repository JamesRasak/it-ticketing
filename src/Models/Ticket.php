<?php
declare(strict_types=1);

namespace App\Models;
use function App\db;

class Ticket {
    public static function create(array $d): int { $s=db()->prepare('INSERT INTO tickets (title,description,priority,category,requester_id,assignee_id) VALUES (:title,:description,:priority,:category,:requester_id,:assignee_id)'); $s->execute([':title'=>$d['title'],':description'=>$d['description'],':priority'=>$d['priority'],':category'=>$d['category']?:null,':requester_id'=>$d['requester_id'],':assignee_id'=>$d['assignee_id']?:null]); return (int)db()->lastInsertId(); }
    public static function find(int $id): ?array { $s=db()->prepare('SELECT t.*, r.name requester_name, a.name assignee_name FROM tickets t LEFT JOIN users r ON r.id=t.requester_id LEFT JOIN users a ON a.id=t.assignee_id WHERE t.id=?'); $s->execute([$id]); $r=$s->fetch(); return $r?:null; }
    public static function forUser(int $userId,string $role): array { if (in_array($role,['agent','admin'],true)){ $s=db()->query('SELECT * FROM tickets ORDER BY updated_at DESC'); return $s->fetchAll(); } $s=db()->prepare('SELECT * FROM tickets WHERE requester_id=? ORDER BY updated_at DESC'); $s->execute([$userId]); return $s->fetchAll(); }
    public static function addComment(int $ticketId,int $userId,string $body): void { $s=db()->prepare('INSERT INTO ticket_comments (ticket_id,user_id,body) VALUES (?,?,?)'); $s->execute([$ticketId,$userId,$body]); }
    public static function comments(int $ticketId): array { $s=db()->prepare('SELECT c.*, u.name FROM ticket_comments c JOIN users u ON u.id=c.user_id WHERE c.ticket_id=? ORDER BY c.created_at ASC'); $s->execute([$ticketId]); return $s->fetchAll(); }
    public static function attachments(int $ticketId): array { $s=db()->prepare('SELECT * FROM ticket_attachments WHERE ticket_id=? ORDER BY created_at ASC'); $s->execute([$ticketId]); return $s->fetchAll(); }
    public static function updateStatus(int $ticketId,string $status): void { $allowed=['open','in_progress','resolved','closed']; if(!in_array($status,$allowed,true)) return; $s=db()->prepare("UPDATE tickets SET status=?, closed_at = IF(? IN ('resolved','closed'), NOW(), NULL) WHERE id=?"); $s->execute([$status,$status,$ticketId]); }
    public static function assign(int $ticketId,?int $assigneeId): void { $s=db()->prepare('UPDATE tickets SET assignee_id=? WHERE id=?'); $s->execute([$assigneeId,$ticketId]); }
    public static function addAttachment(int $ticketId,int $userId,string $filename,string $original,string $mime,int $size): void { $s=db()->prepare('INSERT INTO ticket_attachments (ticket_id,user_id,filename,original_name,mime_type,size) VALUES (?,?,?,?,?,?)'); $s->execute([$ticketId,$userId,$filename,$original,$mime,$size]); }
}
