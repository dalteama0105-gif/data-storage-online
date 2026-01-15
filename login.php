<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Data Storage</title>
    <style>
        /* Global Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            /* This centers the login card */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative; /* Important context for absolute elements */
        }

        /* --- HEADER (Fixed) --- */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            /* Changed: We don't strictly need justify-content here anymore */
            padding: 0 40px;
            z-index: 1000;
        }

        /* New Class: Pins the title to the exact center */
        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap; /* Prevents text from breaking if screen is small */
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-center h2 {
            font-size: 20px;
            color: #333;
            margin: 0;
        }

        /* --- LOGIN CARD STYLES --- */
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 320px;
            text-align: center;
            /* No margin needed because body flex centers it */
        }

        .login-card h2 { margin-bottom: 20px; color: #333; }
        
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 13px; color: #666; margin-bottom: 5px; }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .btn-submit:hover { background: #1d4ed8; }
        
        .error-msg { color: red; font-size: 13px; margin-bottom: 10px; }
        .back-link { display: block; margin-top: 15px; font-size: 12px; text-decoration: none; color: #666; }

        /* --- FOOTER STYLES (Fixed to Bottom) --- */
        .bottom-footer {
            position: fixed; /* Takes it out of the flex flow */
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 15px;
            text-align: center;
            background: #e9ecef;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <header class="top-header">
        <div class="header-left">
            <img src="logo.jpg" alt="Logo" class="logo-img" style="height:40px; display:block;">
        </div>

        <div class="header-center">
            <span style="font-weight:bold; font-size:18px; color:#2563eb;">Data Storage Online</span>
        </div>
    </header>
    
    <div class="login-card">
        <h2>System Login</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <p class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <form action="action_login.php" method="post">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-submit">Login</button>
            <div style="margin-top: 15px; font-size: 14px;">
                No account? <a href="register.php" style="color: #2563eb; font-weight: bold;">Sign Up Here</a>
            </div>
        </form>
        
        <a href="index.php" class="back-link">← Back to Dashboard</a>
    </div>
    
    <footer class="bottom-footer">
        <p>© 2026 Data Storage Online. All rights reserved.</p>
    </footer>

</body>
</html>