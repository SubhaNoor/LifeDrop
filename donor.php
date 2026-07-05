<?php
include 'db.php';

$blood_group = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';

// ডাটাবেজ থেকে সার্চ করা ডোনারদের খুঁজে বের করা
$sql = "SELECT * FROM donors WHERE blood_group = '$blood_group' AND area LIKE '%$area%'";
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
        .navbar { background-color: #dc2626; padding: 15px 40px; color: white; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; font-weight: bold; }
        .container { max-width: 900px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1e293b; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #cbd5e1; }
        th { background-color: #f8fafc; color: #334155; }
        .no-results { text-align: center; color: #64748b; padding: 20px; font-size: 18px; }
        .call-btn { background-color: #dc2626; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

    <div class="navbar">
        <div style="font-weight: bold; font-size: 24px;">LifeDrop Droplet</div>
        <a href="home.php">Back to Home</a>
    </div>

    <div class="container">
        <h2>Search Results for <?php echo htmlspecialchars($blood_group); ?> in <?php echo htmlspecialchars($area); ?></h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Donor Name</th>
                    <th>Blood Group</th>
                    <th>Area</th>
                    <th>Action</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="color: #dc2626; font-weight: bold;"><?php echo htmlspecialchars($row['blood_group']); ?></td>
                    <td><?php echo htmlspecialchars($row['area']); ?></td>
                    <td><a href="tel:<?php echo $row['phone']; ?>" class="call-btn">📞 Call</a></td>
                </tr>
                <?php endwhile; ?> <!-- এখানে ঠিক করা হয়েছে -->
            </table>
        <?php else: ?>
            <div class="no-results">❌ No donors found matching your criteria.</div>
        <?php endif; ?>
    </div>

</body>
</html>