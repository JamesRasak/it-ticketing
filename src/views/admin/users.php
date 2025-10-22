<?php

/** @var array $users */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Admin • Users</h1>
    <a href="/admin" class="btn btn-outline-secondary">Back to Admin</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role'] ?? 'user') ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($u['created_at'] ?? 'now'))) ?></td>
                            <td>
                                <form method="post" action="/admin/users/<?= (int)$u['id'] ?>/role" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
                                    <select name="role" class="form-select form-select-sm" style="width: 140px;">
                                        <?php foreach (['user', 'agent', 'admin'] as $r): ?>
                                            <option value="<?= $r ?>" <?= ($u['role'] ?? 'user') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>