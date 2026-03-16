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
        
        /* 统一的样式调整（与 index.php 保持完全一致） */
        .date-filter { background: var(--input-bg); color: var(--text-main); border: 1px solid var(--border-color); padding: 5px; border-radius: 4px; font-size: 13px; }
        .file-table th:nth-child(1), .file-table td:nth-child(1) { width: 40px; text-align: center; }
        .file-table th:nth-child(2), .file-table td:nth-child(2) { width: 50px; text-align: center; }
        .file-table th:nth-child(3), .file-table td:nth-child(3) { width: 35%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .file-table th:nth-child(4), .file-table td:nth-child(4) { width: 190px; }
        .file-table th:nth-child(5), .file-table td:nth-child(5) { width: 80px; text-align: center; }
        .file-table th:nth-child(6), .file-table td:nth-child(6) { width: 180px; min-width: 180px; }
        .action-icon-group { display: flex; align-items: center; gap: 12px; }
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
            <div class="toolbar-actions">
                <button class="action-btn primary" onclick="document.getElementById('iso-file-upload').click()">
                    <ion-icon name="cloud-upload-outline"></ion-icon> Upload File
                </button>
                <input type="file" id="iso-file-upload" style="display: none;" multiple>
                
                <button class="action-btn" id="btn-bulk-delete" style="display: none; background: #dc2626; color: white; border-color: #dc2626; margin-left: 10px;">
                    <ion-icon name="trash-outline"></ion-icon> Delete Selected
                </button>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; margin-left: auto;">
                <input type="date" id="date-start" class="date-filter" title="Start Date">
                <span style="color:var(--text-muted); font-size:13px;">to</span>
                <input type="date" id="date-end" class="date-filter" title="End Date">
                
                <input type="text" id="file-search" placeholder="Search files..." 
                       style="padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 4px; background: var(--input-bg); color: var(--text-main);">
            </div>
        </div>

        <div class="file-workspace" style="height: 65vh; border-radius: 0 0 8px 8px;">
            <div class="file-table-container">
                <table class="file-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-files"></th>
                            <th>No</th>
                            <th>File Name</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="col-actions">Actions</th>
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
        const searchInput = document.getElementById('file-search');
        const dateStartInput = document.getElementById('date-start');
        const dateEndInput = document.getElementById('date-end');
        const selectAllCheckbox = document.getElementById('select-all-files');
        const btnBulkDelete = document.getElementById('btn-bulk-delete');

        let allFiles = []; // 保存所有文件用于搜索和过滤

        function loadIsoFiles() {
            fetch('action_list_files.php?dir=' + encodeURIComponent(currentPath))
                .then(res => res.json())
                .then(files => {
                    allFiles = files;
                    renderTable();
                });
        }

        function renderTable() {
            tableBody.innerHTML = '';
            
            // 重置复选框和批量删除按钮
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            toggleBulkButtons();

            const searchVal = searchInput.value.toLowerCase().trim();
            const startVal = dateStartInput.value;
            const endVal = dateEndInput.value;

            // 根据搜索框和日期过滤文件
            const filesToShow = allFiles.filter(file => {
                if (searchVal !== '' && !file.name.toLowerCase().includes(searchVal)) return false;
                if (startVal || endVal) {
                    const fileDate = file.date.substring(0, 10);
                    if (startVal && fileDate < startVal) return false;
                    if (endVal && fileDate > endVal) return false;
                }
                return true;
            });

            if (filesToShow.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #888;">No files found.</td></tr>';
                return;
            }
            
            // 渲染表格
            filesToShow.forEach((file, index) => {
                let ext = file.name.split('.').pop().toUpperCase();
                if(file.type === 'folder') ext = 'FOLDER';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="checkbox" class="file-checkbox" value="${file.relativePath}"></td>
                    <td style="text-align:center;">${index + 1}</td>
                    <td style="cursor:pointer; color:#2563eb; font-weight:500;" onclick="window.open('view_file.php?f=' + encodeURIComponent('${file.relativePath}'), '_blank')">
                        <ion-icon name="document-outline" style="vertical-align:bottom; margin-right:5px;"></ion-icon> ${file.name}
                    </td>
                    <td>${file.date ? file.date : '-'}</td>
                    <td style="text-align:center;"><span class="badge" style="background:#e5e7eb; padding:4px 8px; border-radius:4px; font-size:12px; font-weight: 600;">${ext}</span></td>
                    <td class="col-actions">
                        <div class="action-icon-group">
                            <span class="icon-btn" title="Rename" onclick="renameIsoFile('${file.name}')">
                                <ion-icon name="create-outline"></ion-icon>
                            </span>
                            <span class="icon-btn delete" title="Delete" onclick="deleteIsoFile('${file.relativePath}')" style="cursor:pointer;">
                                <ion-icon name="trash-outline"></ion-icon>
                            </span>
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // 监听搜索与日期过滤
        searchInput.addEventListener('input', renderTable);
        dateStartInput.addEventListener('change', renderTable);
        dateEndInput.addEventListener('change', renderTable);

        // 批量复选框逻辑
        selectAllCheckbox.addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.file-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            toggleBulkButtons();
        });

        tableBody.addEventListener('change', (e) => {
            if(e.target.classList.contains('file-checkbox')) {
                toggleBulkButtons();
                if(!e.target.checked) selectAllCheckbox.checked = false;
            }
        });

        function toggleBulkButtons() {
            const count = document.querySelectorAll('.file-checkbox:checked').length;
            if(count > 0) {
                btnBulkDelete.style.display = 'inline-flex';
                btnBulkDelete.innerHTML = '<ion-icon name="trash-outline" style="margin-right:5px;"></ion-icon> Delete (' + count + ')';
            } else {
                btnBulkDelete.style.display = 'none';
            }
        }

        // 单个重命名逻辑
        window.renameIsoFile = function(oldName) {
            let newName = prompt("Enter new name:", oldName);
            if (newName && newName !== oldName) {
                const fd = new FormData();
                fd.append('oldName', oldName);
                fd.append('newName', newName);
                fd.append('path', currentPath); 
                fetch('action_rename.php', { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                    if (res.success) loadIsoFiles();
                    else alert("Error: " + res.message);
                });
            }
        };

        // 单个删除逻辑
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

        // 批量删除逻辑
        btnBulkDelete.addEventListener('click', async () => {
            const selected = Array.from(document.querySelectorAll('.file-checkbox:checked'));
            if(selected.length === 0) return;
            
        if(!confirm('Are you sure you want to delete ' + selected.length + ' items?')) return;
            btnBulkDelete.disabled = true;
            btnBulkDelete.textContent = 'Deleting...';

            for (const checkbox of selected) {
                const fd = new FormData();
                fd.append('filename', checkbox.value);
                await fetch('action_delete.php', { method: 'POST', body: fd });
            }

            btnBulkDelete.disabled = false;
            loadIsoFiles();
        });

        // 文件上传逻辑
        fileUpload.addEventListener('change', async function() {
            if(this.files.length === 0) return;
            
            for(let file of this.files) {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('relativePath', currentPath + '/' + file.name);
                
                await fetch('action_upload.php', { method: 'POST', body: fd });
            }
            
            this.value = ''; // 重置文件输入框
            loadIsoFiles();
        });

        // 初始化加载表格
        loadIsoFiles();
    </script>
</body>
</html>