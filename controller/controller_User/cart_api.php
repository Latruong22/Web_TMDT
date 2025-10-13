<?php
/**
 * CART CONTROLLER - Xử lý các thao tác giỏ hàng
 * 
 * Endpoints:
 * - GET /api/cart - Lấy danh sách sản phẩm trong giỏ hàng
 * - POST /api/cart/add - Thêm sản phẩm vào giỏ hàng
 * - PUT /api/cart/update - Cập nhật số lượng sản phẩm
 * - DELETE /api/cart/remove - Xóa sản phẩm khỏi giỏ hàng
 * - GET /api/cart/suggested - Lấy sản phẩm gợi ý
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../model/database.php';
require_once '../../model/product_model.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action);
            break;
        case 'POST':
            handlePostRequest($action);
            break;
        case 'PUT':
            handlePutRequest($action);
            break;
        case 'DELETE':
            handleDeleteRequest($action);
            break;
        default:
            sendResponse(405, ['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    sendResponse(500, ['error' => $e->getMessage()]);
}

function handleGetRequest($action) {
    switch ($action) {
        case 'suggested':
            getSuggestedProducts();
            break;
        case 'validate':
            validateCartItems();
            break;
        default:
            sendResponse(400, ['error' => 'Invalid action']);
    }
}

function handlePostRequest($action) {
    switch ($action) {
        case 'add':
            addToCart();
            break;
        case 'validate-promo':
            validatePromoCode();
            break;
        default:
            sendResponse(400, ['error' => 'Invalid action']);
    }
}

function handlePutRequest($action) {
    switch ($action) {
        case 'update':
            updateCartItem();
            break;
        default:
            sendResponse(400, ['error' => 'Invalid action']);
    }
}

function handleDeleteRequest($action) {
    switch ($action) {
        case 'remove':
            removeFromCart();
            break;
        case 'clear':
            clearCart();
            break;
        default:
            sendResponse(400, ['error' => 'Invalid action']);
    }
}

/**
 * Lấy sản phẩm gợi ý dựa trên giỏ hàng hiện tại
 */
function getSuggestedProducts() {
    global $conn;
    
    // Lấy 4 sản phẩm ngẫu nhiên có status = 'active'
    $query = "SELECT product_id, name, price, manual_discount, image, category_id 
              FROM products 
              WHERE status = 'active' 
              ORDER BY RAND() 
              LIMIT 4";
              
    $result = $conn->query($query);
    
    if (!$result) {
        sendResponse(500, ['error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $originalPrice = $row['price'];
        $discountedPrice = $originalPrice * (100 - $row['manual_discount']) / 100;
        
        // Tạo đường dẫn hình ảnh
        $imagePath = getProductImagePath($row['product_id'], $row['image']);
        
        $products[] = [
            'id' => (int)$row['product_id'],
            'name' => $row['name'],
            'price' => (int)$discountedPrice,
            'originalPrice' => (int)$originalPrice,
            'discount' => (int)$row['manual_discount'],
            'image' => $imagePath,
            'category_id' => (int)$row['category_id']
        ];
    }
    
    sendResponse(200, [
        'success' => true,
        'products' => $products
    ]);
}

/**
 * Thêm sản phẩm vào giỏ hàng (server-side validation)
 */
function addToCart() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        sendResponse(400, ['error' => 'Missing product ID']);
        return;
    }
    
    $productId = (int)$input['product_id'];
    $quantity = (int)($input['quantity'] ?? 1);
    $size = $input['size'] ?? null;
    
    // Validate product exists and is active
    $product = getProductById($productId);
    if (!$product || $product['status'] !== 'active') {
        sendResponse(400, ['error' => 'Product not found or inactive']);
        return;
    }
    
    // Check stock
    if ($product['stock'] < $quantity) {
        sendResponse(400, [
            'error' => 'Insufficient stock',
            'available_stock' => (int)$product['stock']
        ]);
        return;
    }
    
    // Calculate price with discount
    $originalPrice = $product['price'];
    $discountedPrice = $originalPrice * (100 - $product['manual_discount']) / 100;
    
    // Create cart item
    $cartItem = [
        'id' => $productId,
        'name' => $product['name'],
        'price' => (int)$discountedPrice,
        'originalPrice' => (int)$originalPrice,
        'quantity' => $quantity,
        'size' => $size,
        'image' => getProductImagePath($productId, $product['image']),
        'color' => 'Đen', // Default color
        'max_stock' => (int)$product['stock']
    ];
    
    sendResponse(200, [
        'success' => true,
        'message' => 'Product added to cart',
        'cart_item' => $cartItem
    ]);
}

/**
 * Validate promo code
 */
