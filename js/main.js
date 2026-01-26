document.addEventListener('DOMContentLoaded', () => {
    
    let currentPath = ''; 
    let allFiles = [];

    // --- Navigation ---
    const navDashboard = document.getElementById('nav-dashboard');
    const navFiles = document.getElementById('nav-files');
    const navSettingsBtn = document.getElementById('nav-settings-btn'); // Triggers Modal

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

    // --- Settings Modal Logic ---
    navSettingsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        settingsModal.classList.add('active');
        if(typeof CURRENT_USER_ROLE !== 'undefined' && CURRENT_USER_ROLE === 'Admin') {
            loadUsers(); // Load users if Admin
        }
    });

    document.getElementById('btn-close-settings').addEventListener('click', () => {
        settingsModal.classList.remove('active');
    });

    // Tab Switching
    const tabs = document.querySelectorAll('.tab-item');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
        });
    });

    // --- File Logic ---
    const tableBody = document.getElementById('file-table-body');
    const statTotal = document.getElementById('stat-total');
    const pieChart = document.getElementById('type-pie-chart');
    const searchInput = document.getElementById('file-search');

    if(searchInput) searchInput.addEventListener('input', renderTable);

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

        const filesToShow = allFiles.filter(file => {
            if (searchVal !== '' && !file.name.toLowerCase().includes(searchVal)) return false;
            return true;
        });

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
            
            if (file.type === 'folder') {
                typeLabel = 'FOLDER';
                iconName = 'folder-open';
                // CHANGED: Click triggers popup, not direct entry
                nameAction = `onclick="showFolderPopup('${file.name}', '${file.relativePath}')" style="cursor:pointer; color:#2563eb; font-weight:bold;"`;
            } else {
                if(file.name.endsWith('.mp3')) typeLabel = 'MP3';
                if(file.name.endsWith('.txt')) typeLabel = 'TXT';
                nameAction = `onclick="window.open('view_file.php?f=' + encodeURIComponent('${file.relativePath}'), '_blank')" style="cursor:pointer;"`;
            }

            // Simple delete button
            const deleteBtn = `<span class="icon-btn delete" onclick="deleteFile('${file.relativePath}')"><ion-icon name="trash-outline"></ion-icon></span>`;

            tr.innerHTML = `
                <td>${index + 1}</td>
                <td ${nameAction}><ion-icon name="${iconName}" style="vertical-align:bottom; margin-right:5px;"></ion-icon> ${file.name}</td>
                <td>${file.date ? file.date.substring(0,10) : '-'}</td>
                <td><span class="badge">${typeLabel}</span></td>
                <td>${deleteBtn}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // --- Global Functions ---

    window.goUpFolder = function() {
        if (currentPath.includes('/')) {
            currentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
        } else {
            currentPath = ''; 
        }
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

    // === NEW: FOLDER POPUP LOGIC ===
    window.showFolderPopup = function(folderName, relPath) {
        const modal = document.getElementById('folderInfoModal');
        const title = document.getElementById('info-folder-name');
        const audio = document.getElementById('info-audio-name');
        const txt = document.getElementById('info-txt-name');
        
        title.textContent = folderName;
        audio.textContent = 'Scanning...';
        txt.textContent = 'Scanning...';
        modal.classList.add('active');

        // Fetch contents of the clicked folder to find MP3/TXT
        fetch('action_list_files.php?dir=' + encodeURIComponent(relPath))
            .then(res => res.json())
            .then(files => {
                const mp3File = files.find(f => f.name.endsWith('.mp3'));
                const txtFile = files.find(f => f.name.endsWith('.txt'));

                audio.textContent = mp3File ? mp3File.name : 'No Audio File';
                txt.textContent = txtFile ? txtFile.name : 'No Text File';
            })
            .catch(() => {
                audio.textContent = 'Error loading info';
                txt.textContent = 'Error loading info';
            });
    };

    // === ADMIN: USER MANAGEMENT ===
    window.loadUsers = function() {
        const tbody = document.getElementById('user-list-body');
        if(!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';

        fetch('action_admin_users.php?action=list')
            .then(r => r.json())
            .then(users => {
                tbody.innerHTML = '';
                users.forEach(u => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${u.name || '-'}</td>
                        <td>${u.department || '-'}</td>
                        <td>${u.username}</td>
                        <td>
                            <button onclick="resetUser('${u.username}')" style="font-size:11px; padding:4px 8px; cursor:pointer;">Reset Pass</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    };

    const btnAddUser = document.getElementById('btn-add-user');
    if(btnAddUser) {
        btnAddUser.addEventListener('click', () => {
            const name = document.getElementById('new_u_name').value;
            const email = document.getElementById('new_u_email').value;
            const phone = document.getElementById('new_u_phone').value;
            const dept = document.getElementById('new_u_dept').value;
            const user = document.getElementById('new_u_username').value;
            const pass = document.getElementById('new_u_pass').value;

            if(!user || !pass) { alert("Username and Password required"); return; }

            const fd = new FormData();
            fd.append('action', 'add');
            fd.append('name', name);
            fd.append('email', email);
            fd.append('phone', phone);
            fd.append('department', dept);
            fd.append('username', user);
            fd.append('password', pass);

            fetch('action_admin_users.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        alert("User Added!");
                        loadUsers();
                        document.getElementById('new_u_username').value = '';
                        document.getElementById('new_u_pass').value = '';
                    } else {
                        alert("Error: " + res.message);
                    }
                });
        });
    }

    window.resetUser = function(username) {
        if(!confirm(`Reset password for ${username} to 'qwer1234'?`)) return;
        
        const fd = new FormData();
        fd.append('action', 'reset');
        fd.append('username', username);
        
        fetch('action_admin_users.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                alert(res.message);
            });
    }

    // --- Upload Modal Logic (Existing) ---
    const uploadModal = document.getElementById('uploadModal');
    const btnOpenUpload = document.getElementById('btn-open-upload-modal');
    const btnSaveFolder = document.getElementById('btn-save-folder');

    if(btnOpenUpload) {
        btnOpenUpload.addEventListener('click', () => {
            uploadModal.classList.add('active');
        });
    }

    if(btnSaveFolder) {
        btnSaveFolder.addEventListener('click', async () => {
            const folderName = document.getElementById('newFolderName').value;
            const audioFile = document.getElementById('modalAudioInput').files[0];
            const textFile = document.getElementById('modalTextInput').files[0];

            if(!folderName) { alert("Folder name required"); return; }

            // 1. Create Folder
            let fd = new FormData();
            fd.append('folder_name', folderName);
            fd.append('current_path', currentPath);
            let res = await fetch('action_create_folder.php', { method:'POST', body:fd });
            let json = await res.json();
            
            if(!json.success) { alert(json.message); return; }

            // 2. Upload Files
            let files = [];
            if(audioFile) files.push(audioFile);
            if(textFile) files.push(textFile);

            for(let f of files) {
                let uFd = new FormData();
                uFd.append('file', f);
                let path = (currentPath ? currentPath + '/' : '') + folderName + '/' + f.name;
                uFd.append('relativePath', path);
                await fetch('action_upload.php', { method:'POST', body:uFd });
            }

            uploadModal.classList.remove('active');
            loadDataAndRender();
        });
    }
    
    // Init
    if(document.getElementById('view-dashboard').style.display !== 'none') {
        loadDataAndRender();
    }
});