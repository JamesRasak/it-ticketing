<?php

declare(strict_types=1);

namespace App\Controllers;

use function App\{view, redirect, flash, auth_required, current_user, role_in, csrf_token, sanitize_filename, upload_dir, upload_url_base, send_email, app_url};
use App\Models\{Ticket, User};

class TicketController
{
    public function dashboard(): void
    {
        auth_required();
        $u = current_user();
        $filters = ['search' => $_GET['search'] ?? null, 'status' => $_GET['status'] ?? null, 'priority' => $_GET['priority'] ?? null,];
        $tickets = Ticket::forUser((int)$u['id'], $u['role'], $filters);
        view('tickets/index.php', ['title' => 'Dashboard', 'tickets' => $tickets, 'filters' => $filters]);
    }
    public function index(): void
    {
        $this->dashboard();
    }
    public function create(): void
    {
        auth_required();
        $agents = User::agents();
        view('tickets/create.php', ['title' => 'Create Ticket', 'agents' => $agents]);
    }
    public function store(): void
    {
        auth_required();
        $u = current_user();
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        $category = trim($_POST['category'] ?? '');
        $assignee_id = isset($_POST['assignee_id']) && $_POST['assignee_id'] !== '' ? (int)$_POST['assignee_id'] : null;
        if ($title === '' || $description === '') {
            flash('danger', 'Title and description are required.');
            redirect('/tickets/create');
        }
        $ticketId = Ticket::create(['title' => $title, 'description' => $description, 'priority' => $priority, 'category' => $category, 'requester_id' => (int)$u['id'], 'assignee_id' => $assignee_id]);

        // Handle optional file upload
        if (isset($_FILES['file']['name']) && is_array($_FILES['file']['name'])) {
            $fileCount = count($_FILES['file']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['file']['name'][$i],
                        'tmp_name' => $_FILES['file']['tmp_name'][$i],
                        'size' => $_FILES['file']['size'][$i],
                    ];
                    $max = (int)(getenv('MAX_UPLOAD_BYTES') ?: 5 * 1024 * 1024);
                    if ($file['size'] > $max) {
                        continue;
                    } // Skip file if too large
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'application/pdf' => 'pdf', 'text/plain' => 'txt'];
                    if (!isset($allowed[$mime])) {
                        continue;
                    } // Skip if unsupported type
                    $ext = $allowed[$mime];
                    $original = sanitize_filename($file['name']);
                    $random = bin2hex(random_bytes(16)) . ".$ext";
                    $dest = upload_dir() . '/' . $random;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        Ticket::addAttachment($ticketId, (int)$u['id'], $random, $original, $mime, (int)$file['size']);
                    }
                }
            }
        }

        // Send notification emails (non-blocking)
        try {
            $link = (app_url() ? app_url() : '') . '/tickets/' . $ticketId;
            // To requester (include ticket id and description)
            $subjectReq = '[Ticket #' . $ticketId . '] Ticket created: ' . $title;
            $bodyReq = "Hello " . ($u['name'] ?? 'User') . ",\n\nYour ticket has been created successfully.\n\nTicket ID: #" . $ticketId . "\nSubject: $title\nPriority: $priority\nCategory: " . ($category ?: 'None') . "\n\nDescription:\n" . ($description ?: '(no description)') . "\n\nView your ticket: $link\n\nThanks,\nIT Support";
            if (!empty($u['email'])) {
                send_email($u['email'], $subjectReq, $bodyReq);
            }
            // To assignee (if any)
            if ($assignee_id) {
                $assignee = User::find((int)$assignee_id);
                if ($assignee && !empty($assignee['email'])) {
                    $subjectAsg = '[Ticket #' . $ticketId . '] New assignment: ' . $title;
                    $bodyAsg = "Hello " . ($assignee['name'] ?? 'Agent') . ",\n\nA new ticket has been assigned to you.\n\nTicket ID: #" . $ticketId . "\nSubject: $title\nPriority: $priority\nCategory: " . ($category ?: 'None') . "\n\nDescription:\n" . ($description ?: '(no description)') . "\n\nOpen the ticket: $link\n\nThanks,\nIT Support";
                    send_email($assignee['email'], $subjectAsg, $bodyAsg);
                }
            }
        } catch (\Throwable $e) { /* swallow */
        }

        flash('success', 'Ticket created successfully.');
        redirect('/tickets/' . $ticketId);
    }
    public function show(int $id): void
    {
        auth_required();
        $u = current_user();
        $ticket = Ticket::find($id);
        if (!$ticket) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        if (!in_array($u['role'], ['agent', 'admin'], true) && (int)$ticket['requester_id'] !== (int)$u['id']) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $comments = Ticket::comments($id);
        $attachments = Ticket::attachments($id);
        $agents = User::agents();
        view('tickets/show.php', ['title' => 'Ticket #' . $id, 'ticket' => $ticket, 'comments' => $comments, 'attachments' => $attachments, 'agents' => $agents]);
    }
    public function comment(int $id): void
    {
        auth_required();
        $u = current_user();
        $ticket = Ticket::find($id);
        if (!$ticket) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        if (!in_array($u['role'], ['agent', 'admin'], true) && (int)$ticket['requester_id'] !== (int)$u['id']) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $body = trim($_POST['body'] ?? '');
        if ($body === '') {
            flash('danger', 'Comment cannot be empty');
            redirect('/tickets/' . $id);
        }
        Ticket::addComment($id, (int)$u['id'], $body);
        flash('success', 'Comment added');
        redirect('/tickets/' . $id);
    }
    public function updateStatus(int $id): void
    {
        auth_required();
        if (!role_in(['agent', 'admin'])) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $status = $_POST['status'] ?? 'open';
        Ticket::updateStatus($id, $status);
        flash('success', 'Status updated');
        redirect('/tickets/' . $id);
    }
    public function assign(int $id): void
    {
        auth_required();
        if (!role_in(['agent', 'admin'])) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $assignee_id = isset($_POST['assignee_id']) && $_POST['assignee_id'] !== '' ? (int)$_POST['assignee_id'] : null;
        Ticket::assign($id, $assignee_id);
        // Notifications on assignment update (non-blocking)
        try {
            $t = Ticket::find($id);
            if ($t) {
                $link = (app_url() ? app_url() : '') . '/tickets/' . $id;
                $requester = User::find((int)$t['requester_id']);
                $assignee = $assignee_id ? User::find((int)$assignee_id) : null;
                // Notify requester
                if ($requester && !empty($requester['email'])) {
                    $who = $assignee ? ($assignee['name'] ?? ('#' . $assignee_id)) : 'Unassigned';
                    $subjectReq = '[Ticket #' . $id . '] Assignment updated';
                    $bodyReq = "Hello " . ($requester['name'] ?? 'User') . ",\n\nYour ticket's assignment has been updated.\n\nTicket ID: #" . $id . "\nTitle: " . ($t['title'] ?? ('#' . $id)) . "\nAssignee: $who\n\nDescription:\n" . ($t['description'] ?? '(no description)') . "\n\nView: $link\n\nThanks,\nIT Support";
                    send_email($requester['email'], $subjectReq, $bodyReq);
                }
                // Notify new assignee (if any)
                if ($assignee && !empty($assignee['email'])) {
                    $subjectAsg = '[Ticket #' . $id . '] Assigned to you';
                    $bodyAsg = "Hello " . ($assignee['name'] ?? 'Agent') . ",\n\nYou have been assigned a ticket.\n\nTicket ID: #" . $id . "\nTitle: " . ($t['title'] ?? ('#' . $id)) . "\nPriority: " . ($t['priority'] ?? 'medium') . "\n\nDescription:\n" . ($t['description'] ?? '(no description)') . "\n\nOpen: $link\n\nThanks,\nIT Support";
                    send_email($assignee['email'], $subjectAsg, $bodyAsg);
                }
            }
        } catch (\Throwable $e) { /* swallow */
        }
        flash('success', 'Assignee updated');
        redirect('/tickets/' . $id);
    }
    public function attach(int $id): void
    {
        auth_required();
        $u = current_user();
        $ticket = Ticket::find($id);
        if (!$ticket) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        if (!in_array($u['role'], ['agent', 'admin'], true) && (int)$ticket['requester_id'] !== (int)$u['id']) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        if (empty($_FILES['file']['name'][0])) {
            flash('danger', 'No files were selected.');
            redirect('/tickets/' . $id);
        }

        $fileCount = count($_FILES['file']['name']);
        $uploadedCount = 0;
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['file']['name'][$i],
                    'tmp_name' => $_FILES['file']['tmp_name'][$i],
                    'size' => $_FILES['file']['size'][$i],
                ];
                $max = (int)(getenv('MAX_UPLOAD_BYTES') ?: 5 * 1024 * 1024);
                if ($file['size'] > $max) {
                    continue;
                }
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'application/pdf' => 'pdf', 'text/plain' => 'txt'];
                if (!isset($allowed[$mime])) {
                    continue;
                }
                $ext = $allowed[$mime];
                $original = sanitize_filename($file['name']);
                $random = bin2hex(random_bytes(16)) . ".$ext";
                $dest = upload_dir() . '/' . $random;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    Ticket::addAttachment($id, (int)$u['id'], $random, $original, $mime, (int)$file['size']);
                    $uploadedCount++;
                }
            }
        }

        flash('success', "$uploadedCount file(s) uploaded successfully.");
        redirect('/tickets/' . $id);
    }
}
