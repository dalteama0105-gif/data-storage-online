document.addEventListener('DOMContentLoaded', () => {
    
    // === Track Current Folder Path ===
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
    
    // Filter Elements
    const filterSelect = document.getElementById('filterSelect');
    
    // === NEW: Date Range Elements ===
    const dateStartInput = document.getElementById('dateStart');
    const dateEndInput = document.getElementById('dateEnd');
    
    const searchInput = document.getElementById('file-search');

    if(filterSelect) filterSelect.addEventListener('change', renderTable);
    // Listen to changes on BOTH date inputs
    if(dateStartInput) dateStartInput.addEventListener('change', renderTable);
    if(dateEndInput) dateEndInput.addEventListener('change', renderTable);
    if(searchInput) searchInput.addEventListener('input', renderTable);

    function loadDataAndRender() {
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

        const filterVal = filterSelect ? filterSelect.value : 'all';
        // Get Date Values
        const startVal = dateStartInput ? dateStartInput.value : '';
        const endVal = dateEndInput ? dateEndInput.value : '';
        
        const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';

        const filesToShow = allFiles.filter(file => {
            // 1. Type Check
            let typeMatch = false;
            if (filterVal === 'all') { typeMatch = true; } 
            else if (file.type === 'folder') { typeMatch = false; } 
            else {
                if(filterVal === 'txt' && file.name.toLowerCase().endsWith('.txt')) typeMatch = true;
                else if(filterVal === 'mp3' && file.name.toLowerCase().endsWith('.mp3')) typeMatch = true;
            }

            // 2. Date Range Check (NEW)
            let dateMatch = true;
            // File date comes as "YYYY-MM-DD HH:MM". We take the first 10 chars "YYYY-MM-DD"
            const fileDate = file.date ? file.date.substring(0, 10) : '';

            if (startVal !== '') {
                if (fileDate < startVal) dateMatch = false;
            }
            if (endVal !== '') {
                if (fileDate > endVal) dateMatch = false;
            }

            // 3. Search Check
            let nameMatch = true;
            if (searchVal !== '') {
                nameMatch = file.name.toLowerCase().includes(searchVal);
            }

            return typeMatch && dateMatch && nameMatch;
        });

        // "Go Back" Row
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

        if(filesToShow.length === 0 && currentPath === '') {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No files found matching filters.</td></tr>';
            return;
        }

        filesToShow.forEach((file, index) => {
            const tr = document.createElement('tr');
            
            let typeLabel = 'FILE';
            let iconName = 'document-outline';
            let clickAction = '';
            let actionButtons = '';

           if (file.type === 'folder') {
                typeLabel = 'FOLDER';
                iconName = 'folder-open';
                clickAction = `onclick="enterFolder('${file.name}')" style="cursor:pointer; color:#2563eb; font-weight:bold;"`;
                
                actionButtons = `
                     <a href="action_download_folder.php?folder=${encodeURIComponent(file.relativePath)}" class="icon-btn download" title="Download Folder as Zip">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                     </a>
                     <span class="icon-btn delete" onclick="deleteFile('${file.relativePath}')" title="Delete Folder">
                        <ion-icon name="trash-outline"></ion-icon>
                    </span>
                `;
            }
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
                <td style="white-space:nowrap;">${actionButtons}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // --- Global Functions ---
    window.enterFolder = function(folderName) {
        if (currentPath === '') {
            currentPath = folderName;
        } else {
            currentPath = currentPath + '/' + folderName;
        }
        loadDataAndRender();
    };

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
        
        const formData = new FormData();
        formData.append('filename', filename);
        
        fetch('action_delete.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) { loadDataAndRender(); } 
                else { alert('Error: ' + data.message); }
            });
    };

    // === NEW: Modal Logic for Upload/Create Folder ===
    const modal = document.getElementById('uploadModal');
    const btnOpenModal = document.getElementById('btn-open-modal');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const btnCancelModal = document.getElementById('btn-cancel-modal');
    const btnSaveModal = document.getElementById('btn-save-modal');

    const inputFolder = document.getElementById('newFolderName');
    const inputAudio = document.getElementById('modalAudioInput');
    const inputText = document.getElementById('modalTextInput');
    
    const labelAudio = document.getElementById('audioFileName');
    const labelText = document.getElementById('textFileName');

    // 1. Open Modal
    if(btnOpenModal) {
        btnOpenModal.addEventListener('click', () => {
            modal.classList.add('active');
            // Reset fields
            inputFolder.value = '';
            inputAudio.value = '';
            inputText.value = '';
            labelAudio.textContent = 'Click to select MP3...';
            labelText.textContent = 'Click to select TXT...';
        });
    }

    // 2. Close Modal
    function closeModal() {
        modal.classList.remove('active');
    }
    if(btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
    if(btnCancelModal) btnCancelModal.addEventListener('click', closeModal);

    // 3. Display Selected Filenames
    if(inputAudio) {
        inputAudio.addEventListener('change', (e) => {
            if(e.target.files.length > 0) labelAudio.textContent = e.target.files[0].name;
        });
    }
    if(inputText) {
        inputText.addEventListener('change', (e) => {
            if(e.target.files.length > 0) labelText.textContent = e.target.files[0].name;
        });
    }

    // 4. SAVE Handler
    if(btnSaveModal) {
        btnSaveModal.addEventListener('click', async () => {
            const folderName = inputFolder.value.trim();
            const audioFile = inputAudio.files[0];
            const textFile = inputText.files[0];

            if(!folderName) {
                alert("Please enter a folder name.");
                return;
            }

            // Step A: Create the Folder
            try {
                const fd = new FormData();
                fd.append('folder_name', folderName);
                fd.append('current_path', currentPath);

                let res = await fetch('action_create_folder.php', { method: 'POST', body: fd });
                let json = await res.json();

                if(!json.success) {
                    alert("Error creating folder: " + json.message);
                    return;
                }

                // Step B: Upload files INTO that folder (if selected)
                const filesToUpload = [];
                if(audioFile) filesToUpload.push(audioFile);
                if(textFile) filesToUpload.push(textFile);

                if(filesToUpload.length > 0) {
                    const uploadPromises = filesToUpload.map(file => {
                        const uploadFd = new FormData();
                        uploadFd.append('file', file);
                        
                        // Construct path: CurrentPath / NewFolder / FileName
                        let uploadPath = folderName + '/' + file.name;
                        if(currentPath !== '') {
                            uploadPath = currentPath + '/' + folderName + '/' + file.name;
                        }
                        
                        uploadFd.append('relativePath', uploadPath);
                        return fetch('action_upload.php', { method: 'POST', body: uploadFd });
                    });

                    await Promise.all(uploadPromises);
                }

                // Step C: Success!
                closeModal();
                loadDataAndRender();
                alert("Saved successfully!");

            } catch (err) {
                console.error(err);
                alert("An error occurred. Check console for details.");
            }
        });
    }

    // Initial Load
    loadDataAndRender();
});