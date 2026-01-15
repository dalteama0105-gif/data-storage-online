<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$user = $_SESSION['user'];
$role = $_SESSION['role'] ?? 'User';

// 1. HANDLE SETTINGS SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_title'])) {
    $new_title = $_POST['app_title'];
    $config_data = ['app_title' => $new_title];
    if (!file_exists('data')) { mkdir('data'); }
    file_put_contents('data/config.json', json_encode($config_data));
    header("Location: index.php?msg=Saved");
    exit();
}

// 2. LOAD CUSTOM TITLE
$app_title = "My Workspace"; 
if (file_exists('data/config.json')) {
    $conf = json_decode(file_get_contents('data/config.json'), true);
    if(isset($conf['app_title']) && !empty($conf['app_title'])) {
        $app_title = $conf['app_title'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Data Storage Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>

    <header class="top-header">
        <div class="header-brand">
            <img src="logo.jpg" alt="Logo" class="logo-img">
            <h2>Data Storage Online</h2>
        </div>
        <div class="header-dynamic-center">
            <h3><?php echo htmlspecialchars($app_title); ?></h3>
        </div>
    </header>

    <div class="app-layout">
        
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="user-profile">
                    <div class="avatar-circle"><?php echo strtoupper(substr($user, 0, 1)); ?></div>
                    <div class="user-info">
                        <span class="u-name"><?php echo htmlspecialchars($user); ?></span>
                        <span class="u-role"><?php echo htmlspecialchars($role); ?></span>
                    </div>
                </div>

                <nav class="nav-menu">
                    <a href="#" class="nav-item active" id="nav-dashboard">
                        <ion-icon name="speedometer-outline"></ion-icon> Dashboard
                    </a>
                    <a href="#" class="nav-item" id="nav-files">
                        <ion-icon name="folder-open-outline"></ion-icon> All Files
                    </a>
                </nav>
            </div>

            <div class="sidebar-bottom">
                <a href="#" class="nav-item" id="nav-settings">
                    <ion-icon name="settings-outline"></ion-icon> Settings
                </a>
                <a href="action_logout.php" class="nav-item logout">
                    <ion-icon name="log-out-outline"></ion-icon> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div id="view-dashboard" class="content-view">
                <h2 class="view-title">Welcome back, <?php echo htmlspecialchars($user); ?>!</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total Files</h3>
                        <div class="big-number" id="stat-total">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>File Types</h3>
                        <div class="pie-chart-wrapper">
                            <div class="pie-chart" id="type-pie-chart"></div>
                            <div class="pie-legend">
                                <span class="legend-item"><span class="dot txt"></span> TXT</span>
                                <span class="legend-item"><span class="dot mp3"></span> MP3</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="view-files" class="content-view" style="display: none;">
                <div class="file-toolbar">
                    <div class="tab-label">All Files</div>
                    <div class="toolbar-actions">
                        
                        <div class="action-btn" style="padding: 0; overflow: hidden;">
                            <select id="filterSelect" style="border: none; background: transparent; padding: 6px 10px; cursor: pointer; outline: none; font-size: 13px; color: #333; height: 100%; width: 100%;">
                                <option value="all">Filter: All</option>
                                <option value="txt">Type: Text (.txt)</option>
                                <option value="mp3">Type: Audio (.mp3)</option>
                            </select>
                        </div>
                        
                        <div class="action-btn primary" id="btn-trigger-upload">
                            <ion-icon name="document-outline"></ion-icon> Upload Files
                        </div>

                        <div class="action-btn primary" id="btn-trigger-folder" style="background: #0ea5e9; border-color: #0ea5e9;">
                            <ion-icon name="folder-open-outline"></ion-icon> Upload Folder
                        </div>
                    </div>
                    <div class="search-group">
                        <input type="date" class="date-picker">
                        <input type="text" id="file-search" placeholder="Search files...">
                    </div>
                </div>
                <div class="file-workspace">
                    <div class="file-table-container">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox"></th>
                                    <th style="width: 50px;">No</th>
                                    <th>File Name</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="file-table-body"></tbody>
                        </table>
                    </div>
                    <div class="folder-sidebar">
                        <h4>Folders</h4>
                        <ul class="folder-list">
                            <li><ion-icon name="folder"></ion-icon> AI Technology</li>
                            <li><ion-icon name="folder"></ion-icon> Complain</li>
                            <li><ion-icon name="folder"></ion-icon> Personal</li>
                            <li><ion-icon name="folder"></ion-icon> Archives</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="view-settings" class="content-view" style="display: none;">
                <h2 class="view-title">System Settings</h2>
                <div class="settings-card">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="app_title">Center Header Title</label>
                            <input type="text" name="app_title" id="app_title" 
                                   value="<?php echo htmlspecialchars($app_title); ?>" 
                                   class="settings-input" placeholder="e.g. My Workspace">
                            <small>Change the text displayed in the center of the top bar.</small>
                        </div>
                        <button type="submit" class="btn-save">Save Changes</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <footer class="bottom-footer">
        <p>© 2026 DAL-sh. All rights reserved.</p>
    </footer>

    <input type="file" id="fileInput" style="display: none;" multiple>
    <input type="file" id="folderInput" style="display: none;" webkitdirectory directory multiple>

    <script src="js/main.js"></script>
</body>
</html>