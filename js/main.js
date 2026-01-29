document.addEventListener('DOMContentLoaded', () => {
    
    let currentPath = ''; 
    let allFiles = [];

    // --- Navigation ---
    const navDashboard = document.getElementById('nav-dashboard');
    const navFiles = document.getElementById('nav-files');
    const navSettingsBtn = document.getElementById('nav-settings-btn'); 

    const viewDashboard = document.getElementById('view-dashboard');
    const viewFiles = document.getElementById('view-files');
    const settingsModal = document.getElementById('settingsModal');

    function switchView(viewName) {
        viewDashboard.style.display = 'none';
        viewFiles.style.display = 'none';
        navDashboard.classList.remove('active');
        navFiles.classList.remove('active');

        if(viewName === 'dashboard') {
            viewDashboard.style.display = 'block';
            navDashboard.classList.add('active');
            loadDataAndRender();
        } else if(viewName === 'files') {
            viewFiles.style.display = 'block';
            navFiles.classList.add('active');
            loadDataAndRender();
        }
    }

    navDashboard.addEventListener('click', (e) => { e.preventDefault(); switchView('dashboard'); });
    navFiles.addEventListener('click', (e) => { e.preventDefault(); switchView('files'); });

    navSettingsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        settingsModal.classList.add('active');
        if(typeof CURRENT_USER_ROLE !== 'undefined' && CURRENT_USER_ROLE === 'Admin') loadUsers();
    });

    document.getElementById('btn-close-settings').addEventListener('click', () => {
        settingsModal.classList.remove('active');
    });

    // Tab Switching
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
        });
    });

    // --- Change Password Modal Logic ---
    const btnOpenPassword = document.getElementById('btn-open-password-modal');
    const passwordModal = document.getElementById('passwordModal');

    if(btnOpenPassword) {
        btnOpenPassword.addEventListener('click', (e) => {
            e.preventDefault();
            passwordModal.classList.add('active');
        });
    }

    // --- File Logic ---
    const tableBody = document.getElementById('file-table-body');
    const statTotal = document.getElementById('stat-total');
    const pieChart = document.getElementById('type-pie-chart');
    const searchInput = document.getElementById('file-search');
    const dateStartInput = document.getElementById('date-start');
    const dateEndInput = document.getElementById('date-end');

    if(searchInput) searchInput.addEventListener('input', renderTable);
    if(dateStartInput) dateStartInput.addEventListener('change', renderTable);
    if(dateEndInput) dateEndInput.addEventListener('change', renderTable);

    function loadDataAndRender() {
        fetch('action_list_files.php?dir=' + encodeURIComponent(currentPath))
            .then(res => res.json())
            .then(data => {
                allFiles = data;
                renderStats();
                renderTable();
            });
    }

    function renderStats() {
        if(!statTotal) return;
        statTotal.textContent = allFiles.length;
        let txtCount = allFiles.filter(f => f.name.endsWith('.txt')).length;
        let mp3Count = allFiles.filter(f => f.name.endsWith('.mp3')).length;
        
        const total = allFiles.length || 1; 
        const txtPercent = (txtCount / total) * 100;
        const mp3Percent = (mp3Count / total) * 100;
        
        if(pieChart) {
            pieChart.style.background = `conic-gradient(#f59e0b 0% ${txtPercent}%, #2563eb ${txtPercent}% ${txtPercent + mp3Percent}%, #e5e7eb 0 100%)`;
        }
    }

    function renderTable() {
    if(!tableBody) return;
    tableBody.innerHTML = '';
    const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const startVal = dateStartInput ? dateStartInput.value : '';
    const endVal = dateEndInput ? dateEndInput.value : '';

    const filesToShow = allFiles.filter(file => {
        // 1. Search Filter
        if (searchVal !== '' && !file.name.toLowerCase().includes(searchVal)) return false;
        
        // 2. Date Filter
        if (startVal || endVal) {
            const fileDate = file.date.substring(0, 10);
            if (startVal && fileDate < startVal) return false;
            if (endVal && fileDate > endVal) return false;
        }
        return true;
    });

    // Add "Go Back" row if in a subfolder
    if (currentPath !== '') {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td></td><td colspan="4"><a href="#" onclick="goUpFolder()" style="font-weight:bold; color:#333; text-decoration:none;">... (Go Back)</a></td>`;
        tableBody.appendChild(tr);
    }

    filesToShow.forEach((file, index) => {
        const tr = document.createElement('tr');
        let typeLabel = 'FILE';
        let iconName = 'document-outline';
        let nameAction = '';

        // Determine icon and click behavior based on type
        if (file.type === 'folder') {
            typeLabel = 'FOLDER';
            iconName = 'folder-open';
            nameAction = `onclick="showFolderPopup('${file.name}', '${file.relativePath}')" style="cursor:pointer; color:#2563eb; font-weight:bold;"`;
        } else {
            if(file.name.endsWith('.mp3')) typeLabel = 'MP3';
            if(file.name.endsWith('.txt')) typeLabel = 'TXT';
            nameAction = `onclick="window.open('view_file.php?f=' + encodeURIComponent('${file.relativePath}'), '_blank')" style="cursor:pointer;"`;
        }

        // Updated Actions: Removed download and rename icons
        const actionsHTML = `
            <div class="action-icon-group">
                <span class="icon-btn delete" title="Delete" onclick="deleteFile('${file.relativePath}')">
                    <ion-icon name="trash-outline"></ion-icon>
                </span>
            </div>
        `;

        tr.innerHTML = `
            <td>${index + 1}</td>
            <td ${nameAction}><ion-icon name="${iconName}" style="vertical-align:bottom; margin-right:5px;"></ion-icon> ${file.name}</td>
            <td>${file.date ? file.date.substring(0,10) : '-'}</td>
            <td><span class="badge">${typeLabel}</span></td>
            <td class="col-actions">${actionsHTML}</td>
        `;
        tableBody.appendChild(tr);
    });
}

    // --- Helpers ---
    window.goUpFolder = function() {
        currentPath = currentPath.includes('/') ? currentPath.substring(0, currentPath.lastIndexOf('/')) : '';
        loadDataAndRender();
    };

    window.deleteFile = function(filename) {
        if(!confirm(`Delete ${filename}?`)) return;
        const fd = new FormData();
        fd.append('filename', filename);
        fetch('action_delete.php', { method: 'POST', body: fd }).then(r=>r.json()).then(d=>{
            if(d.success) loadDataAndRender();
            else alert(d.message);
        });
    };

    window.renameFile = function(oldName, type) {
        let newName = prompt("Enter new name:", oldName);
        if (newName && newName !== oldName) {
            const fd = new FormData();
            fd.append('oldName', oldName);
            fd.append('newName', newName);
            fd.append('path', currentPath); 
            fetch('action_rename.php', { method: 'POST', body: fd }).then(r=>r.json()).then(res => {
                if (res.success) loadDataAndRender();
                else alert("Error: " + res.message);
            });
        }
    };

    window.downloadSingleFile = function(relativePath, fileName) {
        const link = document.createElement('a');
        link.href = `uploads/${CURRENT_USER_NAME}/${relativePath}`;
        link.download = fileName; 
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // --- Updated Folder Info Popup Logic ---
    window.showFolderPopup = function(folderName, relPath) {
        const modal = document.getElementById('folderInfoModal');
        
        // 1. Reset text content and show modal
        document.getElementById('info-folder-name').textContent = folderName;
        document.getElementById('info-audio-name').textContent = 'Scanning...';
        document.getElementById('info-txt-name').textContent = 'Scanning...';
        modal.classList.add('active');

        // 2. Attach click events to the new popup action icons
        document.getElementById('modal-folder-download').onclick = () => {
            window.location.href = 'action_download_folder.php?folder=' + encodeURIComponent(relPath);
        };

        document.getElementById('modal-folder-rename').onclick = () => {
            modal.classList.remove('active');
            renameFile(folderName, 'folder');
        };

        // 3. Fetch folder contents to update file details
        fetch('action_list_files.php?dir=' + encodeURIComponent(relPath))
            .then(res => res.json())
            .then(files => {
                const mp3 = files.find(f => f.name.endsWith('.mp3'));
                const txt = files.find(f => f.name.endsWith('.txt'));
                
                document.getElementById('info-audio-name').textContent = mp3 ? mp3.name : 'No Audio';
                document.getElementById('info-txt-name').textContent = txt ? txt.name : 'No Text';
            })
            .catch(err => {
                console.error("Error fetching folder details:", err);
                document.getElementById('info-audio-name').textContent = 'Error loading';
                document.getElementById('info-txt-name').textContent = 'Error loading';
            });
    };

    // --- Admin & Upload ---
    window.loadUsers = function() {
        const tbody = document.getElementById('user-list-body');
        if(!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
        fetch('action_admin_users.php?action=list').then(r => r.json()).then(users => {
            tbody.innerHTML = '';
            users.forEach(u => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${u.name||'-'}</td>
                    <td>${u.department||'-'}</td>
                    <td>${u.username}</td>
                    <td>
                        <button onclick="resetUser('${u.username}')" 
                                style="background: #f59e0b; color: white; border:none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 6px; font-weight: 500; transition: background 0.2s;">
                            Reset
                        </button>
                        <button onclick="deleteUser('${u.username}')" 
                                style="background: #dc2626; color: white; border:none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 500; transition: background 0.2s;">
                            Delete
                        </button>
                    </td>`;
                tbody.appendChild(tr);
            });
        });
    };

    window.deleteUser = function(u) {
        if(!confirm(`Are you sure you want to DELETE user: ${u}? This action cannot be undone.`)) return;
        const fd = new FormData(); 
        fd.append('action', 'delete'); 
        fd.append('username', u);
        fetch('action_admin_users.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(r => {
                alert(r.message);
                if (r.success) loadUsers();
            });
    };

    const btnAddUser = document.getElementById('btn-add-user');
    if(btnAddUser) {
        btnAddUser.addEventListener('click', () => {
            const inputs = ['new_u_name','new_u_email','new_u_phone','new_u_dept','new_u_username','new_u_pass'].map(id => document.getElementById(id).value);
            if(!inputs[4] || !inputs[5]) { alert("Username and Password required"); return; }
            
            const fd = new FormData();
            fd.append('action', 'add');
            fd.append('name', inputs[0]); fd.append('email', inputs[1]); fd.append('phone', inputs[2]);
            fd.append('department', inputs[3]); fd.append('username', inputs[4]); fd.append('password', inputs[5]);

            fetch('action_admin_users.php', { method: 'POST', body: fd }).then(r => r.json()).then(res => {
                if(res.success) { alert("User Added!"); loadUsers(); } 
                else { alert(res.message); }
            });
        });
    }

    window.resetUser = function(u) {
        if(!confirm(`Reset password for ${u}?`)) return;
        const fd = new FormData(); fd.append('action', 'reset'); fd.append('username', u);
        fetch('action_admin_users.php', { method: 'POST', body: fd }).then(r=>r.json()).then(r=>alert(r.message));
    };

    const uploadModal = document.getElementById('uploadModal');
    const btnOpenUpload = document.getElementById('btn-open-upload-modal');
    const btnSaveFolder = document.getElementById('btn-save-folder');

    if(btnOpenUpload) btnOpenUpload.addEventListener('click', () => uploadModal.classList.add('active'));

    if(btnSaveFolder) {
        btnSaveFolder.addEventListener('click', async () => {
            const folderInput = document.getElementById('newFolderName');
            const audioInput = document.getElementById('modalAudioInput');
            const txtInput = document.getElementById('modalTextInput');
            
            const folderName = folderInput.value;
            if(!folderName) { alert("Folder name required"); return; }

            let fd = new FormData();
            fd.append('folder_name', folderName);
            fd.append('current_path', currentPath);
            let res = await fetch('action_create_folder.php', { method:'POST', body:fd });
            let json = await res.json();
            
            if(!json.success) { alert(json.message); return; }

            let files = [];
            if(audioInput.files[0]) files.push(audioInput.files[0]);
            if(txtInput.files[0]) files.push(txtInput.files[0]);

            for(let f of files) {
                let uFd = new FormData();
                uFd.append('file', f);
                let path = (currentPath ? currentPath + '/' : '') + folderName + '/' + f.name;
                uFd.append('relativePath', path);
                await fetch('action_upload.php', { method:'POST', body:uFd });
            }

            folderInput.value = '';
            audioInput.value = '';
            txtInput.value = '';
            uploadModal.classList.remove('active');
            loadDataAndRender();
        });
    }
    
    if(document.getElementById('view-dashboard').style.display !== 'none') loadDataAndRender();
});