# ============================================
# CLEANUP SCRIPT - DỌN DẸP FILES THỪA
# ============================================
# Ngày: 12/10/2025
# Mục đích: Xóa debug files và old documentation
# ============================================

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "🗑️  CLEANUP SCRIPT - WEB_TMDT" -ForegroundColor Yellow
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Set root directory
$rootDir = "c:\xampp\htdocs\Web_TMDT"
Set-Location $rootDir

# ============================================
# 1. XÓA DEBUG/TEST FILES
# ============================================
Write-Host "📋 Step 1: Xóa Debug/Test Files..." -ForegroundColor Green

$debugFiles = @(
    "view\User\check_database.php",
    "view\User\debug_detail.php",
    "view\User\debug_product.php",
    "view\User\fix_categories.php",
    "view\User\quick_fix_test.php",
    "view\User\simple_product_test.php",
    "view\User\test_image_paths.php",
    "check_images.php",
    "fix_database.php",
    "setup_product_folders.php"
)

$deletedCount = 0
foreach ($file in $debugFiles) {
    $fullPath = Join-Path $rootDir $file
    if (Test-Path $fullPath) {
        Remove-Item $fullPath -Force
        Write-Host "  ✅ Deleted: $file" -ForegroundColor Gray
        $deletedCount++
    } else {
        Write-Host "  ⚠️  Not found: $file" -ForegroundColor DarkGray
    }
}

Write-Host "  📊 Deleted $deletedCount debug files" -ForegroundColor Cyan
Write-Host ""

# ============================================
# 2. XÓA OLD DOCUMENTATION (OPTIONAL)
# ============================================
Write-Host "📋 Step 2: Xóa Old Documentation Files..." -ForegroundColor Green
Write-Host "  ⚠️  CẢNH BÁO: Sẽ xóa các file .md cũ" -ForegroundColor Yellow
Write-Host "  💡 File README.md, DEVELOPER_GUIDE.md, TODO.md sẽ được giữ lại" -ForegroundColor Cyan
Write-Host ""

$answer = Read-Host "  Bạn có muốn xóa old documentation không? (y/n)"

if ($answer -eq 'y' -or $answer -eq 'Y') {
    $oldDocs = @(
        "ADMIN_FEATURES_COMPLETED.md",
        "ADMIN_LAYOUT_CHECKLIST.md",
        "ADMIN_LAYOUT_SUMMARY.md",
        "ADMIN_LAYOUT_UPDATE.md",
        "ADMIN_MULTIPLE_IMAGES_SOLUTION.md",
        "BANNER_CATEGORY_FIX.md",
        "BUGFIX_PRICE_RANGE.md",
        "CONGRATULATIONS.md",
        "DAILY_SUMMARY_2025_10_11.md",
        "DAILY_SUMMARY_2025_10_12.md",
        "FINAL_COMPLETION_REPORT.md",
        "FINAL_IMAGE_FIX_COMPLETE.md",
        "LANDING_PAGE_COMPLETED.md",
        "MULTIPLE_IMAGES_IMPLEMENTATION.md",
        "NO_PYTHON_CONFIRMATION.md",
        "OPTIMAL_SOLUTION_RECOMMENDATION.md",
        "PRODUCT_DETAIL_100_PERCENT.md",
        "PRODUCT_DETAIL_DEEP_FIX.md",
        "PRODUCT_DETAIL_IMPROVEMENTS.md",
        "PRODUCT_DETAIL_PATH_FIX.md",
        "PRODUCT_DETAIL_QUICK_FIX.md",
        "PRODUCT_DETAIL_TROUBLESHOOTING.md",
        "PRODUCT_LIST_100_PERCENT.md",
        "PRODUCT_LIST_SUMMARY.md",
        "PROGRESS_REPORT.md",
        "QUICK_CHECKLIST.md",
        "SETUP_PRODUCT_FOLDERS_GUIDE.md",
        "SINGLE_HOME_PAGE.md",
        "TEST_PRODUCT_LIST.md",
        "UPDATE_SUMMARY.md",
        "VIETNAMESE_LANGUAGE_UPDATE.md"
    )
    
    $docDeletedCount = 0
    foreach ($doc in $oldDocs) {
        $fullPath = Join-Path $rootDir $doc
        if (Test-Path $fullPath) {
            Remove-Item $fullPath -Force
            Write-Host "  ✅ Deleted: $doc" -ForegroundColor Gray
            $docDeletedCount++
        }
    }
    
    Write-Host "  📊 Deleted $docDeletedCount documentation files" -ForegroundColor Cyan
} else {
    Write-Host "  ⏭️  Skipped documentation cleanup" -ForegroundColor Yellow
}

