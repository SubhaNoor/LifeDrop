<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$current_user = $_SESSION['user'];

// এই ডোনার যাদের রিকোয়েস্ট অ্যাকসেপ্ট করেছে তাদের লিস্ট আনা
$sql = "SELECT r.id, u.name, u.blood_group, u.phone, r.created_at 
        FROM blood_requests r 
        JOIN users u ON r.sender_phone = u.phone 
        WHERE r.receiver_phone = '$current_user' AND r.status = 'Accepted'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donated Blood History</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
        .navbar { background-color: #dc2626; padding: 15px 40px; color: white; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 850px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1e293b; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #cbd5e1; }
        th { background-color: #f8fafc; }
    </style>
</head>
<body>

    <div class="navbar">
        <div style="font-weight: bold; font-size: 24px;">LifeDrop Droplet</div>
        <a href="home.php">Back to Dashboard</a>
    </div>

    <div class="container">
        <h2>My Blood Donation History 💝</h2>
        <p style="color: #64748b; text-align:center;">"The best among you are those who bring benefit to others." Thank you for saving lives!</p>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Recipient Name</th>
                    <th>Blood Group Required</th>
                    <th>Recipient Contact</th>
                    <th>Donation Date</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="color:#dc2626; font-weight:bold;"><?php echo htmlspecialchars($row['blood_group']); ?></td>
                    <td><a href="tel:<?php echo $row['phone']; ?>" style="color:#2563eb; font-weight:bold; text-decoration:none;">📞 Call</a></td>
                    <td><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div style="text-align:center; color:#64748b; margin-top:30px;">❌ You haven't donated blood through any app request yet.</div>
        <?php endif; ?>
    </div>

</body>
</html>