<!-- Modal for New/Edit Task (Bootstrap 5) -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-panel border-dark border-opacity-10 p-0 overflow-hidden shadow-lg">
            <div class="modal-header border-bottom border-dark border-opacity-5 p-4">
                <h5 class="modal-title fw-bold text-dark">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="task-form" method="POST" action="task.php">
                <div class="modal-body p-4 text-dark">
                    <div class="mb-3">
                        <label class="form-label text-dark">Task Name</label>
                        <input type="text" name="task_name" id="modal-task-name" class="form-control" placeholder="What needs to be done?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark">Description</label>
                        <textarea name="description" id="modal-description" class="form-control" rows="2" placeholder="Add more details..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label text-dark">Due Date</label>
                            <input type="date" name="due_date" id="modal-due-date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label text-dark">Start</label>
                            <input type="time" name="task_time" id="modal-task-time" class="form-control" value="<?= date('H:i'); ?>" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label text-dark">End</label>
                            <input type="time" name="end_time" id="modal-end-time" class="form-control" value="<?= date('H:i', strtotime('+1 hour')); ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label text-dark">Priority</label>
                            <select name="priority" id="modal-priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label text-dark">Category</label>
                            <select name="category" id="modal-category" class="form-select">
                                <option value="Personal">Personal</option>
                                <option value="Academic">Academic</option>
                                <option value="Project">Project</option>
                                <option value="Work">Work</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label text-dark">Recurrence</label>
                            <select name="recurrence" id="modal-recurrence" class="form-select">
                                <option value="none">None</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tag Collaborators Section -->
                    <div class="mt-4 p-3 rounded-4 bg-light bg-opacity-50 border border-dark border-opacity-5">
                        <label class="form-label text-dark fw-bold mb-2"><i class="bi bi-people-fill me-2 text-primary"></i>Tag Collaborators</label>
                        <div class="input-group mb-2">
                            <input type="text" id="user-tag-input" class="form-control" placeholder="Search exact full username...">
                            <button type="button" id="btn-tag-search" class="btn btn-primary rounded-end-pill px-3"><i class="bi bi-search"></i></button>
                        </div>
                        <div id="tagged-users-container" class="d-flex flex-wrap gap-2"></div>
                        <input type="hidden" name="collaborators" id="collaborators-hidden" value="">
                    </div>
                </div>
                <div class="modal-footer border-top border-dark border-opacity-5 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_task" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
