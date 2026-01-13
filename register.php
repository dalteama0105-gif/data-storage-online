<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; font-family: sans-serif; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin-top: 10px;}
        button:hover { background: #218838; }
        .error { color: red; font-size: 13px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Account</h2>
        
        <?php if(isset($_GET['error'])) echo '<p class="error">'.htmlspecialchars($_GET['error']).'</p>'; ?>

        <form action="action_register.php" method="post">
            <input type="text" name="username" placeholder="Choose a Username" required>
            <input type="password" name="password" placeholder="Choose a Password" required>
            <button type="submit">Sign Up</button>
        </form>
        
        <p style="margin-top: 15px; font-size: 14px;">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</body>
</html>