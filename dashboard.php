<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            background-color: #f5f5f7;
        }
        
        .sidebar {
            width: 60px;
            height: 100vh;
            background-color: #1a1a1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
        }
        
        .sidebar-icon {
            width: 28px;
            height: 28px;
            margin-bottom: 30px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-content {
            margin-left: 60px;
            width: calc(100% - 60px);
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
        }
        
        .search-bar {
            flex-grow: 1;
            margin: 0 20px;
            max-width: 400px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 20px;
            border: none;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .start-meeting-btn {
            background-color: #1a1a1a;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .start-meeting-btn svg {
            margin-left: 8px;
        }
        
        .cards-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .card-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-label {
            display: block;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .input-field {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .input-field input {
            border: none;
            width: 100%;
            outline: none;
        }
        
        .copy-btn, .refresh-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #1a1a1a;
            color: white;
        }
        
        .btn-secondary {
            background-color: #f5f5f7;
            color: #1a1a1a;
        }
        
        .storage-bar {
            height: 8px;
            width: 100%;
            background-color: #eee;
            border-radius: 4px;
            margin: 15px 0;
            display: flex;
        }
        
        .storage-segment {
            height: 100%;
            border-radius: 4px;
        }
        
        .storage-segment-1 {
            background-color: #a29bfe;
            width: 35%;
        }
        
        .storage-segment-2 {
            background-color: #81ecec;
            width: 25%;
        }
        
        .storage-segment-3 {
            background-color: #ffeaa7;
            width: 15%;
        }
        
        .storage-segment-4 {
            background-color: #74b9ff;
            width: 25%;
        }
        
        .storage-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .storage-used {
            font-size: 20px;
            font-weight: bold;
        }
        
        .storage-total {
            font-size: 20px;
            color: #666;
        }
        
        .storage-percentage {
            font-size: 16px;
            color: #666;
        }
        
        .file-categories {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        
        .file-category {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            display: flex;
        }
        
        .category-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .documents-icon {
            background-color: #e9e3ff;
            color: #6c5ce7;
        }
        
        .videos-icon {
            background-color: #e3f9ff;
            color: #00cec9;
        }
        
        .images-icon {
            background-color: #fff5e3;
            color: #fdcb6e;
        }
        
        .music-icon {
            background-color: #e3e9ff;
            color: #74b9ff;
        }
        
        .category-info {
            display: flex;
            flex-direction: column;
        }
        
        .category-name {
            font-size: 14px;
            font-weight: 500;
        }
        
        .category-files {
            font-size: 12px;
            color: #666;
        }
        
        .category-size {
            font-size: 16px;
            font-weight: bold;
            margin-left: auto;
        }
        
        .promo-card {
            display: flex;
            align-items: center;
        }
        
        .promo-content {
            flex: 1;
        }
        
        .promo-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .promo-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .promo-image {
            width: 150px;
            height: 150px;
            object-fit: contain;
        }
        
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php
    // Generate random ID and password for demo
    $user_id = rand(100, 999) . ' ' . rand(100, 999) . ' ' . rand(100, 999);
    $password = bin2hex(random_bytes(5));
    
    // Storage data
    $used_storage = 87;
    $total_storage = 512;
    $percentage = round(($used_storage / $total_storage) * 100);
    
    // File categories
    $categories = [
        [
            'name' => 'Documents',
            'files' => 1238,
            'size' => 38,
            'icon' => 'document'
        ],
        [
            'name' => 'Videos',
            'files' => 129,
            'size' => 32,
            'icon' => 'video'
        ],
        [
            'name' => 'Images',
            'files' => 567,
            'size' => 17,
            'icon' => 'image'
        ],
        [
            'name' => 'Music',
            'files' => 258,
            'size' => 13,
            'icon' => 'music'
        ]
    ];
    ?>

    <div class="sidebar">
        <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
        </div>
        <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
        <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
        </div>
        <div style="flex-grow: 1;"></div>
        <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </div>
        <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="title">Home</div>
            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Search">
            </div>
            <button class="start-meeting-btn">
                Start Meeting
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
            </button>
            <div class="avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
        </div>

        <div class="cards-container">
            <div class="card">
                <div class="card-title">Remote control</div>
                <div class="card-subtitle">To share access with your device to someone.</div>
                
                <div class="input-group">
                    <label class="input-label">YOUR ID</label>
                    <div class="input-field">
                        <input type="text" value="<?php echo $user_id; ?>" readonly>
                        <button class="copy-btn" onclick="copyToClipboard('<?php echo $user_id; ?>')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="input-group">
                    <label class="input-label">PASSWORD</label>
                    <div class="input-field">
                        <input type="text" value="<?php echo $password; ?>" readonly>
                        <button class="refresh-btn" onclick="refreshPassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">Take the control</div>
                <div class="card-subtitle">Of someone's device remotely.</div>
                
                <div class="input-group">
                    <label class="input-label">PARTNER ID</label>
                    <div class="input-field">
                        <input type="text" placeholder="Enter code">
                    </div>
                </div>
                
                <div class="button-group">
                    <button class="btn btn-primary">Connect</button>
                    <button class="btn btn-secondary">Browse Files</button>
                </div>
                
                <div class="button-group">
                    <button class="btn btn-secondary">Submit</button>
                </div>
            </div>

            <div class="card">
                <div class="card-title">Storage capacity</div>
                
                <div class="storage-info">
                    <div>
                        <span class="storage-used"><?php echo $used_storage; ?></span>
                        <span class="storage-total"> / <?php echo $total_storage; ?> Gb</span>
                    </div>
                    <div class="storage-percentage"><?php echo $percentage; ?> %</div>
                </div>
                
                <div class="storage-bar">
                    <div class="storage-segment storage-segment-1"></div>
                    <div class="storage-segment storage-segment-2"></div>
                    <div class="storage-segment storage-segment-3"></div>
                    <div class="storage-segment storage-segment-4"></div>
                </div>
                
                <div class="file-categories">
                    <?php foreach ($categories as $category): ?>
                    <div class="file-category">
                        <div class="category-icon <?php echo strtolower($category['name']); ?>-icon">
                            <?php if ($category['icon'] == 'document'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            <?php elseif ($category['icon'] == 'video'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                            <?php elseif ($category['icon'] == 'image'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <?php elseif ($category['icon'] == 'music'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <div class="category-name"><?php echo $category['name']; ?></div>
                            <div class="category-files"><?php echo $category['files']; ?> files</div>
                        </div>
                        <div class="category-size"><?php echo $category['size']; ?> Gb</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: right; margin-top: 15px;">
                    <button class="btn btn-secondary">Send Files</button>
                </div>
            </div>

            <div class="card promo-card">
                <div class="promo-content">
                    <div class="promo-title">Mota in full throttle!</div>
                    <div class="promo-text">
                        Receive our special -25% offer<br>
                        and use all our features<br>
                        without any restrictions.
                    </div>
                    <button class="btn btn-primary">Upgrade now</button>
                </div>
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-KdIasgEAJTKk4WJPTCwgNtbWm7F0NV.png" alt="Promo illustration" class="promo-image">
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Copied to clipboard!');
        }
        
        function refreshPassword() {
            // In a real app, this would make an AJAX call to generate a new password
            // For demo purposes, we'll just reload the page
            location.reload();
        }
    </script>
</body>
</html>