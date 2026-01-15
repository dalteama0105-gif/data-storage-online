document.addEventListener('DOMContentLoaded', () => {
    
    // ================= 1. 状态管理 =================
    let tabsData = {
        'tab-1': [] 
    };
    
    let activeTabId = 'tab-1';
    let tabCounter = 1;
    let selectedFileIndex = null; 

    // ================= 2. DOM 元素 =================
    const tabsContainer = document.getElementById('tabs-container');
    const tabAddBtn = document.querySelector('.tab-add');
    const mainContentArea = document.getElementById('main-content-area');
    const fileInput = document.getElementById('fileInput');

    // 菜单相关
    const contextMenu = document.getElementById('context-menu');
    const ctxOpen = document.getElementById('ctx-open');
    const ctxDownload = document.getElementById('ctx-download');
    const ctxDelete = document.getElementById('ctx-delete');

    // 弹窗相关
    const insertBtn = document.getElementById('btn-insert-files');
    const modal = document.getElementById('file-modal');
    const cancelBtn = document.getElementById('btn-modal-cancel');
    const selectBtn = document.getElementById('btn-modal-select');
    const closeX = document.querySelector('.close-modal-x');


    // ================= 3. 核心功能函数 =================

    // --- A. 渲染文件列表 ---
    function renderCurrentFiles() {
        mainContentArea.innerHTML = ''; 
        
        const files = tabsData[activeTabId] || [];
        
        if (files.length === 0) {
            mainContentArea.innerHTML = '<p style="color:#999; text-align:center; margin-top:50px;">Folder is empty. Click "Insert Files" to add content.</p>';
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'main-file-grid';

        files.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'main-file-item';
            
            // 图标判断逻辑
            let icon = '📄';
            let fType = file.type || '';
            if (file.name.endsWith('.mp3') || fType.includes('audio')) icon = '🎵';
            else if (file.name.endsWith('.txt') || fType.includes('text')) icon = '📝';
            else if (fType.includes('image')) icon = '🖼️';
            else if (file.name.endsWith('.pdf') || fType.includes('pdf')) icon = '📕';

            item.innerHTML = `
                <div class="main-file-icon">${icon}</div>
                <div class="main-file-name">${file.name}</div>
            `;

            // 右键菜单事件
            item.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                selectedFileIndex = index;
                contextMenu.style.top = `${e.pageY}px`;
                contextMenu.style.left = `${e.pageX}px`;
                contextMenu.style.display = 'block';
            });

            grid.appendChild(item);
        });

        mainContentArea.appendChild(grid);
    }

    // --- B. 切换标签页 ---
    function switchTab(tabElement) {
        document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
        tabElement.classList.add('active');
        activeTabId = tabElement.dataset.id;
        renderCurrentFiles();
    }

    // --- C. 从服务器加载文件 (关键修复) ---
    function loadServerFiles() {
        console.log("正在从服务器获取文件列表...");
        fetch('action_list_files.php')
            .then(response => response.json())
            .then(files => {
                if (files.length === 0) return;

                // 简单的去重处理（可选）：如果 tab-1 还是空的，就放进去
                // 如果你想每次刷新都覆盖，可以用 tabsData['tab-1'] = [];
                if (tabsData['tab-1'].length === 0) {
                    files.forEach(file => {
                        // 补全类型，方便显示图标
                        if(file.type === 'mp3') file.type = 'audio/mp3';
                        else if(file.type === 'txt') file.type = 'text/plain';
                        else if(['jpg','png','jpeg'].includes(file.type)) file.type = 'image/jpeg';
                        
                        tabsData['tab-1'].push(file);
                    });
                    renderCurrentFiles();
                }
            })
            .catch(err => console.error("Error loading files:", err));
    }


    // ================= 4. 事件监听 =================

    // --- 标签页点击 ---
    tabsContainer.addEventListener('click', (e) => {
        const tabItem = e.target.closest('.tab-item');
        if (e.target.classList.contains('close-tab')) {
            e.stopPropagation();
            if (tabItem) {
                const idToDelete = tabItem.dataset.id;
                delete tabsData[idToDelete];
                tabItem.remove();
                if (activeTabId === idToDelete) {
                    const remainingTabs = document.querySelectorAll('.tab-item');
                    if (remainingTabs.length > 0) switchTab(remainingTabs[remainingTabs.length - 1]);
                    else { mainContentArea.innerHTML = ''; activeTabId = null; }
                }
            }
            return;
        }
        if (tabItem) switchTab(tabItem);
    });

    // --- 添加新标签 ---
    tabAddBtn.addEventListener('click', () => {
        tabCounter++;
        const newId = `tab-${tabCounter}`;
        tabsData[newId] = [];
        const newTab = document.createElement('div');
        newTab.className = 'tab-item';
        newTab.dataset.id = newId;
        newTab.innerHTML = `<span class="tab-name">New Tab</span><span class="close-tab">×</span>`;
        tabsContainer.insertBefore(newTab, tabAddBtn);
        switchTab(newTab);
    });

    // --- 弹窗逻辑 ---
    if(insertBtn) insertBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if(!activeTabId) { alert("Please add a tab first!"); return; }
        modal.classList.add('active');
    });
    const closeModal = () => modal.classList.remove('active');
    if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if(closeX) closeX.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    if(selectBtn) selectBtn.addEventListener('click', () => fileInput.click());


    // --- 核心：文件上传处理 (Fetch API) ---
    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if (files.length > 0 && activeTabId) {
            files.forEach(file => {
                const formData = new FormData();
                formData.append('file', file);

                fetch('action_upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Uploaded:", data.file.name);
                        tabsData[activeTabId].push({
                            name: data.file.name,
                            path: data.file.path,
                            type: data.file.type,
                            isServerFile: true 
                        });
                        renderCurrentFiles();
                    } else {
                        alert("Upload failed: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Upload error.");
                });
            });
            closeModal();
            fileInput.value = '';
        }
    });

    // ================= FOLDER UPLOAD LOGIC =================

    // 1. Get Elements
    const folderInput = document.getElementById('folderInput');
    const btnInsertFolder = document.getElementById('btn-insert-folder');

    // 2. Trigger Hidden Input when clicking "Import Folder"
    if (btnInsertFolder) {
        btnInsertFolder.addEventListener('click', (e) => {
            e.preventDefault();
            if (!activeTabId) { alert("Please add a tab first!"); return; }
            folderInput.click(); // Opens the Folder Selection Dialog
        });
    }

    // 3. Handle the Folder Upload
    if (folderInput) {
        folderInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);

            if (files.length > 0 && activeTabId) {
                console.log(`Uploading ${files.length} files from folder...`);
                
                // Iterate through all files inside the folder
                files.forEach(file => {
                    const formData = new FormData();
                    formData.append('file', file);
                    
                    // CRITICAL: Send the folder path (e.g. "MyFolder/image.png")
                    // This allows PHP to create the subdirectories.
                    formData.append('relativePath', file.webkitRelativePath); 

                    fetch('action_upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log("Uploaded:", data.file.name);
                            // Update UI
                            tabsData[activeTabId].push({
                                name: data.file.name, // You might want to show full path here?
                                path: data.file.path,
                                type: data.file.type,
                                isServerFile: true 
                            });
                            renderCurrentFiles();
                        } else {
                            console.error("Upload failed for " + file.name);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });

                // Clear input
                folderInput.value = '';
            }
        });
    }

    // ================= 5. 右键菜单功能 =================

    document.addEventListener('click', () => {
        contextMenu.style.display = 'none';
    });

    // --- 打开文件 ---
    ctxOpen.addEventListener('click', () => {
        if (selectedFileIndex !== null && activeTabId) {
            const fileData = tabsData[activeTabId][selectedFileIndex];
            
            if (fileData.isServerFile) {
                // 如果是服务器文件，直接打开路径
                window.open(fileData.path, '_blank');
            } else if(fileData.originalFile) {
                // 如果是未上传的本地文件(兼容旧逻辑)
                const fileUrl = URL.createObjectURL(fileData.originalFile);
                window.open(fileUrl, '_blank');
            }
        }
    });

    // --- 下载文件 (修复版) ---
    ctxDownload.addEventListener('click', () => {
        if (selectedFileIndex !== null && activeTabId) {
            const fileData = tabsData[activeTabId][selectedFileIndex];
            let downloadUrl = '';

            // 判断是服务器文件还是本地 Blob
            if (fileData.isServerFile) {
                downloadUrl = fileData.path;
            } else if (fileData.originalFile) {
                downloadUrl = URL.createObjectURL(fileData.originalFile);
            }

            if (downloadUrl) {
                const tempLink = document.createElement('a');
                tempLink.href = downloadUrl;
                // 注意：对于服务器上的跨域文件，download 属性可能不生效，只会打开
                // 但因为我们是 localhost，通常可以直接下载
                tempLink.download = fileData.name; 
                document.body.appendChild(tempLink);
                tempLink.click();
                document.body.removeChild(tempLink);
                
                if(!fileData.isServerFile) URL.revokeObjectURL(downloadUrl);
            }
        }
    });

    // --- 删除文件 (修改版：连接后端) ---
    ctxDelete.addEventListener('click', () => {
        if (selectedFileIndex !== null && activeTabId) {
            const fileData = tabsData[activeTabId][selectedFileIndex];
            
            // 确认一下 (可选，防止误删)
            if(!confirm(`Are you sure you want to delete "${fileData.name}"?`)) return;

            // 1. 准备数据发送给 PHP
            const formData = new FormData();
            formData.append('filename', fileData.name);

            // 2. 发送请求
            fetch('action_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // === 后端删除成功，现在更新前端 ===
                    console.log("File deleted from server");
                    
                    // 从数组中移除
                    tabsData[activeTabId].splice(selectedFileIndex, 1);
                    
                    // 重新渲染界面
                    renderCurrentFiles();
                } else {
                    alert("Delete failed: " + data.message);
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert("Cannot connect to server.");
            });
        }
    });

    // ================= 6. 初始化 =================
    // 页面加载完毕后，立刻尝试去服务器拉取文件
    renderCurrentFiles();
    loadServerFiles();

}); // <--- 整个代码结束的大括号

