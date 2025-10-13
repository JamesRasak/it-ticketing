<?php
/**
 * @var array $tickets
 * @var array $filters
 * @var string $title
 */

// Helper function to get a Bootstrap badge class based on ticket status
function get_status_badge_class(string $status): string
{
    return match ($status) {
        'open' => 'bg-success',
        'in_progress' => 'bg-primary',
        'resolved' => 'bg-secondary',
        'closed' => 'bg-dark',
        default => 'bg-light text-dark',
    };
}

// Helper function to get a Bootstrap text class based on ticket priority
function get_priority_text_class(string $priority): string
{
    return match ($priority) {
        'low' => 'text-info',
        'medium' => 'text-success',
        'high' => 'text-warning',
        'urgent' => 'text-danger',
        default => 'text-dark',
    };
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">All Tickets</h1>
    <a href="/tickets/create" class="btn btn-primary">Create New Ticket</a>
</div>

<form method="get" action="/tickets" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label for="search" class="form-label">Search</label>
            <input type="search" id="search" name="search" class="form-control" placeholder="Search by subject or ID..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All</option>
                <?php foreach (['open', 'in_progress', 'resolved', 'closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="priority" class="form-label">Priority</label>
            <select id="priority" name="priority" class="form-select">
                <option value="">All</option>
                <?php foreach (['low', 'medium', 'high', 'urgent'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 1.05rem;">
            <thead class="table-light">
            <tr>
                <th scope="col">Ticket ID</th>
                <th scope="col" style="width: 40%;">Subject</th>
                <th scope="col">Requester</th>
                <th scope="col">Priority</th>
                <th scope="col">Status</th>
                <th scope="col">Last Updated</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No tickets match your criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <tr style="cursor: pointer;" onclick="window.location='/tickets/<?= (int)$ticket['id'] ?>';">
                        <td class="py-3">
                            <div class="fw-bold">#<?= (int)$ticket['id'] ?></div>
                        </td>                       
                        <td class="py-3">
                            <div class="fw-bold"><?= htmlspecialchars($ticket['title']) ?></div>
                        </td>
                        <td class="py-3"><?= htmlspecialchars($ticket['requester_name']) ?></td>
                        <td class="py-3"><span class="text-<?= get_priority_text_class($ticket['priority']) ?>"><?= htmlspecialchars(ucfirst($ticket['priority'])) ?></span></td>
                        <td class="py-3">
                            <span class="badge rounded-pill <?= get_status_badge_class($ticket['status']) ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $ticket['status']))) ?></span>
                        </td>
                        <td class="py-3"><?= htmlspecialchars(date('M d, Y', strtotime($ticket['last_activity_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
