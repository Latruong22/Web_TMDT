<?php
session_start();
require_once '../../model/auth_middleware.php';

// Cho phép guest xem product detail
if (isset($_SESSION['user_id'])) {
    checkSessionTimeout();
}

require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';

// Lấy product_id từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: product_list.php');
    exit;
}

// Lấy thông tin sản phẩm
$product = getProductById($product_id);

if (!$product) {
    header('Location: product_list.php');
    exit;
}

// Lấy thông tin category để hiển thị brand name
$category = getCategoryById($product['category_id']);
$brand_name = $category ? strtoupper($category['name']) : 'SNOWBOARD';

// Lấy sản phẩm liên quan (cùng category, khác product_id)
$all_products = getAllProducts();
$related_products = array_filter($all_products, function($p) use ($product_id, $product) {
    return $p['product_id'] != $product_id 
           && $p['category_id'] == $product['category_id'] 
           && $p['status'] === 'active';
});
$related_products = array_slice($related_products, 0, 6);

// Helper function: Get first image from product folder
function getProductThumbnail($product_id, $fallback_image = '') {
    $sp_folder = "Sp" . $product_id;
    $folder_path = $_SERVER['DOCUMENT_ROOT'] . "/Web_TMDT/Images/product/" . $sp_folder . "/";
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
        // Convert relative database path to absolute
        return "/Web_TMDT/" . $fallback_image;
    }
    
    // Last resort: placeholder
    return "/Web_TMDT/Images/product/placeholder.jpg";
}

// ==================================================
// IMAGE LOADING - FIXED VERSION (ABSOLUTE PATH)
// ==================================================

// Use ABSOLUTE path from /Web_TMDT/ to avoid relative path issues
$sp_folder_name = "Sp" . $product_id;
$image_folder_filesystem = $_SERVER['DOCUMENT_ROOT'] . "/Web_TMDT/Images/product/" . $sp_folder_name . "/";
$image_folder_url = "/Web_TMDT/Images/product/" . $sp_folder_name . "/";

// Try to load images from Sp{id} folder
$detail_images = [];

if (is_dir($image_folder_filesystem)) {
    $files = scandir($image_folder_filesystem);
    if ($files) {
        foreach ($files as $file) {
            // Skip . and .. and check for image extensions
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                // Use ABSOLUTE URL from root
                $detail_images[] = $image_folder_url . $file;
            }
        }
    }
}

// Sort images naturally (1.jpg before 10.jpg)
if (!empty($detail_images)) {
    natsort($detail_images);
    $detail_images = array_values($detail_images); // Re-index array
}

// Fallback to main product image if no detail images found
if (empty($detail_images)) {
    // Also use absolute path for fallback
    $detail_images[] = "/Web_TMDT/Images/product/" . $product['image'];
}

// Debug output
echo "<!-- DEBUG: Loaded " . count($detail_images) . " images for product $product_id -->";
echo "<!-- DEBUG: Folder path: " . $image_folder_filesystem . " -->";
echo "<!-- DEBUG: Folder exists: " . (is_dir($image_folder_filesystem) ? 'YES' : 'NO') . " -->";
echo "<!-- DEBUG: Image URLs: " . implode(", ", $detail_images) . " -->";

// ==================================================
// BANNER MAPPING - CATEGORY BASED (ABSOLUTE PATH)
// ==================================================

$category_banners = [
    4 => 'baner_product.jpg',  // Ván trượt tuyết (Snowboards)
    5 => 'ski-boots4.jpg',     // Giày trượt tuyết (Boots)
    6 => 'goggles1.jpg',       // Phụ kiện (Goggles/Accessories)
];

// Get banner based on category_id
$category_id = intval($product['category_id']);
$banner_filename = isset($category_banners[$category_id]) 
    ? $category_banners[$category_id] 
    : 'baner_product.jpg';

// Verify banner file exists
$banner_path_filesystem = $_SERVER['DOCUMENT_ROOT'] . "/Web_TMDT/Images/baner/" . $banner_filename;
if (!file_exists($banner_path_filesystem)) {
    // Fallback to default if banner not found
    $banner_filename = 'baner_product.jpg';
}

// Use absolute URL
$banner_image_url = "/Web_TMDT/Images/baner/" . $banner_filename;

