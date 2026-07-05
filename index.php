<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeDrop - Emergency Blood Network</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #dc2626; 
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }
        .landing-hero {
            padding: 100px 20px;
            text-align: center;
        }
        .landing-hero h1 {
            font-size: 42px;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .landing-hero p {
            font-size: 18px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 40px auto;
            line-height: 1.6;
        }
        .cta-buttons a {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            margin: 0 10px;
            transition: 0.2s;
        }
        .btn-login {
            background-color: #dc2626;
            color: white;
        }
        .btn-login:hover { background-color: #b91c1c; }
        .btn-register {
            background-color: white;
            color: #dc2626;
            border: 2px solid #dc2626;
        }
        .btn-register:hover { background-color: #fef2f2; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">LifeDrop Droplet</div>
        <div>
            <a href="index.php">Home</a>
            <a href="login.html">Login</a>
            <a href="register.html">Register</a>
        </div>
    </div>

    <div class="landing-hero">
        <h1>Welcome to LifeDrop</h1>
        <p>Every droplet counts. Join our real-time emergency blood and platelet coordination network. Log in now to find available blood donors near your area instantly, or register yourself to save lives.</p>
        
        <div class="cta-buttons">
            <a href="login.html" class="btn-login">Log In to Find Blood</a>
            <a href="register.html" class="btn-register">Register as a Member</a>
        </div>
    </div>

</body>
</html>