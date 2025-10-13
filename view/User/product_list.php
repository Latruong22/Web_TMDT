<?php
session_start();
require_once '../../model/auth_middleware.php';

// Cho phép khách truy cập trang sản phẩm (không yêu cầu đăng nhập)
// requireUser(); // Commented để cho guest xem
if (isset($_SESSION['user_id'])) {
    checkSessionTimeout(); // Chỉ check timeout khi đã login
}

require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';

// Helper function: Get first image from product folder
function getProductThumbnail($product_id, $fallback_image = '') {
    $sp_folder = "Sp" . $product_id;
    
    // Use __DIR__ for reliable path resolution
    $folder_path = __DIR__ . "/../../Images/product/" . $sp_folder . "/";
    $folder_url = "/Web_TMDT/Images/product/" . $sp_folder . "/";
    
    // Try to get first image from folder
    if (is_dir($folder_path)) {
        $files = scandir($folder_path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                return $folder_url . $file;
            }
        }
    }
    
    // Fallback to database image with absolute path
    if ($fallback_image) {
        // Check if already absolute URL
        if (strpos($fallback_image, 'http') === 0 || strpos($fallback_image, '/Web_TMDT/') === 0) {
            return $fallback_image;
        }
        // Convert relative database path to absolute
        return "/Web_TMDT/" . $fallback_image;
    }
    
    // Last resort: placeholder
    return "/Web_TMDT/Images/product/placeholder.jpg";
}

// Lấy tham số lọc
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12; // Số sản phẩm mỗi trang
$offset = ($page - 1) * $limit;

// Lấy tất cả sản phẩm
$all_products = getAllProducts();

// Lọc theo danh mục
if ($category_id > 0) {
    $all_products = array_filter($all_products, function($p) use ($category_id) {
        return $p['category_id'] == $category_id && $p['status'] === 'active';
    });
} else {
    $all_products = array_filter($all_products, function($p) {
        return $p['status'] === 'active';
    });
}

// Lọc tìm kiếm
if ($search) {
    $search_lower = function_exists('mb_strtolower') ? mb_strtolower($search, 'UTF-8') : strtolower($search);
    $all_products = array_filter($all_products, function($p) use ($search_lower) {
        $name_lower = function_exists('mb_strtolower') ? mb_strtolower($p['name'], 'UTF-8') : strtolower($p['name']);
        $desc_lower = function_exists('mb_strtolower') ? mb_strtolower($p['description'] ?? '', 'UTF-8') : strtolower($p['description'] ?? '');
        return strpos($name_lower, $search_lower) !== false || strpos($desc_lower, $search_lower) !== false;
    });
}

