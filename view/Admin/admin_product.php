<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';

$products = getAllProducts();
$categories = getAllCategories();

$action = $_GET['action'] ?? '';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit_product = ($action === 'edit' && $edit_id) ? getProductById($edit_id) : null;
$msg = $_GET['msg'] ?? '';

$message_map = [
    'created' => 'Đã thêm sản phẩm mới thành công.',
    'updated' => 'Đã cập nhật sản phẩm thành công.',
    'deleted' => 'Đã chuyển sản phẩm sang trạng thái ngừng kinh doanh.',
    'removed' => 'Đã xóa sản phẩm khỏi hệ thống.',
    'invalid' => 'Vui lòng kiểm tra lại thông tin sản phẩm.',
    'error' => 'Có lỗi xảy ra. Vui lòng thử lại.',
    'noimage' => 'Vui lòng chọn ảnh cho sản phẩm.',
];
$alert_text = $message_map[$msg] ?? ($msg && !isset($message_map[$msg]) ? $msg : '');

$statuses = [
    'active' => 'Đang bán',
    'inactive' => 'Ngừng bán',
];

function truncateText($text, $limit = 120) {
    if (!$text) {
        return '';
    }
    if (function_exists('mb_strlen')) {
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit, 'UTF-8') . '...';
    }
    if (strlen($text) <= $limit) {
        return $text;
    }
    return substr($text, 0, $limit) . '...';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="../../Css/Admin/admin_product.css">
</head>
<body>
    <div class="admin-container">
        <header class="page-header">
            <h1>Quản lý sản phẩm</h1>
            <a class="back-link" href="admin_home.php">← Quay lại bảng điều khiển</a>
        </header>

        <?php if ($alert_text): ?>
            <div class="alert <?php echo in_array($msg, ['created', 'updated', 'deleted', 'removed']) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($alert_text); ?>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <div class="form-header">
                <h2><?php echo $edit_product ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới'; ?></h2>
                <?php if ($edit_product): ?>
                    <a class="btn-secondary" href="admin_product.php">+ Thêm sản phẩm mới</a>
                <?php endif; ?>
            </div>
            <form class="product-form" method="post" action="../../controller/controller_Admin/admin_product_controller.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'update' : 'add'; ?>">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo (int)$edit_product['product_id']; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_product['image'] ?? ''); ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <label>
                        Tên sản phẩm
                        <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>" required>
                    </label>
                    <label>
                        Giá bán (VND)
                        <input type="number" name="price" step="1000" min="0" value="<?php echo isset($edit_product['price']) ? htmlspecialchars($edit_product['price']) : ''; ?>" required>
                    </label>
                    <label>
                        Khuyến mãi (%)
                        <input type="number" name="manual_discount" step="0.1" min="0" max="100" value="<?php echo isset($edit_product['manual_discount']) ? htmlspecialchars($edit_product['manual_discount']) : 0; ?>">
                        <small>Nhập % giảm giá thủ công (0 - 100). Để 0 nếu không khuyến mãi.</small>
                    </label>
                    <label>
                        Số lượng trong kho
                        <input type="number" name="stock" min="0" value="<?php echo isset($edit_product['stock']) ? (int)$edit_product['stock'] : 0; ?>" required>
                    </label>
                    <label>
                        Danh mục
                        <select name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int)$category['category_id']; ?>" <?php echo ($edit_product && (int)$edit_product['category_id'] === (int)$category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Trạng thái
                        <select name="status" required>
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo ($edit_product ? $edit_product['status'] === $value : $value === 'active') ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Ảnh sản phẩm
                        <input type="file" name="image" accept="image/*" <?php echo $edit_product ? '' : 'required'; ?>>
                        <small>Định dạng: JPG, PNG, GIF, WEBP. Tối đa 2MB.</small>
                    </label>
                </div>

                <label>
                    Mô tả sản phẩm
                    <textarea name="description" rows="4" placeholder="Nhập mô tả chi tiết..."><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                </label>

                <?php if ($edit_product && !empty($edit_product['image'])): ?>
                    <div class="current-image">
                        <p>Ảnh hiện tại:</p>
                        <img src="../../<?php echo htmlspecialchars($edit_product['image']); ?>" alt="Ảnh sản phẩm">
                    </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="btn-primary"><?php echo $edit_product ? 'Cập nhật' : 'Thêm sản phẩm'; ?></button>
                    <?php if ($edit_product): ?>
                        <a class="btn-secondary" href="admin_product.php">Hủy</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="table-section">
            <h2>Danh sách sản phẩm</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Khuyến mãi</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" class="empty">Chưa có sản phẩm nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>#<?php echo (int)$product['product_id']; ?></td>
                                    <td class="table-image">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php else: ?>
                                            <span class="no-image">Không có ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <p class="description"><?php echo htmlspecialchars(truncateText($product['description'] ?? '')); ?></p>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'Không xác định'); ?></td>
                                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</td>
                                    <td>
                                        <?php
                                        $discount = (float)($product['manual_discount'] ?? 0);
                                        if ($discount > 0) {
                                            $finalPrice = max($product['price'] * (1 - $discount / 100), 0);
                                            $discountText = rtrim(rtrim(number_format($discount, 2, ',', '.'), '0'), ',');
                                            if ($discountText === '') {
                                                $discountText = '0';
                                            }
                                            echo '<strong>' . $discountText . '%</strong><br>';
                                            echo '<span class="muted">Giá sau giảm: ' . number_format($finalPrice, 0, ',', '.') . ' ₫</span>';
                                        } else {
                                            echo '<span class="muted">Không</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo (int)$product['stock']; ?></td>
                                    <td>
                                        <span class="status <?php echo $product['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $statuses[$product['status']] ?? $product['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></td>
                                    <td class="actions">
                                        <a class="btn-link" href="admin_product.php?action=edit&id=<?php echo (int)$product['product_id']; ?>">Sửa</a>
                                        <a class="btn-link danger" href="../../controller/controller_Admin/admin_product_controller.php?action=delete&id=<?php echo (int)$product['product_id']; ?>" data-confirm="Chuyển sản phẩm sang trạng thái ngừng bán?">Ngừng bán</a>
                                        <?php if ($product['status'] === 'inactive'): ?>
                                            <a class="btn-link warning" href="../../controller/controller_Admin/admin_product_controller.php?action=hard-delete&id=<?php echo (int)$product['product_id']; ?>&image=<?php echo urlencode($product['image'] ?? ''); ?>" data-confirm="Xóa vĩnh viễn sản phẩm này?">Xóa</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="../../Js/Admin/product.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-confirm]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (!confirm(link.getAttribute('data-confirm'))) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
