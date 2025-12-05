<?php
// Hata raporlamayı aç (geliştirme için)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
session_start();

// Güvenlik ayarları
$PASSWORD = '123456'; // Buraya güçlü bir şifre koyun
$UPLOAD_DIR = 'files/'; // Upload klasörü (relative path)
$THUMB_DIR = 'files/thumbs/'; // Thumbnail klasörü

// Klasörleri oluştur
if (!is_dir($UPLOAD_DIR)) {
    if (!mkdir($UPLOAD_DIR, 0755, true)) {
        die('Klasör oluşturulamadı: ' . $UPLOAD_DIR);
    }
}

if (!is_dir($THUMB_DIR)) {
    if (!mkdir($THUMB_DIR, 0755, true)) {
        die('Thumbnail klasörü oluşturulamadı: ' . $THUMB_DIR);
    }
}

// Resim formatları
$IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

// Random string oluşturma fonksiyonu
function generateRandomString($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Resim boyutlandırma fonksiyonu (895x595)
function resizeImage($source, $destination, $targetWidth = 895, $targetHeight = 595, $quality = 80) {
    $info = getimagesize($source);
    if ($info === false) return false;
    
    $image = false;
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/bmp':
            $image = imagecreatefrombmp($source);
            break;
        default:
            return false;
    }
    
    if ($image === false) return false;
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Hedef boyutlar
    $newWidth = $targetWidth;
    $newHeight = $targetHeight;
    
    // Orantılı boyutlandırma (kırpma yapmadan)
    $ratio = $width / $height;
    if ($newWidth / $newHeight > $ratio) {
        $newWidth = $newHeight * $ratio;
    } else {
        $newHeight = $newWidth / $ratio;
    }
    
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // PNG ve GIF şeffaflığı için
    if ($info['mime'] == 'image/png' || $info['mime'] == 'image/gif') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // WebP olarak kaydet
    $result = imagewebp($resizedImage, $destination, $quality);
    
    imagedestroy($image);
    imagedestroy($resizedImage);
    
    return $result;
}

// WebP dönüştürme fonksiyonu (boyutlandırma eklenmiş)
function convertToWebP($source, $destination, $quality = 80) {
    return resizeImage($source, $destination, 895, 595, $quality);
}

