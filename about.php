<?php
// about.php - NOVEL NEST About Us Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - NOVEL NEST</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background: #f8f8fa; }
        .about-container { max-width: 900px; margin: 60px auto; background: #fff; border-radius: 20px; box-shadow: 0 4px 24px #0002; padding: 40px; text-align: center; }
        #designers {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
        }
        .designer-card {
            display: flex;
            flex-direction: row;
            width: 800px;
            height: 420px;
            margin: 32px 36px;
            background: #18181b;
            border-radius: 22px;
            box-shadow: 0 8px 32px #0003;
            color: #ff3366;
            border: 3px solid #ff3366;
            transition: box-shadow 0.3s;
            cursor: default;
        }
        .designer-card-front, .designer-card-back {
            position: static;
            width: 50%;
            height: 100%;
            backface-visibility: visible;
            border-radius: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #18181b;
            color: #ff3366;
            border: none;
        }
        .designer-card-front {
            border-right: 2px solid #ff3366;
            border-radius: 22px 0 0 22px;
        }
        .designer-card-back {
            border-radius: 0 22px 22px 0;
        }
        .designer-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: #fff3;
            margin-bottom: 22px;
            object-fit: cover;
            border: 4px solid #fff;
        }
        .designer-name {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .designer-role {
            font-size: 1.2rem;
            margin-bottom: 16px;
            opacity: 0.9;
        }
        .designer-desc {
            font-size: 1.08rem;
            padding: 0 22px;
            color: #fff;
        }
        .designer-links {
            margin-top: 18px;
            display: flex;
            gap: 18px;
            justify-content: center;
        }
        .designer-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #222;
            color: #fff;
            font-size: 1.5rem;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .designer-link.github { background: #18181b; color: #ffeb3b; }
        .designer-link.youtube { background: #c4302b; color: #fff; }
        .designer-link.facebook { background: #1877f3; color: #fff; }
        .designer-link:hover { filter: brightness(1.2); }
        @media (max-width: 900px) {
            .designer-card { width: 98vw; height: 420px; margin: 18px 0; }
        }
    </style>
</head>
<body>
    <div style="background:#222;padding:0 0 2px 0;">
        <div style="max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:18px 32px 10px 32px;">
            <div style="font-size:2.1rem;font-weight:bold;color:#fff;letter-spacing:2px;font-family:sans-serif;text-shadow:0 2px 8px #0002;">
                NOVEL NEST
            </div>
            <div>
                <a href="index.php" style="background:#ff3366;color:#fff;font-weight:600;padding:8px 22px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px #0001;transition:background .2s;">Home</a>
            </div>
        </div>
    </div>
    <div class="about-container" style="background:#222; color:#ff3366;">
        <h2 style="color:#ff3366;">About the Designers</h2>
        <div id="designers">
            <div class="designer-card">
                <div class="designer-card-front">
                    <img src="assets/about/jubair.jpg" alt="Jubair Ahammad Akter" class="designer-photo">
                    <div class="designer-name">Jubair Ahammad Akter</div>
                    <div class="designer-role">CSEDU, University of Dhaka</div>
                    <div class="designer-desc">Click to view</div>
                    <div class="designer-links">
                        <a href="https://facebook.com/ahmedsativa.adib" class="designer-link facebook" target="_blank" title="Facebook"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.406.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.406 24 22.674V1.326C24 .592 23.406 0 22.675 0"/></svg></a>
                        <a href="https://github.com/Jubair-Adib" class="designer-link github" target="_blank" title="GitHub"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.084-.729.084-.729 1.205.084 1.84 1.236 1.84 1.236 1.07 1.834 2.809 1.304 3.495.997.108-.775.418-1.305.762-1.605-2.665-.305-5.466-1.334-5.466-5.931 0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.553 3.297-1.23 3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.803 5.624-5.475 5.921.43.372.823 1.102.823 2.222 0 1.606-.014 2.898-.014 3.293 0 .322.216.694.825.576C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg></a>
                        <a href="https://youtube.com/@jubairahammadakter1666" class="designer-link youtube" target="_blank" title="YouTube"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a2.994 2.994 0 0 0-2.112-2.112C19.458 3.5 12 3.5 12 3.5s-7.458 0-9.386.574A2.994 2.994 0 0 0 .502 6.186C0 8.114 0 12 0 12s0 3.886.502 5.814a2.994 2.994 0 0 0 2.112 2.112C4.542 20.5 12 20.5 12 20.5s7.458 0 9.386-.574a2.994 2.994 0 0 0 2.112-2.112C24 15.886 24 12 24 12s0-3.886-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                        <a href="https://www.linkedin.com/in/jubair-ahammad-akter-0b0aa3316/" class="designer-link linkedin" target="_blank" title="LinkedIn"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.76 0-5 2.24-5 5v14c0 2.76 2.24 5 5 5h14c2.76 0 5-2.24 5-5v-14c0-2.76-2.24-5-5-5zm-11 19h-3v-9h3v9zm-1.5-10.28c-.97 0-1.75-.79-1.75-1.75s.78-1.75 1.75-1.75 1.75.79 1.75 1.75-.78 1.75-1.75 1.75zm15.5 10.28h-3v-4.5c0-1.08-.02-2.47-1.5-2.47-1.5 0-1.73 1.17-1.73 2.39v4.58h-3v-9h2.89v1.23h.04c.4-.75 1.38-1.54 2.84-1.54 3.04 0 3.6 2 3.6 4.59v4.72z"/></svg></a>
                    </div>
                </div>
                <div class="designer-card-back">
                    <div class="designer-name">Jubair Ahammad Akter</div>
                    <div class="designer-role">Competitive Programmer & Developer</div>
                    <div class="designer-desc" style="text-align:left;">
                        <b>🎓 Academic Background</b><br>
                        University of Dhaka, B.Sc. in CSE (CSEDU)<br>
                        Notre Dame College, Dhaka<br><br>
                        <b>💻 Areas of Expertise</b><br>
                        • Competitive Programming (Algorithms, Data Structures, DP, Graphs)<br>
                        • Mathematics Olympiads & problem-solving<br>
                        • Software Development & Open Source (modular code, Git, GitHub)<br><br>
                        <b>Contact:</b><br>
                        <a href="https://mail.google.com/mail/?view=cm&to=akteradib007@gmail.com" target="_blank" style="color:#fff;text-decoration:underline;">akteradib007@gmail.com</a>
                    </div>
                    <!-- <div class="designer-links">
                        <a href="https://facebook.com/ahmedsativa.adib" class="designer-link facebook" target="_blank" title="Facebook"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.406.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.406 24 22.674V1.326C24 .592 23.406 0 22.675 0"/></svg></a>
                        <a href="https://github.com/Jubair-Adib" class="designer-link github" target="_blank" title="GitHub"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.084-.729.084-.729 1.205.084 1.84 1.236 1.84 1.236 1.07 1.834 2.809 1.304 3.495.997.108-.775.418-1.305.762-1.605-2.665-.305-5.466-1.334-5.466-5.931 0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.553 3.297-1.23 3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.803 5.624-5.475 5.921.43.372.823 1.102.823 2.222 0 1.606-.014 2.898-.014 3.293 0 .322.216.694.825.576C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg></a>
                        <a href="https://youtube.com/@jubairahammadakter1666" class="designer-link youtube" target="_blank" title="YouTube"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a2.994 2.994 0 0 0-2.112-2.112C19.458 3.5 12 3.5 12 3.5s-7.458 0-9.386.574A2.994 2.994 0 0 0 .502 6.186C0 8.114 0 12 0 12s0 3.886.502 5.814a2.994 2.994 0 0 0 2.112 2.112C4.542 20.5 12 20.5 12 20.5s7.458 0 9.386-.574a2.994 2.994 0 0 0 2.112-2.112C24 15.886 24 12 24 12s0-3.886-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                    </div> -->
                </div>
            </div>
            <div class="designer-card">
                <div class="designer-card-front">
                    <img src="assets/about/ariful.jpg" alt="Ariful Islam" class="designer-photo">
                    <div class="designer-name">Ariful Islam</div>
                    <div class="designer-role">CSEDU, University of Dhaka</div>
                    <div class="designer-desc">Click to view</div>
                    <div class="designer-links">
                        <a href="https://www.facebook.com/ariful.islam.857203" class="designer-link facebook" target="_blank" title="Facebook"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.406.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.406 24 22.674V1.326C24 .592 23.406 0 22.675 0"/></svg></a>
                        <a href="https://github.com/arif-5223" class="designer-link github" target="_blank" title="GitHub"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.416-4.042-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.084-.729.084-.729 1.205.084 1.84 1.236 1.84 1.236 1.07 1.834 2.809 1.304 3.495.997.108-.775.418-1.305.762-1.605-2.665-.305-5.466-1.334-5.466-5.931 0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.553 3.297-1.23 3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.803 5.624-5.475 5.921.43.372.823 1.102.823 2.222 0 1.606-.014 2.898-.014 3.293 0 .322.216.694.825.576C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg></a>
                        <!-- <a href="https://youtube.com/@jubairahammadakter1666" class="designer-link youtube" target="_blank" title="YouTube"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a2.994 2.994 0 0 0-2.112-2.112C19.458 3.5 12 3.5 12 3.5s-7.458 0-9.386.574A2.994 2.994 0 0 0 .502 6.186C0 8.114 0 12 0 12s0 3.886.502 5.814a2.994 2.994 0 0 0 2.112 2.112C4.542 20.5 12 20.5 12 20.5s7.458 0 9.386-.574a2.994 2.994 0 0 0 2.112-2.112C24 15.886 24 12 24 12s0-3.886-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a> -->
                    </div>
                </div>
                <div class="designer-card-back">
                    <div class="designer-name">Ariful Islam</div>
                    <div class="designer-role">Specialist & Team Player</div>
                    <div class="designer-desc" style="text-align:left;">
                        <b>🎓 Alma Mater:</b><br>
                        University of Dhaka (CSEDU)<br>
                        Rajuk Uttara Model College<br><br>
                        <b>💻 Strengths:</b><br>
                        • Academically strong (Rajuk, DU CSE)<br>
                        • Programming contests, coding groups, CSEDU events<br>
                        • Balanced: Loves cricket, team spirit, competitive mindset<br><br>
                        <b>Contact:</b><br>
                        <a href="https://mail.google.com/mail/?view=cm&to=ariful@gmail.com" target="_blank" style="color:#fff;text-decoration:underline;">arafislam0015@gmail.com</a>
                    </div>
                    <!-- <div class="designer-links">
                        <a href="https://www.facebook.com/ariful.islam.857203" class="designer-link facebook" target="_blank" title="Facebook"><svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.595 0 0 .592 0 1.326v21.348C0 23.406.595 24 1.325 24h11.495v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.797.143v3.24l-1.918.001c-1.504 0-1.797.715-1.797 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116C23.406 24 24 23.406 24 22.674V1.326C24 .592 23.406 0 22.675 0"/></svg></a>
                    </div> -->
                </div>
            </div>
        </div>
        <!-- <p style="margin-top:40px;color:#888;">Click on a card to view more about each designer.</p> -->
        <div style="margin-top:32px;">
            <a href="index.php" style="background:#ff3366;color:#fff;font-weight:600;padding:8px 22px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px #0001;transition:background .2s;">&larr; Back to Home</a>
        </div>
    </div>
    <!-- Footer -->
<div class="news-footer-bar" style="background:linear-gradient(90deg,#222 0%,#ff3366 100%);color:#fff;padding:36px 0 20px 0;box-shadow:0 -4px 24px #ff336633;text-align:center;margin-top:48px;border-radius:32px 32px 0 0;">
    <div class="footer-links" style="margin-bottom:12px;">
        <a href="index.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.15rem;transition:color .2s;">Home</a>
        <a href="contact.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.15rem;transition:color .2s;">Contact</a>
        <a href="about.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.15rem;transition:color .2s;">About Us</a>
        <a href="news.php" style="color:#ffb347;margin:0 18px;font-weight:bold;text-decoration:none;font-size:1.15rem;transition:color .2s;">News</a>
    </div>
    <div style="margin-top:8px;font-size:1rem;opacity:.85;letter-spacing:1px;">
        &copy; 2025 <span style="color:#ff3366;font-weight:bold;">NOVEL NEST</span>. All rights reserved.
    </div>
</div>
</body>
</html>