// Sắp xếp sản phẩm
$all_products = array_values($all_products); // Đánh số lại index
switch ($sort) {
    case 'price_asc':
        usort($all_products, function($a, $b) {
            $discount_a = floatval($a['manual_discount'] ?? 0);
            $discount_b = floatval($b['manual_discount'] ?? 0);
            $price_a = $a['price'] * (1 - $discount_a / 100);
            $price_b = $b['price'] * (1 - $discount_b / 100);
            return $price_a - $price_b;
        });
        break;
    case 'price_desc':
        usort($all_products, function($a, $b) {
            $discount_a = floatval($a['manual_discount'] ?? 0);
            $discount_b = floatval($b['manual_discount'] ?? 0);
            $price_a = $a['price'] * (1 - $discount_a / 100);
            $price_b = $b['price'] * (1 - $discount_b / 100);
            return $price_b - $price_a;
        });
        break;
    case 'name_asc':
        usort($all_products, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        break;
    case 'newest':
    default:
        usort($all_products, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        break;
}

// Phân trang
$total_products = count($all_products);
$total_pages = ceil($total_products / $limit);
$products = array_slice($all_products, $offset, $limit);

// Lấy tất cả danh mục để lọc
$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Khám phá bộ sưu tập sản phẩm snowboard chất lượng cao. Giá tốt, giao hàng nhanh, uy tín hàng đầu.">
    <meta name="keywords" content="snowboard, ván trượt tuyết, thiết bị trượt tuyết, mua ván trượt">
    <meta name="author" content="Snowboard Shop">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Sản phẩm Snowboard - Snowboard Shop">
    <meta property="og:description" content="Khám phá bộ sưu tập sản phẩm snowboard chất lượng cao">
    <meta property="og:type" content="website">
    <meta property="og:image" content="../../Images/logo/logo.jpg">
    
    <title><?php 
        if ($search) {
            echo 'Tìm kiếm "' . htmlspecialchars($search) . '" - Snowboard Shop';
        } elseif ($category_id > 0) {
            $current_cat = array_filter($categories, function($c) use ($category_id) {
                return $c['category_id'] == $category_id;
            });
            $current_cat = reset($current_cat);
            echo htmlspecialchars($current_cat['name']) . ' - Snowboard Shop';
        } else {
            echo 'Sản phẩm - Snowboard Shop';
        }
    ?></title>
    
    <!-- Preconnect for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/product_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Font override để đảm bảo fonts hoạt động -->
    <style>
        body, p, div, span, a, button, input, select, textarea, .card-text, .btn, .nav-link { font-family: "Barlow", sans-serif !important; font-weight: 500 !important; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .card-title, .product-title { font-family: "Righteous", sans-serif !important; }
        /* Giữ font mặc định cho icons - Enhanced */
        .fas, .far, .fal, .fab, [class*="fa-"], 
        i.fas, i.far, i.fal, i.fab, i[class*="fa-"],
        .footer .fas, .footer .far, .footer .fal, .footer .fab, .footer [class*="fa-"],
        .social-links i, .social-links [class*="fa-"] { 
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important; 
            font-weight: 900 !important;
        }
    </style>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../Images/logo/logo.jpg">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <img src="/Web_TMDT/Images/logo/logo.jpg" alt="Logo" class="logo-img">
                <span class="ms-2 fw-bold">SNOWBOARD SHOP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php"><i class="fas fa-home me-1"></i>Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="product_list.php"><i class="fas fa-snowboarding me-1"></i>Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Giỏ hàng
                            <span class="cart-badge" id="cart-count">0</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="order_history.php"><i class="fas fa-history me-1"></i>Đơn hàng</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['fullname']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="../../controller/controller_User/controller.php?action=logout">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="page-banner">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <h1 class="banner-title animate-fade-in">Bộ Sưu Tập Snowboard</h1>
            <p class="banner-subtitle animate-fade-in-delay">Khám phá các sản phẩm chất lượng cao cho mùa trượt tuyết</p>
            <div class="banner-breadcrumb animate-fade-in-delay-2">
                <a href="home.php"><i class="fas fa-home"></i> Trang chủ</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Sản phẩm</span>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Filter Toggle Button for Mobile -->
        <button class="btn btn-dark d-lg-none mb-3 w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filterSidebar" aria-expanded="false" aria-controls="filterSidebar">
            <i class="fas fa-filter me-2"></i>Bộ lọc & Tìm kiếm
            <i class="fas fa-chevron-down ms-2"></i>
        </button>
        
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-lg-3 mb-4">
                <div class="collapse d-lg-block" id="filterSidebar">
                <div class="filter-sidebar">
                    <!-- Search Box -->
                    <div class="filter-section">
                        <h5 class="filter-title"><i class="fas fa-search me-2"></i>Tìm kiếm</h5>
                        <form method="get" action="product_list.php">
                            <!-- Category Select -->
                            <div class="mb-3">
                                <select name="category" class="form-select" id="categorySelect">
                                    <option value="0" <?php echo $category_id == 0 ? 'selected' : ''; ?>>Tất cả sản phẩm</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" 
                                                <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Search Input -->
                            <div class="search-box">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Tìm kiếm sản phẩm..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h5 class="filter-title"><i class="fas fa-list me-2"></i>Danh mục</h5>
                        <ul class="category-list">
                            <li>
                                <a href="product_list.php" class="category-link <?php echo $category_id == 0 ? 'active' : ''; ?>">
                                    <span>Tất cả sản phẩm</span>
                                    <span class="badge"><?php echo count(array_filter(getAllProducts(), function($p) { return $p['status'] === 'active'; })); ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): 
                                $cat_count = count(array_filter(getAllProducts(), function($p) use ($cat) {
                                    return $p['category_id'] == $cat['category_id'] && $p['status'] === 'active';
                                }));
                            ?>
                                <li>
                                    <a href="?category=<?php echo $cat['category_id']; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                                       class="category-link <?php echo $category_id == $cat['category_id'] ? 'active' : ''; ?>">
                                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                        <span class="badge"><?php echo $cat_count; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Price Filter Info -->
                    <div class="filter-section">
                        <h5 class="filter-title"><i class="fas fa-tag me-2"></i>Khoảng giá</h5>
                        <div class="price-info">
                            <?php
                            $prices = array_map(function($p) {
                                $discount = floatval($p['manual_discount'] ?? 0);
                                return $p['price'] * (1 - $discount / 100);
                            }, $all_products);
                            if (!empty($prices)) {
                                $min_price = min($prices);
                                $max_price = max($prices);
                                echo '<p class="mb-1"><strong>Thấp nhất:</strong> ' . number_format($min_price, 0, ',', '.') . 'đ</p>';
                                echo '<p class="mb-0"><strong>Cao nhất:</strong> ' . number_format($max_price, 0, ',', '.') . 'đ</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Banner Ad -->
                    <div class="filter-section">
                        <div class="sidebar-banner">
                            <img src="/Web_TMDT/Images/baner/baner4.jpg" alt="Sale Banner">
                            <div class="sidebar-banner-content">
                                <h6>Khuyến mãi đặc biệt</h6>
                                <p>Giảm giá lên đến 30%</p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="col-lg-9">
                <!-- Toolbar -->
                <div class="products-toolbar">
                    <div class="toolbar-left">
                        <h4 class="results-title">
                            <?php if ($search): ?>
                                Kết quả tìm kiếm "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php elseif ($category_id > 0): 
                                $current_cat = array_filter($categories, function($c) use ($category_id) {
                                    return $c['category_id'] == $category_id;
                                });
                                $current_cat = reset($current_cat);
                            ?>
                                <?php echo htmlspecialchars($current_cat['name']); ?>
                            <?php else: ?>
                                Tất cả sản phẩm
                            <?php endif; ?>
                        </h4>
                        <p class="results-count">Hiển thị <?php echo count($products); ?> / <?php echo $total_products; ?> sản phẩm</p>
                    </div>
                    <div class="toolbar-right">
                        <form method="get" action="product_list.php" class="sort-form">
                            <label for="sortSelect">Sắp xếp:</label>
                            <select name="sort" id="sortSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                            </select>
                            <?php if ($category_id > 0): ?>
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                            <?php endif; ?>
                            <?php if ($search): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <input type="hidden" name="page" value="<?php echo $page; ?>">
                        </form>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): 
                            $discount_percent = (float)($product['manual_discount'] ?? 0);
                            $final_price = $discount_percent > 0 ? $product['price'] * (1 - $discount_percent / 100) : $product['price'];
                            $stock_status = $product['stock'] > 0 ? 'in-stock' : 'out-of-stock';
                        ?>
                            <div class="product-card" data-aos="fade-up" itemscope itemtype="https://schema.org/Product">
                                <div class="product-image-wrapper">
                                    <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" aria-label="Xem chi tiết <?php echo htmlspecialchars($product['name']); ?>">
                                        <img src="<?php echo getProductThumbnail($product['product_id'], $product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-image"
                                             loading="lazy"
                                             itemprop="image">
                                    </a>
                                    <?php if ($discount_percent > 0): ?>
                                        <span class="discount-badge">-<?php echo $discount_percent; ?>%</span>
                                    <?php endif; ?>
                                    <?php if ($product['stock'] == 0): ?>
                                        <span class="stock-badge out">Hết hàng</span>
                                    <?php elseif ($product['stock'] <= 5): ?>
                                        <span class="stock-badge low">Còn <?php echo $product['stock']; ?></span>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <button class="action-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                                <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>
                                                title="Thêm vào giỏ">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" 
                                           class="action-btn" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h6 class="product-title">
                                        <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" itemprop="name">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h6>
                                    <div class="product-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                        <?php if ($discount_percent > 0): ?>
                                            <span class="price-old"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                                            <span class="price-current" itemprop="price" content="<?php echo $final_price; ?>">
                                                <?php echo number_format($final_price, 0, ',', '.'); ?>đ
                                            </span>
                                        <?php else: ?>
                                            <span class="price-current" itemprop="price" content="<?php echo $product['price']; ?>">
                                                <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                            </span>
                                        <?php endif; ?>
                                        <meta itemprop="priceCurrency" content="VND">
                                        <link itemprop="availability" href="https://schema.org/<?php echo $product['stock'] > 0 ? 'InStock' : 'OutOfStock'; ?>">
                                    </div>
                                    <div class="product-meta">
                                        <span class="<?php echo $stock_status; ?>" aria-label="Tình trạng: <?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>">
                                            <i class="fas fa-box" aria-hidden="true"></i>
                                            <?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="pagination-wrapper" aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort='.$sort : ''; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                if ($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort='.$sort : ''; ?>">1</a>
                                    </li>
                                    <?php if ($start > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort='.$sort : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end < $total_pages): ?>
                                    <?php if ($end < $total_pages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort='.$sort : ''; ?>"><?php echo $total_pages; ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort='.$sort : ''; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        <a href="product_list.php" class="btn btn-dark">Xem tất cả sản phẩm</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">SNOWBOARD SHOP</h5>
                    <p class="text-white-50">Điểm đến lý tưởng cho những người đam mê trượt tuyết và thể thao mùa đông.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Liên kết</h6>
                    <ul class="list-unstyled">
                        <li><a href="home.php" class="text-white-50 text-decoration-none">Trang chủ</a></li>
                        <li><a href="product_list.php" class="text-white-50 text-decoration-none">Sản phẩm</a></li>
                        <li><a href="cart.php" class="text-white-50 text-decoration-none">Giỏ hàng</a></li>
                        <li><a href="order_history.php" class="text-white-50 text-decoration-none">Đơn hàng</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Chính sách</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Chính sách bảo mật</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Điều khoản sử dụng</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Chính sách đổi trả</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Hướng dẫn mua hàng</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Liên hệ</h6>
                    <ul class="list-unstyled text-white-50">
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Quận XYZ, TP.HCM</li>
                        <li><i class="fas fa-phone me-2"></i>0123 456 789</li>
                        <li><i class="fas fa-envelope me-2"></i>info@snowboardshop.vn</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center text-white-50">
                <p class="mb-0">&copy; 2025 Snowboard Shop. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="addToCartToast" class="toast" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Thành công</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Đã thêm sản phẩm vào giỏ hàng!
            </div>
        </div>
    </div>

    <script src="/Web_TMDT/config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Web_TMDT/Js/User/product_list.js?v=<?= time() ?>"></script>
    <script>
        // Update cart count on page load (handled by product_list.js now)
        // No need for inline script - updateCartCount() is called in product_list.js
        
        // Update on storage change (when cart updated in other tabs)
        window.addEventListener('storage', function(e) {
            if (e.key === 'cart') {
                updateCartCount();
            }
        });
    </script>

    <!-- Back to Top Button -->
    <button id="backToTopBtn" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Back to Top functionality
        const backToTopBtn = document.getElementById('backToTopBtn');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.opacity = '1';
                backToTopBtn.style.visibility = 'visible';
            } else {
                backToTopBtn.style.opacity = '0';
                backToTopBtn.style.visibility = 'hidden';
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
