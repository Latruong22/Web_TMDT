# ========================================
# 🚀 SERVE WEB_TMDT PROJECT
# ========================================
# Script tự động serve PHP project với XAMPP
# Ngày: 13/10/2025

Write-Host "🚀 STARTING WEB_TMDT PROJECT..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Yellow

# 1. Kiểm tra XAMPP Apache
Write-Host "🔍 Kiểm tra XAMPP Apache..." -ForegroundColor Yellow

$apacheProcess = Get-Process httpd -ErrorAction SilentlyContinue
if ($apacheProcess) {
    Write-Host "✅ Apache đang chạy (PID: $($apacheProcess.Id -join ', '))" -ForegroundColor Green
} else {
    Write-Host "❌ Apache chưa chạy!" -ForegroundColor Red
    Write-Host "🔧 Đang khởi động XAMPP..." -ForegroundColor Yellow
    
    # Thử start XAMPP
    $xamppPath = "C:\xampp\xampp_control.exe"
    if (Test-Path $xamppPath) {
        Start-Process $xamppPath
        Write-Host "📋 XAMPP Control Panel đã mở - Hãy click Start Apache!" -ForegroundColor Cyan
        Write-Host "⏳ Chờ 5 giây..." -ForegroundColor Yellow
        Start-Sleep 5
    } else {
        Write-Host "❌ XAMPP không tìm thấy tại: $xamppPath" -ForegroundColor Red
        Write-Host "💡 Hãy cài đặt XAMPP hoặc update đường dẫn" -ForegroundColor Yellow
    }
}

# 2. Kiểm tra MySQL
Write-Host "`n🗄️  Kiểm tra MySQL..." -ForegroundColor Yellow

$mysqlProcess = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host "✅ MySQL đang chạy (PID: $($mysqlProcess.Id))" -ForegroundColor Green
} else {
    Write-Host "⚠️  MySQL chưa chạy - Cần thiết cho database!" -ForegroundColor Yellow
}

# 3. Kiểm tra port 80
Write-Host "`n🌐 Kiểm tra port 80..." -ForegroundColor Yellow

$port80 = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue
if ($port80) {
    Write-Host "✅ Port 80 đang hoạt động" -ForegroundColor Green
} else {
    Write-Host "❌ Port 80 không available!" -ForegroundColor Red
}

# 4. Kiểm tra project folder
Write-Host "`n📁 Kiểm tra project folder..." -ForegroundColor Yellow

$projectPath = "C:\xampp\htdocs\Web_TMDT"
if (Test-Path $projectPath) {
    Write-Host "✅ Project tồn tại: $projectPath" -ForegroundColor Green
} else {
    Write-Host "❌ Project không tìm thấy!" -ForegroundColor Red
    Write-Host "💡 Đảm bảo project ở: $projectPath" -ForegroundColor Yellow
}

# 5. Test XAMPP dashboard
Write-Host "`n🏠 Test XAMPP dashboard..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "http://localhost/" -TimeoutSec 5 -ErrorAction Stop
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ XAMPP dashboard accessible" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ XAMPP dashboard không truy cập được" -ForegroundColor Red
    Write-Host "💡 Đảm bảo Apache đã start trong XAMPP Control Panel" -ForegroundColor Yellow
}

# 6. Tạo quick URLs
Write-Host "`n🎯 PROJECT URLs:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Yellow

$urls = @(
    "http://localhost/Web_TMDT/",
    "http://localhost/Web_TMDT/view/User/home.php",
    "http://localhost/Web_TMDT/view/User/product_list.php", 
    "http://localhost/Web_TMDT/view/Admin/admin_home.php",
    "http://localhost/Web_TMDT/check_database.php"
)

foreach ($url in $urls) {
    Write-Host "🔗 $url" -ForegroundColor White
}

# 7. Mở browser tự động
Write-Host "`n🌐 Mở browser..." -ForegroundColor Yellow

$choice = Read-Host "`nBạn có muốn mở browser tự động? (y/n)"
if ($choice -eq 'y' -or $choice -eq 'Y' -or $choice -eq '') {
    try {
        Start-Process "http://localhost/Web_TMDT/"
        Write-Host "✅ Browser đã mở!" -ForegroundColor Green
    } catch {
        Write-Host "❌ Lỗi mở browser: $_" -ForegroundColor Red
    }
}

# 8. Development tips
Write-Host "`n💡 DEVELOPMENT TIPS:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "🔧 Edit files trong VS Code" -ForegroundColor White
Write-Host "💾 Save (Ctrl+S)" -ForegroundColor White  
Write-Host "🔄 Refresh browser (F5)" -ForegroundColor White
Write-Host "🐛 Debug với F12 → Console tab" -ForegroundColor White
Write-Host "🌐 Network tab để check requests" -ForegroundColor White

# 9. Monitoring commands
Write-Host "`n📊 MONITORING COMMANDS:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "Apache:  Get-Process httpd" -ForegroundColor White
Write-Host "MySQL:   Get-Process mysqld" -ForegroundColor White
Write-Host "Port 80: Get-NetTCPConnection -LocalPort 80 -State Listen" -ForegroundColor White
Write-Host "Logs:    C:\xampp\apache\logs\error.log" -ForegroundColor White

Write-Host "`n🎉 READY TO DEVELOP!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Yellow