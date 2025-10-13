# Script: Stop Port 3000 Process
# Mục đích: Dừng process đang chạy trên port 3000 (Live Server, Node.js, etc.)

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  DỪNG PROCESS TRÊN PORT 3000" -ForegroundColor Yellow
Write-Host "========================================`n" -ForegroundColor Cyan

# Tìm process trên port 3000
Write-Host "Đang tìm process trên port 3000..." -ForegroundColor Yellow

try {
    $connection = Get-NetTCPConnection -LocalPort 3000 -ErrorAction Stop
    $processId = $connection.OwningProcess
    $process = Get-Process -Id $processId -ErrorAction Stop
    
    Write-Host "`n✅ Tìm thấy:" -ForegroundColor Green
    Write-Host "  • Process: $($process.ProcessName)" -ForegroundColor White
    Write-Host "  • PID: $($process.Id)" -ForegroundColor White
    Write-Host "  • Path: $($process.Path)" -ForegroundColor Gray
    
    # Xác nhận trước khi kill
    Write-Host "`n⚠️  Bạn có muốn dừng process này không? (Y/N): " -ForegroundColor Yellow -NoNewline
    $confirm = Read-Host
    
    if ($confirm -eq 'Y' -or $confirm -eq 'y') {
        Stop-Process -Id $processId -Force
        Write-Host "`n✅ Đã dừng process thành công!" -ForegroundColor Green
        Start-Sleep -Seconds 1
    } else {
        Write-Host "`n❌ Đã hủy." -ForegroundColor Red
    }
    
} catch {
    Write-Host "`n✅ Không có process nào đang chạy trên port 3000" -ForegroundColor Green
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  KẾT QUẢ" -ForegroundColor Yellow
Write-Host "========================================`n" -ForegroundColor Cyan

# Check lại port 3000
$stillRunning = Get-NetTCPConnection -LocalPort 3000 -ErrorAction SilentlyContinue

if ($stillRunning) {
    Write-Host "⚠️  Port 3000 vẫn đang được sử dụng" -ForegroundColor Yellow
} else {
    Write-Host "✅ Port 3000 đã được giải phóng" -ForegroundColor Green
}

Write-Host "`n📝 Bước tiếp theo:" -ForegroundColor Cyan
Write-Host "  1. Đóng tất cả browser tabs" -ForegroundColor White
Write-Host "  2. Mở: http://localhost/Web_TMDT/view/User/product_list.php" -ForegroundColor White
Write-Host "  3. Nhấn Ctrl+F5 để hard refresh`n" -ForegroundColor White

Write-Host "📄 Chi tiết: ERR_TOO_MANY_REDIRECTS_FIX.md`n" -ForegroundColor Gray
