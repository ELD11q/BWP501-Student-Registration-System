<?php
require "db.php";
checkLogin();

$perPage = 10;
$search = trim($_GET['search'] ?? "");
$page = max(1, (int)($_GET['page'] ?? 1));

$columns = ["id", "name", "email", "phone", "created_at"];
$sort = in_array($_GET['sort'] ?? "", $columns) ? $_GET['sort'] : "id";
$order = ($_GET['order'] ?? "") == "asc" ? "ASC" : "DESC";

$where = "";
$params = [];
if ($search != "") {
    $where = "WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM students $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();

$totalPages = max(1, ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare("SELECT * FROM students $where ORDER BY $sort $order LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$students = $stmt->fetchAll();

function makeLink($newPage, $newSort = null)
{
    global $search, $sort, $order;
    $linkOrder = strtolower($order);
    if ($newSort) {
        $linkOrder = ($newSort == $sort && $order == "ASC") ? "desc" : "asc";
    }
    return "view_students.php?" . http_build_query([
        "search" => $search,
        "sort" => $newSort ?? $sort,
        "order" => $linkOrder,
        "page" => $newPage
    ]);
}

$isAjax = isset($_GET['ajax']);

if (!$isAjax) {
    $title = "عرض الطلاب";
    include "header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>الطلاب</h2>
    <a href="add_student.php" class="btn btn-success">+ إضافة طالب</a>
</div>

<form id="searchForm" method="GET" action="view_students.php" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" id="search" class="form-control" placeholder="ابحث بالاسم أو البريد أو رقم الجوال" value="<?= clean($search) ?>">
        <button class="btn btn-primary" type="submit">بحث</button>
    </div>
</form>

<?php } ?>

<div id="results">
<p class="text-muted">المجموع: <?= $total ?> طالب - صفحة <?= $page ?> من <?= $totalPages ?></p>

<?php if (empty($students)): ?>
    <div class="alert alert-info">لا يوجد طلاب.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-primary">
            <tr>
                <th><a href="<?= makeLink(1, 'id') ?>">الرقم</a></th>
                <th>الصورة</th>
                <th><a href="<?= makeLink(1, 'name') ?>">الاسم</a></th>
                <th><a href="<?= makeLink(1, 'email') ?>">البريد الإلكتروني</a></th>
                <th><a href="<?= makeLink(1, 'phone') ?>">الجوال</a></th>
                <th class="d-none d-md-table-cell">العنوان</th>
                <th>الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td>
                        <?php if ($s['profile_picture']): ?>
                            <img src="<?= clean($s['profile_picture']) ?>" class="student-photo" alt="photo">
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= clean($s['name']) ?></td>
                    <td><?= clean($s['email']) ?></td>
                    <td><?= clean($s['phone']) ?></td>
                    <td class="d-none d-md-table-cell"><?= clean($s['address']) ?></td>
                    <td class="text-nowrap">
                        <a href="update_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="delete_student.php" method="POST" class="d-inline delete-form">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= makeLink($page - 1) ?>">السابق</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= makeLink($i) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= makeLink($page + 1) ?>">التالي</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
</div>

<?php
if (!$isAjax) {
    include "footer.php";
}
?>
