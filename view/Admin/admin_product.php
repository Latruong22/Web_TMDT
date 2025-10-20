<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';

// Get filter parameters
$filter_category = $_GET['category'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$filter_search = trim($_GET['search'] ?? '');
$filter_price_min = $_GET['price_min'] ?? '';
$filter_price_max = $_GET['price_max'] ?? '';

$filters = [
    'category' => $filter_category,
    'status' => $filter_status,
    'search' => $filter_search,
    'price_min' => $filter_price_min,
    'price_max' => $filter_price_max,
];

$products = getAdminProducts($filters);
$categories = getAllCategories();
$stats = getProductStats();

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Snowboard Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
    <link rel="stylesheet" href="../../Css/Admin/admin_product.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../Images/logo/logo.jpg" alt="Logo" class="sidebar-logo">
            <h4 class="sidebar-title">Snowboard Admin</h4>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="admin_home.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Bảng điều khiển</span>
            </a>
            <a href="admin_product.php" class="nav-link active">
                <i class="fas fa-box"></i>
                <span>Quản lý sản phẩm</span>
            </a>
            <a href="admin_order.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Quản lý đơn hàng</span>
            </a>
            <a href="admin_user.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Quản lý người dùng</span>
            </a>
            <a href="admin_promotion.php" class="nav-link">
                <i class="fas fa-tags"></i>
                <span>Khuyến mãi & Voucher</span>
            </a>
            <a href="admin_review.php" class="nav-link">
                <i class="fas fa-star"></i>
                <span>Quản lý đánh giá</span>
            </a>
            <a href="admin_revenue.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Báo cáo doanh thu</span>
            </a>
            <a href="admin_email.php" class="nav-link">
                <i class="fas fa-envelope"></i>
                <span>Gửi Email</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="../../controller/controller_User/controller.php?action=logout" class="nav-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-title">
                <h5 class="mb-0">Quản lý sản phẩm</h5>
            </div>
            <div class="navbar-right">
                <span class="navbar-time" id="dashboard-clock"></span>
                <div class="navbar-user">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></span>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="container-fluid py-4">
            <div class="admin-container">

                <?php if ($alert_text): ?>
                    <div class="alert alert-<?php echo in_array($msg, ['created', 'updated', 'deleted', 'removed']) ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($alert_text); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-lg">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <h6 class="text-muted">Tổng sản phẩm</h6>
                                <h3 class="mb-0 text-primary"><?php echo number_format($stats['total_products'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <h6 class="text-muted">Đang bán</h6>
                                <h3 class="mb-0 text-success"><?php echo number_format($stats['active_products'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg">
                        <div class="card text-center border-danger">
                            <div class="card-body">
                                <h6 class="text-muted">Ngừng bán</h6>
                                <h3 class="mb-0 text-danger"><?php echo number_format($stats['inactive_products'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <h6 class="text-muted">Còn hàng</h6>
                                <h3 class="mb-0 text-info"><?php echo number_format($stats['in_stock_products'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <h6 class="text-muted">Hết hàng</h6>
                                <h3 class="mb-0 text-warning"><?php echo number_format($stats['out_of_stock_products'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modern Filter Panel -->
                <div class="filter-panel mb-4">
                    <div class="filter-header" onclick="productFilterManager.toggleFilterPanel()">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-filter me-2"></i>
                            <h5 class="mb-0">Bộ lọc sản phẩm</h5>
                            <span id="filterBadge" class="filter-badge ms-2">0</span>
                        </div>
                        <i class="fas fa-chevron-down" id="filterToggleIcon"></i>
                    </div>
                    <div class="filter-body" id="filterBody">
                        <div id="activeFilters" class="active-filters mb-3"></div>
                        <form method="get" id="productFilterForm" class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">
                                    <i class="fas fa-folder me-1"></i>
                                    Danh mục
                                </label>
                                <select name="category" class="form-select filter-select">
                                    <option value="all" <?php echo $filter_category === 'all' ? 'selected' : ''; ?>>Tất cả danh mục</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['category_id']; ?>" <?php echo $filter_category == $cat['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Trạng thái
                                </label>
                                <select name="status" class="form-select filter-select">
                                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Đang bán</option>
                                    <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Ngừng bán</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-search me-1"></i>
                                    Tìm kiếm
                                </label>
                                <div class="input-group">
                                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Tên sản phẩm..." value="<?php echo htmlspecialchars($filter_search); ?>">
                                    <span class="input-group-text" id="searchSpinner" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Giá từ
                                </label>
                                <input type="number" name="price_min" class="form-control filter-price" placeholder="0" min="0" step="1000" value="<?php echo htmlspecialchars($filter_price_min); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Giá đến
                                </label>
                                <input type="number" name="price_max" class="form-control filter-price" placeholder="999999999" min="0" step="1000" value="<?php echo htmlspecialchars($filter_price_max); ?>">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="productFilterManager.clearAllFilters()" title="Xóa tất cả bộ lọc">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </form>
                        <div class="filter-loading" id="filterLoading" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="form-section card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo $edit_product ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm mới'; ?></h5>
                        <?php if ($edit_product): ?>
                            <a class="btn btn-sm btn-secondary" href="admin_product.php">
                                <i class="fas fa-plus me-1"></i>Thêm sản phẩm mới
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../../controller/controller_Admin/admin_product_controller.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $edit_product ? 'update' : 'add'; ?>">
                            <?php if ($edit_product): ?>
                                <input type="hidden" name="product_id" value="<?php echo (int)$edit_product['product_id']; ?>">
                                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_product['image'] ?? ''); ?>">
                            <?php endif; ?>

                            <!-- Thông tin cơ bản -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <i class="fas fa-tag text-primary me-1"></i>Tên sản phẩm
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control" placeholder="Nhập tên sản phẩm..." value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <!-- Giá và khuyến mãi -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign text-success me-1"></i>Giá bán (VND)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="price" class="form-control" step="1000" min="0" placeholder="0" value="<?php echo isset($edit_product['price']) ? htmlspecialchars($edit_product['price']) : ''; ?>" required>
                                        <span class="input-group-text">₫</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-percent text-warning me-1"></i>Khuyến mãi (%)
                                    </label>
                                    <input type="number" name="manual_discount" class="form-control" step="0.1" min="0" max="100" placeholder="0" value="<?php echo isset($edit_product['manual_discount']) ? htmlspecialchars($edit_product['manual_discount']) : 0; ?>">
                                    <small class="form-text text-muted">Để 0 nếu không khuyến mãi</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-warehouse text-info me-1"></i>Số lượng trong kho
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="stock" class="form-control" min="0" placeholder="0" value="<?php echo isset($edit_product['stock']) ? (int)$edit_product['stock'] : 0; ?>" required>
                                </div>
                            </div>

                            <!-- Danh mục và trạng thái -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-list text-primary me-1"></i>Danh mục
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo (int)$category['category_id']; ?>" <?php echo ($edit_product && (int)$edit_product['category_id'] === (int)$category['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-toggle-on text-success me-1"></i>Trạng thái
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="status" class="form-select" required>
                                        <?php foreach ($statuses as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" <?php echo ($edit_product ? $edit_product['status'] === $value : $value === 'active') ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Mô tả -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-align-left text-secondary me-1"></i>Mô tả sản phẩm
                                </label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Nhập mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- Ảnh sản phẩm chính -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-image text-danger me-1"></i>Ảnh chính (Main Image)
                                    <?php if (!$edit_product): ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="file" name="main_image" class="form-control" accept="image/*" id="mainImageInput" <?php echo $edit_product ? '' : 'required'; ?>>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Ảnh đại diện hiển thị trong danh sách sản phẩm. Định dạng: JPG, PNG, GIF, WEBP. Tối đa 2MB.
                                </small>
                                
                                <?php if ($edit_product && !empty($edit_product['image'])): ?>
                                    <div class="mt-3">
                                        <label class="form-label text-muted">Ảnh hiện tại:</label>
                                        <div class="border rounded p-2" style="max-width: 200px;">
                                            <img src="../../<?php echo htmlspecialchars($edit_product['image']); ?>" alt="Ảnh sản phẩm" class="img-fluid rounded" id="currentImage">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Main Image Preview -->
                                <div id="mainImagePreview" class="mt-3" style="display: none;">
                                    <label class="form-label text-muted">Xem trước:</label>
                                    <div class="border rounded p-2" style="max-width: 200px;">
                                        <img id="mainPreviewImg" src="" alt="Preview" class="img-fluid rounded">
                                    </div>
                                </div>
                            </div>

                            <!-- Ảnh chi tiết (Multiple) -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-images text-info me-1"></i>Ảnh chi tiết (Detail Gallery)
                                    <span class="badge bg-info">Tùy chọn</span>
                                </label>
                                <input type="file" name="detail_images[]" class="form-control" accept="image/*" id="detailImagesInput" multiple>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Chọn nhiều ảnh (Ctrl + Click hoặc Shift + Click). Tối đa 8 ảnh. Mỗi ảnh tối đa 2MB.
                                </small>
                                
                                <!-- Detail Images Preview -->
                                <div id="detailImagesPreview" class="mt-3" style="display: none;">
                                    <label class="form-label text-muted">Đã chọn <span id="detailImageCount">0</span> ảnh:</label>
                                    <div id="detailImagesList" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                            <div class="form-actions mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i><?php echo $edit_product ? 'Cập nhật' : 'Thêm sản phẩm'; ?>
                                </button>
                                <?php if ($edit_product): ?>
                                    <a class="btn btn-secondary" href="admin_product.php">
                                        <i class="fas fa-times me-1"></i>Hủy
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="table-section card">
                    <div class="card-header">
                        <h5 class="mb-0">Danh sách sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                <td colspan="10" class="text-center empty">Chưa có sản phẩm nào.</td>
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
                                        <a class="btn btn-sm btn-primary me-1" href="admin_product.php?action=edit&id=<?php echo (int)$product['product_id']; ?>">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <a class="btn btn-sm btn-warning me-1" href="../../controller/controller_Admin/admin_product_controller.php?action=delete&id=<?php echo (int)$product['product_id']; ?>" data-confirm="Chuyển sản phẩm sang trạng thái ngừng bán?">
                                            <i class="fas fa-ban"></i> Ngừng bán
                                        </a>
                                        <?php if ($product['status'] === 'inactive'): ?>
                                            <a class="btn btn-sm btn-danger" href="../../controller/controller_Admin/admin_product_controller.php?action=hard-delete&id=<?php echo (int)$product['product_id']; ?>&image=<?php echo urlencode($product['image'] ?? ''); ?>" data-confirm="Xóa vĩnh viễn sản phẩm này?">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../Js/Admin/home.js"></script>
    <script src="../../Js/Admin/product.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Confirm delete
            document.querySelectorAll('[data-confirm]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (!confirm(link.getAttribute('data-confirm'))) {
                        e.preventDefault();
                    }
                });
            });

            // Main image preview
            const mainImageInput = document.getElementById('mainImageInput');
            const mainImagePreview = document.getElementById('mainImagePreview');
            const mainPreviewImg = document.getElementById('mainPreviewImg');
            
            if (mainImageInput) {
                mainImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Check file size (2MB = 2097152 bytes)
                        if (file.size > 2097152) {
                            alert('Kích thước ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 2MB.');
                            mainImageInput.value = '';
                            mainImagePreview.style.display = 'none';
                            return;
                        }
                        
                        // Show preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            mainPreviewImg.src = e.target.result;
                            mainImagePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        mainImagePreview.style.display = 'none';
                    }
                });
            }

            // Detail images preview (Multiple)
            const detailImagesInput = document.getElementById('detailImagesInput');
            const detailImagesPreview = document.getElementById('detailImagesPreview');
            const detailImagesList = document.getElementById('detailImagesList');
            const detailImageCount = document.getElementById('detailImageCount');
            
            if (detailImagesInput) {
                detailImagesInput.addEventListener('change', function(e) {
                    const files = e.target.files;
                    
                    if (files.length > 0) {
                        // Check max 8 images
                        if (files.length > 8) {
                            alert('Chỉ được chọn tối đa 8 ảnh chi tiết!');
                            detailImagesInput.value = '';
                            detailImagesPreview.style.display = 'none';
                            return;
                        }
                        
                        // Clear previous previews
                        detailImagesList.innerHTML = '';
                        let validFiles = 0;
                        
                        // Show previews
                        Array.from(files).forEach((file, index) => {
                            // Check file size
                            if (file.size > 2097152) {
                                alert(`Ảnh "${file.name}" quá lớn! Vui lòng chọn ảnh nhỏ hơn 2MB.`);
                                return;
                            }
                            
                            validFiles++;
                            
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const div = document.createElement('div');
                                div.className = 'border rounded p-1';
                                div.style.width = '100px';
                                div.innerHTML = `
                                    <img src="${e.target.result}" alt="Detail ${index + 1}" class="img-fluid rounded" style="width: 100%; height: 90px; object-fit: cover;">
                                    <small class="text-muted d-block text-center mt-1">#${index + 1}</small>
                                `;
                                detailImagesList.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        });
                        
                        if (validFiles > 0) {
                            detailImageCount.textContent = validFiles;
                            detailImagesPreview.style.display = 'block';
                        } else {
                            detailImagesInput.value = '';
                            detailImagesPreview.style.display = 'none';
                        }
                    } else {
                        detailImagesPreview.style.display = 'none';
                    }
                });
            }

            // Format price input
            const priceInput = document.querySelector('input[name="price"]');
            if (priceInput) {
                priceInput.addEventListener('blur', function() {
                    if (this.value) {
                        // Round to nearest 1000
                        const value = Math.round(parseFloat(this.value) / 1000) * 1000;
                        this.value = value;
                    }
                });
            }
        });
    </script>
</body>
</html>
