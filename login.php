<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Data Storage</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f0f2f5;
            font-family: sans-serif;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 320px;
            text-align: center;
        }
        .login-card h2 { margin-bottom: 20px; color: #333; }
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 13px; color: #666; margin-bottom: 5px; }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box; /* 关键：防止输入框撑破容器 */
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
        }
        .btn-submit:hover { background: #1d4ed8; }
        .error-msg { color: red; font-size: 13px; margin-bottom: 10px; }
        .back-link { display: block; margin-top: 15px; font-size: 12px; text-decoration: none; color: #666; }
    </style>
</head>
<body>

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

</body>
</html>