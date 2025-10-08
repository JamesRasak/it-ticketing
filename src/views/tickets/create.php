<div class="row">
  <div class="col-md-8">
    <h3 class="mb-3">Create Ticket</h3>
    <form method="post" action="/tickets/create">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="6" required></textarea>
      </div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Category</label>
          <input type="text" name="category" class="form-control" placeholder="e.g., Network, Email">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Assign to (optional)</label>
          <select name="assignee_id" class="form-select">
            <option value="">— Unassigned —</option>
            <?php foreach ($agents as $a): ?>
              <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <button class="btn btn-primary">Create</button>
    </form>
  </div>
</div>
