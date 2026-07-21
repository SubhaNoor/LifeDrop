<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_name']) && !isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$user_phone = $_SESSION['user'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user'];

// ডোনারের বর্তমান ম্যানুয়াল স্ট্যাটাস ও লকআউট তথ্য চেক করা
$donor_query = $conn->query("SELECT status, last_donation_date FROM donors WHERE phone='$user_phone'");
$is_donor = $donor_query->num_rows > 0;
$donor_status = 1;
$is_locked = false;

if ($is_donor) {
    $donor_data = $donor_query->fetch_assoc();
    $donor_status = $donor_data['status'];

    if (!empty($donor_data['last_donation_date'])) {
        $days = (strtotime(date('Y-m-d')) - strtotime($donor_data['last_donation_date'])) / (60 * 60 * 24);
        if ($days < 90) {
            $is_locked = true;
        }
    }
}

// ডোনারের নিজের স্ট্যাটাস নিজে টগল করার প্রসেস
if (isset($_GET['toggle_status']) && $is_donor && !$is_locked) {
    $new_status = ($donor_status == 1) ? 0 : 1;
    $conn->query("UPDATE donors SET status=$new_status WHERE phone='$user_phone'");
    header("Location: home.php");
    exit();
}

// ইনবাউন্ড রিকোয়েস্ট চেক করা (অন্যরা যে রিকোয়েস্ট পাঠিয়েছে)
$incoming_requests = $conn->query("SELECT r.id, u.name, u.phone, u.blood_group, r.status FROM blood_requests r JOIN users u ON r.sender_phone = u.phone WHERE r.receiver_phone='$user_phone' AND r.status='Pending'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeDrop - Dashboard</title>
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
            --warn-bg: #FDECEC;
            --warn-text: #9A1B1B;
            --shadow: 0 10px 30px -12px rgba(23, 20, 15, 0.18);
            --radius: 16px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FAF8F4;
            margin: 0;
            padding: 0;
            color: var(--ink);
            line-height: 1.5;
            position: relative;
            overflow-x: hidden;
        }

        /* ---------- Signature background: oversized blood-type typography ---------- */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
        }
        .type-mark {
            position: absolute;
            font-family: 'Sora', sans-serif;
            font-weight: 800;
            color: var(--crimson);
            opacity: 0.055;
            line-height: 1;
            user-select: none;
            white-space: nowrap;
        }
        .tm1 { font-size: 340px; top: -90px;  left: -60px;  transform: rotate(-8deg); }
        .tm2 { font-size: 260px; bottom: -80px; right: -50px; transform: rotate(6deg); color: var(--success); opacity: 0.05; }
        .tm3 { font-size: 170px; top: 46%;   right: 3%;    transform: rotate(-5deg); }
        .tm4 { font-size: 130px; bottom: 8%; left: 4%;     transform: rotate(4deg); opacity: 0.045; }

        .bg-drop {
            position: absolute;
            opacity: 0.12;
            animation: bg-float 8s ease-in-out infinite;
        }
        .bg-drop svg { width: 100%; height: 100%; display: block; }
        .bg-drop.b1 { top: 14%; left: 7%;  width: 26px; animation-delay: 0s; }
        .bg-drop.b2 { bottom: 16%; right: 10%; width: 30px; animation-delay: 1.4s; }

        @keyframes bg-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-14px) rotate(4deg); }
        }

        /* ---------- Vitals strip: hospital-monitor ticker under the navbar ---------- */
        .vitals-strip {
            background: var(--ink);
            color: rgba(255,255,255,0.85);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 7px 40px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.04em;
            position: sticky;
            top: 61px;
            z-index: 400;
            overflow: hidden;
        }
        .vitals-strip .live-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
            text-transform: uppercase;
            color: #FF7A93;
        }
        .vitals-strip .live-tag .dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #FF5C7A;
            box-shadow: 0 0 0 0 rgba(255,92,122,0.6);
            animation: pulse-dot 1.8s infinite;
        }
        .vitals-strip .ekg {
            flex-shrink: 0;
            height: 20px;
            width: 140px;
            overflow: hidden;
        }
        .vitals-strip .ekg svg {
            height: 20px;
            width: 280px;
            animation: ekg-scroll 2.4s linear infinite;
        }
        @keyframes ekg-scroll {
            from { transform: translateX(0); }
            to   { transform: translateX(-140px); }
        }
        .vitals-strip .ticker-text {
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            font-size: 11.5px;
        }
        @media (max-width: 640px) {
            .vitals-strip { padding: 7px 18px; top: 0; }
            .vitals-strip .ticker-text { display: none; }
        }
        h1, h2 {
            font-family: 'Sora', sans-serif;
            color: var(--ink);
            letter-spacing: -0.01em;
        }

        /* ---------- Navbar ---------- */
        .navbar {
            background: linear-gradient(120deg, var(--crimson-dark), var(--crimson));
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: sticky;
            top: 0;
            z-index: 500;
            box-shadow: 0 6px 20px -8px rgba(143, 12, 44, 0.5);
        }
        .navbar .logo {
            font-family: 'Sora', sans-serif;
            font-size: 21px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 9px;
            letter-spacing: -0.01em;
        }
        .navbar .logo svg { flex-shrink: 0; }
        .navbar nav { display: flex; align-items: center; gap: 4px; }
        .navbar a, .navbar .welcome-text {
            color: rgba(255,255,255,0.92);
            text-decoration: none;
            font-weight: 600;
            font-size: 14.5px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.15s ease;
        }
        .navbar a:hover { background-color: rgba(255,255,255,0.14); }
        .navbar .logout-btn {
            background-color: white;
            color: var(--crimson-dark);
            padding: 8px 16px;
            border-radius: 8px;
            margin-left: 4px;
        }
        .navbar .logout-btn:hover { background-color: #FDECEC; }

        /* ---------- Layout ---------- */
        .container {
            max-width: 880px;
            margin: 48px auto;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 44px;
            border-radius: 22px;
            box-shadow: var(--shadow);
            text-align: center;
            border: 1px solid var(--line);
            position: relative;
            z-index: 1;
        }

        .eyebrow {
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
            margin-bottom: 18px;
        }

        h1 { font-size: 30px; font-weight: 800; margin: 0 0 8px; position: relative; z-index: 1; }
        .subtitle { color: var(--ink-soft); font-size: 15.5px; margin: 0 auto 22px; max-width: 520px; }

        .hero-pulse {
            width: 100%;
            max-width: 320px;
            margin: 0 auto 8px;
            opacity: 0.55;
        }
        .hero-pulse .pulse-line {
            stroke: var(--crimson);
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 380;
            stroke-dashoffset: 380;
            animation: draw-hero-pulse 2.4s ease-out forwards infinite;
        }
        @keyframes draw-hero-pulse {
            0%   { stroke-dashoffset: 380; }
            55%  { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: 0; }
        }

        /* ---------- Status Panel ---------- */
        .status-panel {
            background: linear-gradient(135deg, #FFFDFB, #FBF3F4);
            padding: 18px 22px;
            border-radius: var(--radius);
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--line);
            text-align: left;
        }
        .status-panel .label {
            font-weight: 700;
            color: var(--ink-soft);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: block;
            margin-bottom: 6px;
        }
        .status-badge {
            padding: 7px 14px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .status-dot {
            width: 8px; height: 8px; border-radius: 50%; display: inline-block;
        }
        .status-on { background-color: var(--success-bg); color: var(--success); }
        .status-on .status-dot { background-color: var(--success); box-shadow: 0 0 0 0 rgba(31,122,77,0.5); animation: pulse-dot 1.8s infinite; }
        .status-off { background-color: var(--warn-bg); color: var(--warn-text); }
        .status-off .status-dot { background-color: var(--warn-text); }
        .status-locked { background-color: #F1EEE9; color: var(--ink-soft); }

        @keyframes pulse-dot {
            0% { box-shadow: 0 0 0 0 rgba(31,122,77,0.45); }
            70% { box-shadow: 0 0 0 7px rgba(31,122,77,0); }
            100% { box-shadow: 0 0 0 0 rgba(31,122,77,0); }
        }

        .status-btn {
            background-color: var(--ink);
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            transition: background-color 0.15s ease, transform 0.1s ease;
            display: inline-block;
        }
        .status-btn:hover { background-color: #000; transform: translateY(-1px); }
        .status-panel small { color: var(--ink-soft); font-size: 12.5px; }

        /* ---------- Search box ---------- */
        .search-box {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 8px;
            flex-wrap: wrap;
        }
        select, input, .btn {
            padding: 13px 16px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            background-color: #FCFBF9;
        }
        select, input { width: 220px; }
        select:focus, input:focus, .btn:focus-visible {
            outline: none;
            border-color: var(--crimson);
            box-shadow: 0 0 0 3px var(--crimson-tint);
        }

        .btn {
            background-color: var(--crimson);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }
        .btn:hover { background-color: var(--crimson-dark); transform: translateY(-1px); }

        .btn-all-donors {
            background-color: var(--ink);
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 10px;
            font-weight: 700;
            padding: 13px 18px;
            font-size: 15px;
            transition: background-color 0.15s ease, transform 0.1s ease;
        }
        .btn-all-donors:hover { background-color: #000; transform: translateY(-1px); }

        /* ---------- Sections ---------- */
        .donor-section, .request-section {
            border-top: 1px dashed var(--line);
            margin-top: 44px;
            padding-top: 34px;
        }

        .request-section h2, .donor-section h2 {
            font-size: 21px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 18px; text-align: left; }
        th {
            padding: 10px 12px;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ink-soft);
            border-bottom: 2px solid var(--line);
        }
        td { padding: 14px 12px; border-bottom: 1px solid var(--line); font-size: 14.5px; }
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

        .acc-btn, .rej-btn {
            display: inline-block;
            padding: 7px 13px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            margin-right: 6px;
            transition: opacity 0.15s ease;
        }
        .acc-btn { background-color: var(--success); color: white; }
        .rej-btn { background-color: var(--warn-text); color: white; }
        .acc-btn:hover, .rej-btn:hover { opacity: 0.85; }

        /* ---------- Become a donor CTA ---------- */
        .donor-section p.cta-text { color: var(--ink-soft); font-size: 16.5px; margin-bottom: 22px; }
        .donor-badge-done {
            color: var(--success);
            font-weight: 700;
            background-color: var(--success-bg);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 999px;
        }
        #openModalBtn { padding: 15px 30px; font-size: 16px; border-radius: 12px; }

        /* ---------- Modal ---------- */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            inset: 0;
            background-color: rgba(23, 20, 15, 0.55);
            backdrop-filter: blur(3px);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content {
            background-color: white;
            padding: 34px;
            border-radius: 20px;
            width: 100%;
            max-width: 540px;
            max-height: 88vh;
            overflow-y: auto;
            box-sizing: border-box;
            box-shadow: 0 25px 60px -15px rgba(0,0,0,0.35);
            position: relative;
            text-align: left;
            animation: modal-in 0.2s ease;
        }
        @keyframes modal-in {
            from { opacity: 0; transform: translateY(10px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .close-btn {
            position: absolute;
            right: 18px;
            top: 14px;
            font-size: 24px;
            font-weight: bold;
            color: var(--ink-soft);
            cursor: pointer;
            line-height: 1;
            width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            transition: background-color 0.15s ease;
        }
        .close-btn:hover { background-color: #F1EEE9; color: var(--ink); }

        .modal-content h2 {
            margin-top: 0;
            text-align: left;
            color: var(--crimson-dark);
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-content > p { color: var(--ink-soft); margin-bottom: 22px; font-size: 14.5px; }

        .modal-form { display: flex; flex-direction: column; gap: 15px; margin-top: 4px; }
        .modal-form label {
            font-weight: 700;
            color: var(--ink-soft);
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: -8px;
        }
        .modal-form input, .modal-form select, .modal-form textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--line);
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            background-color: #FCFBF9;
        }
        .modal-form input:focus, .modal-form select:focus, .modal-form textarea:focus {
            outline: none;
            border-color: var(--crimson);
            box-shadow: 0 0 0 3px var(--crimson-tint);
        }
        .modal-form textarea { font-family: 'Inter', sans-serif; resize: vertical; }
        .modal-form button[type="submit"] {
            width: 100%;
            margin-top: 8px;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 15.5px;
        }

        @media (max-width: 640px) {
            .navbar { padding: 14px 18px; flex-wrap: wrap; gap: 10px; }
            .container { margin: 20px auto; padding: 26px; border-radius: 16px; }
            .status-panel { flex-direction: column; align-items: flex-start; gap: 14px; }
            .search-box select, .search-box input { width: 100%; }
            table, thead, tbody, th, td, tr { display: block; }
            th { display: none; }
            td { padding: 6px 4px; border-bottom: none; }
            tr { padding: 12px 0; border-bottom: 1px solid var(--line); }
        }
    </style>
</head>
<body>

    <div class="bg-scene">
        <div class="type-mark tm1">O+</div>
        <div class="type-mark tm2">A-</div>
        <div class="type-mark tm3">B+</div>
        <div class="type-mark tm4">AB+</div>
        <div class="bg-drop b1"><svg viewBox="0 0 24 24" fill="#C4123D"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
        <div class="bg-drop b2"><svg viewBox="0 0 24 24" fill="#C4123D"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
    </div>

    <div class="navbar">
        <div class="logo">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="white"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg>
            LifeDrop Droplet
        </div>
        <nav>
            <a href="home.php">Dashboard</a>
            <a href="donated.php">💝 Donated</a>
            <a href="received.php">📩 Received</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" style="background-color: rgba(255,255,255,0.18);">🛡️ Admin Panel</a>
            <?php endif; ?>
            <a href="profile.php?phone=<?php echo $user_phone; ?>">👋 Hello, <?php echo htmlspecialchars($user_name); ?></a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </div>

    <div class="vitals-strip">
        <span class="live-tag"><span class="dot"></span>Live</span>
        <span class="ekg">
            <svg viewBox="0 0 280 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 10 H45 L52 10 L58 2 L66 18 L73 10 L80 10 L86 5 L92 15 L98 10 H140 L145 10 L151 2 L159 18 L166 10 L173 10 L179 5 L185 15 H140 L185 15 H228 L233 10 L239 2 L247 18 L254 10 L261 10 L267 5 L273 15 H280" fill="none" stroke="#2FE38A" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="ticker-text">System status: matching donors in real-time · Every droplet counts</span>
    </div>

    <div class="container">

        <!-- ডোনার স্ট্যাটাস কন্ট্রোল প্যানেল -->
        <?php if ($is_donor): ?>
            <div class="status-panel">
                <div>
                    <span class="label">Your Donor Availability</span>
                    <?php if ($is_locked): ?>
                        <span class="status-badge status-locked"><span class="status-dot"></span>Locked (Cooldown)</span>
                    <?php elseif ($donor_status == 1): ?>
                        <span class="status-badge status-on"><span class="status-dot"></span>Available</span>
                    <?php else: ?>
                        <span class="status-badge status-off"><span class="status-dot"></span>Unavailable</span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (!$is_locked): ?>
                        <a href="home.php?toggle_status=1" class="status-btn">
                            <?php echo ($donor_status == 1) ? "Turn Off Status" : "Turn On Status"; ?>
                        </a>
                    <?php else: ?>
                        <small>Locked after donation</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <span class="eyebrow">Real-time matching</span>
        <h1>Find Blood Donors in Real-Time</h1>
        <div class="hero-pulse">
            <svg viewBox="0 0 320 34" width="100%" height="34">
                <path class="pulse-line" d="M0 17 H100 L115 17 L125 5 L138 29 L149 17 L162 17 L172 10 L182 24 L192 17 H320" />
            </svg>
        </div>
        <p class="subtitle">Select blood group and area to find available donors instantly.</p>

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
            <a href="donor.php?view=all" class="btn-all-donors">📋 All Available Donors</a>
        </form>

        <!-- ইনকামিং ব্লাড রিকোয়েস্ট বোর্ড -->
        <?php if ($incoming_requests->num_rows > 0): ?>
            <div class="request-section">
                <h2>🚨 Pending Blood Requests For You</h2>
                <table>
                    <tr>
                        <th>Patient/Receiver</th>
                        <th>Blood Group</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                    <?php while($req = $incoming_requests->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['name']); ?></td>
                        <td><span class="blood-chip"><?php echo htmlspecialchars($req['blood_group']); ?></span></td>
                        <td><?php echo htmlspecialchars($req['phone']); ?></td>
                        <td>
                            <a href="handle_request.php?id=<?php echo $req['id']; ?>&action=accept" class="acc-btn">✓ Accept</a>
                            <a href="handle_request.php?id=<?php echo $req['id']; ?>&action=reject" class="rej-btn">✕ Reject</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- 🟢 নতুন কোশ্চেন এবং পপ-আপ বাটন সেকশন -->
        <div class="donor-section">
            <h2>Want to save lives? ❤️</h2>
            <p class="cta-text">
                <?php echo $is_donor ? "You are already a proud member of our donor family!" : "Would you like to register yourself as a real-time blood donor?"; ?>
            </p>

            <?php if (!$is_donor): ?>
                <button type="button" class="btn" id="openModalBtn">Yes, I Want to Become a Donor</button>
            <?php else: ?>
                <p class="donor-badge-done">✓ You are already a registered donor.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 🟢 পপ-আপ মোডাল ফরম (Modal Form) -->
    <div class="modal" id="donorModal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h2>🩸 Donor Registration Form</h2>
            <p>Please provide your accurate medical and location history.</p>

            <form action="add_donor.php" method="POST" class="modal-form">
                <label>Full Name</label>
                <input type="text" name="donor_name" value="<?php echo htmlspecialchars($user_name); ?>" required>

                <label>Phone Number (Locked)</label>
                <input type="tel" name="donor_phone" value="<?php echo htmlspecialchars($user_phone); ?>" readonly style="background-color: #F1EEE9; cursor: not-allowed; color: var(--ink-soft);">

                <label>Your Age</label>
                <input type="number" name="donor_age" placeholder="Min 18 - Max 65 Years" min="18" max="65" required>

                <label>Area / Current Location</label>
                <input type="text" name="donor_area" placeholder="e.g. Uttara, Dhaka" required>

                <label>Last Donation Date (If any)</label>
                <input type="date" name="last_donation_date">

                <label>Do you smoke?</label>
                <select name="is_smoker" required>
                    <option value="">Select Option</option>
                    <option value="No">No, I don't smoke</option>
                    <option value="Yes">Yes, I smoke</option>
                </select>

                <label>Blood Group</label>
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

                <label>Medical Notes (Other diseases/Allergies if any)</label>
                <textarea name="medical_notes" rows="3" placeholder="e.g. Diabetes, High BP, None"></textarea>

                <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Submit & Become a Donor</button>
            </form>
        </div>
    </div>

    <!-- 🟢 জাভাস্ক্রিপ্ট কোড পপ-আপ ওপেন এবং ক্লোজ করার জন্য -->
    <script>
        const modal = document.getElementById('donorModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');

        function openModal() {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (openBtn) {
            openBtn.addEventListener('click', openModal);
        }

        closeBtn.addEventListener('click', closeModal);

        window.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
        });
    </script>
</body>
</html>