Write-Host ""

# ============================================
# 3. XÓA OLD SQL SCRIPTS (OPTIONAL)
# ============================================
Write-Host "📋 Step 3: Xóa Old SQL Scripts..." -ForegroundColor Green
Write-Host "  ⚠️  CẢNH BÁO: Sẽ xóa các file .sql (trừ snowboard_web.sql)" -ForegroundColor Yellow
Write-Host ""

$sqlAnswer = Read-Host "  Bạn có muốn xóa old SQL scripts không? (y/n)"

if ($sqlAnswer -eq 'y' -or $sqlAnswer -eq 'Y') {
    $oldSql = @(
        "insert_test_accounts.sql",
        "update_categories.sql",
        "update_product_images.sql"
    )
    
    $sqlDeletedCount = 0
    foreach ($sql in $oldSql) {
        $fullPath = Join-Path $rootDir $sql
        if (Test-Path $fullPath) {
            Remove-Item $fullPath -Force
            Write-Host "  ✅ Deleted: $sql" -ForegroundColor Gray
            $sqlDeletedCount++
        }
    }
    
    Write-Host "  📊 Deleted $sqlDeletedCount SQL files" -ForegroundColor Cyan
} else {
    Write-Host "  ⏭️  Skipped SQL cleanup" -ForegroundColor Yellow
}

Write-Host ""

# ============================================
# 4. SUMMARY
# ============================================
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "✅ CLEANUP HOÀN TẤT!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📊 SUMMARY:" -ForegroundColor Yellow
Write-Host "  • Debug files deleted: $deletedCount" -ForegroundColor White
if ($answer -eq 'y' -or $answer -eq 'Y') {
    Write-Host "  • Documentation files deleted: $docDeletedCount" -ForegroundColor White
}
if ($sqlAnswer -eq 'y' -or $sqlAnswer -eq 'Y') {
    Write-Host "  • SQL files deleted: $sqlDeletedCount" -ForegroundColor White
}
Write-Host ""
Write-Host "✅ FILES GIỮ LẠI:" -ForegroundColor Green
Write-Host "  • README.md" -ForegroundColor White
Write-Host "  • DEVELOPER_GUIDE.md" -ForegroundColor White
Write-Host "  • TODO.md" -ForegroundColor White
Write-Host "  • DAILY_SUMMARY_2025_10_12_FINAL.md (NEW)" -ForegroundColor White
Write-Host "  • snowboard_web.sql" -ForegroundColor White
Write-Host ""
Write-Host "🎉 Dự án đã được dọn dẹp gọn gàng!" -ForegroundColor Green
Write-Host ""

# ============================================
# 5. RECOMMENDATION
# ============================================
Write-Host "💡 KHUYẾN NGHỊ:" -ForegroundColor Yellow
Write-Host "  1. Backup database trước khi deploy" -ForegroundColor White
Write-Host "  2. Đọc DAILY_SUMMARY_2025_10_12_FINAL.md để hiểu toàn bộ thay đổi" -ForegroundColor White
Write-Host "  3. Test toàn bộ tính năng trước khi production" -ForegroundColor White
Write-Host "  4. Kiểm tra lại file permissions" -ForegroundColor White
Write-Host ""

Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
