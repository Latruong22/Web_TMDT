<?php
/**
 * Admin Email Management Page
 * Gửi email thông báo, khuyến mãi cho users
 */

session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';

// Kiểm tra quyền admin
requireAdmin();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Email - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
    <link rel="stylesheet" href="../../Css/Admin/admin_email.css">
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
            <a href="admin_product.php" class="nav-link">
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
            <a href="admin_email.php" class="nav-link active">
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
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Quản lý Email</h5>
            </div>
            <div class="navbar-right">
                <span class="admin-info">
                    <i class="fas fa-user-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                </span>
            </div>
        </nav>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">

            <!-- Email Form -->
            <div class="content-body">
                <div class="row">
                    <!-- Form gửi email -->
                    <div class="col-lg-8">
                        <div class="card email-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-paper-plane me-2"></i>Soạn Email
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="emailForm">
                                    <!-- Chọn người nhận -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-users me-2"></i>Người nhận
                                        </label>
                                        <div class="recipient-options">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="recipientAll" value="all" checked>
                                                <label class="form-check-label" for="recipientAll">
                                                    <i class="fas fa-users me-1"></i>Tất cả users
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="recipientIndividual" value="individual">
                                                <label class="form-check-label" for="recipientIndividual">
                                                    <i class="fas fa-user me-1"></i>Chọn user cụ thể
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- User selection (ẩn mặc định) -->
                                    <div class="mb-4 user-selection-container" style="display: none;">
                                        <label class="form-label">Chọn users:</label>
                                        <div class="user-list-container" id="userListContainer">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Đang tải...</span>
                                                </div>
                                                <p class="text-muted mt-2 mb-0">Đang tải danh sách users...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Template selection -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-file-alt me-2"></i>Template
                                        </label>
                                        <select class="form-select" id="templateSelect">
                                            <option value="custom">Tùy chỉnh (Custom)</option>
                                            <option value="general">Thông báo chung (General)</option>
                                            <option value="promo">Khuyến mãi (Promotion)</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Chọn template hoặc tự soạn nội dung
                                        </small>
                                    </div>

                                    <!-- Subject -->
                                    <div class="mb-4">
                                        <label for="emailSubject" class="form-label fw-bold">
                                            <i class="fas fa-heading me-2"></i>Tiêu đề <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="emailSubject" 
                                               placeholder="VD: Thông báo khuyến mãi đặc biệt" required>
                                    </div>

                                    <!-- Message Editor -->
                                    <div class="mb-4">
                                        <label for="emailMessage" class="form-label fw-bold">
                                            <i class="fas fa-align-left me-2"></i>Nội dung <span class="text-danger">*</span>
                                        </label>
                                        
                                        <!-- Editor toolbar -->
                                        <div class="editor-toolbar">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="document.execCommand('bold', false, null);" title="Bold">
                                                <i class="fas fa-bold"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="document.execCommand('italic', false, null);" title="Italic">
                                                <i class="fas fa-italic"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="document.execCommand('underline', false, null);" title="Underline">
                                                <i class="fas fa-underline"></i>
                                            </button>
                                            <div class="vr mx-2"></div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="insertVariable('{fullname}');" title="Insert fullname">
                                                <i class="fas fa-user"></i> {fullname}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="insertVariable('{email}');" title="Insert email">
                                                <i class="fas fa-envelope"></i> {email}
                                            </button>
                                        </div>
                                        
                                        <!-- Content editable div -->
                                        <div class="email-editor" id="emailEditor" contenteditable="true" 
                                             data-placeholder="Nhập nội dung email tại đây... Sử dụng {fullname} để hiển thị tên người nhận.">
                                        </div>
                                        
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Sử dụng <code>{fullname}</code> và <code>{email}</code> để personalize email
                                        </small>
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary" id="sendEmailBtn">
                                            <i class="fas fa-paper-plane me-2"></i>Gửi Email
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="previewBtn">
                                            <i class="fas fa-eye me-2"></i>Xem trước
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="clearBtn">
                                            <i class="fas fa-eraser me-2"></i>Xóa
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Info & Preview sidebar -->
                    <div class="col-lg-4">
                        <!-- Quick Info -->
                        <div class="card info-card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Hướng dẫn</h6>
                            </div>
                            <div class="card-body">
                                <ul class="small mb-0">
                                    <li class="mb-2">Chọn <strong>Tất cả users</strong> để gửi email hàng loạt</li>
                                    <li class="mb-2">Hoặc chọn <strong>User cụ thể</strong> để gửi riêng lẻ</li>
                                    <li class="mb-2">Sử dụng <code>{fullname}</code> để hiển thị tên người nhận</li>
                                    <li class="mb-2">Click <strong>Xem trước</strong> để kiểm tra email</li>
                                    <li>Email sẽ được gửi qua Gmail SMTP</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Email templates -->
                        <div class="card template-card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Templates có sẵn</h6>
                            </div>
                            <div class="card-body">
                                <div class="template-item" data-template="general">
                                    <h6><i class="fas fa-bullhorn me-2"></i>Thông báo chung</h6>
                                    <p class="small text-muted mb-2">Template cho thông báo, cập nhật</p>
                                </div>
                                <div class="template-item" data-template="promo">
                                    <h6><i class="fas fa-gift me-2"></i>Khuyến mãi</h6>
                                    <p class="small text-muted mb-2">Template cho ưu đãi, giảm giá</p>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Click template để xem mẫu
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Xem trước Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent" class="email-preview"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script>
        // Toggle sidebar cho mobile
        document.getElementById('menuToggle')?.addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('active');
        });
        
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.remove('active');
        });
    </script>
    
    <!-- Custom JS -->
    <script src="../../Js/Admin/email.js"></script>
</body>
</html>
