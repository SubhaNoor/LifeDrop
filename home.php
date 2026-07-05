<?php
session_start();

// সিকিউরিটি চেক: ইউজার লগইন না থাকলে লগইন পেজে পাঠিয়ে দেবে
if (!isset($_SESSION['user_name']) && !isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeDrop - Dashboard</title>
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
        .navbar a, .navbar .welcome-text {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }
        .navbar .logout-btn {
            background-color: white;
            color: #dc2626;
            padding: 5px 12px;
            border-radius: 4px;
            margin-left: 10px;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { color: #1e293b; margin-bottom: 10px; }
        
        .search-box, .donor-form {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        select, input, .btn {
            padding: 12px 20px;
            font-size: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        select, input { width: 220px; }
        .btn {
            background-color: #dc2626;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover { background-color: #b91c1c; }
        .donor-section {
            border-top: 2px dashed #cbd5e1;
            margin-top: 40px;
            padding-top: 30px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">LifeDrop Droplet</div>
        <div>
            <a href="home.php">Dashboard</a>
            <span class="welcome-text">👋 Hello, <?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Find Blood Donors in Real-Time</h1>
        <p style="color: #64748b;">Select blood group and area to find available donors instantly.</p>

        <!-- সার্চ ফর্ম -->
        <form action="donor.php" method="GET" class="search-box">
            <select name="blood_group" required>
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>

            <input type="text" name="area" placeholder="Enter Area (e.g. Uttara)" required>

            <button type="submit" class="btn">Search Donors</button>
        </form>

        <!-- ডোনার রেজিস্ট্রেশন ফর্ম -->
        <div class="donor-section">
            <h2>Register as a Blood Donor</h2>
            <p style="color: #64748b;">Want to save lives? Drop your info below to become a donor.</p>
            
            <form action="add_donor.php" method="POST" class="donor-form">
                <input type="text" name="donor_name" placeholder="Full Name" required>
                <input type="tel" name="donor_phone" placeholder="Phone Number" required>
                <input type="text" name="donor_area" placeholder="Area (e.g. Uttara)" required>
                
                <select name="donor_blood" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
                
                <button type="submit" class="btn" style="width: 100%; max-width: 240px;">Become a Donor</button>
            </form>
        </div>
    </div>

</body>
</html>