<?php
session_start();
include("db.php");
include("navbar.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM team WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$member) {
    header("Location: about.php");
    exit();
}

$staff_no = str_pad($member['id'], 3, '0', STR_PAD_LEFT);
$since_year = !empty($member['created_at']) ? date('Y', strtotime($member['created_at'])) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AnnSatu - <?= htmlspecialchars($member['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --cream:#FBF3E9;
    --card:#FFFFFF;
    --ink:#241C15;
    --ink-soft:#6B5F52;
    --red:#FF3B3B;
    --ember:#FF7B00;
    --line:#EAE0D3;
}

body{
    font-family:'Work Sans', sans-serif;
    background:
        radial-gradient(circle at 8% 12%, rgba(255,123,0,0.07) 0, transparent 40%),
        radial-gradient(circle at 92% 85%, rgba(255,59,59,0.07) 0, transparent 42%),
        var(--cream);
    color:var(--ink);
    min-height:100vh;
}

/* NAVBAR (shared styling with about.php) */
nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:15px 40px;
    position:sticky;
    top:0;
    z-index:1000;
    box-shadow:0 2px 12px rgba(0,0,0,0.1);
}
nav h2{ color:var(--red); font-size:24px; font-family:'Work Sans', sans-serif; }
nav ul{ display:flex; list-style:none; gap:25px; }
nav ul li a{
    text-decoration:none;
    color:#333;
    font-weight:600;
    position:relative;
    transition:0.3s;
    padding:5px 0;
}
nav ul li a::after{
    content:"";
    position:absolute;
    width:0%;
    height:3px;
    left:0;
    bottom:-5px;
    background:var(--red);
    transition:0.4s;
}
nav ul li a:hover{ color:var(--red); transform:scale(1.05); }
nav ul li a:hover::after{ width:100%; }

/* BACK LINK */
.back-bar{
    max-width:960px;
    margin:36px auto 0;
    padding:0 24px;
}
.back-link{
    display:inline-flex;
    align-items:center;
    gap:8px;
    text-decoration:none;
    color:var(--ink);
    font-weight:600;
    font-size:14px;
    background:var(--card);
    border:1px solid var(--line);
    padding:9px 18px 9px 14px;
    border-radius:100px;
    transition:0.25s ease;
}
.back-link svg{ transition:0.25s ease; }
.back-link:hover{
    border-color:var(--red);
    color:var(--red);
}
.back-link:hover svg{ transform:translateX(-3px); }

/* PROFILE CARD */
.profile-wrap{
    max-width:960px;
    margin:26px auto 100px;
    padding:0 24px;
}

.profile-card{
    position:relative;
    background:var(--card);
    border-radius:26px;
    box-shadow:0 24px 60px -20px rgba(36,28,21,0.25);
    overflow:hidden;
    display:grid;
    grid-template-columns:1fr 1.35fr;
    animation:riseIn 0.7s cubic-bezier(0.16,1,0.3,1) both;
}

/* PHOTO SIDE */
.profile-img-wrap{
    position:relative;
    min-height:420px;
    background:linear-gradient(160deg, var(--red), var(--ember));
    padding:22px;
}

.profile-img{
    width:100%;
    height:100%;
    border-radius:18px;
    background-size:cover;
    background-position:center;
    background-color:#fde2d2;
    box-shadow:inset 0 0 0 1px rgba(255,255,255,0.25);
}

/* SIGNATURE ELEMENT: hand-stamped kitchen-ticket role badge */
.stamp{
    position:absolute;
    top:34px;
    right:-6px;
    width:118px;
    height:118px;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    transform:rotate(-11deg);
}
.stamp::before{
    content:"";
    position:absolute;
    inset:0;
    border-radius:50%;
    border:2.5px dashed rgba(255,255,255,0.85);
    background:rgba(36,28,21,0.28);
    backdrop-filter:blur(2px);
}
.stamp span{
    position:relative;
    font-family:'Bebas Neue', sans-serif;
    letter-spacing:1px;
    font-size:15px;
    line-height:1.15;
    color:#fff;
    padding:0 12px;
    text-transform:uppercase;
}

