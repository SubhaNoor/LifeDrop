<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeDrop - Emergency Blood Network</title>
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
            --success: #1F7A4D;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            margin: 0;
            padding: 0;
            color: var(--ink);
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
        .navbar a {
            color: rgba(255,255,255,0.92);
            text-decoration: none;
            font-weight: 600;
            font-size: 14.5px;
            margin-left: 6px;
            padding: 9px 14px;
            border-radius: 8px;
            transition: background-color 0.15s ease;
        }
        .navbar a:hover { background-color: rgba(255,255,255,0.14); }

        /* ---------- Hero ---------- */
        .landing-hero {
            position: relative;
            padding: 130px 20px 150px;
            text-align: center;
            overflow: hidden;
            background: radial-gradient(ellipse 120% 80% at 50% -10%, #2A0812 0%, #4A0F1E 38%, #17070C 100%);
            isolation: isolate;
        }

        /* floating blood-drop illustration layer */
        .hero-art {
            position: absolute;
            inset: 0;
            z-index: -1;
            opacity: 0.9;
        }
        .drop {
            position: absolute;
            opacity: 0.16;
            animation: float 7s ease-in-out infinite;
        }
        .drop svg { width: 100%; height: 100%; display: block; }
        .drop.d1 { top: 8%;  left: 6%;  width: 60px;  animation-delay: 0s; }
        .drop.d2 { top: 60%; left: 12%; width: 34px;  animation-delay: 1.2s; opacity: 0.12;}
        .drop.d3 { top: 18%; right: 9%; width: 46px;  animation-delay: 0.6s; }
        .drop.d4 { top: 68%; right: 15%; width: 70px; animation-delay: 2s; }
        .drop.d5 { top: 40%; left: 45%; width: 26px;  animation-delay: 1.6s; opacity: 0.10; }
        .drop.d6 { bottom: 6%; right: 40%; width: 40px; animation-delay: 0.9s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-18px) rotate(4deg); }
        }

        /* heartbeat pulse line under the hero copy */
        .pulse-wrap {
            width: 100%;
            max-width: 560px;
            margin: 0 auto 34px;
            opacity: 0.85;
        }
        .pulse-line {
            stroke: var(--crimson);
            stroke-width: 2.4;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 620;
            stroke-dashoffset: 620;
            animation: draw-pulse 2.6s ease-out forwards infinite;
        }
        @keyframes draw-pulse {
            0%   { stroke-dashoffset: 620; }
            55%  { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: 0; }
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #FFD9E1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.18);
            padding: 7px 16px;
            border-radius: 999px;
            margin-bottom: 26px;
        }
        .eyebrow .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #FF5C7A;
            box-shadow: 0 0 0 0 rgba(255,92,122,0.6);
            animation: pulse-dot 1.8s infinite;
        }
        @keyframes pulse-dot {
            0% { box-shadow: 0 0 0 0 rgba(255,92,122,0.5); }
            70% { box-shadow: 0 0 0 8px rgba(255,92,122,0); }
            100% { box-shadow: 0 0 0 0 rgba(255,92,122,0); }
        }

        .landing-hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 48px;
            font-weight: 800;
            color: white;
            margin: 0 0 20px;
            letter-spacing: -0.02em;
        }
        .landing-hero h1 span { color: #FF7A93; }

        .landing-hero p {
            font-size: 17.5px;
            color: rgba(255,255,255,0.72);
            max-width: 580px;
            margin: 0 auto 44px auto;
            line-height: 1.65;
        }

        .cta-buttons a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 30px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 12px;
            margin: 0 8px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }
        .btn-login {
            background-color: var(--crimson);
            color: white;
            box-shadow: 0 12px 26px -10px rgba(196, 18, 61, 0.65);
        }
        .btn-login:hover { background-color: #DA1546; transform: translateY(-2px); }
        .btn-register {
            background-color: rgba(255,255,255,0.06);
            color: white;
            border: 1.5px solid rgba(255,255,255,0.3);
        }
        .btn-register:hover { background-color: rgba(255,255,255,0.12); transform: translateY(-2px); }

        /* ---------- Stats strip ---------- */
        .stats {
            max-width: 880px;
            margin: -64px auto 0;
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 50px -18px rgba(23,20,15,0.25);
            border: 1px solid #E9E3DB;
            display: flex;
            flex-wrap: wrap;
        }
        .stats .stat {
            flex: 1 1 33%;
            padding: 30px 20px;
            text-align: center;
            border-right: 1px solid #EFEAE3;
        }
        .stats .stat:last-child { border-right: none; }
        .stats .stat .num {
            font-family: 'Sora', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--crimson);
        }
        .stats .stat .lbl {
            font-size: 13px;
            color: var(--ink-soft);
            font-weight: 600;
            margin-top: 4px;
        }

        /* ---------- How it works ---------- */
        .section {
            max-width: 980px;
            margin: 100px auto 60px;
            padding: 0 24px;
            text-align: center;
        }
        .section .kicker {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--crimson);
        }
        .section h2 {
            font-family: 'Sora', sans-serif;
            font-size: 30px;
            font-weight: 800;
            margin: 10px 0 46px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            text-align: left;
        }
        .card {
            background: white;
            border: 1px solid #E9E3DB;
            border-radius: 16px;
            padding: 28px 24px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px -16px rgba(23,20,15,0.2); }
        .card .icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--crimson-tint);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }
        .card h3 { font-family: 'Sora', sans-serif; font-size: 16.5px; margin: 0 0 8px; }
        .card p { font-size: 14px; color: var(--ink-soft); line-height: 1.6; margin: 0; }

        footer {
            text-align: center;
            padding: 44px 20px 36px;
            color: var(--ink-soft);
            font-size: 13.5px;
            border-top: 1px solid #E9E3DB;
            margin-top: 90px;
        }

        @media (max-width: 720px) {
            .navbar { padding: 14px 18px; }
            .landing-hero { padding: 90px 18px 120px; }
            .landing-hero h1 { font-size: 32px; }
            .cta-buttons a { display: block; margin: 10px auto; max-width: 280px; }
            .stats { margin-top: -46px; }
            .stats .stat { flex: 1 1 100%; border-right: none; border-bottom: 1px solid #EFEAE3; }
            .stats .stat:last-child { border-bottom: none; }
            .cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg>
            LifeDrop Droplet
        </div>
        <div>
            <a href="index.php">Home</a>
            <a href="login.html">Login</a>
            <a href="register.html">Register</a>
        </div>
    </div>

    <div class="landing-hero">
        <div class="hero-art">
            <div class="drop d1"><svg viewBox="0 0 24 24" fill="#FF5C7A"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
            <div class="drop d2"><svg viewBox="0 0 24 24" fill="#FF5C7A"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
            <div class="drop d3"><svg viewBox="0 0 24 24" fill="#FF5C7A"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
            <div class="drop d4"><svg viewBox="0 0 24 24" fill="#FF5C7A"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
            <div class="drop d5"><svg viewBox="0 0 24 24" fill="#FF5C7A"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
            <div class="drop d6"><svg viewBox="0 0 24 24" fill="#FF5C7A"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14z"/></svg></div>
        </div>

        <span class="eyebrow"><span class="dot"></span>Live donor network</span>

        <h1>Every <span>droplet</span> counts.</h1>
        <p>Join our real-time emergency blood and platelet coordination network. Log in now to find available blood donors near your area instantly, or register yourself to save lives.</p>

        <div class="pulse-wrap">
            <svg viewBox="0 0 560 60" width="100%" height="60">
                <path class="pulse-line" d="M0 30 H180 L205 30 L220 8 L240 52 L258 30 L275 30 L290 18 L305 42 L320 30 H560" />
            </svg>
        </div>

        <div class="cta-buttons">
            <a href="login.html" class="btn-login">🔍 Log In to Find Blood</a>
            <a href="register.html" class="btn-register">Register as a Member</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat"><div class="num">1,200+</div><div class="lbl">Registered Donors</div></div>
        <div class="stat"><div class="num">350+</div><div class="lbl">Lives Connected</div></div>
        <div class="stat"><div class="num">24/7</div><div class="lbl">Emergency Matching</div></div>
    </div>

    <div class="section">
        <div class="kicker">How it works</div>
        <h2>From request to donor in three steps</h2>
        <div class="cards">
            <div class="card">
                <div class="icon">🩸</div>
                <h3>1. Register your blood group</h3>
                <p>Sign up in minutes and mark your blood group, area, and availability so people nearby can find you.</p>
            </div>
            <div class="card">
                <div class="icon">📍</div>
                <h3>2. Search by area & group</h3>
                <p>Need blood urgently? Filter donors by blood group and location to see who's available right now.</p>
            </div>
            <div class="card">
                <div class="icon">🤝</div>
                <h3>3. Connect instantly</h3>
                <p>Send a request, get accepted, and coordinate directly with the donor — no waiting, no middleman.</p>
            </div>
        </div>
    </div>

    <footer>
        © <?php echo date("Y"); ?> LifeDrop Droplet — Built to connect donors and patients, faster.
    </footer>

</body>
</html>