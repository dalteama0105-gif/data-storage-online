document.addEventListener('DOMContentLoaded', () => {
    
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

    navDashboard.addEventListener('click', (e) => { e.preventDefault(); switchView('dashboard'); });
    navFiles.addEventListener('click', (e) => { e.preventDefault(); switchView('files'); });
    if(navSettings) navSettings.addEventListener('click', (e) => { e.preventDefault(); switchView('settings'); });

    // --- File Logic ---
    let allFiles = [];
    const tableBody = document.getElementById('file-table-body');
    const statTotal = document.getElementById('stat-total');
    const pieChart = document.getElementById('type-pie-chart');

    // Upload & Filter Elements
    const btnUpload = document.getElementById('btn-trigger-upload');
    const fileInput = document.getElementById('fileInput');
    const btnFolder = document.getElementById('btn-trigger-folder');
    const folderInput = document.getElementById('folderInput');
    
    // === NEW: FILTER ELEMENT ===
    const filterSelect = document.getElementById('filterSelect');
    if(filterSelect) {
        // When user changes filter, re-render the table
        filterSelect.addEventListener('change', renderTable);
    }

    function loadDataAndRender() {
        fetch('action_list_files.php')
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

        // 1. Get current filter
        const filterVal = filterSelect ? filterSelect.value : 'all';

        // 2. Filter the array
        const filesToShow = allFiles.filter(file => {
            if(filterVal === 'all') return true;
            if(filterVal === 'txt' && file.name.toLowerCase().endsWith('.txt')) return true;
            if(filterVal === 'mp3' && file.name.toLowerCase().endsWith('.mp3')) return true;
            return false;
        });

        if(filesToShow.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No files found matching current filter.</td></tr>';
            return;
        }

        // 3. Render filtered files
        filesToShow.forEach((file, index) => {
            const tr = document.createElement('tr');
            
            let typeLabel = 'FILE';
            if(file.name.endsWith('.mp3')) typeLabel = 'MP3';
            if(file.name.endsWith('.txt')) typeLabel = 'TXT';

            tr.innerHTML = `
                <td><input type="checkbox"></td>
                <td>${index + 1}</td>
                <td>${file.name}</td>
                <td>${file.date || '-'}</td>
                <td><span class="badge">${typeLabel}</span></td>
                <td style="white-space:nowrap;">
                    <span class="icon-btn open" onclick="window.open('${file.path}', '_blank')" title="Open">
                        <ion-icon name="eye-outline"></ion-icon>
                    </span>
                    <a href="${file.path}" download="${file.name}" class="icon-btn download" title="Download">
                        <ion-icon name="cloud-download-outline"></ion-icon>
                    </a>
                    <span class="icon-btn delete" onclick="deleteFile('${file.name}')" title="Delete">
                        <ion-icon name="trash-outline"></ion-icon>
                    </span>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // --- Unified Upload Handler ---
    function handleUpload(fileList) {
        const files = Array.from(fileList);
        if(files.length === 0) return;

        let processed = 0;
        files.forEach(file => {
            const formData = new FormData();
            formData.append('file', file);
            if(file.webkitRelativePath) {
                formData.append('relativePath', file.webkitRelativePath);
            }

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

    loadDataAndRender();
});