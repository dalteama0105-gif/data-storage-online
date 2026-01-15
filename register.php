<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Data Storage</title>
    <style>
        /* Global Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            /* Flexbox centers the card */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative; 
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

        /* --- MAIN CARD --- */
        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 320px;
            text-align: center;
        }

        .card h2 { margin-bottom: 20px; color: #333; }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f9f9f9;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #28a745; /* Green for Register */
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            font-weight: 600;
            transition: background 0.3s;
        }
        button:hover { background: #218838; }

        .error { 
            background: #ffe6e6; 
            color: #d93025; 
            padding: 10px; 
            border-radius: 4px; 
            font-size: 13px; 
            margin-bottom: 15px; 
            border: 1px solid #ffcccc;
        }

        /* --- FOOTER (Fixed) --- */
        .bottom-footer {
            position: fixed;
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
    
    <div class="card">
        <h2>Create Account</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="action_register.php" method="post">
            <input type="text" name="username" placeholder="Choose a Username" required>
            <input type="password" name="password" placeholder="Choose a Password" required>
            <button type="submit">Sign Up</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 14px; color:#666;">
            Already have an account? <br>
            <a href="login.php" style="color: #2563eb; font-weight: bold; text-decoration:none;">Log In here</a>
        </p>
    </div>

    <footer class="bottom-footer">
        <p>© 2026 Data Storage Online. All rights reserved.</p>
    </footer>

</body>
</html>