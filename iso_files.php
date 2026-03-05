<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$user = $_SESSION['user'];
$iso_folder = $_GET['folder'] ?? '';

// 2. Prevent hacking (Directory Traversal)
if (empty($iso_folder) || strpos($iso_folder, '..') !== false || strpos($iso_folder, '/') !== false) {
    die("Invalid ISO folder.");
}

// 3. Auto-create the ISO folder if it doesn't exist (prevents loading root files)
$base_dir = __DIR__ . '/uploads/' . $user . '/';
$iso_path = $base_dir . $iso_folder;
if (!is_dir($iso_path)) {
    mkdir($iso_path, 0777, true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISO Files - <?php echo htmlspecialchars($iso_folder); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <style>
        body { display: flex; flex-direction: column; background: var(--bg-body); height: 100vh; margin: 0; }
        .iso-container { padding: 30px; max-width: 1400px; margin: 0 auto; width: 100%; flex: 1; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: var(--text-main); text-decoration: none; font-weight: 600; font-size: 15px; transition: 0.2s; }
        .back-btn:hover { color: var(--primary); }
    </style>
</head>
<body>

    <header class="top-header" style="padding: 0 40px;">
        <div class="header-brand" style="border:none; width: auto; background:transparent; padding-left: 0;">
            <img src="logo.png" alt="Logo" class="logo-img">
            <h2 style="margin-left: 10px;">Data Storage Online</h2>
        </div>
        <div class="header-dynamic-center">
            <h3>ISO Documentation: <?php echo htmlspecialchars(str_replace('_', ' ', $iso_folder)); ?></h3>
        </div>
    </header>

    <div class="iso-container">
        <a href="index.php" class="back-btn">
            <ion-icon name="arrow-back-outline"></ion-icon> Back to Dashboard
        </a>
        
        <div class="file-toolbar">
            <div class="tab-label"><?php echo htmlspecialchars($iso_folder); ?> Files</div>
            <div class="toolbar-actions">
                <button class="action-btn primary" onclick="document.getElementById('iso-file-upload').click()">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Upload File
                </button>
                <input type="file" id="iso-file-upload" style="display: none;" multiple>
            </div>
        </div>

        <div class="file-workspace" style="height: 65vh;">
            <div class="file-table-container">
                <table class="file-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th>File Name</th>
                            <th style="width: 150px;">Date</th>
                            <th style="width: 100px; text-align: center;">Type</th>
                            <th class="col-actions" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="iso-file-table-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const currentPath = "<?php echo $iso_folder; ?>";
        const tableBody = document.getElementById('iso-file-table-body');
        const fileUpload = document.getElementById('iso-file-upload');

        function loadIsoFiles() {
            fetch('action_list_files.php?dir=' + encodeURIComponent(currentPath))
                .then(res => res.json())
                .then(files => {
                    tableBody.innerHTML = '';
                    if (files.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #888;">No files uploaded yet.</td></tr>';
                        return;
                    }
                    
                    files.forEach((file, index) => {
                        let ext = file.name.split('.').pop().toUpperCase();
                        if(file.type === 'folder') ext = 'FOLDER';

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="text-align:center;">${index + 1}</td>
                            <td style="cursor:pointer; color:#2563eb; font-weight:500;" onclick="window.open('view_file.php?f=' + encodeURIComponent('${file.relativePath}'), '_blank')">
                                <ion-icon name="document-outline" style="vertical-align:bottom; margin-right:5px;"></ion-icon> ${file.name}
                            </td>
                            <td>${file.date ? file.date : '-'}</td>
                            <td style="text-align:center;"><span class="badge" style="background:#e5e7eb; padding:4px 8px; border-radius:4px; font-size:12px; font-weight: 600;">${ext}</span></td>
                            <td class="col-actions">
                                <div class="action-icon-group">
                                    <span class="icon-btn delete" title="Delete" onclick="deleteIsoFile('${file.relativePath}')" style="cursor:pointer;">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </span>
                                </div>
                            </td>
                        `;
                        tableBody.appendChild(tr);
                    });
                });
        }

        // Delete Logic
        window.deleteIsoFile = function(filename) {
            if(!confirm('Delete this file?')) return;
            const fd = new FormData();
            fd.append('filename', filename);
            fetch('action_delete.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if(d.success) loadIsoFiles();
                    else alert(d.message);
                });
        };

        // Upload Logic
        fileUpload.addEventListener('change', async function() {
            if(this.files.length === 0) return;
            
            for(let file of this.files) {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('relativePath', currentPath + '/' + file.name);
                
                await fetch('action_upload.php', { method: 'POST', body: fd });
            }
            
            this.value = ''; // reset file input
            loadIsoFiles();
        });

        // Initialize table
        loadIsoFiles();
    </script>
</body>
</html>