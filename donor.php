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

// বেস কুয়েরি: ৯০ দিনের কুলডাউন এবং ম্যানুয়াল স্ট্যাটাস (status = 1) দুটিই চেক করবে
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #F7F5F2;
            --ink: #17140F;
            --ink-soft: #57524A;
            --crimson: #C4123D;
            --crimson-dark: #8F0C2C;
            --crimson-tint: #FCE7EB;
            --card: #FFFFFF;
            --line: #E9E3DB;
            --success: #1F7A4D;
            --blue: #2563EB;
            --blue-tint: #EAF1FE;
            --warn-bg: #F1EEE9;
            --amber: #B45309;
            --amber-tint: #FEF3E2;
            --shadow: 0 10px 30px -12px rgba(23, 20, 15, 0.18);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            background-image:
                radial-gradient(circle at 8% 0%, rgba(196, 18, 61, 0.06), transparent 40%),
                radial-gradient(circle at 100% 10%, rgba(196, 18, 61, 0.05), transparent 35%);
            margin: 0;
            padding: 0;
            color: var(--ink);
            line-height: 1.5;
        }

        /* ---------- Navbar ---------- */
        .navbar {
            background: linear-gradient(120deg, var(--crimson-dark), var(--crimson));
            padding: 16px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 500;
            box-shadow: 0 6px 20px -8px rgba(143, 12, 44, 0.5);
        }
        .navbar .logo {
            font-family: 'Sora', sans-serif;
            font-weight: 800;
            font-size: 21px;
            display: flex;
            align-items: center;
            gap: 9px;
            letter-spacing: -0.01em;
        }
        .navbar a {
            color: rgba(255,255,255,0.92);
            text-decoration: none;
            font-weight: 600;
            font-size: 14.5px;
            padding: 9px 14px;
            border-radius: 8px;
            transition: background-color 0.15s ease;
        }
        .navbar a:hover { background-color: rgba(255,255,255,0.14); }

        /* ---------- Container ---------- */
        .container {
            max-width: 980px;
            margin: 48px auto;
            background: var(--card);
            padding: 44px;
            border-radius: 22px;
            box-shadow: var(--shadow);
            border: 1px solid var(--line);
        }

        .eyebrow {
            display: flex;
            justify-content: center;
        }
        .eyebrow span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--crimson);
            background: var(--crimson-tint);
            padding: 6px 14px;
            border-radius: 999px;
            margin-bottom: 16px;
        }

        h2 {
            font-family: 'Sora', sans-serif;
            color: var(--ink);
            text-align: center;
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 30px;
            letter-spacing: -0.01em;
        }

        /* ---------- Table ---------- */
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th {
            padding: 12px;
            text-align: left;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ink-soft);
            background-color: #FBF9F6;
            border-bottom: 2px solid var(--line);
        }
        th:first-child { border-top-left-radius: 10px; }
        th:last-child { border-top-right-radius: 10px; }
        td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14.5px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #FCFAF8; }

        .donor-name-link {
            color: var(--ink);
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .donor-name-link:hover { color: var(--crimson); }

        .blood-chip {
            display: inline-block;
            font-weight: 800;
            color: var(--crimson);
            background: var(--crimson-tint);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 13px;
        }

        .area-cell { color: var(--ink-soft); }

        .rating-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--amber-tint);
            color: var(--amber);
            font-weight: 700;
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .action-cell { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .call-btn {
            background-color: var(--blue-tint);
            color: var(--blue);
            padding: 7px 13px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            transition: background-color 0.15s ease;
        }
        .call-btn:hover { background-color: #DCE8FD; }

        .req-btn {
            background-color: var(--crimson);
            color: white;
            padding: 7px 13px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }
        .req-btn:hover { background-color: var(--crimson-dark); transform: translateY(-1px); }

        .disabled-btn {
            background-color: var(--warn-bg);
            color: var(--ink-soft);
            padding: 7px 13px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: not-allowed;
            display: inline-block;
        }
        .self-tag { color: var(--ink-soft); font-size: 13px; font-weight: 600; padding: 7px 4px; }

        /* ---------- Empty state ---------- */
        .no-results {
            text-align: center;
            margin-top: 10px;
            padding: 50px 20px;
            border: 1.5px dashed var(--line);
            border-radius: 16px;
            background-color: #FBF9F6;
        }
        .no-results .icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--crimson-tint);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
        }
        .no-results p { color: var(--ink-soft); font-size: 15px; margin: 0 0 20px; }
        .no-results a {
            display: inline-block;
            background-color: var(--crimson);
            color: white;
            text-decoration: none;
            font-weight: 700;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }
        .no-results a:hover { background-color: var(--crimson-dark); transform: translateY(-1px); }

        @media (max-width: 640px) {
            .navbar { padding: 14px 18px; }
            .container { margin: 20px auto; padding: 26px; border-radius: 16px; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { padding: 14px 0; border-bottom: 1px solid var(--line); }
            td { padding: 5px 4px; border-bottom: none; }
            td::before {
                content: attr(data-label);
                display: block;
                font-weight: 700;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: var(--ink-soft);
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg>
            LifeDrop Droplet
        </div>
        <a href="home.php">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="eyebrow"><span>🔍 Donor Search</span></div>
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
                    <td data-label="Donor Name">
                        <a href="profile.php?phone=<?php echo $row['phone']; ?>" class="donor-name-link"><?php echo htmlspecialchars($row['name']); ?> 🔗</a>
                    </td>
                    <td data-label="Rating"><span class="rating-pill">⭐ <?php echo $row['avg_rating']; ?></span></td>
                    <td data-label="Blood Group"><span class="blood-chip"><?php echo htmlspecialchars($row['blood_group']); ?></span></td>
                    <td data-label="Area" class="area-cell"><?php echo htmlspecialchars($row['area']); ?></td>
                    <td data-label="Action" class="action-cell">
                        <a href="tel:<?php echo $row['phone']; ?>" class="call-btn">📞 Call</a>

                        <?php
                        // নিজের নিজেকে রিকোয়েস্ট পাঠানো বন্ধ করা এবং অলরেডি রিকোয়েস্ট পাঠানো হয়েছে কিনা চেক করা
                        if ($row['phone'] == $current_user) {
                            echo "<span class='self-tag'>You</span>";
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
            <div class="no-results">
                <div class="icon">🩸</div>
                <p>No donors found matching your criteria.</p>
                <a href="home.php">Try a Different Search</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>