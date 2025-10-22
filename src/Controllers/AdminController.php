<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{Ticket, User};
use function App\{view, redirect, role_in, flash, csrf_token};

class AdminController
{
    public function index(): void
    {
        if (!role_in(['admin'])) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
        ];

        // As admin, show all tickets with optional filters
        $tickets = Ticket::forUser(0, 'admin', $filters);
        $agents = User::agents();

        view('admin/index.php', [
            'title' => 'Admin • Tickets',
            'tickets' => $tickets,
            'filters' => $filters,
            'agents' => $agents,
        ]);
    }

    /**
     * Show user management table
     */
    public function users(): void
    {
        if (!role_in(['admin'])) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $users = User::all();

        view('admin/users.php', [
            'title' => 'Admin • Users',
            'users' => $users,
        ]);
    }

    /**
     * Update a single user's role (POST)
     */
    public function updateUserRole(int $id): void
    {
        if (!role_in(['admin'])) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        // CSRF check performed by Router on POST
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['user', 'agent', 'admin'], true)) {
            flash('danger', 'Invalid role');
            redirect('/admin/users');
        }

        if (User::updateRole($id, $role)) {
            flash('success', 'User role updated');
        } else {
            flash('danger', 'Failed to update role');
        }
        redirect('/admin/users');
    }
}
