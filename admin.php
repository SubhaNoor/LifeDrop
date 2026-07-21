<?php
session_start();
include 'db.php';

// 🔒 শুধুমাত্র admin role থাকলেই এই পেজ অ্যাক্সেস করা যাবে
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['user'];

// 📊 সামারি স্ট্যাটস
$total_users_res    = $conn->query("SELECT COUNT(*) AS c FROM users");
$total_users        = $total_users_res ? $total_users_res->fetch_assoc()['c'] : 0;

$total_donors_res   = $conn->query("SELECT COUNT(*) AS c FROM donors");
$total_donors       = $total_donors_res ? $total_donors_res->fetch_assoc()['c'] : 0;

$pending_req_res     = $conn->query("SELECT COUNT(*) AS c FROM blood_requests WHERE status='Pending'");
$pending_req_count   = $pending_req_res ? $pending_req_res->fetch_assoc()['c'] : 0;

$accepted_req_res    = $conn->query("SELECT COUNT(*) AS c FROM blood_requests WHERE status='Accepted'");
$accepted_req_count  = $accepted_req_res ? $accepted_req_res->fetch_assoc()['c'] : 0;

$locked_res = $conn->query("SELECT COUNT(*) AS c FROM donors WHERE last_donation_date IS NOT NULL AND DATEDIFF(CURDATE(), last_donation_date) < 90");
$locked_count = $locked_res ? $locked_res->fetch_assoc()['c'] : 0;

$bg_covered_res = $conn->query("SELECT COUNT(DISTINCT blood_group) AS c FROM donors");
$bg_covered = $bg_covered_res ? $bg_covered_res->fetch_assoc()['c'] : 0;

// 🩸 ব্লাড গ্রুপ ব্রেকডাউন
$bg_breakdown = $conn->query("SELECT blood_group, COUNT(*) AS c FROM users GROUP BY blood_group ORDER BY c DESC");
$bg_rows = [];
$bg_max = 1;
if ($bg_breakdown) {
    while ($row = $bg_breakdown->fetch_assoc()) {
        $bg_rows[] = $row;
        if ($row['c'] > $bg_max) $bg_max = $row['c'];
    }
}

// 📍 টপ ৫ এলাকা
$top_areas = $conn->query("SELECT area, COUNT(*) AS c FROM donors WHERE area IS NOT NULL AND area <> '' GROUP BY area ORDER BY c DESC LIMIT 5");
$area_rows = [];
$area_max = 1;
if ($top_areas) {
    while ($row = $top_areas->fetch_assoc()) {
        $area_rows[] = $row;
        if ($row['c'] > $area_max) $area_max = $row['c'];
    }
}

