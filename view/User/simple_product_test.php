<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Product Detail Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; margin-bottom: 30px; }
        .product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .image-section { }
        .main-image-container {
            width: 100%;
            max-width: 450px;
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 0 auto 20px auto;
        }
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .thumbnails {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: border 0.3s;
        }
        .thumbnail:hover,
        .thumbnail.active {
            border-color: #007bff;
        }
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .info-section h2 { color: #333; margin-bottom: 15px; }
        .price { font-size: 2rem; color: #dc3545; font-weight: bold; margin: 20px 0; }
        .old-price { text-decoration: line-through; color: #6c757d; font-size: 1.2rem; margin-right: 10px; }
        .discount { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.9rem; }
        .stock { color: #28a745; margin: 10px 0; }
        .description { line-height: 1.8; color: #555; margin: 20px 0; }
        .add-to-cart { 
            background: #007bff; 
            color: white; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 5px; 
            font-size: 1.1rem; 
            cursor: pointer; 
            margin-top: 20px;
        }
        .add-to-cart:hover { background: #0056b3; }
        .debug { 
            background: #fff3cd; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .debug h3 { color: #856404; margin-bottom: 10px; }
        .debug pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <?php
        require_once '../../model/database.php';
        require_once '../../model/product_model.php';

        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
        $product = getProductById($product_id);

        if (!$product) {
            echo "<h1>Product not found!</h1>";
            exit;
        }

        // Load images - SIMPLE VERSION
        $images = [];
        $folder_path = __DIR__ . "/../../Images/product/Sp" . $product_id . "/";
        
        if (is_dir($folder_path)) {
            $files = scandir($folder_path);
            foreach ($files as $file) {
                if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                    // Use absolute path from /Web_TMDT/
                    $images[] = "/Web_TMDT/Images/product/Sp" . $product_id . "/" . $file;
                }
            }
        }
        
        // Fallback
        if (empty($images)) {
            $images[] = "/Web_TMDT/Images/product/" . $product['image'];
        }
        
        $final_price = $product['price'] - ($product['price'] * $product['manual_discount'] / 100);
        ?>

        <h1><?= htmlspecialchars($product['name']) ?></h1>

        <!-- Debug Info -->
        <div class="debug">
            <h3>🔍 Debug Info</h3>
            <p><strong>Product ID:</strong> <?= $product_id ?></p>
            <p><strong>Category ID:</strong> <?= $product['category_id'] ?></p>
            <p><strong>Folder Path:</strong> <code><?= $folder_path ?></code></p>
            <p><strong>Folder Exists:</strong> <?= is_dir($folder_path) ? '✅ YES' : '❌ NO' ?></p>
            <p><strong>Images Found:</strong> <?= count($images) ?></p>
            <pre><?php print_r($images); ?></pre>
        </div>

        <div class="product-layout">
            <!-- Left: Images -->
            <div class="image-section">
                <div class="main-image-container">
                    <img src="<?= $images[0] ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="main-image" 
                         id="mainImage"
                         onerror="this.style.border='5px solid red'; this.alt='IMAGE FAILED TO LOAD: <?= $images[0] ?>';">
                </div>

                <?php if (count($images) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($images as $index => $img): ?>
                    <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                         onclick="changeImage('<?= $img ?>', this)">
                        <img src="<?= $img ?>" 
                             alt="Thumbnail <?= $index + 1 ?>"
                             onerror="this.style.border='3px solid red';">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Info -->
            <div class="info-section">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                
                <div class="price">
                    <span class="old-price"><?= number_format($product['price']) ?>đ</span>
                    <?= number_format($final_price) ?>đ
                    <span class="discount">-<?= $product['manual_discount'] ?>%</span>
                </div>

                <div class="stock">
                    ✅ Còn hàng: <?= $product['stock'] ?> sản phẩm
                </div>

                <div class="description">
                    <h3>Mô tả sản phẩm:</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>

                <button class="add-to-cart" onclick="alert('Đã thêm vào giỏ hàng!')">
                    🛒 Thêm vào giỏ hàng
                </button>
            </div>
        </div>

        <!-- Test other products -->
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3>Test Other Products:</h3>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                <a href="?id=<?= $i ?>" 
                   style="padding: 10px 20px; background: <?= $i == $product_id ? '#28a745' : '#007bff' ?>; color: white; text-decoration: none; border-radius: 5px;">
                    Product <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <script>
        function changeImage(src, thumbnail) {
            document.getElementById('mainImage').src = src;
            
            // Remove active class from all
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            
            // Add active to clicked
            thumbnail.classList.add('active');
        }

        // Log any image errors
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                console.error('Failed to load image:', this.src);
            });
            img.addEventListener('load', function() {
                console.log('Successfully loaded:', this.src);
            });
        });
    </script>
</body>
</html>
