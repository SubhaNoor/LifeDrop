<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$current_user = $_SESSION['user'];

// রেটিং সাবমিট হ্যান্ডেল করা
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $donor_phone = $_POST['donor_phone'];
    $rating = $_POST['rating'];
    
    $conn->query("INSERT INTO donor_ratings (donor_phone, rating) VALUES ('$donor_phone', '$rating')");
    echo "<script>alert('Rating submitted successfully!'); window.location.href='received.php';</script>";
    exit();
}

// এই ইউজার যাদের থেকে রক্ত পেয়েছে (Accepted Requests) তাদের লিস্ট আনা
$sql = "SELECT r.id, d.name, d.blood_group, d.phone, d.area 
        FROM blood_requests r 
        JOIN donors d ON r.receiver_phone = d.phone 
        WHERE r.sender_phone = '$current_user' AND r.status = 'Accepted'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Blood History</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
        .navbar { background-color: #dc2626; padding: 15px 40px; color: white; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 850px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1e293b; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #cbd5e1; }
        th { background-color: #f8fafc; }
        select, .btn { padding: 5px 10px; border-radius: 4px; border: 1px solid #cbd5e1; }
        .btn { background-color: #2563eb; color: white; border: none; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div style="font-weight: bold; font-size: 24px;">LifeDrop Droplet</div>
        <a href="home.php">Back to Dashboard</a>
    </div>

    <div class="container">
        <h2>Blood Received History</h2>
        <p style="color: #64748b; text-align:center;">Here are the donors who accepted your requests. Please rate them to help others.</p>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Donor Name</th>
                    <th>Blood Group</th>
                    <th>Area</th>
                    <th>Give Rating</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="color:#dc2626; font-weight:bold;"><?php echo htmlspecialchars($row['blood_group']); ?></td>
                    <td><?php echo htmlspecialchars($row['area']); ?></td>
                    <td>
                        <form action="received.php" method="POST" style="display:inline-flex; gap:5px;">
                            <input type="hidden" name="donor_phone" value="<?php echo $row['phone']; ?>">
                            <select name="rating" required>
                                <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                <option value="4">⭐⭐⭐⭐ (4)</option>
                                <option value="3">⭐⭐⭐ (3)</option>
                                <option value="2">⭐⭐ (2)</option>
                                <option value="1">⭐ (1)</option>
                            </select>
                            <button type="submit" name="submit_rating" class="btn">Submit</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div style="text-align:center; color:#64748b; margin-top:30px;">❌ You haven't received blood through any request yet.</div>
        <?php endif; ?>
    </div>

</body>
</html>