function validatePromoCode() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['promo_code']) || !isset($input['subtotal'])) {
        sendResponse(400, ['error' => 'Missing promo code or subtotal']);
        return;
    }
    
    $promoCode = strtoupper(trim($input['promo_code']));
    $subtotal = (float)$input['subtotal'];
    
    // Predefined promo codes (in real app, store in database)
    $promoCodes = [
        'WELCOME10' => [
            'type' => 'percentage',
            'value' => 10,
            'min_order' => 200000,
            'description' => 'Giảm 10% cho đơn hàng từ 200,000₫'
        ],
        'SAVE50K' => [
            'type' => 'fixed',
            'value' => 50000,
            'min_order' => 300000,
            'description' => 'Giảm 50,000₫ cho đơn hàng từ 300,000₫'
        ],
        'SNOWBOARD20' => [
            'type' => 'percentage',
            'value' => 20,
            'min_order' => 500000,
            'description' => 'Giảm 20% cho đơn hàng từ 500,000₫'
        ],
        'FREESHIP' => [
            'type' => 'shipping',
            'value' => 30000,
            'min_order' => 100000,
            'description' => 'Miễn phí vận chuyển cho đơn hàng từ 100,000₫'
        ]
    ];
    
    if (!isset($promoCodes[$promoCode])) {
        sendResponse(400, [
            'success' => false,
            'error' => 'Mã giảm giá không hợp lệ'
        ]);
        return;
    }
    
    $promo = $promoCodes[$promoCode];
    
    if ($subtotal < $promo['min_order']) {
        sendResponse(400, [
            'success' => false,
            'error' => "Mã giảm giá yêu cầu đơn hàng tối thiểu " . number_format($promo['min_order']) . "₫"
        ]);
        return;
    }
    
    // Calculate discount
    $discountAmount = 0;
    switch ($promo['type']) {
        case 'percentage':
            $discountAmount = $subtotal * $promo['value'] / 100;
            break;
        case 'fixed':
            $discountAmount = $promo['value'];
            break;
        case 'shipping':
            $discountAmount = $promo['value']; // Will be applied to shipping
            break;
    }
    
    sendResponse(200, [
        'success' => true,
        'promo_code' => $promoCode,
        'type' => $promo['type'],
        'discount_amount' => (int)$discountAmount,
        'description' => $promo['description']
    ]);
}

/**
 * Validate cart items (check stock, price changes, etc.)
 */
function validateCartItems() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['cart_items'])) {
        sendResponse(400, ['error' => 'Missing cart items']);
        return;
    }
    
    $cartItems = $input['cart_items'];
    $validatedItems = [];
    $hasChanges = false;
    
    foreach ($cartItems as $item) {
        $productId = (int)$item['id'];
        $requestedQuantity = (int)$item['quantity'];
        
        // Get current product data
        $product = getProductById($productId);
        
        if (!$product || $product['status'] !== 'active') {
            // Product no longer available
            $hasChanges = true;
            continue;
        }
        
        // Check price changes
        $currentPrice = $product['price'] * (100 - $product['manual_discount']) / 100;
        $cartPrice = (float)$item['price'];
        
        if (abs($currentPrice - $cartPrice) > 0.01) {
            $hasChanges = true;
        }
        
        // Check stock
        $availableQuantity = min($requestedQuantity, (int)$product['stock']);
        if ($availableQuantity < $requestedQuantity) {
            $hasChanges = true;
        }
        
        $validatedItems[] = [
            'id' => $productId,
            'name' => $product['name'],
            'price' => (int)$currentPrice,
            'originalPrice' => (int)$product['price'],
            'quantity' => $availableQuantity,
            'requested_quantity' => $requestedQuantity,
            'size' => $item['size'] ?? null,
            'image' => getProductImagePath($productId, $product['image']),
            'color' => $item['color'] ?? 'Đen',
            'max_stock' => (int)$product['stock'],
            'price_changed' => abs($currentPrice - $cartPrice) > 0.01,
            'stock_limited' => $availableQuantity < $requestedQuantity
        ];
    }
    
    sendResponse(200, [
        'success' => true,
        'has_changes' => $hasChanges,
        'validated_items' => $validatedItems
    ]);
}

/**
 * Update cart item quantity
 */
function updateCartItem() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
        sendResponse(400, ['error' => 'Missing product ID or quantity']);
        return;
    }
    
    $productId = (int)$input['product_id'];
    $quantity = (int)$input['quantity'];
    
    if ($quantity < 1 || $quantity > 99) {
        sendResponse(400, ['error' => 'Invalid quantity']);
        return;
    }
    
    // Validate product and stock
    $product = getProductById($productId);
    if (!$product || $product['status'] !== 'active') {
        sendResponse(400, ['error' => 'Product not found or inactive']);
        return;
    }
    
    if ($product['stock'] < $quantity) {
        sendResponse(400, [
            'error' => 'Insufficient stock',
            'available_stock' => (int)$product['stock']
        ]);
        return;
    }
    
    sendResponse(200, [
        'success' => true,
        'message' => 'Cart item updated',
        'product_id' => $productId,
        'quantity' => $quantity,
        'max_stock' => (int)$product['stock']
    ]);
}

/**
 * Remove item from cart
 */
function removeFromCart() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        sendResponse(400, ['error' => 'Missing product ID']);
        return;
    }
    
    $productId = (int)$input['product_id'];
    
    sendResponse(200, [
        'success' => true,
        'message' => 'Item removed from cart',
        'product_id' => $productId
    ]);
}

/**
 * Clear all items from cart
 */
function clearCart() {
    sendResponse(200, [
        'success' => true,
        'message' => 'Cart cleared'
    ]);
}

/**
 * Get product image path
 */
function getProductImagePath($productId, $defaultImage) {
    // Try to get first image from product folder
    $folderPath = __DIR__ . "/../../Images/product/Sp{$productId}/";
    
    // Log for debugging
    error_log("DEBUG: Looking for images in: " . $folderPath);
    
    if (is_dir($folderPath)) {
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                $imagePath = "../../Images/product/Sp{$productId}/{$file}";
                error_log("DEBUG: Found image: " . $imagePath);
                return $imagePath;
            }
        }
    }
    
    // Fallback to default image or placeholder
    if ($defaultImage) {
        $fallbackPath = "../../Images/product/{$defaultImage}";
        error_log("DEBUG: Using fallback image: " . $fallbackPath);
        return $fallbackPath;
    }
    
    // Final fallback - return placeholder
    error_log("DEBUG: Using final fallback: ../../Images/product/placeholder.jpg");
    return "../../Images/product/placeholder.jpg";
}

/**
 * Send JSON response
 */
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// getProductById function is already defined in product_model.php
?>