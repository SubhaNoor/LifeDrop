<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}
$current_user = $_SESSION['user']; // লগইন থাকা ইউজারের ফোন নম্বর

$view_all = isset($_GET['view']) && $_GET['view'] == 'all';
$blood_group = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';

// বেস কুয়েরি: ৯০ দিনের কুলডাউন এবং ম্যানুয়াল স্ট্যাটাস (status = 1) দুটিই চেক করবে
$base_sql = "SELECT *, 
            (SELECT IFNULL(ROUND(AVG(rating), 1), 'No Rating') FROM donor_ratings WHERE donor_phone = donors.phone) as avg_rating 
            FROM donors 
            WHERE status = 1 
            AND (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 90)";

if ($view_all) {
    $sql = $base_sql;
    $heading_text = "All Available Blood Donors";
} else {
    $sql = $base_sql . " AND blood_group = '$blood_group' AND area LIKE '%$area%'";
    $heading_text = "Search Results for " . htmlspecialchars($blood_group) . " in " . htmlspecialchars($area);
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Donors</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
        .navbar { background-color: #dc2626; padding: 15px 40px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 950px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1e293b; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #cbd5e1; }
        th { background-color: #f8fafc; color: #334155; }
        .no-results { text-align: center; color: #64748b; padding: 20px; font-size: 18px; }
        .call-btn { background-color: #2563eb; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; margin-right: 5px; }
        .req-btn { background-color: #dc2626; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; border: none; cursor: pointer; }
        .disabled-btn { background-color: #94a3b8; color: white; padding: 6px 12px; border-radius: 4px; font-size: 14px; text-decoration: none; cursor: not-allowed; }
        .rating-star { color: #f59e0b; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <div style="font-weight: bold; font-size: 24px;">LifeDrop Droplet</div>
        <a href="home.php">Back to Dashboard</a>
    </div>

    <div class="container">
        <h2><?php echo $heading_text; ?></h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Donor Name</th>
                    <th>Rating</th>
                    <th>Blood Group</th>
                    <th>Area</th>
                    <th>Action</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                  <!-- donor.php এর ভেতরের নামের লাইনটি এমন হবে -->
<td><a href="profile.php?phone=<?php echo $row['phone']; ?>" style="color: #2563eb; font-weight: bold; text-decoration: none;"><?php echo htmlspecialchars($row['name']); ?> 🔗</a></td>
                    <td><span class="rating-star">⭐</span> <?php echo $row['avg_rating']; ?></td>
                    <td style="color: #dc2626; font-weight: bold;"><?php echo htmlspecialchars($row['blood_group']); ?></td>
                    <td><?php echo htmlspecialchars($row['area']); ?></td>
                    <td>
                        <a href="tel:<?php echo $row['phone']; ?>" class="call-btn">📞 Call</a>
                        
                        <?php
                        // নিজের নিজেকে রিকোয়েস্ট পাঠানো বন্ধ করা এবং অলরেডি রিকোয়েস্ট পাঠানো হয়েছে কিনা চেক করা
                        if ($row['phone'] == $current_user) {
                            echo "<span class='disabled-cmd' style='color:#64748b;'>You</span>";
                        } else {
                            $check_req = $conn->query("SELECT status FROM blood_requests WHERE sender_phone='$current_user' AND receiver_phone='{$row['phone']}' AND status='Pending'");
                            if ($check_req->num_rows > 0) {
                                echo "<span class='disabled-btn'>⏳ Requested</span>";
                            } else {
                                echo "<a href='send_request.php?donor_phone={$row['phone']}' class='req-btn'>📢 Request</a>";
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="no-results">❌ No donors found matching your criteria.</div>
        <?php endif; ?>
    </div>

</body>
</html>