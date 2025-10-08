<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Tickets</h3>
  <a href="/tickets/create" class="btn btn-success">New Ticket</a>
</div>
<table class="table table-striped align-middle">
  <thead><tr><th>#</th><th>Title</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Updated</th></tr></thead>
  <tbody>
  <?php foreach ($tickets as $t): ?>
    <tr>
      <td><a href="/tickets/<?= (int)$t['id'] ?>">#<?= (int)$t['id'] ?></a></td>
      <td><?= htmlspecialchars($t['title']) ?></td>
      <td><span class="badge text-bg-secondary"><?= htmlspecialchars($t['status']) ?></span></td>
      <td><?= htmlspecialchars($t['priority']) ?></td>
      <td><?= $t['assignee_id'] ? (int)$t['assignee_id'] : '-' ?></td>
      <td><?= htmlspecialchars($t['updated_at']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
