<?php
session_start();
include("db.php");
include("navbar.php");

// Get About content
$about = $conn->query("SELECT * FROM about_content LIMIT 1");

// Get Team members (fetch into an array so we can both render cards
// and hand the same data to JS for the click-through modal)
$team_result = $conn->query("SELECT * FROM team");
$team_rows = $team_result ? $team_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AnnSatu - About</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    font-family:'Segoe UI', sans-serif;
    box-sizing:border-box;
}

/* NAVBAR */
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

nav h2{
    color:#ff3b3b;
    font-size:24px;
}

nav ul{
    display:flex;
    list-style:none;
    gap:25px;
}

nav ul li a{
    text-decoration:none;
    color:#333;
    font-weight:600;
    position:relative;
    transition:0.3s;
    padding:5px 0;
}

/* HOVER ANIMATION */
nav ul li a::after{
    content:"";
    position:absolute;
    width:0%;
    height:3px;
    left:0;
    bottom:-5px;
    background:#ff3b3b;
    transition:0.4s;
}

nav ul li a:hover{
    color:#ff3b3b;
    transform:scale(1.05);
}

nav ul li a:hover::after{
    width:100%;
}

/* HERO */
.hero{
    height:55vh;
    background:
    linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),
    url("https://images.unsplash.com/photo-1504674900247-0877df9cc836");
    background-size:cover;
    background-position:center;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    color:white;
}

.hero h1{
    font-size:55px;
    text-shadow:2px 2px 10px black;
}

/* OUR STORY SECTION */
.story-section {
    padding: 70px 20px;
    text-align: center;
    background: #fff;
}

.story-section h2 {
    color: #ff3b3b;
    font-size: 36px;
    margin-bottom: 20px;
}

.story-content {
    max-width: 800px;
    margin: 0 auto;
}

.story-content p {
    font-size: 16px;
    color: #555;
    line-height: 1.8;
    margin-bottom: 20px;
    text-align: justify; 
    text-align-last: center; 
}

/* SECTION */
.section{
    padding:70px 20px;
    text-align:center;
}

.section h2{
    color:#ff3b3b;
    font-size:32px;
    margin-bottom:20px;
}

.section p{
    max-width:800px;
    margin:auto;
    color:#555;
    line-height:1.6;
}

/* CARDS */
.cards{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:30px;
    margin-top:40px;
}

.card{
    width:280px;
    background:rgba(255,255,255,0.9);
    backdrop-filter:blur(10px);
    padding:25px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    transition:0.3s;
    border:1px solid #eee;
}

.card:hover{
    transform:translateY(-10px);
}

