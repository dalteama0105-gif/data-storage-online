<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$user = $_SESSION['user'];
$role = $_SESSION['role'] ?? 'User'; // 'Admin' or 'Employee'

// Unique config file for user
$safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $user);
$user_config_file = 'data/config_' . $safe_filename . '.json';

// === 1. LOAD CONFIG ===
$config = [];
if (file_exists($user_config_file)) {
    $config = json_decode(file_get_contents($user_config_file), true);
}

// Default values
$app_title = $config['app_title'] ?? "My Workspace";
$footer_txt= $config['footer_text'] ?? "© 2026 Data Storage Online. All rights reserved.";
$theme     = $config['theme'] ?? 'light'; 
$lang      = $config['lang'] ?? 'en';     

// === 2. TRANSLATION DICTIONARY ===
$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'all_files' => 'Files',
        'settings'  => 'Settings',
        'logout'    => 'Logout',
        'welcome'   => 'Welcome back',
        'tot_files' => 'Total Files',
        'file_type' => 'File Types',
        'search'    => 'Search files...',
        'col_name'  => 'File Name',
        'col_date'  => 'Date',
        'col_type'  => 'Type',
        'col_act'   => 'Actions',
        'folders'   => 'Folders',
    ],
    'ms' => [
        'dashboard' => 'Papan Pemuka',
        'all_files' => 'Fail',
        'settings'  => 'Tetapan',
        'logout'    => 'Log Keluar',
        'welcome'   => 'Selamat Kembali',
        'tot_files' => 'Jumlah Fail',
        'file_type' => 'Jenis Fail',
        'search'    => 'Cari fail...',
        'col_name'  => 'Nama Fail',
        'col_date'  => 'Tarikh',
        'col_type'  => 'Jenis',
        'col_act'   => 'Tindakan',
        'folders'   => 'Folder',
    ]
];
$t = $translations[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['dashboard']; ?> - Data Storage</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script>
        const CURRENT_USER_ROLE = "<?php echo $role; ?>";
        const CURRENT_USER_NAME = "<?php echo $user; ?>";
    </script>
</head>
<body class="<?php echo ($theme === 'dark') ? 'dark-mode' : ''; ?>">

    <header class="top-header">
        <div class="header-brand">
            <img src="logo.png" alt="Logo" class="logo-img">
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
                        <ion-icon name="speedometer-outline"></ion-icon> <?php echo $t['dashboard']; ?>
                    </a>
                    <a href="#" class="nav-item" id="nav-files">
                        <ion-icon name="folder-open-outline"></ion-icon> <?php echo $t['all_files']; ?>
                    </a>
                </nav>
            </div>

            <div class="sidebar-bottom">
                <a href="#" class="nav-item" id="nav-settings-btn">
                    <ion-icon name="settings-outline"></ion-icon> <?php echo $t['settings']; ?>
                </a>
                <a href="action_logout.php" class="nav-item logout">
                    <ion-icon name="log-out-outline"></ion-icon> <?php echo $t['logout']; ?>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div id="view-dashboard" class="content-view">
                <h2 class="view-title"><?php echo $t['welcome']; ?>, <?php echo htmlspecialchars($user); ?>!</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <h3><?php echo $t['tot_files']; ?></h3>
                        <div class="big-number" id="stat-total">0</div>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $t['file_type']; ?></h3>
                        <div class="pie-chart-wrapper">
                            <div class="pie-chart" id="type-pie-chart"></div>
                            <div class="pie-legend">
                                <span class="legend-item"><span class="dot txt"></span> TXT</span>
                                <span class="legend-item"><span class="dot mp3"></span> MP3</span>
                                <span class="legend-item"><span class="dot folder"></span> FOLDER</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="view-files" class="content-view" style="display: none;">
                <div class="file-toolbar">
                    <button class="action-btn primary" id="btn-open-upload-modal">
                        <ion-icon name="add-circle-outline"></ion-icon> Import Folder
                    </button>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="file-search" placeholder="<?php echo $t['search']; ?>" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>
                <div class="file-workspace">
                    <div class="file-table-container">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th><?php echo $t['col_name']; ?></th>
                                    <th><?php echo $t['col_date']; ?></th>
                                    <th><?php echo $t['col_type']; ?></th>
                                    <th><?php echo $t['col_act']; ?></th>
                                </tr>
                            </thead>
                            <tbody id="file-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="bottom-footer">
        <p><?php echo htmlspecialchars($footer_txt); ?></p>
    </footer>

    <div class="modal-overlay" id="uploadModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Create New Folder</div>
                <button class="btn-close-modal" onclick="document.getElementById('uploadModal').classList.remove('active')"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="modal-body">
                <div class="modal-input-group">
                    <label>Folder Name</label>
                    <input type="text" id="newFolderName" class="settings-input" placeholder="e.g. Project Alpha">
                </div>
                <div class="modal-input-group">
                    <label>Import Audio (Optional)</label>
                    <input type="file" id="modalAudioInput" accept=".mp3,audio/mpeg">
                </div>
                <div class="modal-input-group">
                    <label>Import Text (Optional)</label>
                    <input type="file" id="modalTextInput" accept=".txt,text/plain">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel" onclick="document.getElementById('uploadModal').classList.remove('active')">Cancel</button>
                <button class="btn-modal btn-confirm" id="btn-save-folder">Save</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="settingsModal">
        <div class="modal-card" style="max-width: 800px;">
            <div class="modal-header">
                <div class="modal-title">Settings</div>
                <button class="btn-close-modal" id="btn-close-settings"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                
                <div class="settings-tabs">
                    <div class="tab-item active" data-tab="gen">General</div>
                    <?php if($role === 'Admin'): ?>
                    <div class="tab-item" data-tab="users">User Management</div>
                    <?php endif; ?>
                </div>

                <div id="tab-gen" class="tab-content active" style="padding: 20px;">
                    <form action="action_save_settings.php" method="post">
                        <div class="form-group">
                            <label>Header Title</label>
                            <input type="text" name="app_title" value="<?php echo htmlspecialchars($app_title); ?>" class="settings-input">
                        </div>
                        <div class="form-group">
                            <label>Footer Text</label>
                            <input type="text" name="footer_text" value="<?php echo htmlspecialchars($footer_txt); ?>" class="settings-input">
                        </div>
                        <div class="form-group">
                            <label>Theme</label>
                            <select name="theme" class="settings-input">
                                <option value="light" <?php if($theme=='light') echo 'selected'; ?>>Light Mode</option>
                                <option value="dark" <?php if($theme=='dark') echo 'selected'; ?>>Dark Mode</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Language</label>
                            <select name="lang" class="settings-input">
                                <option value="en" <?php if($lang=='en') echo 'selected'; ?>>English</option>
                                <option value="ms" <?php if($lang=='ms') echo 'selected'; ?>>Malay</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-save">Save General Settings</button>
                    </form>
                </div>

                <?php if($role === 'Admin'): ?>
                <div id="tab-users" class="tab-content" style="padding: 20px;">
                    
                    <div style="background: var(--hover-bg); padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                        <h4 style="margin-bottom:10px;">Register New User</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="text" id="new_u_name" placeholder="Name" class="settings-input">
                            <input type="text" id="new_u_email" placeholder="Email" class="settings-input">
                            <input type="text" id="new_u_phone" placeholder="Phone" class="settings-input">
                            <input type="text" id="new_u_dept" placeholder="Department" class="settings-input">
                            <input type="text" id="new_u_username" placeholder="Username (Login ID)" class="settings-input">
                            <input type="password" id="new_u_pass" placeholder="Password" class="settings-input">
                        </div>
                        <button id="btn-add-user" class="btn-save" style="margin-top:10px; background:#10b981;">Add User</button>
                    </div>

                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Dept</th>
                                    <th>Username</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="user-list-body">
                                </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="modal-overlay" id="folderInfoModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title" id="info-folder-name">Folder Details</div>
                <button class="btn-close-modal" onclick="document.getElementById('folderInfoModal').classList.remove('active')"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <ion-icon name="folder-open" style="font-size: 64px; color: #f59e0b; margin-bottom: 20px;"></ion-icon>
                
                <div class="info-row" style="margin-bottom: 15px; text-align: left; background: var(--bg-body); padding: 15px; border-radius: 8px;">
                    <strong style="display:block; color:var(--text-muted); font-size:12px;">Audio File:</strong>
                    <span id="info-audio-name" style="font-size:16px; font-weight:600;">Loading...</span>
                </div>

                <div class="info-row" style="margin-bottom: 20px; text-align: left; background: var(--bg-body); padding: 15px; border-radius: 8px;">
                    <strong style="display:block; color:var(--text-muted); font-size:12px;">Text File:</strong>
                    <span id="info-txt-name" style="font-size:16px; font-weight:600;">Loading...</span>
                </div>

                <div id="info-actions">
                    </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button class="btn-modal btn-cancel" onclick="document.getElementById('folderInfoModal').classList.remove('active')">Back</button>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>