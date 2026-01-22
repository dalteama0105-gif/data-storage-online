<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$user = $_SESSION['user'];
$role = $_SESSION['role'] ?? 'User';

// Unique config file for user
$safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $user);
$user_config_file = 'data/config_' . $safe_filename . '.json';

// === 1. LOAD CONFIG ===
$config = [];
if (file_exists($user_config_file)) {
    $config = json_decode(file_get_contents($user_config_file), true);
}

// Default values if not set
$app_title = $config['app_title'] ?? "My Workspace";
$theme     = $config['theme'] ?? 'light'; // 'light' or 'dark'
$lang      = $config['lang'] ?? 'en';     // 'en', 'zh', or 'ms'

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
        'upload_f'  => 'Upload Files',
        'upload_d'  => 'Upload Folder',
        'search'    => 'Search files...',
        'col_name'  => 'File Name',
        'col_date'  => 'Date',
        'col_type'  => 'Type',
        'col_act'   => 'Actions',
        'set_title' => 'Center Header Title',
        'set_theme' => 'Theme Mode',
        'set_lang'  => 'Language',
        'save'      => 'Save Changes',
        'folders'   => 'Folders',
        'txt_desc'  => 'Change the text displayed in the center of the top bar.'
    ],
    'ms' => [
        'dashboard' => 'Papan Pemuka',
        'all_files' => 'Fail',
        'settings'  => 'Tetapan',
        'logout'    => 'Log Keluar',
        'welcome'   => 'Selamat Kembali',
        'tot_files' => 'Jumlah Fail',
        'file_type' => 'Jenis Fail',
        'upload_f'  => 'Muat Naik Fail',
        'upload_d'  => 'Muat Naik Folder',
        'search'    => 'Cari fail...',
        'col_name'  => 'Nama Fail',
        'col_date'  => 'Tarikh',
        'col_type'  => 'Jenis',
        'col_act'   => 'Tindakan',
        'set_title' => 'Tajuk Pengepala',
        'set_theme' => 'Mod Tema',
        'set_lang'  => 'Bahasa',
        'save'      => 'Simpan Perubahan',
        'folders'   => 'Folder',
        'txt_desc'  => 'Tukar teks yang dipaparkan di tengah bar atas.'
    ]
];

// Get text for current language
$t = $translations[$lang];

// === 3. HANDLE SAVE SETTINGS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_settings') {
    $new_title = $_POST['app_title'] ?? $app_title;
    $new_theme = $_POST['theme'] ?? $theme;
    $new_lang  = $_POST['lang'] ?? $lang;
    
    $save_data = [
        'app_title' => $new_title,
        'theme'     => $new_theme,
        'lang'      => $new_lang
    ];
    
    if (!file_exists('data')) { mkdir('data'); }
    file_put_contents($user_config_file, json_encode($save_data));
    
    header("Location: index.php?msg=Saved");
    exit();
}
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
                <a href="#" class="nav-item" id="nav-settings">
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
                    
                    <button class="action-btn primary" id="btn-open-modal" style="padding: 8px 16px; font-weight: 600;">
                        <ion-icon name="add-circle-outline" style="font-size:18px;"></ion-icon> 
                        Import Folder
                    </button>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <div class="action-btn" style="padding: 0 5px; overflow: hidden; border: 1px solid var(--border-color); display:flex; align-items:center;">
                                <span style="font-size: 11px; color: var(--text-muted); margin-right: 2px;">From</span>
                                <input type="date" id="dateStart" style="border: none; outline: none; padding: 5px 0; background: transparent; color: inherit; font-size: 12px; width: 100px;">
                            </div>
                            
                            <span style="color: var(--text-muted);">-</span>

                            <div class="action-btn" style="padding: 0 5px; overflow: hidden; border: 1px solid var(--border-color); display:flex; align-items:center;">
                                <span style="font-size: 11px; color: var(--text-muted); margin-right: 2px;">To</span>
                                <input type="date" id="dateEnd" style="border: none; outline: none; padding: 5px 0; background: transparent; color: inherit; font-size: 12px; width: 100px;">
                            </div>
                        </div>

                        <div class="search-group">
                            <input type="text" id="file-search" placeholder="<?php echo $t['search']; ?>">
                        </div>
                    </div>

                </div>
                <div class="file-workspace">
                    <div class="file-table-container">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox"></th>
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

            <div id="view-settings" class="content-view" style="display: none;">
                <h2 class="view-title"><?php echo $t['settings']; ?></h2>
                <div class="settings-card">
                    <form action="" method="post">
                        <input type="hidden" name="action" value="save_settings">
                        
                        <div class="form-group">
                            <label for="app_title"><?php echo $t['set_title']; ?></label>
                            <input type="text" name="app_title" id="app_title" 
                                   value="<?php echo htmlspecialchars($app_title); ?>" 
                                   class="settings-input">
                            <small><?php echo $t['txt_desc']; ?></small>
                        </div>

                        <div class="form-group">
                            <label><?php echo $t['set_theme']; ?></label>
                            <select name="theme" class="settings-input">
                                <option value="light" <?php if($theme=='light') echo 'selected'; ?>>Light Mode (Default)</option>
                                <option value="dark" <?php if($theme=='dark') echo 'selected'; ?>>Dark Mode</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?php echo $t['set_lang']; ?></label>
                            <select name="lang" class="settings-input">
                                <option value="en" <?php if($lang=='en') echo 'selected'; ?>>English</option>
                                <option value="ms" <?php if($lang=='ms') echo 'selected'; ?>>Malay (Bahasa Melayu)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-save"><?php echo $t['save']; ?></button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <footer class="bottom-footer">
        <p>© 2026 Data Storage Online. All rights reserved.</p>
    </footer>

    <div class="modal-overlay" id="uploadModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Create New Folder</div>
                <button class="btn-close-modal" id="btn-close-modal">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="modal-input-group">
                    <label>Folder Name</label>
                    <input type="text" id="newFolderName" class="settings-input" placeholder="e.g. Project Alpha">
                </div>

                <div class="modal-input-group">
                    <label>Import Audio File (Optional)</label>
                    <div class="file-upload-box" onclick="document.getElementById('modalAudioInput').click()">
                        <ion-icon name="musical-notes-outline" style="font-size: 24px; color: var(--primary);"></ion-icon>
                        <p id="audioFileName">Click to select MP3...</p>
                        <input type="file" id="modalAudioInput" accept=".mp3,audio/mpeg" style="display: none;">
                    </div>
                </div>

                <div class="modal-input-group">
                    <label>Import Text File (Optional)</label>
                    <div class="file-upload-box" onclick="document.getElementById('modalTextInput').click()">
                        <ion-icon name="document-text-outline" style="font-size: 24px; color: #f59e0b;"></ion-icon>
                        <p id="textFileName">Click to select TXT...</p>
                        <input type="file" id="modalTextInput" accept=".txt,text/plain" style="display: none;">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-cancel" id="btn-cancel-modal">Cancel</button>
                <button class="btn-modal btn-confirm" id="btn-save-modal">Save</button>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>