// ⏳ পেন্ডিং রিকোয়েস্ট মনিটরিং
$pending_list = $conn->query("SELECT r.id, s.name AS sender_name, s.phone AS sender_phone, rec.blood_group AS needed_group
                               FROM blood_requests r
                               JOIN users s ON r.sender_phone = s.phone
                               JOIN users rec ON r.receiver_phone = rec.phone
                               WHERE r.status = 'Pending'
                               ORDER BY r.id DESC LIMIT 50");

// 🕓 সাম্প্রতিক অ্যাক্টিভিটি (শেষ ১০টা, যেকোনো স্ট্যাটাস)
$recent_activity = $conn->query("SELECT r.id, s.name AS sender_name, rec.name AS receiver_name, r.status
                                  FROM blood_requests r
                                  JOIN users s ON r.sender_phone = s.phone
                                  JOIN users rec ON r.receiver_phone = rec.phone
                                  ORDER BY r.id DESC LIMIT 10");

// 🧑‍🤝‍🧑 ডোনার ডিরেক্টরি
$donor_list = $conn->query("SELECT id, name, phone, blood_group, area, status FROM donors ORDER BY id DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeDrop - Admin Control Room</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #F7F3EE;
            --panel: #FFFFFF;
            --panel-2: #F1EEE9;
            --line: #E5DFD6;
            --ink: #1B1712;
            --ink-soft: #6B6459;
            --crimson: #C4123D;
            --crimson-tint: #FCE7EB;
            --success: #1F7A4D;
            --success-tint: #E3F5EA;
            --warn: #B45309;
            --warn-tint: #FBEEDD;
            --blue: #2563EB;
            --blue-tint: #EAF1FE;
            --shadow: 0 10px 30px -14px rgba(27, 23, 18, 0.14);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            margin: 0; padding: 0;
            color: var(--ink);
            line-height: 1.5;
        }
        h1, h2, h3 { font-family: 'Sora', sans-serif; letter-spacing: -0.01em; margin: 0; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        .navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--line);
            padding: 16px 40px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 500;
        }
        .navbar .brand { display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 800; }
        .navbar .brand .badge {
            background: var(--crimson-tint); color: var(--crimson); font-size: 11px; font-weight: 700;
            letter-spacing: 0.06em; text-transform: uppercase; padding: 3px 9px; border-radius: 999px;
            border: 1px solid rgba(196,18,61,0.3);
        }
        .nav-right { display: flex; align-items: center; gap: 10px; }
        .navbar a {
            color: var(--ink-soft); text-decoration: none; font-weight: 600; font-size: 13.5px;
            padding: 8px 14px; border-radius: 8px; border: 1px solid var(--line); transition: all 0.15s ease;
        }
        .navbar a:hover { color: var(--ink); border-color: #D8D0C4; background: var(--panel-2); }
        .navbar a.logout { color: var(--crimson); border-color: rgba(196,18,61,0.25); }

        .wrap { max-width: 1180px; margin: 0 auto; padding: 40px 24px 70px; }
        .page-head { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 30px; }
        .page-head .eyebrow { display: inline-flex; align-items: center; gap: 7px; font-size: 11.5px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--success); margin-bottom: 8px; }
        .page-head .eyebrow .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--success); box-shadow: 0 0 0 0 rgba(31,122,77,0.4); animation: pulse-dot 1.8s infinite; }
        @keyframes pulse-dot { 0% { box-shadow: 0 0 0 0 rgba(31,122,77,0.4); } 70% { box-shadow: 0 0 0 7px rgba(31,122,77,0); } 100% { box-shadow: 0 0 0 0 rgba(31,122,77,0); } }
        .page-head h1 { font-size: 27px; font-weight: 800; }
        .page-head .sub { color: var(--ink-soft); font-size: 14px; margin-top: 4px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card { background: #FFFFFF; border: 1px solid var(--line); border-radius: 16px; padding: 22px; box-shadow: var(--shadow); transition: transform 0.15s ease; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; margin-bottom: 14px; }
        .stat-card .num { font-family: 'Sora', sans-serif; font-size: 28px; font-weight: 800; }
        .stat-card .lbl { color: var(--ink-soft); font-size: 12.5px; font-weight: 600; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.03em; }
        .stat-card.crimson .icon { background: var(--crimson-tint); color: var(--crimson); }
        .stat-card.success .icon { background: var(--success-tint); color: var(--success); }
        .stat-card.warn .icon { background: var(--warn-tint); color: var(--warn); }
        .stat-card.blue .icon { background: var(--blue-tint); color: var(--blue); }

        .panel-grid { display: grid; grid-template-columns: 1fr; gap: 18px; }
        @media (min-width: 980px) { .panel-grid.two { grid-template-columns: 1fr 1fr; align-items: start; } }
        .panel { background: #FFFFFF; border: 1px solid var(--line); border-radius: 16px; padding: 24px; box-shadow: var(--shadow); }
        .panel-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--line); }
        .panel-title h2 { font-size: 15.5px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .panel-title .count-chip { background: var(--panel-2); border: 1px solid var(--line); color: var(--ink-soft); font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }

        .bg-bar-row { display: grid; grid-template-columns: 46px 1fr 30px; align-items: center; gap: 10px; margin-bottom: 12px; }
        .bg-bar-row .bg-label { font-weight: 800; color: var(--crimson); font-size: 13.5px; }
        .bg-bar-track { background: var(--panel-2); border-radius: 999px; height: 8px; overflow: hidden; }
        .bg-bar-fill { height: 100%; background: linear-gradient(90deg, var(--crimson), #FF8FA3); border-radius: 999px; }
        .bg-bar-row .bg-count { font-size: 12.5px; color: var(--ink-soft); text-align: right; }

        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 460px; }
        th { text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); border-bottom: 1px solid var(--line); white-space: nowrap; }
        td { padding: 12px; border-bottom: 1px solid var(--line); font-size: 13.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--panel-2); }

        .donor-link { color: var(--ink); font-weight: 700; text-decoration: none; }
        .donor-link:hover { color: var(--crimson); text-decoration: underline; }

        .chip { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
        .chip-crimson { background: var(--crimson-tint); color: var(--crimson); }
        .chip-success { background: var(--success-tint); color: var(--success); }
        .chip-warn { background: var(--warn-tint); color: var(--warn); }
        .chip-muted { background: var(--panel-2); color: var(--ink-soft); border: 1px solid var(--line); }

        .row-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .row-actions a { font-size: 12px; font-weight: 700; text-decoration: none; padding: 6px 11px; border-radius: 7px; display: inline-block; white-space: nowrap; }
        .act-accept { background: var(--success-tint); color: var(--success); }
        .act-reject { background: var(--crimson-tint); color: var(--crimson); }

        .empty-row { text-align: center; color: var(--ink-soft); font-size: 13.5px; padding: 30px 0; }

        .activity-list { display: flex; flex-direction: column; gap: 4px; }
        .activity-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 2px; border-bottom: 1px solid var(--line); gap: 10px; }
        .activity-row:last-child { border-bottom: none; }
        .activity-text { font-size: 13px; color: var(--ink); }

        .search-input { width: 100%; padding: 11px 14px; border-radius: 10px; border: 1px solid var(--line); background: var(--panel-2); color: var(--ink); font-family: 'Inter', sans-serif; font-size: 14px; margin-bottom: 14px; }
        .search-input:focus { outline: none; border-color: var(--crimson); box-shadow: 0 0 0 3px var(--crimson-tint); }

        .remove-btn { font-size: 12px; font-weight: 700; text-decoration: none; padding: 6px 11px; border-radius: 7px; display: inline-block; background: var(--crimson-tint); color: var(--crimson); }
        .remove-btn:hover { opacity: 0.8; }

        @media (max-width: 640px) {
            .navbar { padding: 14px 18px; }
            .wrap { padding: 26px 16px 50px; }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="brand">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#C4123D"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg>
            LifeDrop <span class="badge">Admin</span>
        </div>
        <div class="nav-right">
            <a href="home.php">Donor View</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="wrap">
        <div class="page-head">
            <div>
                <div class="eyebrow"><span class="dot"></span>Control Room — Live</div>
                <h1>Welcome back, <?php echo htmlspecialchars($admin_name); ?></h1>
                <div class="sub">Monitor donors, requests, and network health in real time.</div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card crimson">
                <div class="icon">👤</div>
                <div class="num mono"><?php echo number_format($total_users); ?></div>
                <div class="lbl">Total Users</div>
            </div>
            <div class="stat-card success">
                <div class="icon">🩸</div>
                <div class="num mono"><?php echo number_format($total_donors); ?></div>
                <div class="lbl">Registered Donors</div>
            </div>
            <div class="stat-card warn">
                <div class="icon">⏳</div>
                <div class="num mono"><?php echo number_format($pending_req_count); ?></div>
                <div class="lbl">Pending Requests</div>
            </div>
            <div class="stat-card blue">
                <div class="icon">✓</div>
                <div class="num mono"><?php echo number_format($accepted_req_count); ?></div>
                <div class="lbl">Successful Matches</div>
            </div>
            <div class="stat-card warn">
                <div class="icon">🔒</div>
                <div class="num mono"><?php echo number_format($locked_count); ?></div>
                <div class="lbl">Locked (Cooldown)</div>
            </div>
            <div class="stat-card success">
                <div class="icon">🧬</div>
                <div class="num mono"><?php echo number_format($bg_covered); ?>/8</div>
                <div class="lbl">Groups Covered</div>
            </div>
        </div>

        <div class="panel-grid two">
            <div class="panel">
                <div class="panel-title"><h2>🩸 Blood Group Breakdown</h2></div>
                <?php if (count($bg_rows) > 0): ?>
                    <?php foreach ($bg_rows as $row): ?>
                        <?php $pct = $bg_max > 0 ? round(($row['c'] / $bg_max) * 100) : 0; ?>
                        <div class="bg-bar-row">
                            <div class="bg-label"><?php echo htmlspecialchars($row['blood_group']); ?></div>
                            <div class="bg-bar-track"><div class="bg-bar-fill" style="width: <?php echo $pct; ?>%;"></div></div>
                            <div class="bg-count mono"><?php echo $row['c']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-row">No user data yet.</div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-title"><h2>📍 Top Donor Areas</h2></div>
                <?php if (count($area_rows) > 0): ?>
                    <?php foreach ($area_rows as $row): ?>
                        <?php $apct = $area_max > 0 ? round(($row['c'] / $area_max) * 100) : 0; ?>
                        <div class="bg-bar-row" style="grid-template-columns: 110px 1fr 30px;">
                            <div class="bg-label" style="color: var(--blue); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['area']); ?></div>
                            <div class="bg-bar-track"><div class="bg-bar-fill" style="width: <?php echo $apct; ?>%; background: linear-gradient(90deg, var(--blue), #93C5FD);"></div></div>
                            <div class="bg-count mono"><?php echo $row['c']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-row">No area data yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-grid two" style="margin-top: 18px;">
            <div class="panel">
                <div class="panel-title">
                    <h2>⏳ Pending Blood Requests</h2>
                    <span class="count-chip"><?php echo $pending_req_count; ?> waiting</span>
                </div>
                <?php if ($pending_list && $pending_list->num_rows > 0): ?>
                    <div class="table-scroll">
                    <table>
                        <tr><th>Requester</th><th>Needs</th><th>Contact</th><th>Action</th></tr>
                        <?php while ($req = $pending_list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['sender_name']); ?></td>
                            <td><span class="chip chip-crimson"><?php echo htmlspecialchars($req['needed_group']); ?></span></td>
                            <td><?php echo htmlspecialchars($req['sender_phone']); ?></td>
                            <td class="row-actions">
                                <a href="admin_handle_request.php?id=<?php echo $req['id']; ?>&action=accept" class="act-accept">Accept</a>
                                <a href="admin_handle_request.php?id=<?php echo $req['id']; ?>&action=reject" class="act-reject">Reject</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                    </div>
                <?php else: ?>
                    <div class="empty-row">No pending requests right now. Network is calm. ✅</div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-title"><h2>🕓 Recent Activity</h2></div>
                <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                    <div class="activity-list">
                        <?php while ($act = $recent_activity->fetch_assoc()): ?>
                            <?php
                                $status = $act['status'];
                                $chip_class = $status === 'Accepted' ? 'chip-success' : ($status === 'Rejected' ? 'chip-crimson' : 'chip-warn');
                            ?>
                            <div class="activity-row">
                                <div class="activity-text">
                                    <span class="mono" style="color: var(--ink-soft);">#<?php echo $act['id']; ?></span>
                                    <?php echo htmlspecialchars($act['sender_name']); ?> → <?php echo htmlspecialchars($act['receiver_name']); ?>
                                </div>
                                <span class="chip <?php echo $chip_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-row">No activity yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel" style="margin-top: 18px;">
            <div class="panel-title">
                <h2>🧑‍🤝‍🧑 Donor Directory</h2>
                <span class="count-chip">Showing latest <?php echo $donor_list ? $donor_list->num_rows : 0; ?></span>
            </div>
            <input type="text" id="donorSearch" class="search-input" placeholder="🔍 Search by name, phone, area, or blood group...">
            <div class="table-scroll">
            <table id="donorTable">
                <tr><th>Name</th><th>Phone</th><th>Blood Group</th><th>Area</th><th>Status</th><th>Action</th></tr>
                <?php if ($donor_list && $donor_list->num_rows > 0): ?>
                    <?php while ($d = $donor_list->fetch_assoc()): ?>
                    <tr>
                        <td><a href="profile.php?phone=<?php echo urlencode($d['phone']); ?>" class="donor-link"><?php echo htmlspecialchars($d['name']); ?></a></td>
                        <td class="mono"><?php echo htmlspecialchars($d['phone']); ?></td>
                        <td><span class="chip chip-crimson"><?php echo htmlspecialchars($d['blood_group']); ?></span></td>
                        <td><?php echo htmlspecialchars($d['area'] ?? '—'); ?></td>
                        <td>
                            <?php if (($d['status'] ?? 1) == 1): ?>
                                <span class="chip chip-success">Available</span>
                            <?php else: ?>
                                <span class="chip chip-muted">Unavailable</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="admin_donor_action.php?id=<?php echo $d['id']; ?>&action=remove"
                               class="remove-btn"
                               onclick="return confirm('Remove <?php echo htmlspecialchars(addslashes($d['name'])); ?> from the donor list?');">Remove</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </table>
            </div>
            <?php if (!$donor_list || $donor_list->num_rows === 0): ?>
                <div class="empty-row">No donors registered yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const donorSearch = document.getElementById('donorSearch');
        const donorTable = document.getElementById('donorTable');
        if (donorSearch && donorTable) {
            donorSearch.addEventListener('input', () => {
                const q = donorSearch.value.trim().toLowerCase();
                const rows = donorTable.querySelectorAll('tr');
                rows.forEach((row, i) => {
                    if (i === 0) return;
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(q) ? '' : 'none';
                });
            });
        }
    </script>

</body>
</html>