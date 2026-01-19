document.addEventListener('DOMContentLoaded', () => {
    
    // === NEW: Track Current Folder Path ===
    let currentPath = ''; 

    // --- Navigation Elements ---
    const navDashboard = document.getElementById('nav-dashboard');
    const navFiles = document.getElementById('nav-files');
    const navSettings = document.getElementById('nav-settings');
    
    const viewDashboard = document.getElementById('view-dashboard');
    const viewFiles = document.getElementById('view-files');
    const viewSettings = document.getElementById('view-settings');

    function switchView(viewName) {
        viewDashboard.style.display = 'none';
        viewFiles.style.display = 'none';
        viewSettings.style.display = 'none';
        
        navDashboard.classList.remove('active');
        navFiles.classList.remove('active');
        if(navSettings) navSettings.classList.remove('active');

        if(viewName === 'dashboard') {
            viewDashboard.style.display = 'block';
            navDashboard.classList.add('active');
            loadDataAndRender();
        } else if(viewName === 'files') {
            viewFiles.style.display = 'block';
            navFiles.classList.add('active');
            loadDataAndRender();
        } else if(viewName === 'settings') {
            viewSettings.style.display = 'block';
            if(navSettings) navSettings.classList.add('active');
        }
    }

    if(navDashboard) navDashboard.addEventListener('click', (e) => { e.preventDefault(); switchView('dashboard'); });
    if(navFiles) navFiles.addEventListener('click', (e) => { e.preventDefault(); switchView('files'); });
    if(navSettings) navSettings.addEventListener('click', (e) => { e.preventDefault(); switchView('settings'); });

    // --- File Logic ---
    let allFiles = [];
    const tableBody = document.getElementById('file-table-body');
    const statTotal = document.getElementById('stat-total');
    const pieChart = document.getElementById('type-pie-chart');

    // Upload Elements
    const btnUpload = document.getElementById('btn-trigger-upload');
    const fileInput = document.getElementById('fileInput');
    const btnFolder = document.getElementById('btn-trigger-folder');
    const folderInput = document.getElementById('folderInput');
    
    // Filter Elements
    const filterSelect = document.getElementById('filterSelect');
    const datePicker = document.querySelector('.date-picker');
    // === NEW: Search Input ===
    const searchInput = document.getElementById('file-search');

    // Add Listeners for Filters
    if(filterSelect) filterSelect.addEventListener('change', renderTable);
    if(datePicker) datePicker.addEventListener('change', renderTable);
    // === NEW: Add Listener for Search ===
    if(searchInput) searchInput.addEventListener('input', renderTable);

    function loadDataAndRender() {
        // Pass the 'currentPath' to PHP so it knows which folder to scan
        fetch('action_list_files.php?dir=' + encodeURIComponent(currentPath))
            .then(res => res.json())
            .then(data => {
                allFiles = data;
                renderStats();
                renderTable();
            })
            .catch(err => console.error("Error loading files:", err));
    }

    function renderStats() {
        if(!statTotal) return;
        // Only count total items in current view
        statTotal.textContent = allFiles.length;

        let txtCount = 0;
        let mp3Count = 0;
        allFiles.forEach(f => {
            if(f.name.endsWith('.txt')) txtCount++;
            else if(f.name.endsWith('.mp3')) mp3Count++;
        });

        const total = allFiles.length || 1; 
        const txtPercent = (txtCount / total) * 100;
        const mp3Percent = (mp3Count / total) * 100;
        const p1 = txtPercent;
        const p2 = txtPercent + mp3Percent;

        if(pieChart) {
            pieChart.style.background = `conic-gradient(#f59e0b 0% ${p1}%, #2563eb ${p1}% ${p2}%, #e5e7eb ${p2}% 100%)`;
        }
    }

    function renderTable() {
        if(!tableBody) return;
        tableBody.innerHTML = '';

        // 1. Get current filter values
        const filterVal = filterSelect ? filterSelect.value : 'all';
        const dateVal = datePicker ? datePicker.value : '';
        // === NEW: Get search value ===
        const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';

        // 2. Filter the array
        const filesToShow = allFiles.filter(file => {
            
            // A. Check Type
            let typeMatch = false;
            
            if (filterVal === 'all') {
                typeMatch = true;
            } 
            else if (file.type === 'folder') {
                // If we are strictly filtering for mp3/txt, usually folders are hidden
                typeMatch = false; 
            } 
            else {
                if(filterVal === 'txt' && file.name.toLowerCase().endsWith('.txt')) typeMatch = true;
                else if(filterVal === 'mp3' && file.name.toLowerCase().endsWith('.mp3')) typeMatch = true;
            }

            // B. Check Date
            let dateMatch = true;
            if (dateVal !== '') {
                // Check if file.date (YYYY-MM-DD HH:MM) starts with the picked date
                if (!file.date.startsWith(dateVal)) {
                    dateMatch = false;
                }
            }

            // === NEW: C. Check Search Name ===
            let nameMatch = true;
            if (searchVal !== '') {
                // Check if file name includes the search text
                nameMatch = file.name.toLowerCase().includes(searchVal);
            }

            // All must be true to show the file
            return typeMatch && dateMatch && nameMatch;
        });

        // 3. Add "Go Back" Row if we are inside a folder
        if (currentPath !== '') {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td></td>
                <td><ion-icon name="arrow-undo" style="font-size:18px; color:#666;"></ion-icon></td>
                <td colspan="4">
                    <a href="#" onclick="goUpFolder()" style="font-weight:bold; color:#333; text-decoration:none; display:flex; align-items:center; gap:5px;">
                        ... (Go Back)
                    </a>
                </td>
            `;
            tableBody.appendChild(tr);
        }

        // 4. Handle Empty State
        if(filesToShow.length === 0 && currentPath === '') {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No files found matching filters.</td></tr>';
            return;
        }

        // 5. Render Rows
        filesToShow.forEach((file, index) => {
            const tr = document.createElement('tr');
            
            let typeLabel = 'FILE';
            let iconName = 'document-outline';
            let clickAction = '';
            let actionButtons = '';

            // --- FOLDER RENDER LOGIC ---
           if (file.type === 'folder') {
                typeLabel = 'FOLDER';
                iconName = 'folder-open';
                // Click name to enter folder
                clickAction = `onclick="enterFolder('${file.name}')" style="cursor:pointer; color:#2563eb; font-weight:bold;"`;
                
                // === UPDATED: Added Download Button for Folders ===
                // It points to our new action_download_folder.php
                actionButtons = `
                     <a href="action_download_folder.php?folder=${encodeURIComponent(file.relativePath)}" class="icon-btn download" title="Download Folder as Zip">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                     </a>
                     <span class="icon-btn delete" onclick="deleteFile('${file.relativePath}')" title="Delete Folder">
                        <ion-icon name="trash-outline"></ion-icon>
                    </span>
                `;
            }
            // --- FILE RENDER LOGIC ---
            else {
                if(file.name.endsWith('.mp3')) typeLabel = 'MP3';
                if(file.name.endsWith('.txt')) typeLabel = 'TXT';
                
                actionButtons = `
                    <span class="icon-btn open" onclick="window.open('view_file.php?f=' + encodeURIComponent('${file.relativePath}'), '_blank')" title="Open">
                        <ion-icon name="eye-outline"></ion-icon>
                    </span>
                    <a href="${file.path}" download="${file.name}" class="icon-btn download" title="Download">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                    </a>
                    <span class="icon-btn delete" onclick="deleteFile('${file.relativePath}')" title="Delete">
                        <ion-icon name="trash-outline"></ion-icon>
                    </span>
                `;
            }

            tr.innerHTML = `
                <td><input type="checkbox"></td>
                <td>${index + 1}</td>
                <td ${clickAction}>
                    <ion-icon name="${iconName}" style="vertical-align:bottom; margin-right:5px;"></ion-icon>
                    ${file.name}
                </td>
                <td>${file.date || '-'}</td>
                <td><span class="badge">${typeLabel}</span></td>
                <td style="white-space:nowrap;">
                    ${actionButtons}
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // --- Global Functions for HTML onClick access ---
    
    // 1. Enter Folder
    window.enterFolder = function(folderName) {
        if (currentPath === '') {
            currentPath = folderName;
        } else {
            currentPath = currentPath + '/' + folderName;
        }
        loadDataAndRender();
    };

    // 2. Go Back Up
    window.goUpFolder = function() {
        if (currentPath.includes('/')) {
            // Remove the last segment
            currentPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
        } else {
            // Back to root
            currentPath = ''; 
        }
        loadDataAndRender();
    };

    // --- Upload Handler (Supports Folders) ---
    function handleUpload(fileList) {
        const files = Array.from(fileList);
        if(files.length === 0) return;

        let processed = 0;
        files.forEach(file => {
            const formData = new FormData();
            formData.append('file', file);
            
            // If inside a folder, prepend currentPath to the relative path
            let uploadPath = file.name;
            if (file.webkitRelativePath) {
                uploadPath = file.webkitRelativePath; 
                if(currentPath !== '') {
                    uploadPath = currentPath + '/' + uploadPath;
                }
            } else {
                if(currentPath !== '') {
                    uploadPath = currentPath + '/' + file.name;
                }
            }
            
            formData.append('relativePath', uploadPath);

            fetch('action_upload.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    processed++;
                    if(processed === files.length) {
                        loadDataAndRender();
                    }
                })
                .catch(err => console.error(err));
        });
    }

    if(btnUpload) btnUpload.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => {
        handleUpload(e.target.files);
        fileInput.value = ''; 
    });

    if(btnFolder) btnFolder.addEventListener('click', () => folderInput.click());
    if(folderInput) {
        folderInput.addEventListener('change', (e) => {
            handleUpload(e.target.files);
            folderInput.value = ''; 
        });
    }

    // --- Delete Handler ---
    window.deleteFile = function(filename) {
        if(!confirm(`Delete ${filename}?`)) return;
        
        const formData = new FormData();
        formData.append('filename', filename);
        
        fetch('action_delete.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) { loadDataAndRender(); } 
                else { alert('Error: ' + data.message); }
            });
    };

    // Initial Load
    loadDataAndRender();
});