/* BIG STATS RIBBON */
.stats{
    background:linear-gradient(45deg,#ff3b3b,#ff7b00);
    color:white;
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:70px;
    padding:60px;
    text-align:center;
}

.stat h3{
    font-size:45px;
}

/* TEAM CONTAINER */
.team{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:30px;
    margin-top:30px;
}

/* TEAM MEMBER CARD (now a button, opens the modal instead of navigating away) */
.member-trigger{
    background:none;
    border:none;
    padding:0;
    font:inherit;
    color:inherit;
    cursor:pointer;
    display:block;
}

.member{
    width:240px;
    background:white;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    transition:0.3s;
}

.member-trigger:hover .member{
    transform:scale(1.08);
}

.member img{
    width:100%;
    height:210px;
    object-fit:cover;
}

.member p{
    padding:15px;
    font-size:18px;
    text-align:center;
}

/* ANIMATION */
.card, .member{
    animation:fadeUp 0.8s ease-in-out;
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(30px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* ============ TEAM MODAL ============ */
.team-modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(24,18,13,0.55);
    backdrop-filter:blur(4px);
    z-index:2000;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
    opacity:0;
    pointer-events:none;
    transition:opacity 0.25s ease;
}
.team-modal-overlay.active{
    opacity:1;
    pointer-events:auto;
}

.team-modal{
    position:relative;
    width:100%;
    max-width:640px;
    max-height:88vh;
    overflow-y:auto;
    background:#FFFDFB;
    border-radius:22px;
    box-shadow:0 30px 70px -20px rgba(0,0,0,0.4);
    transform:translateY(18px) scale(0.97);
    transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);
    font-family:'Work Sans', sans-serif;
}
.team-modal-overlay.active .team-modal{
    transform:translateY(0) scale(1);
}

.modal-close{
    position:absolute;
    top:16px;
    right:16px;
    width:36px;
    height:36px;
    border-radius:50%;
    border:none;
    background:rgba(0,0,0,0.06);
    color:#333;
    font-size:20px;
    line-height:1;
    cursor:pointer;
    z-index:2;
}
.modal-close:hover{ background:rgba(255,59,59,0.12); color:#ff3b3b; }

.modal-photo-wrap{
    position:relative;
    height:230px;
    background:linear-gradient(160deg,#ff3b3b,#ff7b00);
    padding:16px;
}
.modal-photo{
    width:100%;
    height:100%;
    border-radius:14px;
    background-size:cover;
    background-position:center;
    background-color:#fde2d2;
}

.modal-index{
    position:absolute;
    left:32px;
    bottom:-18px;
    background:#241C15;
    color:#fff;
    font-family:'Bebas Neue', sans-serif;
    font-size:14px;
    letter-spacing:1px;
    padding:8px 16px;
    border-radius:100px;
    box-shadow:0 6px 14px rgba(0,0,0,0.25);
}

.modal-body{
    padding:36px 32px 28px;
}

.modal-body h2{
    font-family:'Bebas Neue', sans-serif;
    font-weight:400;
    font-size:38px;
    letter-spacing:0.5px;
    color:#241C15;
    margin-bottom:10px;
}

.modal-role{
    display:inline-block;
    color:#ff3b3b;
    background:rgba(255,59,59,0.09);
    font-weight:700;
    font-size:12.5px;
    padding:7px 16px;
    border-radius:100px;
    text-transform:uppercase;
    letter-spacing:0.6px;
    margin-bottom:20px;
}

.modal-bio{
    color:#6B5F52;
    font-size:15.5px;
    line-height:1.8;
    border-left:3px solid #EAE0D3;
    padding-left:16px;
    margin-bottom:8px;
}
.modal-bio.no-bio{ font-style:italic; color:#b7ab9c; }

.modal-profile-link{
    display:inline-block;
    margin-top:18px;
    font-size:14px;
    font-weight:700;
    color:#ff3b3b;
    text-decoration:none;
}
.modal-profile-link:hover{ text-decoration:underline; }

.modal-nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:20px 32px 28px;
    border-top:1px solid #EFE7DA;
    margin-top:14px;
}

.nav-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    border:1px solid #EAE0D3;
    background:#fff;
    color:#241C15;
    font-weight:700;
    font-size:14px;
    padding:11px 20px;
    border-radius:100px;
    cursor:pointer;
    transition:0.2s ease;
}
.nav-btn:hover:not(:disabled){
    border-color:#ff3b3b;
    color:#ff3b3b;
}
.nav-btn:disabled{
    opacity:0.35;
    cursor:not-allowed;
}
.nav-btn.next-btn{
    background:linear-gradient(135deg,#ff3b3b,#ff7b00);
    color:#fff;
    border:none;
}
.nav-btn.next-btn:hover:not(:disabled){
    color:#fff;
    filter:brightness(1.06);
}

@media (max-width: 500px){
    .modal-body{ padding:32px 24px 24px; }
    .modal-nav{ padding:18px 24px 24px; }
    .modal-body h2{ font-size:32px; }
}
</style>
</head>

<body>

<!-- HERO IMAGE BANNER -->
<div class="hero">
    <h1>About AnnSatu 🍔</h1>
</div>

<!-- STORY LINES -->
<section class="story-section">
    <h2>Our Story</h2>
    <div class="story-content">
        <p>
            Founded with a passion for connecting food lovers with their favorite culinary destinations, 
            <strong>AnnSatu</strong> has grown into Nepal’s leading food delivery platform. We bridge the
            gap between local food enthusiasts and the city's finest restaurants, bringing diverse, high-quality, 
            and authentic flavors straight to your doorstep.
        </p>
        <p>
            Whether you are craving traditional local delicacies like steaming MoMos, specialized fusion items, 
            or international comfort foods like piping hot pizzas and artisanal burgers, our goal remains the same: 
            delivering exceptional culinary experiences with speed, freshness, and care. 
        </p>
    </div>
</section>

<!-- COUNTER ACCENT BLOCK -->
<div class="stats">
    <div>
        <h3>10K+</h3>
        <p>Happy Customers</p>
    </div>

    <div>
        <h3>500+</h3>
        <p>Restaurants</p>
    </div>

    <div>
        <h3>1M+</h3>
        <p>Orders Delivered</p>
    </div>
</div>

<!-- MEMBERS PORTAL -->
<section class="section">
    <h2>Our Team</h2>

    <div class="team">
        <?php foreach($team_rows as $i => $row) { ?>
            <button type="button" class="member-trigger" onclick="openTeamModal(<?= $i ?>)">
                <div class="member">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <p>
                        <b><?php echo htmlspecialchars($row['name']); ?></b><br>
                        <span style="font-size: 14px; color: #777;"><?php echo htmlspecialchars($row['role']); ?></span>
                    </p>
                </div>
            </button>
        <?php } ?>
    </div>
</section>

<!-- TEAM MODAL: click a photo, see a brief description, step through everyone with Next -->
<div class="team-modal-overlay" id="teamModalOverlay">
    <div class="team-modal">
        <button type="button" class="modal-close" onclick="closeTeamModal()">&times;</button>

        <div class="modal-photo-wrap">
            <div class="modal-photo" id="modalPhoto"></div>
            <div class="modal-index" id="modalIndex"></div>
        </div>

        <div class="modal-body">
            <h2 id="modalName"></h2>
            <span class="modal-role" id="modalRole"></span>
            <p class="modal-bio" id="modalBio"></p>
            <a href="#" class="modal-profile-link" id="modalProfileLink">View full profile &rarr;</a>
        </div>

        <div class="modal-nav">
            <button type="button" class="nav-btn prev-btn" id="prevBtn" onclick="stepMember(-1)">&larr; Previous</button>
            <button type="button" class="nav-btn next-btn" id="nextBtn" onclick="stepMember(1)">Next &rarr;</button>
        </div>
    </div>
</div>

<script>
const teamData = <?php echo json_encode($team_rows); ?>;
let currentIndex = 0;
const overlay = document.getElementById('teamModalOverlay');

function renderMember(index){
    const m = teamData[index];
    document.getElementById('modalPhoto').style.backgroundImage =
        "url('" + (m.image || 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=600') + "')";
    document.getElementById('modalIndex').textContent = (index + 1) + ' / ' + teamData.length;
    document.getElementById('modalName').textContent = m.name;
    document.getElementById('modalRole').textContent = m.role;

    const bioEl = document.getElementById('modalBio');
    if(m.bio && m.bio.trim() !== ''){
        bioEl.textContent = m.bio;
        bioEl.classList.remove('no-bio');
    } else {
        bioEl.textContent = 'No bio has been added for this team member yet.';
        bioEl.classList.add('no-bio');
    }

    document.getElementById('modalProfileLink').href = 'member_details.php?id=' + m.id;

    document.getElementById('prevBtn').disabled = (index === 0);
    document.getElementById('nextBtn').disabled = (index === teamData.length - 1);
}

function openTeamModal(index){
    currentIndex = index;
    renderMember(currentIndex);
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeTeamModal(){
    overlay.classList.remove('active');
    document.body.style.overflow = '';
}

function stepMember(delta){
    const next = currentIndex + delta;
    if(next < 0 || next >= teamData.length) return;
    currentIndex = next;
    renderMember(currentIndex);
}

overlay.addEventListener('click', (e) => {
    if(e.target === overlay) closeTeamModal();
});

document.addEventListener('keydown', (e) => {
    if(!overlay.classList.contains('active')) return;
    if(e.key === 'Escape') closeTeamModal();
    if(e.key === 'ArrowRight') stepMember(1);
    if(e.key === 'ArrowLeft') stepMember(-1);
});
</script>

</body>
</html>