<div class="row">
  <div class="col-lg-8">
    <h3 class="mb-1">#<?= (int)$ticket['id'] ?> — <?= htmlspecialchars($ticket['title']) ?></h3>
    <div class="mb-3 text-muted">Status: <strong><?= htmlspecialchars($ticket['status']) ?></strong> · Priority: <strong><?= htmlspecialchars($ticket['priority']) ?></strong></div>

    <div class="card mb-3">
      <div class="card-header">Description</div>
      <div class="card-body"><pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($ticket['description']) ?></pre></div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Comments</div>
      <div class="card-body">
        <?php foreach ($comments as $c): ?>
          <div class="mb-3">
            <div class="small text-muted">By <?= htmlspecialchars($c['name']) ?> at <?= htmlspecialchars($c['created_at']) ?></div>
            <div><?= nl2br(htmlspecialchars($c['body'])) ?></div>
          </div>
          <hr>
        <?php endforeach; ?>
        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/comment">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <div class="mb-3">
            <textarea name="body" class="form-control" rows="3" placeholder="Add a comment..."></textarea>
          </div>
          <button class="btn btn-secondary">Comment</button>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Attachments</div>
      <div class="card-body">
        <?php if (!$attachments): ?>
          <div class="text-muted">No attachments.</div>
        <?php else: ?>
          <ul>
            <?php foreach ($attachments as $a): ?>
              <li><a href="<?= \App\upload_url_base() . '/' . htmlspecialchars($a['filename']) ?>" target="_blank"><?= htmlspecialchars($a['original_name']) ?></a> <span class="text-muted small">(<?= htmlspecialchars($a['mime_type']) ?>, <?= (int)$a['size'] ?> bytes)</span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/attach" enctype="multipart/form-data" class="mt-2">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <div class="input-group">
            <input type="file" name="file" class="form-control">
            <button class="btn btn-outline-primary">Upload</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Metadata</div>
      <div class="card-body">
        <div><strong>Requester:</strong> <?= htmlspecialchars($ticket['requester_name'] ?? ('#' . $ticket['requester_id'])) ?></div>
        <div><strong>Assignee:</strong> <?= htmlspecialchars($ticket['assignee_name'] ?? '-') ?></div>
        <div><strong>Category:</strong> <?= htmlspecialchars($ticket['category'] ?? '-') ?></div>
        <div><strong>Created:</strong> <?= htmlspecialchars($ticket['created_at']) ?></div>
        <div><strong>Updated:</strong> <?= htmlspecialchars($ticket['updated_at']) ?></div>
      </div>
    </div>

    <?php if (in_array($_SESSION['user']['role'], ['agent','admin'], true)): ?>
    <div class="card mb-3">
      <div class="card-header">Agent Actions</div>
      <div class="card-body">
        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/status" class="mb-2">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <label class="form-label">Status</label>
          <div class="input-group">
          <select name="status" class="form-select">
            <?php foreach (['open','in_progress','resolved','closed'] as $s): ?>
              <option value="<?= $s ?>" <?= $ticket['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-secondary">Update</button>
          </div>
        </form>

        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/assign">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <label class="form-label">Assign to</label>
          <div class="input-group">
          <select name="assignee_id" class="form-select">
            <option value="">— Unassigned —</option>
            <?php foreach ($agents as $a): ?>
              <option value="<?= (int)$a['id'] ?>" <?= ((int)($ticket['assignee_id']??0)===(int)$a['id'])?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-secondary">Assign</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
