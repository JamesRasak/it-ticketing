<?php
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
<div class="row">
  <div class="col-lg-8">
    <h2 class="mb-1"><?= htmlspecialchars($ticket['title']) ?></h2>
    <div class="mb-3 text-muted">Ticket ID: #<?= (int)$ticket['id'] ?> &middot; Status: <span class="badge rounded-pill <?= get_status_badge_class($ticket['status']) ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $ticket['status']))) ?></span> &middot; Priority: <strong class="<?= get_priority_text_class($ticket['priority']) ?>"><?= htmlspecialchars(ucfirst($ticket['priority'])) ?></strong></div>

    <div class="card mb-4">
      <style>
          #drop-zone-show {
              border: 2px dashed #ccc;
              border-radius: 0.375rem;
              padding: 2rem;
              text-align: center;
              cursor: pointer;
              transition: border-color 0.2s, background-color 0.2s;
          }
          #drop-zone-show.drag-over {
              border-color: var(--bs-primary);
              background-color: var(--bs-primary-bg-subtle);
          }
      </style>
      <div class="card-header">Description</div>
      <div class="card-body"><pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($ticket['description']) ?></pre></div>
    </div>

    <div class="card mb-4">
      <div class="card-header">Comments</div>
      <div class="card-body" style="max-height: 500px; overflow-y: auto;">
        <?php if (empty($comments)): ?>
            <p class="text-muted">No comments yet.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
              <div class="d-flex mb-3">
                  <div class="flex-shrink-0 me-3">
                      <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><?= htmlspecialchars(strtoupper(substr($c['name'], 0, 1))) ?></div>
                  </div>
                  <div class="flex-grow-1 card bg-light border-0">
                      <div class="card-body p-2">
                          <div class="small text-muted"><strong><?= htmlspecialchars($c['name']) ?></strong> &middot; <?= htmlspecialchars(date('M d, Y H:i', strtotime($c['created_at']))) ?></div>
                          <div class="mt-1"><?= nl2br(htmlspecialchars($c['body'])) ?></div>
                      </div>
                  </div>
              </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/comment">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <div class="mb-3">
            <textarea name="body" class="form-control" rows="3" placeholder="Add a comment..."></textarea>
          </div>
          <button class="btn btn-primary">Add Comment</button>
        </form>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">Add Attachment</div>
      <div class="card-body">
        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/attach" enctype="multipart/form-data" class="mt-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <div class="input-group">
            <label for="file-attach" id="drop-zone-show" class="w-100">
                <div class="fs-4 text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-paperclip" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0V3z"/></svg>
                </div>
                <div>Drag & Drop files here or <span class="text-primary">browse</span></div>
            </label>
            <input type="file" name="file[]" id="file-attach" class="d-none" multiple>
          </div>
          <div id="file-previews-show" class="mt-3"></div>
          <div class="d-grid mt-3">
              <button class="btn btn-primary btn-lg">Upload Attachments</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header">Metadata</div>
      <div class="card-body">
        <p class="mb-1"><strong class="d-block">Requester</strong> <?= htmlspecialchars($ticket['requester_name'] ?? ('#' . $ticket['requester_id'])) ?></p>
        <p class="mb-1"><strong class="d-block">Assignee</strong> <?= htmlspecialchars($ticket['assignee_name'] ?? 'Unassigned') ?></p>
        <p class="mb-0"><strong class="d-block">Category</strong> <?= htmlspecialchars($ticket['category'] ?? 'None') ?></p>
        <hr>
        <div class="small text-muted">Created: <?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['created_at']))) ?></div>
        <div class="small text-muted">Updated: <?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['updated_at']))) ?></div>
      </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Attachments</div>
        <div class="card-body">
            <?php if (!$attachments): ?>
                <p class="text-muted mb-0">No attachments.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($attachments as $a): ?>
                        <a href="<?= \App\upload_url_base() . '/' . htmlspecialchars($a['filename']) ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="fs-4 me-3 text-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/></svg></div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($a['original_name']) ?></div>
                                <small class="text-muted"><?= round((int)$a['size'] / 1024, 1) ?> KB</small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (in_array($_SESSION['user']['role'], ['agent','admin'], true)): ?>
    <div class="card mb-4">
      <div class="card-header">Agent Actions</div>
      <div class="card-body">
        <form method="post" action="/tickets/<?= (int)$ticket['id'] ?>/update">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
          <div class="mb-3">
              <label for="status" class="form-label fw-bold">Status</label>
              <select id="status" name="status" class="form-select">
                <?php foreach (['open','in_progress','resolved','closed'] as $s): ?>
                  <option value="<?= $s ?>" <?= $ticket['status']===$s?'selected':'' ?>><?= htmlspecialchars(ucwords(str_replace('_', ' ', $s))) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label for="assignee_id" class="form-label fw-bold">Assign to</label>
              <select id="assignee_id" name="assignee_id" class="form-select">
                <option value="">— Unassigned —</option>
                <?php foreach ($agents as $a): ?>
                  <option value="<?= (int)$a['id'] ?>" <?= ((int)($ticket['assignee_id']??0)===(int)$a['id'])?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
              </select>
          </div>
          <div class="d-grid">
              <button class="btn btn-primary">Update Ticket</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('file-attach');
    const dropZone = document.getElementById('drop-zone-show');
    const previewsContainer = document.getElementById('file-previews-show');
    const fileBuffer = new DataTransfer(); // This will be our single source of truth for files.

    const renderPreviews = () => {
        previewsContainer.innerHTML = '';
        if (fileBuffer.files.length === 0) return;

        Array.from(fileBuffer.files).forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'd-flex align-items-center justify-content-between bg-light rounded p-2 mb-2';

            const fileInfo = document.createElement('div');
            fileInfo.className = 'd-flex align-items-center flex-grow-1 me-3';
            fileInfo.innerHTML = `<div class="fs-4 me-3 text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/></svg></div>`;

            const textInfo = document.createElement('div');
            textInfo.innerHTML = `<div class="fw-bold text-truncate">${file.name}</div><small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>`;
            fileInfo.appendChild(textInfo);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn-close';
            removeButton.setAttribute('aria-label', 'Remove');
            removeButton.onclick = () => {
                const newBuffer = new DataTransfer();
                Array.from(fileBuffer.files)
                    .filter((_, i) => i !== index)
                    .forEach(file => newBuffer.items.add(file));

                fileBuffer.items.clear();
                Array.from(newBuffer.files).forEach(file => fileBuffer.items.add(file));
                fileInput.files = fileBuffer.files;
                renderPreviews();
            };

            previewItem.appendChild(fileInfo);
            previewItem.appendChild(removeButton);
            previewsContainer.appendChild(previewItem);
        });
    };

    const handleFiles = (files) => {
        for (const file of files) {
            fileBuffer.items.add(file);
        }
        fileInput.files = fileBuffer.files;
        renderPreviews();
    };

    fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            handleFiles(e.dataTransfer.files);
        });
    }

    fileInput.addEventListener('click', (e) => {
        e.target.value = '';
    });
});
</script>
