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

// এই ইউজার যাদের থেকে রক্ত পেয়েছে (Accepted Requests) তাদের লিস্ট আনা
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
            --blue: #2563EB;
            --blue-dark: #1D4ED8;
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
        }
        .navbar a:hover { background-color: rgba(255,255,255,0.14); }

        /* ---------- Container ---------- */
        .container {
            max-width: 900px;
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
            color: var(--blue);
            background: var(--blue-tint);
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

        .subtitle {
            color: var(--ink-soft);
            text-align: center;
            font-size: 15px;
            max-width: 480px;
            margin: 0 auto 34px;
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
        td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14.5px; vertical-align: middle; }
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

        .area-cell { color: var(--ink-soft); font-weight: 600; font-size: 13.5px; }

        /* ---------- Rating form ---------- */
        .rate-form {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .rate-form select {
            padding: 9px 12px;
            border-radius: 8px;
            border: 1.5px solid var(--line);
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            background-color: #FCFBF9;
            color: var(--ink);
        }
        .rate-form select:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px var(--blue-tint);
        }
        .rate-form .btn {
            padding: 9px 16px;
            border-radius: 8px;
            border: none;
            background-color: var(--blue);
            color: white;
            font-weight: 700;
            font-size: 13.5px;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }
        .rate-form .btn:hover { background-color: var(--blue-dark); transform: translateY(-1px); }

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
            background: var(--blue-tint);
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
            td { padding: 6px 4px; border-bottom: none; }
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
            .rate-form { width: 100%; }
            .rate-form select { flex: 1; }
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
        <div class="eyebrow"><span>📩 Received History</span></div>
        <h2>Blood Received History</h2>
        <p class="subtitle">Here are the donors who accepted your requests. Please rate them to help others.</p>

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
                    <td data-label="Donor Name"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td data-label="Blood Group"><span class="blood-chip"><?php echo htmlspecialchars($row['blood_group']); ?></span></td>
                    <td data-label="Area" class="area-cell"><?php echo htmlspecialchars($row['area']); ?></td>
                    <td data-label="Give Rating">
                        <form action="received.php" method="POST" class="rate-form">
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
            <div class="empty-state">
                <div class="icon">📩</div>
                <p>You haven't received blood through any request yet.</p>
                <a href="home.php">Find a Donor</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>