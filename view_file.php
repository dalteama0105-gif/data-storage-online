<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];
$filename = $_GET['f'] ?? '';

// === 修正开始 ===
// 2. Prevent hacking (Directory Traversal)
// 旧代码使用了 basename($filename)，这会导致文件夹路径丢失。
// 新逻辑：只要不包含 ".." 就可以允许访问子文件夹。

if (strpos($filename, '..') !== false) {
    die("Error: Invalid file path.");
}

// 移除开头的斜杠，防止绝对路径攻击
$safe_filename = ltrim($filename, '/');

$file_path = "uploads/$username/$safe_filename";
$web_path  = "uploads/$username/$safe_filename"; 
// === 修正结束 ===

// 3. Check if file exists
if (empty($filename) || !file_exists($file_path)) {
    die("Error: File not found or you do not have permission to view it.");
}

// 4. Determine File Type
$ext = strtolower(pathinfo($safe_filename, PATHINFO_EXTENSION));
$is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$is_audio = in_array($ext, ['mp3', 'wav', 'ogg']);
$is_video = in_array($ext, ['mp4', 'webm']);
$is_text  = in_array($ext, ['txt', 'json', 'php', 'js', 'css', 'html', 'md']); // Added md

// Read text content securely if needed
$text_content = "";
if ($is_text) {
    $text_content = htmlspecialchars(file_get_contents($file_path));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View <?php echo htmlspecialchars(basename($safe_filename)); ?> - Data Storage</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <style>
        /* 1. Make the body a Flex Container (Fixes Footer) */
        body { 
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            min-height: 100vh; 
            margin: 0;
        }

        /* 2. Update the Viewer Container */
        .viewer-container {
            width: 90%;            
            max-width: 1600px;     
            min-height: 75vh;      
            margin: 30px auto;     
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;   
        }

        /* 3. Header Styling */
        .file-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .file-title h2 { margin: 0; font-size: 24px; color: #333; }
        .file-meta { font-size: 14px; color: #888; margin-top: 5px; }
        
        /* 4. Content Previews */
        .preview-box { width: 100%; text-align: center; flex: 1; }
        
        img.preview { 
            max-width: 100%; 
            max-height: 70vh; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        
        audio, video { width: 100%; max-width: 800px; margin-top: 40px; outline: none; }
        
        .code-block {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            overflow-x: auto;
            font-family: 'Consolas', monospace;
            white-space: pre-wrap;
            max-height: 700px; 
            font-size: 14px;
        }

        /* 5. Buttons */
        .btn-back {
            text-decoration: none;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: 0.2s;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-back:hover { background: #e5e7eb; color: #2563eb; }
        
        .download-btn {
            background: #2563eb;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 500;
            margin-top: 30px;
            transition: background 0.2s;
        }
        .download-btn:hover { background: #1d4ed8; }

        /* 6. Footer Fix */
        .bottom-footer {
            margin-top: auto; 
            width: 100%;
            background: #000; 
            color: #fff;
            padding: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <header class="top-header" style="padding: 0 20px;">
        <div class="header-brand" style="border:none; background:transparent; padding:0;">
            <img src="logo.png" alt="Logo" class="logo-img">
            <h2>Data Storage Online</h2>
        </div>
        <div class="header-dynamic-center">
            <h3>File Viewer</h3>
        </div>
    </header>

    <div class="viewer-container">
        
        <div class="file-header">
            <div>
                <a href="#" onclick="window.close()" class="btn-back">
                    <ion-icon name="arrow-back-outline"></ion-icon> Close Window
                </a>
            </div>
            <div class="file-title" style="text-align:right;">
                <h2><?php echo htmlspecialchars(basename($safe_filename)); ?></h2>
                <div class="file-meta">
                    Type: <?php echo strtoupper($ext); ?> | 
                    Size: <?php echo round(filesize($file_path) / 1024, 2); ?> KB
                </div>
            </div>
        </div>

        <div class="preview-box">
            
            <?php if ($is_image): ?>
                <img src="<?php echo $web_path; ?>" class="preview" alt="Image Preview">
            
            <?php elseif ($is_audio): ?>
                <div style="padding: 40px; background: #f8f9fa; border-radius: 12px;">
                    <ion-icon name="musical-notes-outline" style="font-size: 64px; color: #2563eb;"></ion-icon>
                    <h3>Audio Preview</h3>
                    <audio controls>
                        <source src="<?php echo $web_path; ?>" type="audio/<?php echo $ext; ?>">
                        Your browser does not support the audio element.
                    </audio>
                </div>

            <?php elseif ($is_video): ?>
                <video controls>
                    <source src="<?php echo $web_path; ?>" type="video/<?php echo $ext; ?>">
                    Your browser does not support the video tag.
                </video>

            <?php elseif ($is_text): ?>
                <div class="code-block"><?php echo $text_content; ?></div>

            <?php else: ?>
                <div style="padding: 50px;">
                    <ion-icon name="document-text-outline" style="font-size: 80px; color: #ccc;"></ion-icon>
                    <p style="margin: 20px 0; color: #666;">Preview not available for this file type.</p>
                </div>
            <?php endif; ?>

            <div style="margin-top: 40px;">
                <a href="<?php echo $web_path; ?>" download="<?php echo basename($safe_filename); ?>" class="download-btn">
                    <ion-icon name="cloud-download-outline"></ion-icon> Download File
                </a>
            </div>

        </div>
    </div>

    <footer class="bottom-footer">
        <p>© 2026 Data Storage Online. All rights reserved.</p>
    </footer>

</body>
</html>