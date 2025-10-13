<form method="post" action="/tickets/create" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(\App\csrf_token()) ?>">
    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="mb-0 h4">Create a New Ticket</h2>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Subject</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <div class="card">
                            <textarea id="description" name="description" class="form-control" style="border-bottom: 0; border-bottom-left-radius: 0; border-bottom-right-radius: 0;" rows="8" required></textarea>
                            <div class="card-footer bg-light py-2">
                                <label for="file" class="btn btn-sm btn-outline-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-paperclip" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0V3z"/></svg>
                                    Attach file
                                </label>
                            </div>
                        </div>
                        <input type="file" id="file" name="file[]" class="d-none" multiple>
                        <div id="file-previews" class="mt-3"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label fw-bold">Priority</label>
                            <select id="priority" name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-bold">Category</label>
                            <input type="text" id="category" name="category" class="form-control" placeholder="e.g., Hardware, Network">
                        </div>
                    </div>

                    <?php if (\App\role_in(['agent', 'admin']) && !empty($agents)): ?>
                        <div class="mb-3">
                            <label for="assignee_id" class="form-label fw-bold">Assign To</label>
                            <select id="assignee_id" name="assignee_id" class="form-select">
                                <option value="">— Unassigned —</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= (int)$agent['id'] ?>"><?= htmlspecialchars($agent['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary btn-lg">Submit Ticket</button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('file');
    const previewsContainer = document.getElementById('file-previews');
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

    // Clear the input to allow selecting the same file again
    fileInput.addEventListener('click', (e) => {
        e.target.value = '';
    });
});
</script>
