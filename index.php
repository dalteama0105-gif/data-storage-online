<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$user = $_SESSION['user'];
$role = $_SESSION['role'] ?? 'User'; 

$safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $user);
$user_config_file = 'data/config_' . $safe_filename . '.json';

// === LOAD CONFIG ===
$config = [];
if (file_exists($user_config_file)) {
    $config = json_decode(file_get_contents($user_config_file), true);
}

$app_title = $config['app_title'] ?? "My Workspace";
$footer_txt= $config['footer_text'] ?? "© 2026 Data Storage Online. All rights reserved.";
$theme     = $config['theme'] ?? 'light'; 
$lang      = $config['lang'] ?? 'en';     

// === TRANSLATIONS ===
$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'all_files' => 'Files',
        'iso'       => 'ISO Documentation',
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
        'iso'       => 'ISO Documentation',
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
    <style>
        /* === ICON LAYOUT FIX === */
        th.col-actions, td.col-actions {
            width: 160px;
            min-width: 160px;
            max-width: 160px;
            white-space: nowrap;
            text-align: left;
        }
        
        .action-icon-group { display: flex; align-items: center; gap: 12px; }
        .date-filter { background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border-color); padding: 5px; border-radius: 4px; font-size: 13px; }

        /* === SETTINGS PAGE CONTAINER === */
        .settings-page-wrapper {
            background: var(--bg-card);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            width: 100%;       
            max-width: 100%;   
            min-height: 75vh;  
        }

        /* === TABLE COLUMN SIZES FOR CHECKBOX === */
        /* Col 1: Checkbox */
        .file-table th:nth-child(1), .file-table td:nth-child(1) { width: 40px; text-align: center; }
        /* Col 2: No */
        .file-table th:nth-child(2), .file-table td:nth-child(2) { width: 50px; text-align: center; }
        /* Col 3: Name */
        .file-table th:nth-child(3), .file-table td:nth-child(3) { width: 35%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        /* Col 4: Date */
        .file-table th:nth-child(4), .file-table td:nth-child(4) { width: 120px; }
        /* Col 5: Type */
        .file-table th:nth-child(5), .file-table td:nth-child(5) { width: 80px; text-align: center; }
        /* Col 6: Actions */
        .file-table th:nth-child(6), .file-table td:nth-child(6) { width: 180px; min-width: 180px; }
    </style>
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
                    <a href="#" class="nav-item" id="nav-iso">
                        <ion-icon name="ribbon-outline"></ion-icon> <?php echo $t['iso']; ?>
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

                    <button class="action-btn" id="btn-bulk-delete" style="display: none; background: #dc2626; color: white; border-color: #dc2626; margin-left: 10px;">
                        <ion-icon name="trash-outline"></ion-icon> Delete Selected
                    </button>
                    
                    <div style="display: flex; gap: 10px; align-items: center; margin-left: auto;">
                        <input type="date" id="date-start" class="date-filter" title="Start Date">
                        <span style="color:var(--text-muted); font-size:13px;">to</span>
                        <input type="date" id="date-end" class="date-filter" title="End Date">
                        
                        <input type="text" id="file-search" placeholder="<?php echo $t['search']; ?>" 
                               style="padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-main);">
                    </div>
                </div>
                
                <div class="file-workspace">
                    <div class="file-table-container">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-files"></th>
                                    <th>No</th>
                                    <th><?php echo $t['col_name']; ?></th>
                                    <th><?php echo $t['col_date']; ?></th>
                                    <th><?php echo $t['col_type']; ?></th>
                                    <th class="col-actions"><?php echo $t['col_act']; ?></th>
                                </tr>
                            </thead>
                            <tbody id="file-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-iso" class="content-view" style="display: none;">
                <div class="file-toolbar">
                    <button class="action-btn primary" style="cursor: default;">
                        <ion-icon name="add-circle-outline"></ion-icon> Import File
                    </button>
                    
                    <div style="display: flex; gap: 10px; align-items: center; margin-left: auto;">
                         <input type="text" id="iso-search" placeholder="Search ISO..." 
                               style="padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-main); width: 200px;">
                    </div>
                </div>

                <div class="iso-workspace">
                    <div class="iso-grid" id="iso-grid-container">
                        </div>
                    <button class="btn-floating-add" id="btn-open-iso-modal">
                        <ion-icon name="add-circle"></ion-icon> Add ISO
                    </button>
                </div>
            </div>

            <div id="view-settings" class="content-view" style="display: none;">
                <h2 class="view-title" style="margin-bottom: 20px;"><?php echo $t['settings']; ?></h2>
                
                <div class="settings-page-wrapper">
                    <div class="settings-tabs">
                        <div class="tab-item active" data-tab="gen">General</div>
                        <?php if($role === 'Admin' || $role === 'Developer'): ?>
                        <div class="tab-item" data-tab="users">User Management</div>
                        <?php endif; ?>
                    </div>

                    <div id="tab-gen" class="tab-content active" style="padding: 30px;">
                        <form id="gen-settings-form" action="action_save_settings.php" method="post">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 20px;">
                                
                                <div>
                                    <div class="form-group">
                                        <label>Header Title</label>
                                        <input type="text" name="app_title" value="<?php echo htmlspecialchars($app_title); ?>" class="settings-input">
                                    </div>
                                    <div class="form-group">
                                        <label>Footer Text</label>
                                        <input type="text" name="footer_text" value="<?php echo htmlspecialchars($footer_txt); ?>" class="settings-input">
                                    </div>
                                </div>

                                <div>
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
                                </div>

                            </div>
                        </form>

                        <hr style="margin: 30px 0; border: 0; border-top: 1px solid var(--border-color);">
                        
                        <h4 style="margin-bottom: 15px; color: var(--text-main);">Security</h4>
                        <div style="margin-bottom: 40px;">
                            <button type="button" id="btn-open-password-modal" class="btn-save" style="background: #10b981;">Change Password</button>
                        </div>

                        <button type="submit" form="gen-settings-form" class="btn-save" style="width: 100%; padding: 15px; font-size: 16px;">
                            Save General Settings
                        </button>
                    </div>

                    <?php if($role === 'Admin' || $role === 'Developer'): ?>
                    <div id="tab-users" class="tab-content" style="padding: 30px;">
                        <div style="background: var(--hover-bg); padding: 20px; border-radius: 6px; margin-bottom: 25px;">
                            <h4 style="margin-bottom:15px;">Register New User</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <input type="text" id="new_u_name" placeholder="Name" class="settings-input">
                                <input type="text" id="new_u_email" placeholder="Email" class="settings-input">
                                <input type="text" id="new_u_phone" placeholder="Phone" class="settings-input">
                                <input type="text" id="new_u_dept" placeholder="Department" class="settings-input">
                                <input type="text" id="new_u_username" placeholder="Username (Login ID)" class="settings-input">
                                <input type="password" id="new_u_pass" placeholder="Password" class="settings-input">
                                <select id="new_u_role" class="settings-input" style="grid-column: span 2;">
                                    <option value="User">User</option>
                                    <option value="Developer">Developer</option>
                                    <option value="Admin">Admin</option>
                                    <option value="ISO User">ISO User</option> </select>
                            </div>
                            <button id="btn-add-user" class="btn-save" style="margin-top:15px; background:#10b981;">Add User</button>
                        </div>

                        <h4 style="margin-bottom:15px;">Existing Users</h4>
                        <div style="overflow-x: auto;">
                            <table class="file-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Username</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="user-list-body"></tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="isoModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Add New ISO</div>
                <button class="btn-close-modal" onclick="document.getElementById('isoModal').classList.remove('active')"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="modal-body">
                <div class="modal-input-group">
                    <label>Select ISO Standard</label>
                    <select id="newIsoSelect" class="settings-input">
                        <option value="9001|Quality Management Systems">ISO 9001 - Quality Management Systems</option>
                        <option value="14001|Environmental Management">ISO 14001 - Environmental Management</option>
                        <option value="27001|Information Security Management">ISO 27001 - Information Security Management</option>
                        <option value="45001|Occupational Health & Safety">ISO 45001 - Occupational Health & Safety</option>
                        <option value="50001|Energy Management">ISO 50001 - Energy Management</option>
                        <option value="22000|Food Safety Management">ISO 22000 - Food Safety Management</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-cancel" onclick="document.getElementById('isoModal').classList.remove('active')">Cancel</button>
                <button class="btn-modal btn-confirm" id="btn-save-iso">Add ISO</button>
            </div>
        </div>
    </div>

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

    <div class="modal-overlay" id="folderInfoModal">
        <div class="modal-card">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <ion-icon name="folder" style="font-size: 24px; color: #1f2937;"></ion-icon>
                </div>
                <button class="btn-close-modal" onclick="document.getElementById('folderInfoModal').classList.remove('active')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="modal-body">
                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1; background: var(--bg-body); padding: 15px; border-radius: 8px;">
                        <span style="color:var(--text-muted); font-size:12px; display:block; margin-bottom:5px;">Folder name:</span>
                        <span id="info-folder-name" style="font-size:16px; font-weight:600;">-</span>
                    </div>
                    <div style="flex: 1; background: var(--bg-body); padding: 15px; border-radius: 8px; display: flex; justify-content: center; align-items: center; gap: 20px;">
                        <div id="modal-folder-download" style="text-align: center; cursor: pointer;">
                            <ion-icon name="cloud-download-outline" style="font-size: 24px; color: var(--text-muted);"></ion-icon>
                            <div style="font-size: 11px; color: var(--text-main);">Download</div>
                        </div>
                    </div>
                </div>

                <div style="background: var(--bg-body); padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: left;">
                    <strong style="display:block; color:var(--text-muted); font-size:12px; margin-bottom:5px;">Audio File:</strong>
                    <span id="info-audio-name" style="font-size:16px; font-weight:600;">Scanning...</span>
                </div>

                <div style="background: var(--bg-body); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left;">
                    <strong style="display:block; color:var(--text-muted); font-size:12px; margin-bottom:5px;">Text File:</strong>
                    <span id="info-txt-name" style="font-size:16px; font-weight:600;">Scanning...</span>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center; background: white; border-top: none;">
                <button class="btn-modal btn-cancel" style="background: white; padding: 8px 30px;" onclick="document.getElementById('folderInfoModal').classList.remove('active')">Back</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="passwordModal" style="z-index: 2100;"> 
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Change Password</div>
                <button class="btn-close-modal" onclick="document.getElementById('passwordModal').classList.remove('active')"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <form action="action_change_password.php" method="post">
                <div class="modal-body">
                    <div class="modal-input-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required class="settings-input">
                    </div>
                    <div class="modal-input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required class="settings-input">
                    </div>
                    <div class="modal-input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required class="settings-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-cancel" onclick="document.getElementById('passwordModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-modal btn-confirm" style="background: #10b981;">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="iso-context-menu" class="custom-context-menu">
        <div class="ctx-item" id="ctx-delete-iso">
            <ion-icon name="trash-outline"></ion-icon> Delete ISO
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>