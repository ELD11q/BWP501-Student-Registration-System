<?php
/*
 * view_students.php - lists students with search (SQL LIKE), sorting and pagination (10 per page).
 * Server side: LIMIT/OFFSET query.  Client side: assets/js/students.js loads the table with AJAX
 * (?ajax=1 returns only the table part) so searching / paging / sorting happen without a reload.
 */
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

const PER_PAGE = 10;

// ---- Read & sanitise parameters ----
$search = trim($_GET['search'] ?? '');
$sortable = ['id' => 'id', 'name' => 'name', 'email' => 'email', 'phone' => 'phone', 'created_at' => 'created_at'];
$sort  = $sortable[$_GET['sort'] ?? ''] ?? 'created_at';     // whitelist (column names cannot be bound)
$order = strtolower($_GET['order'] ?? '') === 'asc' ? 'ASC' : 'DESC';
$page  = max(1, (int)($_GET['page'] ?? 1));

// ---- Search condition ----
$where  = '';
$params = [];
if ($search !== '') {
    $where = 'WHERE name LIKE :s1 OR email LIKE :s2 OR phone LIKE :s3';
    $like  = '%' . $search . '%';
    $params = [':s1' => $like, ':s2' => $like, ':s3' => $like];
}

// ---- Count for pagination ----
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / PER_PAGE));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * PER_PAGE;

// ---- Fetch the current page ----
$stmt = $pdo->prepare("SELECT * FROM students $where ORDER BY $sort $order LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll();

/** Builds a URL to this page keeping the current filters. */
function list_url(array $changes): string
{
    global $search, $sort, $order, $page;
    $q = array_merge(['search' => $search, 'sort' => $sort, 'order' => strtolower($order), 'page' => $page], $changes);
    if ($q['search'] === '') {
        unset($q['search']);
    }
    return 'view_students.php?' . http_build_query($q);
}

/** Sortable column header link with an arrow icon. */
function sort_link(string $col, string $label): string
{
    global $sort, $order;
    $isActive = $sort === $col;
    $newOrder = ($isActive && $order === 'ASC') ? 'desc' : 'asc';
    $icon = $isActive ? ($order === 'ASC' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up text-white-50';
    return '<a class="sort-link" href="' . e(list_url(['sort' => $col, 'order' => $newOrder, 'page' => 1])) . '">'
        . e($label) . ' <i class="bi ' . $icon . '"></i></a>';
}

/** The table + pagination block (returned alone for AJAX requests). */
function render_results(): void
{
    global $students, $total, $page, $totalPages, $offset, $search;
    ?>
    <div id="results">
        <p class="text-muted small mb-2">
            <?php if ($total): ?>
                Showing <?= $offset + 1 ?>–<?= $offset + count($students) ?> of <?= $total ?> student<?= $total === 1 ? '' : 's' ?>
                <?= $search !== '' ? ' matching "<strong>' . e($search) . '</strong>"' : '' ?>
            <?php endif; ?>
        </p>

        <?php if (!$students): ?>
            <div class="text-center text-muted py-5 border rounded bg-white">
                <i class="bi bi-inbox fs-1"></i>
                <p class="mt-2 mb-0"><?= $search !== '' ? 'No students match your search.' : 'No students yet. Add the first one!' ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-primary">
                    <tr>
                        <th><?= sort_link('id', '#') ?></th>
                        <th>Photo</th>
                        <th><?= sort_link('name', 'Name') ?></th>
                        <th><?= sort_link('email', 'Email') ?></th>
                        <th><?= sort_link('phone', 'Phone') ?></th>
                        <th class="d-none d-lg-table-cell">Address</th>
                        <th class="d-none d-md-table-cell"><?= sort_link('created_at', 'Added') ?></th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= (int)$s['id'] ?></td>
                            <td>
                                <?php if ($s['profile_picture']): ?>
                                    <img src="<?= e($s['profile_picture']) ?>" class="avatar" alt="<?= e($s['name']) ?>">
                                <?php else: ?>
                                    <span class="avatar avatar-placeholder"><?= e(mb_strtoupper(mb_substr($s['name'], 0, 1))) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= e($s['name']) ?></td>
                            <td><?= e($s['email']) ?></td>
                            <td><?= e($s['phone']) ?></td>
                            <td class="d-none d-lg-table-cell text-truncate" style="max-width:220px"><?= e($s['address']) ?></td>
                            <td class="d-none d-md-table-cell small text-muted"><?= e(date('Y-m-d', strtotime($s['created_at']))) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="update_student.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i><span class="d-none d-xl-inline"> Edit</span>
                                </a>
                                <a href="delete_student.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete"
                                   data-id="<?= (int)$s['id'] ?>" data-name="<?= e($s['name']) ?>" title="Delete">
                                    <i class="bi bi-trash"></i><span class="d-none d-xl-inline"> Delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="mt-3" aria-label="Student pages">
                    <ul class="pagination justify-content-center flex-wrap">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e(list_url(['page' => $page - 1])) ?>">&laquo; Previous</a>
                        </li>
                        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e(list_url(['page' => $p])) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e(list_url(['page' => $page + 1])) ?>">Next &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

// AJAX request: return only the results block
if (isset($_GET['ajax'])) {
    render_results();
    exit;
}

$pageTitle   = 'View Students';
$pageScripts = ['assets/js/students.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
    <h2 class="mb-0"><i class="bi bi-people text-primary"></i> Students</h2>
    <a href="add_student.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Student</a>
</div>

<form id="searchForm" class="mb-3" method="GET" action="view_students.php" role="search">
    <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <input type="hidden" name="order" value="<?= e(strtolower($order)) ?>">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" class="form-control" id="search" name="search" value="<?= e($search) ?>"
               placeholder="Search by name, email or phone..." aria-label="Search students">
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
</form>

<?php render_results(); ?>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="delete_student.php">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteName"></strong>? This cannot be undone.
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" id="deleteId">
                <input type="hidden" name="return" id="deleteReturn">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