// Debug output
echo "<!-- DEBUG BANNER: Product ID: {$product['product_id']}, Category ID: $category_id, Banner: $banner_filename -->";
echo "<!-- DEBUG BANNER: Banner URL: $banner_image_url -->";
echo "<!-- DEBUG BANNER: Banner file exists: " . (file_exists($banner_path_filesystem) ? 'YES' : 'NO') . " -->";

// Kiểm tra category để hiển thị size selector
// Category 4 = Snowboards, Category 5 = Boots (Giày), Category 6 = Accessories (không cần size)
$is_snowboard = ($product['category_id'] == 4);
$is_shoe = ($product['category_id'] == 5) || 
           (stripos($product['name'], 'giày') !== false) || 
           (stripos($product['name'], 'boot') !== false);
$needs_size_selector = $is_snowboard || $is_shoe;

// Tính giá sau discount
$final_price = $product['price'] - ($product['price'] * $product['manual_discount'] / 100);
$discount_amount = $product['price'] - $final_price;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($product['name']) ?> - Snowboard Shop</title>
    <meta name="description" content="<?= htmlspecialchars(substr($product['description'], 0, 160)) ?>">
    <meta name="keywords" content="snowboard, <?= htmlspecialchars($product['name']) ?>, mua <?= htmlspecialchars($product['name']) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($product['name']) ?> - Snowboard Shop">
    <meta property="og:description" content="<?= htmlspecialchars(substr($product['description'], 0, 160)) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($detail_images[0]) ?>">
    <meta property="og:type" content="product">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/Web_TMDT/config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/Web_TMDT/Css/User/product_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/Web_TMDT/Images/logo/logo.jpg">
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
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart me-1"></i>Giỏ hàng
                                <span class="cart-badge" id="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="order_history.php"><i class="fas fa-history me-1"></i>Đơn hàng</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['fullname']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="../../controller/controller_User/controller.php?action=logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Category Banner (Image Only - No Overlay) -->
    <div class="category-banner">
        <img src="<?= $banner_image_url ?>" 
             alt="<?= htmlspecialchars($product['name']) ?> Banner" 
             class="banner-image">
    </div>

    <!-- Breadcrumb -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="product_list.php">Sản phẩm</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Detail Section -->
    <section class="product-detail-section py-5">
        <div class="container">
            <div class="row">
                <!-- Left: Image Gallery -->
                <div class="col-lg-6 mb-4">
                    <div class="product-gallery">
                        <!-- Main Image with Zoom -->
                        <div class="main-image-container">
                            <img src="<?= htmlspecialchars($detail_images[0]) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="main-image" 
                                 id="mainImage">
                            <div class="zoom-lens" id="zoomLens"></div>
                            <button class="fullscreen-btn" id="fullscreenBtn">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>

                        <!-- Thumbnail Gallery -->
                        <?php if (count($detail_images) > 1): ?>
                        <div class="thumbnail-gallery mt-3">
                            <?php foreach ($detail_images as $index => $image): ?>
                                <div class="thumbnail-item <?= $index === 0 ? 'active' : '' ?>" 
                                     data-image="<?= htmlspecialchars($image) ?>">
                                    <img src="<?= htmlspecialchars($image) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?> - Ảnh <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Product Info -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <!-- Brand/Category Name -->
                        <div class="product-brand mb-2">
                            <?= htmlspecialchars($brand_name) ?>
                        </div>
                        
                        <!-- Product Name -->
                        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                        <!-- Rating & Stock -->
                        <div class="product-meta d-flex align-items-center mb-3">
                            <div class="rating me-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                                <span class="ms-2">(4.5/5)</span>
                            </div>
                            <div class="stock-status">
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Còn hàng (<?= $product['stock'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Hết hàng
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Price Section with Label -->
                        <div class="product-price-section mb-4">
                            <div class="price-label mb-1">Price</div>
                            <div class="price-display">
                                <span class="current-price"><?= number_format($final_price, 0, ',', '.') ?>₫</span>
                                <?php if ($product['manual_discount'] > 0): ?>
                                    <span class="original-price text-decoration-line-through text-muted ms-2">
                                        <?= number_format($product['price'], 0, ',', '.') ?>₫
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="promotion-text mt-2">
                                <span class="text-danger">
                                    Xem mô tả sản phẩm.
                                </span>
                                <a href="#" class="view-details-link ms-1" id="viewDetailsLink">view details</a>
                            </div>
                        </div>

                        <!-- Size Selector (Snowboards and Boots only) -->
                        <?php if ($needs_size_selector): ?>
                        <div class="size-selector mb-3">
                            <div class="size-label mb-2">
                                Size: <span class="size-placeholder">Select a Size</span>
                            </div>
                            <div class="size-options-grid" id="sizeOptions">
                                <?php 
                                // Xác định size options dựa trên category
                                if ($is_shoe) {
                                    // Giày trượt tuyết - size EU
                                    $sizes = ['38', '39', '40', '41', '42', '43', '44', '45', '46'];
                                } else {
                                    // Snowboards - size theo chiều rộng
                                    $sizes = ['157(Wide)', '161(Wide)', '163(Ultra-Wide)'];
                                }
                                foreach ($sizes as $size): 
                                ?>
                                    <label class="size-option-label">
                                        <input type="radio" name="product-size" value="<?= htmlspecialchars($size) ?>" class="size-radio">
                                        <span class="size-button"><?= htmlspecialchars($size) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Size Specifications Link (only show if size selector exists) -->
                        <?php if ($needs_size_selector): ?>
                        <div class="size-specs-link mb-4">
                            <a href="#" class="specs-link">Size Specifications</a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>

                        <!-- Action Buttons with Quantity Dropdown -->
                        <div class="product-actions-wrapper">
                            <?php if ($product['stock'] > 0): ?>
                                <div class="action-row">
                                    <!-- Quantity Dropdown -->
                                    <select class="quantity-dropdown" id="quantity">
                                        <?php for ($i = 1; $i <= $product['stock']; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    
                                    <!-- Add to Cart Button (Yellow) -->
                                    <button class="btn-add-to-cart" id="addToCartBtn">
                                        <i class="fas fa-shopping-cart me-2"></i>
                                        Add To Cart
                                    </button>
                                    
                                    <!-- Wishlist Heart Icon -->
                                    <button class="btn-wishlist-icon">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                                
                                <!-- View Entire Kit Link -->
                                <div class="view-kit-link mt-3">
                                    <a href="#" class="kit-link">
                                        <i class="far fa-heart me-1"></i>
                                        View Entire Kit
                                    </a>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg w-100" disabled>
                                    <i class="fas fa-times-circle me-2"></i>
                                    Sản phẩm đã hết hàng
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <section class="related-products py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">
                <i class="fas fa-snowflake me-2"></i>
                Sản phẩm liên quan
            </h2>
            <div class="row">
                <?php foreach ($related_products as $related): 
                    $related_final_price = $related['price'] - ($related['price'] * $related['manual_discount'] / 100);
                ?>
                    <div class="col-md-4 col-lg-2 mb-4">
                        <a href="product_detail.php?id=<?= $related['product_id'] ?>" class="text-decoration-none">
                            <div class="related-product-card">
                                <div class="related-product-image">
                                    <img src="<?= getProductThumbnail($related['product_id'], $related['image']) ?>" 
                                         alt="<?= htmlspecialchars($related['name']) ?>">
                                    <?php if ($related['manual_discount'] > 0): ?>
                                        <span class="badge-discount">-<?= $related['manual_discount'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="related-product-info">
                                    <h6 class="related-product-name"><?= htmlspecialchars($related['name']) ?></h6>
                                    <div class="related-product-price">
                                        <span class="price"><?= number_format($related_final_price, 0, ',', '.') ?>₫</span>
                                        <?php if ($related['manual_discount'] > 0): ?>
                                            <span class="old-price"><?= number_format($related['price'], 0, ',', '.') ?>₫</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Content Blocks - Bootstrap Creative Design -->
    <section class="content-blocks py-5">
        <div class="container">
            <!-- Block One - Image Left, Text Right -->
            <div class="content-block block-one mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="block-image" data-aos="fade-right">
                            <img src="/Web_TMDT/Images/baner/skis-block-one.jpg" 
                                 alt="Premium Snowboard Equipment" 
                                 class="img-fluid rounded-4 shadow-lg">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="block-content" data-aos="fade-left">
                            <h2 class="block-title mb-4">
                                <i class="fas fa-award text-warning me-2"></i>
                                Chất Lượng Hàng Đầu
                            </h2>
                            <p class="block-text mb-4">
                                Sản phẩm được nhập khẩu chính hãng từ các thương hiệu uy tín trên thế giới. 
                                Chúng tôi cam kết 100% hàng chính hãng, bảo hành đầy đủ theo tiêu chuẩn quốc tế.
                            </p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Chất liệu cao cấp, bền bỉ</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Công nghệ tiên tiến</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Kiểm định nghiêm ngặt</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Bảo hành 12 tháng</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Block Two - Split 50/50 with Background -->
            <div class="content-block block-two mb-5 bg-light rounded-4 p-5">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="block-content" data-aos="fade-up">
                            <h2 class="block-title mb-4">
                                <i class="fas fa-snowflake text-primary me-2"></i>
                                Thiết Kế Đột Phá
                            </h2>
                            <p class="block-text">
                                Được thiết kế bởi các chuyên gia hàng đầu, sản phẩm mang đến trải nghiệm 
                                trượt tuyết hoàn hảo với độ cân bằng và kiểm soát tuyệt vời.
                            </p>
                            <div class="stats-grid mt-4">
                                <div class="stat-item">
                                    <h3 class="stat-number">15+</h3>
                                    <p class="stat-label">Năm Kinh Nghiệm</p>
                                </div>
                                <div class="stat-item">
                                    <h3 class="stat-number">50K+</h3>
                                    <p class="stat-label">Khách Hàng</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="block-image" data-aos="fade-up" data-aos-delay="200">
                            <img src="/Web_TMDT/Images/baner/skis-block-two.jpg" 
                                 alt="Innovative Design" 
                                 class="img-fluid rounded-4 shadow block-image">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Block Three - Full Width Image with Overlay Text -->
            <div class="content-block block-three">
                <div class="block-image-overlay" data-aos="zoom-in">
                    <img src="/Web_TMDT/Images/baner/skis-block-three.jpg" 
                         alt="Adventure Awaits" 
                         class="img-fluid rounded-4 w-100">
                    <div class="overlay-content">
                        <h2 class="overlay-title">Sẵn Sàng Chinh Phục Tuyết Trắng</h2>
                        <p class="overlay-text">
                            Đừng chỉ mơ về chuyến phiêu lưu tuyết trắng. Hãy biến nó thành hiện thực cùng 
                            trang thiết bị chuyên nghiệp từ Snowboard Shop.
                        </p>
                        <a href="product_list.php" class="btn btn-light btn-lg mt-3">
                            <i class="fas fa-shopping-bag me-2"></i>Khám Phá Thêm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fullscreen Image Modal -->
    <div class="fullscreen-modal" id="fullscreenModal">
        <button class="close-fullscreen" id="closeFullscreen">
            <i class="fas fa-times"></i>
        </button>
        <img src="" alt="Fullscreen Image" id="fullscreenImage">
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="cartToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto">Thành công</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Đã thêm sản phẩm vào giỏ hàng!
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/Web_TMDT/config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Product data
        const productData = {
            id: <?= $product_id ?>,
            name: <?= json_encode($product['name']) ?>,
            price: <?= $final_price ?>,
            image: <?= json_encode($product['image']) ?>,
            stock: <?= $product['stock'] ?>,
            isShoe: <?= $is_shoe ? 'true' : 'false' ?>,
            needsSize: <?= $needs_size_selector ? 'true' : 'false' ?>,
            isLoggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>
        };
    </script>
    <script src="/Web_TMDT/Js/User/product_detail.js?v=<?= time() ?>"></script>

    <!-- Promotion Details Modal -->
    <div class="modal fade" id="promotionModal" tabindex="-1" aria-labelledby="promotionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content promotion-modal">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h5 class="modal-title mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Chi tiết sản phẩm: <span class="text-dark"><?= htmlspecialchars($product['name']) ?></span>
                    </h5>
                    
                    <div class="product-details-section">
                        <h6 class="detail-section-title">
                            <i class="fas fa-align-left me-2"></i>Mô tả sản phẩm
                        </h6>
                        <div class="detail-content">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        </div>
                    </div>

                    <div class="modal-footer-note mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Sản phẩm chính hãng, bảo hành 12 tháng. Miễn phí vận chuyển cho đơn hàng từ 5 triệu đồng.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5 mt-5">
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
