document.addEventListener('DOMContentLoaded', () => {
    
    let currentPath = ''; 
    let allFiles = [];

    // --- Navigation ---
    const navDashboard = document.getElementById('nav-dashboard');
    const navFiles = document.getElementById('nav-files');
    const navIso = document.getElementById('nav-iso'); 
    const navSettings = document.getElementById('nav-settings'); 

    const viewDashboard = document.getElementById('view-dashboard');
    const viewFiles = document.getElementById('view-files');
    const viewIso = document.getElementById('view-iso'); 
    const viewSettings = document.getElementById('view-settings');

    function switchView(viewName) {
        viewDashboard.style.display = 'none';
        viewFiles.style.display = 'none';
        viewIso.style.display = 'none';
        viewSettings.style.display = 'none';
        
        navDashboard.classList.remove('active');
        navFiles.classList.remove('active');
        if(navIso) navIso.classList.remove('active');
        navSettings.classList.remove('active');

        if(viewName === 'dashboard') {
            viewDashboard.style.display = 'block';
            navDashboard.classList.add('active');
            loadDataAndRender();
        } else if(viewName === 'files') {
            viewFiles.style.display = 'block';
            navFiles.classList.add('active');
            loadDataAndRender();
        } else if(viewName === 'iso') { 
            viewIso.style.display = 'block';
            if(navIso) navIso.classList.add('active');
            loadIsoData();
        } else if(viewName === 'settings') {
            viewSettings.style.display = 'block';
            navSettings.classList.add('active');
            if(typeof CURRENT_USER_ROLE !== 'undefined' && (CURRENT_USER_ROLE === 'Admin' || CURRENT_USER_ROLE === 'Developer')) {
                loadUsers();
            }
        }
    }

    navDashboard.addEventListener('click', (e) => { e.preventDefault(); switchView('dashboard'); });
    navFiles.addEventListener('click', (e) => { e.preventDefault(); switchView('files'); });
    if(navIso) navIso.addEventListener('click', (e) => { e.preventDefault(); switchView('iso'); });
    navSettings.addEventListener('click', (e) => { e.preventDefault(); switchView('settings'); });

    // --- ISO LOGIC ---
    const btnOpenIsoModal = document.getElementById('btn-open-iso-modal');
    const isoModal = document.getElementById('isoModal');
    const btnSaveIso = document.getElementById('btn-save-iso');
    const isoSearch = document.getElementById('iso-search');
    const ctxMenu = document.getElementById('iso-context-menu');
    let rightClickedIsoId = null;

    if(btnOpenIsoModal) btnOpenIsoModal.addEventListener('click', () => isoModal.classList.add('active'));
    
    // Global Click to close context menu
    window.addEventListener('click', () => {
        if(ctxMenu) ctxMenu.style.display = 'none';
    });

    if(isoSearch) {
        isoSearch.addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.iso-card').forEach(card => {
                const txt = card.textContent.toLowerCase();
                card.style.display = txt.includes(val) ? 'block' : 'none';
            });
        });
    }

    if(btnSaveIso) {
        btnSaveIso.addEventListener('click', async () => {
            const select = document.getElementById('newIsoSelect');
            if(!select || !select.value) return;

            const parts = select.value.split('|');
            const num = parts[0];
            const name = parts[1];

            const fd = new FormData();
            fd.append('iso_number', num);
            fd.append('iso_name', name);

            const res = await fetch('action_iso.php', { method: 'POST', body: fd });
            const json = await res.json();

            if(json.success) {
                select.selectedIndex = 0;
                isoModal.classList.remove('active');
                loadIsoData();
            } else {
                alert(json.message);
            }
        });
    }

    // Context Menu Action: Delete
    const btnCtxDelete = document.getElementById('ctx-delete-iso');
    if(btnCtxDelete) {
        btnCtxDelete.addEventListener('click', async () => {
            if(!rightClickedIsoId) return;
            if(!confirm("Are you sure you want to delete this ISO?")) return;

            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', rightClickedIsoId);

            const res = await fetch('action_iso.php', { method: 'POST', body: fd });
            const json = await res.json();

            if(json.success) {
                loadIsoData();
            } else {
                alert(json.message);
            }
        });
    }

    function loadIsoData() {
        const grid = document.getElementById('iso-grid-container');
        if(!grid) return;
        grid.innerHTML = '<p>Loading...</p>';
        
        fetch('action_iso.php')
            .then(r => r.json())
            .then(data => {
                grid.innerHTML = '';
                if(data.length === 0) {
                    grid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#888;">No ISOs found. Click "Add ISO" to create one.</p>';
                    return;
                }
                data.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'iso-card';
                    card.innerHTML = `
                        <div class="iso-img-container">
                            <img src="${item.image}" alt="ISO" class="iso-img">
                        </div>
                        <div class="iso-title">ISO ${item.number}</div>
                        <div class="iso-subtitle">${item.name}</div>
                    `;

                    // Right Click (Context Menu)
                    card.addEventListener('contextmenu', (e) => {
                        e.preventDefault();
                        rightClickedIsoId = item.id;
                        
                        // Position the menu at mouse coordinates
                        ctxMenu.style.display = 'block';
                        ctxMenu.style.left = e.pageX + 'px';
                        ctxMenu.style.top = e.pageY + 'px';
                    });

                    // Left Click (Go to Files "Page")
                    // We assume files for this ISO are stored in a folder named "ISO_{NUMBER}"
                    card.addEventListener('click', (e) => {
                        // Prevent triggering if we are clicking the menu logic
                        // Reload page with a query param to open that folder
                        window.location.href = `index.php?iso_folder=ISO_${item.number}`;
                    });

                    grid.appendChild(card);
                });
            });
    }

    // --- Tab Switching ---
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
        });
    });

    // --- Change Password Modal ---
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
    const selectAllCheckbox = document.getElementById('select-all-files');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    
    // REMOVED BTN BULK DOWNLOAD LOGIC HERE

    if(searchInput) searchInput.addEventListener('input', renderTable);
    if(dateStartInput) dateStartInput.addEventListener('change', renderTable);
    if(dateEndInput) dateEndInput.addEventListener('change', renderTable);

    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.file-checkbox');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            toggleBulkButtons();
        });
    }

    function toggleBulkButtons() {
        const count = document.querySelectorAll('.file-checkbox:checked').length;
        
        if(count > 0) {
            if(btnBulkDelete) {
                btnBulkDelete.style.display = 'inline-flex';
                btnBulkDelete.innerHTML = `<ion-icon name="trash-outline" style="margin-right:5px;"></ion-icon> Delete (${count})`;
            }
            // Removed download button display logic
        } else {
            if(btnBulkDelete) btnBulkDelete.style.display = 'none';
        }
    }

    if(tableBody) {
        tableBody.addEventListener('change', (e) => {
            if(e.target.classList.contains('file-checkbox')) {
                toggleBulkButtons();
                if(!e.target.checked && selectAllCheckbox) selectAllCheckbox.checked = false;
            }
        });
    }

    if(btnBulkDelete) {
        btnBulkDelete.addEventListener('click', async () => {
            const selected = Array.from(document.querySelectorAll('.file-checkbox:checked'));
            if(selected.length === 0) return;
            
            if(!confirm(`Are you sure you want to delete ${selected.length} items? This action cannot be undone.`)) return;

            btnBulkDelete.disabled = true;
            btnBulkDelete.textContent = 'Deleting...';

            for (const checkbox of selected) {
                const fd = new FormData();
                fd.append('filename', checkbox.value);
                await fetch('action_delete.php', { method: 'POST', body: fd });
            }

            btnBulkDelete.disabled = false;
            loadDataAndRender();
        });
    }
    
    // REMOVED BULK DOWNLOAD EVENT LISTENER

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

        if(selectAllCheckbox) selectAllCheckbox.checked = false;
        toggleBulkButtons();

        const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const startVal = dateStartInput ? dateStartInput.value : '';
        const endVal = dateEndInput ? dateEndInput.value : '';

        const filesToShow = allFiles.filter(file => {
            if (searchVal !== '' && !file.name.toLowerCase().includes(searchVal)) return false;
            if (startVal || endVal) {
                const fileDate = file.date.substring(0, 10);
                if (startVal && fileDate < startVal) return false;
                if (endVal && fileDate > endVal) return false;
            }
            return true;
        });

        if (currentPath !== '') {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td></td><td></td><td colspan="4"><a href="#" onclick="goUpFolder()" style="font-weight:bold; color:#333; text-decoration:none;">... (Go Back)</a></td>`;
            tableBody.appendChild(tr);
        }

        filesToShow.forEach((file, index) => {
            const tr = document.createElement('tr');
            let typeLabel = 'FILE';
            let iconName = 'document-outline';
            let nameAction = '';

            if (file.type === 'folder') {
                typeLabel = 'FOLDER';
                iconName = 'folder-open';
                nameAction = `onclick="showFolderPopup('${file.name}', '${file.relativePath}')" style="cursor:pointer; color:#2563eb; font-weight:bold;"`;
            } else {
                if(file.name.endsWith('.mp3')) typeLabel = 'MP3';
                if(file.name.endsWith('.txt')) typeLabel = 'TXT';
                nameAction = `onclick="window.open('view_file.php?f=' + encodeURIComponent('${file.relativePath}'), '_blank')" style="cursor:pointer;"`;
            }

            const actionsHTML = `
                <div class="action-icon-group">
                    <span class="icon-btn" title="Rename" onclick="renameFile('${file.name}', '${file.type}')">
                        <ion-icon name="create-outline"></ion-icon>
                    </span>
                    <span class="icon-btn delete" title="Delete" onclick="deleteFile('${file.relativePath}')">
                        <ion-icon name="trash-outline"></ion-icon>
                    </span>
                </div>
            `;

            tr.innerHTML = `
                <td><input type="checkbox" class="file-checkbox" value="${file.relativePath}"></td>
                <td>${index + 1}</td>
                <td ${nameAction}><ion-icon name="${iconName}" style="vertical-align:bottom; margin-right:5px;"></ion-icon> ${file.name}</td>
                <td>${file.date ? file.date.substring(0,10) : '-'}</td>
                <td><span class="badge">${typeLabel}</span></td>
                <td class="col-actions">${actionsHTML}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

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

    window.showFolderPopup = function(folderName, relPath) {
        const modal = document.getElementById('folderInfoModal');
        
        document.getElementById('info-folder-name').textContent = folderName;
        document.getElementById('info-audio-name').textContent = 'Scanning...';
        document.getElementById('info-txt-name').textContent = 'Scanning...';
        modal.classList.add('active');

        document.getElementById('modal-folder-download').onclick = () => {
            window.location.href = 'action_download_folder.php?folder=' + encodeURIComponent(relPath);
        };

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
            const roleSelect = document.getElementById('new_u_role');
            if(roleSelect) fd.append('role', roleSelect.value);

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
    
    // --- STARTUP LOGIC: Handle URL Params for "Left Click Navigation" ---
    // If the URL is index.php?iso_folder=ISO_9001, we automatically switch to the files tab
    // and open that folder.
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('iso_folder')) {
        const targetFolder = urlParams.get('iso_folder');
        currentPath = targetFolder;
        switchView('files');
        // Clean the URL so refreshing doesn't stick us here forever (optional)
        window.history.replaceState({}, document.title, "index.php");
    } else if(document.getElementById('view-dashboard').style.display !== 'none') {
        loadDataAndRender();
    }
});