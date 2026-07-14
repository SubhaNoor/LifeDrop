<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$current_user = $_SESSION['user'];

// এই ডোনার যাদের রিকোয়েস্ট অ্যাকসেপ্ট করেছে তাদের লিস্ট আনা
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
            --success-bg: #E3F5EA;
            --blue: #2563EB;
            --blue-tint: #EAF1FE;
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
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .navbar a:hover { background-color: rgba(255,255,255,0.14); }

        /* ---------- Container ---------- */
        .container {
            max-width: 880px;
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
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 10px;
            letter-spacing: -0.01em;
        }

        .quote {
            color: var(--ink-soft);
            text-align: center;
            font-size: 15px;
            max-width: 480px;
            margin: 0 auto 34px;
            font-style: italic;
            line-height: 1.6;
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
        td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #FCFAF8; }

        .blood-chip {
            display: inline-block;
            font-weight: 800;
            color: var(--crimson);
            background: var(--crimson-tint);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 13px;
        }

        .call-link {
            color: var(--blue);
            font-weight: 700;
            text-decoration: none;
            background-color: var(--blue-tint);
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            transition: background-color 0.15s ease;
        }
        .call-link:hover { background-color: #DCE8FD; }

        .date-cell { color: var(--ink-soft); font-weight: 600; font-size: 13.5px; }

        /* ---------- Empty state ---------- */
        .empty-state {
            text-align: center;
            margin-top: 24px;
            padding: 50px 20px;
            border: 1.5px dashed var(--line);
            border-radius: 16px;
            background-color: #FBF9F6;
        }
        .empty-state .icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--crimson-tint);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
        }
        .empty-state p { color: var(--ink-soft); font-size: 15px; margin: 0 0 20px; }
        .empty-state a {
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
        .empty-state a:hover { background-color: var(--crimson-dark); transform: translateY(-1px); }

        @media (max-width: 640px) {
            .navbar { padding: 14px 18px; }
            .container { margin: 20px auto; padding: 26px; border-radius: 16px; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { padding: 14px 0; border-bottom: 1px solid var(--line); }
            td { padding: 5px 4px; border-bottom: none; display: flex; justify-content: space-between; align-items: center; }
            td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 11.5px;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: var(--ink-soft);
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
        <div class="eyebrow"><span>💝 Donation History</span></div>
        <h2>My Blood Donation History</h2>
        <p class="quote">"The best among you are those who bring benefit to others." Thank you for saving lives!</p>

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
                    <td data-label="Recipient Name"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td data-label="Blood Group"><span class="blood-chip"><?php echo htmlspecialchars($row['blood_group']); ?></span></td>
                    <td data-label="Contact"><a href="tel:<?php echo $row['phone']; ?>" class="call-link">📞 Call</a></td>
                    <td data-label="Donation Date" class="date-cell"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">🩸</div>
                <p>You haven't donated blood through any app request yet.</p>
                <a href="home.php">Find Someone to Help</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>