// Thumbnail oluşturma fonksiyonu
function createThumbnail($source, $destination, $maxWidth = 150, $maxHeight = 150, $quality = 80) {
    $info = getimagesize($source);
    if ($info === false) return false;
    
    $image = false;
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/bmp':
            $image = imagecreatefrombmp($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if ($image === false) return false;
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Orantılı boyutlandırma
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
    
    // PNG ve GIF şeffaflığı için
    if ($info['mime'] == 'image/png' || $info['mime'] == 'image/gif') {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // WebP olarak kaydet
    $result = imagewebp($thumbnail, $destination, $quality);
    
    imagedestroy($image);
    imagedestroy($thumbnail);
    
    return $result;
}

// Dosyanın resim olup olmadığını kontrol et
function isImage($filename) {
    global $IMAGE_EXTENSIONS;
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $IMAGE_EXTENSIONS);
}

// Login form fonksiyonu (aynı kaldı)
function showLoginForm($error = false) {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dosya Yöneticisi - Giriş</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .login-container {
                background: white;
                padding: 2rem;
                border-radius: 15px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px;
            }
            
            .login-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            
            .login-header h1 {
                color: #333;
                margin-bottom: 0.5rem;
            }
            
            .login-header p {
                color: #666;
                font-size: 0.9rem;
            }
            
            .form-group {
                margin-bottom: 1.5rem;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                color: #333;
                font-weight: 500;
            }
            
            .form-group input {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s;
            }
            
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .btn {
                width: 100%;
                padding: 0.75rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                cursor: pointer;
                transition: transform 0.2s;
            }
            
            .btn:hover {
                transform: translateY(-2px);
            }
            
            .error {
                color: #e74c3c;
                text-align: center;
                margin-top: 1rem;
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1>🔒 Dosya Yöneticisi</h1>
                <p>Devam etmek için şifrenizi girin</p>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Şifre</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Giriş Yap</button>
                <?php if ($error): ?>
                    <div class="error">❌ Hatalı şifre!</div>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Giriş kontrolü (aynı kaldı)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (isset($_POST['login']) && isset($_POST['password']) && $_POST['password'] === $PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = isset($_POST['login']);
        showLoginForm($loginError);
    }
}

// Çıkış işlemi (aynı kaldı)
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$message = '';

// Upload işlemi (güncellendi)
if (isset($_POST['upload']) && isset($_FILES['files'])) {
    $totalFiles = count($_FILES['files']['name']);
    $successCount = 0;
    $errorCount = 0;
    
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
            $originalName = $_FILES['files']['name'][$i];
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $fileName = pathinfo($originalName, PATHINFO_FILENAME);
            $tempFile = $_FILES['files']['tmp_name'][$i];
            
            // Yeni dosya adı oluştur (12 random karakter + -nornaio)
            $randomString = generateRandomString(12);
            $newFileName = $randomString . '-nornaio';
            
            // Resim dosyası ise WebP'ye dönüştür ve boyutlandır
            if (isImage($originalName) && function_exists('imagewebp')) {
                $webpFileName = $newFileName . '.webp';
                $uploadFile = $UPLOAD_DIR . $webpFileName;
                
                if (convertToWebP($tempFile, $uploadFile, 85)) {
                    // Thumbnail oluştur
                    $thumbFile = $THUMB_DIR . $webpFileName;
                    createThumbnail($uploadFile, $thumbFile, 200, 200, 80);
                    
                    $successCount++;
                } else {
                    // WebP dönüştürme başarısız, orijinal formatı koru
                    $uploadFile = $UPLOAD_DIR . $newFileName . '.' . $fileExtension;
                    if (move_uploaded_file($tempFile, $uploadFile)) {
                        // Thumbnail oluştur
                        $thumbFile = $THUMB_DIR . $newFileName . '.webp';
                        createThumbnail($uploadFile, $thumbFile, 200, 200, 80);
                        
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            } else {
                // Resim değilse normal yükleme (random isimle)
                $uploadFile = $UPLOAD_DIR . $newFileName . '.' . $fileExtension;
                if (move_uploaded_file($tempFile, $uploadFile)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        } else {
            $errorCount++;
        }
    }
    
    if ($successCount > 0 && $errorCount == 0) {
        $message = '<div class="alert success">✅ ' . $successCount . ' dosya başarıyla yüklendi!</div>';
    } elseif ($successCount > 0 && $errorCount > 0) {
        $message = '<div class="alert warning">⚠️ ' . $successCount . ' dosya başarıyla yüklendi, ' . $errorCount . ' dosya yüklenirken hata oluştu!</div>';
    } elseif ($errorCount > 0) {
        $message = '<div class="alert error">❌ ' . $errorCount . ' dosya yüklenirken hata oluştu!</div>';
    }
}

// Delete işlemi (aynı kaldı)
if (isset($_GET['delete'])) {
    $deleteFile = $UPLOAD_DIR . basename($_GET['delete']);
    $deleteThumb = $THUMB_DIR . pathinfo($deleteFile, PATHINFO_FILENAME) . '.webp';
    
    if (file_exists($deleteFile) && is_file($deleteFile)) {
        $deleted = unlink($deleteFile);
        
        // Thumbnail'i de sil
        if (file_exists($deleteThumb)) {
            unlink($deleteThumb);
        }
        
        if ($deleted) {
            $message = '<div class="alert success">✅ Dosya başarıyla silindi!</div>';
        } else {
            $message = '<div class="alert error">❌ Dosya silinirken hata oluştu!</div>';
        }
    } else {
        $message = '<div class="alert error">❌ Dosya bulunamadı!</div>';
    }
}

// Dosyaları listele
function getFiles($dir) {
    $files = array();
    if (is_dir($dir)) {
        $items = scandir($dir);
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item != '.' && $item != '..' && is_file($dir . $item)) {
                    $files[] = array(
                        'name' => $item,
                        'size' => filesize($dir . $item),
                        'modified' => filemtime($dir . $item),
                        'type' => pathinfo($item, PATHINFO_EXTENSION)
                    );
                }
            }
        }
    }
    return $files;
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

function getFileIcon($extension) {
    $extension = strtolower($extension);
    $icons = array(
        'pdf' => '📄', 'doc' => '📝', 'docx' => '📝', 'txt' => '📄',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'webp' => '🖼️',
        'mp4' => '🎬', 'avi' => '🎬', 'mov' => '🎬',
        'mp3' => '🎵', 'wav' => '🎵',
        'zip' => '📦', 'rar' => '📦',
        'html' => '🌐', 'css' => '🎨', 'js' => '⚡', 'php' => '🐘'
    );
    return isset($icons[$extension]) ? $icons[$extension] : '📄';
}

// Base URL'i otomatik tespit et (aynı kaldı)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['PHP_SELF']);
$baseUrl = $protocol . '://' . $host . $scriptDir . '/' . $UPLOAD_DIR;

$files = getFiles($UPLOAD_DIR);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dosya Yöneticisi</title>
 <style>
        :root {
            --primary-dark: #210F37;
            --secondary-dark: #4F1C51;
            --accent-1: #A55B4B;
            --accent-2: #DCA06D;
            --text-light: #f8f9fa;
            --text-dark: #333;
            --bg-light: #f8f9fa;
            --bg-dark: #121212;
            --card-light: #ffffff;
            --card-dark: #1e1e1e;
            --border-light: #e1e5e9;
            --border-dark: #333;
        }

        [data-theme="light"] {
            --primary: var(--primary-dark);
            --secondary: var(--secondary-dark);
            --accent: var(--accent-1);
            --text: var(--text-dark);
            --bg: var(--bg-light);
            --card: var(--card-light);
            --border: var(--border-light);
            --header-gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --upload-btn-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        [data-theme="dark"] {
            --primary: var(--accent-2);
            --secondary: var(--accent-1);
            --accent: var(--secondary-dark);
            --text: var(--text-light);
            --bg: var(--bg-dark);
            --card: var(--card-dark);
            --border: var(--border-dark);
            --header-gradient: linear-gradient(135deg, var(--secondary-dark) 0%, var(--primary-dark) 100%);
            --upload-btn-gradient: linear-gradient(135deg, var(--accent-1) 0%, var(--accent-2) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            background: var(--header-gradient);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .header h1 {
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .theme-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        
        .upload-section {
            background: var(--card);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
        }
        
        .upload-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .file-input-wrapper {
            flex: 1;
            min-width: 250px;
        }
        
        .file-input-wrapper label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }
        
        .file-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px dashed var(--border);
            border-radius: 8px;
            background: var(--bg);
            cursor: pointer;
            transition: all 0.3s;
            color: var(--text);
        }
        
        .file-input:hover {
            border-color: var(--primary);
            background: var(--card);
        }
        
        .upload-btn {
            padding: 0.75rem 2rem;
            background: var(--upload-btn-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.2s;
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
        }
        
        .files-section {
            background: var(--card);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
        }
        
        .files-header {
            background: var(--bg);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
        }
        
        .files-header h2 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text);
        }
        
        .file-item {
            display: grid;
            grid-template-columns: auto 1fr auto auto auto;
            gap: 1rem;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
            align-items: center;
            transition: background 0.2s;
        }
        
        .file-item:hover {
            background: var(--bg);
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .file-icon {
            font-size: 1.5rem;
            position: relative;
        }
        
        .file-thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .file-thumbnail:hover {
            transform: scale(1.05);
        }
        
        .file-info {
            display: flex;
            flex-direction: column;
        }
        
        .file-name {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.25rem;
            word-break: break-all;
        }
        
        .file-meta {
            font-size: 0.875rem;
            color: var(--text);
            opacity: 0.8;
        }
        
        .file-size {
            font-weight: 500;
            color: var(--text);
            opacity: 0.9;
        }
        
        .file-date {
            color: var(--text);
            opacity: 0.8;
            font-size: 0.875rem;
        }
        
        .file-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 0.875rem;
        }
        
        .download-btn {
            background: rgba(25, 118, 210, 0.1);
            color: #1976d2;
        }
        
        .download-btn:hover {
            background: #1976d2;
            color: white;
        }
        
        .copy-btn {
            background: rgba(245, 124, 0, 0.1);
            color: #f57c00;
        }
        
        .copy-btn:hover {
            background: #f57c00;
            color: white;
        }
        
        .delete-btn {
            background: rgba(211, 47, 47, 0.1);
            color: #d32f2f;
        }
        
        .delete-btn:hover {
            background: #d32f2f;
            color: white;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .alert.success {
            background: rgba(40, 167, 69, 0.2);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .alert.error {
            background: rgba(220, 53, 69, 0.2);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .alert.warning {
            background: rgba(255, 193, 7, 0.2);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .alert.info {
            background: rgba(23, 162, 184, 0.2);
            color: #0c5460;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text);
            opacity: 0.7;
        }
        
        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        /* Modal için stiller */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            cursor: pointer;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
        }
        
        .modal-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #bbb;
        }
        
        /* Kopyalama bildirimi için stil */
        .copy-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .copy-notification.show {
            opacity: 1;
        }
        
        /* Yükleme animasyonu için stil */
        .progress-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        
        .progress-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            border: 1px solid var(--border);
        }
        
        .progress-title {
            margin-bottom: 1rem;
            font-size: 1.2rem;
            color: var(--text);
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: var(--bg);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--header-gradient);
            width: 0%;
            transition: width 0.3s;
        }
        
        .progress-text {
            font-size: 0.9rem;
            color: var(--text);
        }
        
        .file-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 1rem;
            text-align: left;
            border: 1px solid var(--border);
            padding: 0.5rem;
            border-radius: 8px;
        }
        
        .file-list-item {
            padding: 0.25rem 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
        }
        
        .file-list-item:last-child {
            border-bottom: none;
        }
        
        .file-status {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: var(--text);
        }
        
        .status-success {
            background: rgba(40, 167, 69, 0.2);
            color: var(--text);
        }
        
        .status-error {
            background: rgba(220, 53, 69, 0.2);
            color: var(--text);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding-bottom: 3rem;
            }
            
            .theme-toggle {
                top: auto;
                bottom: 1rem;
                right: 1rem;
            }
            
            .upload-form {
                flex-direction: column;
            }
            
            .file-item {
                grid-template-columns: auto 1fr;
                gap: 0.5rem;
                padding: 1rem;
            }
            
            .file-size, .file-date {
                display: none;
            }
            
            .file-actions {
                grid-column: 1 / -1;
                justify-content: center;
                margin-top: 0.5rem;
            }
            
            .file-thumbnail {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body data-theme="light">
      <div class="container">
        <header class="header">
            <h1>📁 Modern Dosya Yöneticisi</h1>
            <a href="?logout=1" class="logout-btn">🚪 Çıkış Yap</a>
            <button class="theme-toggle" id="themeToggle">🌙</button>
        </header>
        
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="upload-section">
            <form method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                <div class="file-input-wrapper">
                    <label for="files">📤 Dosya Seçin (Çoklu seçim yapabilirsiniz, resimler otomatik WebP'ye dönüştürülür)</label>
                    <input type="file" id="files" name="files[]" class="file-input" multiple required>
                </div>
                <button type="submit" name="upload" class="upload-btn">⬆️ Yükle</button>
            </form>
        </div>
        
        <div class="files-section">
            <div class="files-header">
                <h2>📋 Dosya Listesi (<?php echo count($files); ?> dosya)</h2>
            </div>
            
            <?php if (empty($files)): ?>
                <div class="empty-state">
                    <div class="icon">📂</div>
                    <h3>Henüz dosya yok</h3>
                    <p>Yukarıdaki form ile dosya yükleyebilirsiniz.</p>
                </div>
            <?php else: ?>
                <div class="files-grid">
                    <?php foreach ($files as $file): ?>
                        <?php
                        $isImageFile = isImage($file['name']);
                        $thumbnailPath = $THUMB_DIR . pathinfo($file['name'], PATHINFO_FILENAME) . '.webp';
                        $hasThumbnail = $isImageFile && file_exists($thumbnailPath);
                        ?>
                        <div class="file-item">
                            <div class="file-icon">
                                <?php if ($hasThumbnail): ?>
                                    <img src="<?php echo $thumbnailPath; ?>" 
                                         alt="<?php echo htmlspecialchars($file['name']); ?>" 
                                         class="file-thumbnail"
                                         onclick="openModal('<?php echo $UPLOAD_DIR . $file['name']; ?>')">
                                <?php else: ?>
                                    <?php echo getFileIcon($file['type']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="file-info">
                                <div class="file-name"><?php echo htmlspecialchars($file['name']); ?></div>
                                <div class="file-meta">
                                    <?php echo strtoupper($file['type']); ?> dosyası
                                    <?php if ($isImageFile): ?>
                                        <span style="color: var(--accent); font-weight: 500;">📸 Resim</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="file-size"><?php echo formatBytes($file['size']); ?></div>
                            <div class="file-date"><?php echo date('d.m.Y H:i', $file['modified']); ?></div>
                            <div class="file-actions">
                                <a href="<?php echo $UPLOAD_DIR . $file['name']; ?>" class="action-btn download-btn" download title="İndir">⬇️</a>
                                <button class="action-btn copy-btn" onclick="copyFileLink('<?php echo $baseUrl . $file['name']; ?>')" title="Linki Kopyala">🔗</button>
                                <a href="?delete=<?php echo urlencode($file['name']); ?>" class="action-btn delete-btn" onclick="return confirm('Bu dosyayı silmek istediğinizden emin misiniz?')" title="Sil">🗑️</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Resim görüntüleme modal'ı -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImage" class="modal-image" src="" alt="">
        </div>
    </div>
    
    <!-- Kopyalama bildirimi -->
    <div id="copyNotification" class="copy-notification">
        ✅ Link panoya kopyalandı!
    </div>
    
    <!-- Yükleme animasyonu modal'ı -->
    <div id="progressModal" class="progress-modal">
        <div class="progress-container">
            <div class="progress-title">Dosyalar yükleniyor...</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">0% (0/0)</div>
            <div class="file-list" id="fileList"></div>
        </div>
    </div>
    
    <script>
        // Tema değiştirme fonksiyonu
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        // Kullanıcının tercihini kontrol et (localStorage'da sakla)
        const currentTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);
        
        themeToggle.addEventListener('click', () => {
            const newTheme = body.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            themeToggle.textContent = theme === 'light' ? '🌙' : '☀️';
        }

        // [Rest of the JavaScript code remains the same]
        // Resim modal'ını açma fonksiyonu
        function openModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = imageSrc;
        }
        
        // Modal'ı kapatma fonksiyonu
        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
        }
        
        // ESC tuşu ile modal'ı kapatma
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Dosya linkini kopyalama fonksiyonu
        function copyFileLink(url) {
            // Navigator clipboard API'si destekleniyorsa kullan
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    showCopyNotification();
                }).catch(function(err) {
                    // Fallback yöntemi
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                // Fallback yöntemi
                fallbackCopyTextToClipboard(url);
            }
        }
        
        // Fallback kopyalama yöntemi (eski tarayıcılar için)
        function fallbackCopyTextToClipboard(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.top = "-1000px";
            textArea.style.left = "-1000px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    showCopyNotification();
                } else {
                    alert('Link kopyalanamadı. Lütfen manuel olarak kopyalayın: ' + text);
                }
            } catch (err) {
                alert('Link kopyalanamadı. Lütfen manuel olarak kopyalayın: ' + text);
            }
            
            document.body.removeChild(textArea);
        }
        
        // Kopyalama bildirimi gösterme
        function showCopyNotification() {
            var notification = document.getElementById('copyNotification');
            notification.classList.add('show');
            
            setTimeout(function() {
                notification.classList.remove('show');
            }, 2000);
        }
        
        // Drag & Drop dosya yükleme
        const fileInput = document.getElementById('files');
        const uploadSection = document.querySelector('.upload-section');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadSection.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadSection.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadSection.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            uploadSection.style.background = 'var(--bg)';
            uploadSection.style.borderColor = 'var(--primary)';
        }
        
        function unhighlight(e) {
            uploadSection.style.background = 'var(--card)';
            uploadSection.style.borderColor = 'var(--border)';
        }
        
        uploadSection.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                // Dosya adlarını göster
                const label = document.querySelector('.file-input-wrapper label');
                if (files.length === 1) {
                    label.textContent = `📤 Seçilen dosya: ${files[0].name}`;
                } else {
                    label.textContent = `📤 ${files.length} dosya seçildi`;
                }
            }
        }
        
        // Dosya seçildiğinde label'ı güncelle
        fileInput.addEventListener('change', function() {
            const label = document.querySelector('.file-input-wrapper label');
            if (this.files.length > 0) {
                if (this.files.length === 1) {
                    label.textContent = `📤 Seçilen dosya: ${this.files[0].name}`;
                } else {
                    label.textContent = `📤 ${this.files.length} dosya seçildi`;
                }
            } else {
                label.textContent = '📤 Dosya Seçin (Çoklu seçim yapabilirsiniz, resimler otomatik WebP\'ye dönüştürülür)';
            }
        });
        
        // Form gönderildiğinde yükleme animasyonunu göster
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const files = document.getElementById('files').files;
            if (files.length > 0) {
                showProgressModal(files);
            }
        });
        
        // Yükleme animasyonunu göster
        function showProgressModal(files) {
            const progressModal = document.getElementById('progressModal');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const fileList = document.getElementById('fileList');
            
            // Dosya listesini oluştur
            fileList.innerHTML = '';
            for (let i = 0; i < files.length; i++) {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-list-item';
                fileItem.innerHTML = `
                    <span>${files[i].name}</span>
                    <span class="file-status status-pending" id="fileStatus${i}">Bekliyor</span>
                `;
                fileList.appendChild(fileItem);
            }
            
            // Modal'ı göster
            progressModal.style.display = 'block';
            
            // Animasyonu başlat
            let uploadedCount = 0;
            const totalFiles = files.length;
            
            // Her 300ms'de bir progress bar'ı güncelle (simülasyon)
            const interval = setInterval(() => {
                uploadedCount++;
                const progress = Math.round((uploadedCount / totalFiles) * 100);
                progressFill.style.width = `${progress}%`;
                progressText.textContent = `${progress}% (${uploadedCount}/${totalFiles})`;
                
                // Dosya durumunu güncelle
                const fileStatus = document.getElementById(`fileStatus${uploadedCount-1}`);
                if (fileStatus) {
                    fileStatus.textContent = 'Yükleniyor...';
                    fileStatus.className = 'file-status status-success';
                }
                
                if (uploadedCount >= totalFiles) {
                    clearInterval(interval);
                    setTimeout(() => {
                        progressModal.style.display = 'none';
                    }, 500);
                }
            }, 300);
        }
    </script>
</body>
</html>