/* INFO SIDE */
.profile-info{
    padding:52px 50px 46px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.eyebrow{
    font-family:'Work Sans', sans-serif;
    font-weight:700;
    font-size:12.5px;
    letter-spacing:2.5px;
    color:var(--ember);
    text-transform:uppercase;
    margin-bottom:14px;
}

.profile-info h1{
    font-family:'Bebas Neue', sans-serif;
    font-weight:400;
    font-size:56px;
    letter-spacing:0.5px;
    line-height:1;
    color:var(--ink);
    margin-bottom:16px;
}

.role-pill{
    display:inline-flex;
    align-self:flex-start;
    align-items:center;
    gap:8px;
    color:var(--red);
    background:rgba(255,59,59,0.09);
    font-weight:700;
    font-size:13px;
    padding:8px 18px;
    border-radius:100px;
    text-transform:uppercase;
    letter-spacing:0.6px;
    margin-bottom:28px;
}
.role-pill svg{ width:14px; height:14px; }

.bio-block{
    position:relative;
    padding-left:22px;
    border-left:3px solid var(--line);
}

.bio-block .quote-mark{
    font-family:'Bebas Neue', sans-serif;
    font-size:52px;
    color:var(--line);
    line-height:0;
    display:block;
    margin-bottom:6px;
}

.bio-block p{
    color:var(--ink-soft);
    font-size:16.5px;
    line-height:1.85;
}

.bio-block .no-bio{
    color:#b7ab9c;
    font-style:italic;
}

.since-chip{
    margin-top:32px;
    display:inline-flex;
    align-items:center;
    gap:9px;
    font-size:13px;
    font-weight:600;
    color:var(--ink-soft);
}
.since-chip .dot{
    width:7px;
    height:7px;
    border-radius:50%;
    background:var(--ember);
}

@keyframes riseIn{
    from{ opacity:0; transform:translateY(28px); }
    to{ opacity:1; transform:translateY(0); }
}

@media (max-width: 760px){
    .profile-card{ grid-template-columns:1fr; }
    .profile-img-wrap{ min-height:280px; }
    .profile-info{ padding:38px 30px 40px; }
    .profile-info h1{ font-size:42px; }
}

@media (prefers-reduced-motion: reduce){
    .profile-card{ animation:none; }
    .back-link, .back-link svg{ transition:none; }
}
</style>
</head>

<body>

<div class="back-bar">
    <a href="about.php" class="back-link">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Our Team
    </a>
</div>

<div class="profile-wrap">
    <div class="profile-card">
        <div class="profile-img-wrap">
            <div class="profile-img" style="background-image:url('<?= htmlspecialchars($member['image'] ?: 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=600') ?>');"></div>
            <div class="stamp"><span><?= htmlspecialchars($member['role']) ?></span></div>
        </div>
        <div class="profile-info">
            <div class="eyebrow">Staff No. <?= htmlspecialchars($staff_no) ?></div>
            <h1><?= htmlspecialchars($member['name']) ?></h1>
            <span class="role-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.86 3.75 3.97V22h2.5v-9.03C11.34 12.86 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                <?= htmlspecialchars($member['role']) ?>
            </span>
            <div class="bio-block">
                <?php if(!empty($member['bio'])): ?>
                    <span class="quote-mark">&ldquo;</span>
                    <p><?= nl2br(htmlspecialchars($member['bio'])) ?></p>
                <?php else: ?>
                    <p class="no-bio">No bio has been added for this team member yet.</p>
                <?php endif; ?>
            </div>
            <?php if($since_year): ?>
                <div class="since-chip"><span class="dot"></span> On the team since <?= htmlspecialchars($since_year) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>