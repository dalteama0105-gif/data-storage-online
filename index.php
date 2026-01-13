<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Storage Online</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="top-header">
        <div class="header-left">
             <img src="logo.jpg" alt="Logo" class="logo-img" style="height:50px;">
        </div>
        <div class="header-center">
            <h2>Data Storage Online</h2>
        </div>
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-content">
                <div class="sidebar-welcome">
                    <span class="welcome-label">Welcome back,</span>
                    <span class="welcome-name"><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Guest'; ?></span>
                </div>
                <div class="search-wrapper">
                    <input type="text" class="sidebar-search" placeholder="Search...">
                </div>
                <div class="menu-group">
                    <h3 class="menu-title">WORKSPACE</h3>
                    <ul class="menu-list">
                        <li><a href="#" id="btn-insert-files" class="active">Insert Files</a></li>
                        <li><a href="#">My Folder</a></li>
                    </ul>
                </div>
                <div class="menu-group">
                    <h3 class="menu-title">MANAGE</h3>
                    <ul class="menu-list">
                        <li><a href="#">Database</a></li>
                        <li><a href="#">Settings</a></li>
                    </ul>
                </div>
            </div>
            <div class="sidebar-footer">
                <?php if(isset($_SESSION['user'])): ?>
                    <a href="action_logout.php" class="login-btn" style="text-decoration:none; display:block; text-align:center; background:#ff4d4f; color:white; border:none;">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="login-btn" style="text-decoration:none; display:block; text-align:center; line-height:30px;">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </aside>

        <main class="main-content">
            <div class="tabs-bar" id="tabs-container">
                <div class="tab-item active" data-id="tab-1">
                    <span class="tab-name">Tab 1</span>
                    <span class="close-tab">×</span>
                </div>
                <div class="tab-add">
                    <span>+</span>
                </div>
            </div>

            <div class="content-area" id="main-content-area">
                </div>
        </main>
    </div>

    <footer class="bottom-footer">
        <p>© 2026 DAL-sh. All rights reserved.</p>
    </footer>

    <div class="modal-overlay" id="file-modal">
        <div class="explorer-window">
            <div class="explorer-header">
                <span class="explorer-title">Open</span>
                <div class="window-controls">
                    <span class="close-modal-x">×</span>
                </div>
            </div>
            <div class="explorer-toolbar">
                <div class="nav-arrows"><span>←</span><span>→</span><span>↑</span></div>
                <div class="address-bar"><span>This PC > Data > Uploads</span></div>
                <div class="search-box"><input type="text" placeholder="Search"></div>
            </div>
            <div class="explorer-body">
                <div class="explorer-sidebar">
                    <ul>
                        <li class="sidebar-item active">📌 Quick access</li>
                        <li class="sidebar-item">💻 This PC</li>
                    </ul>
                </div>
                <div class="explorer-content">
                    <div class="file-grid">
                        <div class="file-item"><div class="file-icon folder">📁</div><span class="file-name">Docs</span></div>
                        <div class="file-item"><div class="file-icon file-img">🎵</div><span class="file-name">Music</span></div>
                    </div>
                </div>
            </div>
            <div class="explorer-footer">
                <div class="input-group">
                    <label>File name:</label>
                    <input type="text" class="filename-input">
                </div>
                <div class="footer-buttons">
                    <button class="explorer-btn select" id="btn-modal-select">Select</button>
                    <button class="explorer-btn cancel" id="btn-modal-cancel">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="context-menu" class="context-menu">
        <ul>
            <li id="ctx-open">Open</li>
            <li id="ctx-download">Download</li>
            <li id="ctx-delete" style="color: #ff4d4f; border-top: 1px solid #eee;">Delete</li>
        </ul>
    </div>

    <input type="file" id="fileInput" style="display: none;" multiple accept=".txt, .mp3, image/*, .pdf" />

    <script src="js/main.js"></script>
</body>
</html>