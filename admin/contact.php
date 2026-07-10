<?php
session_start();
include("navbar.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Foodmandu</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f8fafc;
            color: #2d3748;
            min-height: 100vh;
        }

        /* HERO HEADER SECTION */
        .hero {
            height: 28vh;
            background: linear-gradient(135deg, #ff3b3b, #ff6a00);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            gap: 10px;
            box-shadow: inset 0 -50px 50px rgba(0,0,0,0.02);
        }

        .hero h1 {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .hero p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 500;
        }

        /* CONTAINER LAYOUT WRAPPER */
        .main-container {
            max-width: 1140px;
            margin: -50px auto 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 4.5fr 7.5fr;
            gap: 40px;
            position: relative;
            z-index: 10;
        }

        /* METADATA CARDS (LEFT BLOCK) */
        .info-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            border: 1px solid #edf2f7;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-3px);
        }

        .icon-box {
            background: rgba(255, 59, 59, 0.08);
            color: #ff3b3b;
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-details h3 {
            font-size: 17px;
            color: #1a202c;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .info-details p {
            font-size: 14px;
            color: #718096;
            line-height: 1.6;
            font-weight: 500;
        }

        /* APP CAPTURE FORM BOX (RIGHT BLOCK) */
        .contact-box {
            background: white;
            padding: 45px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.03);
            border: 1px solid #edf2f7;
        }

        .contact-box h2 {
            font-size: 26px;
            color: #1a202c;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .contact-box .subtitle {
            color: #718096;
            font-size: 15px;
            margin-bottom: 35px;
            font-weight: 500;
        }

        /* FORM WORKSPACE GRIDS */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group.no-margin {
            margin-bottom: 0;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            font-size: 15px;
            color: #2d3748;
            background: #f8fafc;
            transition: all 0.25s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #ff3b3b;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 59, 59, 0.08);
        }

        /* ACTION SUBMIT BUTTON */
        .btn-container {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-box button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff3b3b, #ff6a00);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 59, 59, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .contact-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 59, 59, 0.3);
        }

        /* RESPONSE MESSAGES */
        #msg {
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            min-height: 20px;
        }

        /* ANIMATION RENDERS */
        .info-card, .contact-box {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .contact-box { animation-delay: 0.1s; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* SCREEN RESPONSIVENESS */
        @media (max-width: 992px) {
            .main-container { grid-template-columns: 1fr; margin-top: 30px; }
            .hero { height: 20vh; }
            .hero h1 { font-size: 32px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>

<body>

<div class="hero">
    <h1>Contact Our Team</h1>
    <p>Got questions or feedback? We would love to hear from you.</p>
</div>

<div class="main-container">

    <div class="info-sidebar">
        <div class="info-card">
            <div class="icon-box">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            </div>
            <div class="info-details">
                <h3>Call Support</h3>
                <p>+977-1-4444444<br>+977-9801144444</p>
            </div>
        </div>

        <div class="info-card">
            <div class="icon-box">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            </div>
            <div class="info-details">
                <h3>Our Head Office</h3>
                <p>Three Star Complex, 3rd Floor<br>Pulchowk, Lalitpur, Nepal</p>
            </div>
        </div>

        <div class="info-card">
            <div class="icon-box">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="info-details">
                <h3>Operating Hours</h3>
                <p>Mon - Sun: 08:00 AM - 10:00 PM<br>Holidays: 09:00 AM - 08:00 PM</p>
            </div>
        </div>
    </div>

    <div class="contact-box">
        <h2>Get in Touch</h2>
        <p class="subtitle">Fill out the brief system setup sequence and we'll reply shortly.</p>

        <div class="form-row">
            <div class="form-group no-margin">
                <label>Your Name</label>
                <input type="text" id="name" placeholder="John Doe">
            </div>
            <div class="form-group no-margin">
                <label>Your Email</label>
                <input type="email" id="email" placeholder="john@example.com">
            </div>
        </div>

        <div class="form-group">
            <label>Subject</label>
            <input type="text" id="subject" placeholder="How can we assist you?">
        </div>

        <div class="form-group">
            <label>Your Message</label>
            <textarea id="message" rows="5" placeholder="Type your detailed message here..."></textarea>
        </div>

        <div class="btn-container">
            <button onclick="sendMessage()">
                <span>Send Message</span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
            <p id="msg"></p>
        </div>
    </div>

</div>

<script>
function sendMessage(){
    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let subject = document.getElementById("subject").value.trim();
    let message = document.getElementById("message").value.trim();

    const msgEl = document.getElementById("msg");

    if(name === "" || email === "" || subject === "" || message === ""){
        msgEl.style.color = "#e53e3e";
        msgEl.innerText = "Please fill all fields!";
        return;
    }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("email", email);
    formData.append("subject", subject);
    formData.append("message", message);

    msgEl.style.color = "#4a5568";
    msgEl.innerText = "Sending your message...";

    fetch("save_message.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            msgEl.style.color = "#38a169";
            msgEl.innerText = "Message sent successfully ✔";

            document.getElementById("name").value = "";
            document.getElementById("email").value = "";
            document.getElementById("subject").value = "";
            document.getElementById("message").value = "";
        } else {
            msgEl.style.color = "#e53e3e";
            msgEl.innerText = data.error || "Something went wrong. Try again.";
        }
    })
    .catch(err => {
        msgEl.style.color = "#e53e3e";
        msgEl.innerText = "Server error. Try again later.";
        console.error(err);
    });
}
</script>

</body>
</html>