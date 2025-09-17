<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Forbidden Codex of Conduct</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="nav-logo">
                    <h2 class="logo-text">The Forbidden Codex</h2>
                </div>
                <div class="nav-buttons">
                    <?php
                    if (isset($_SESSION['user_id'])):
                    ?>
                        <a href="public/account/index.php" class="btn btn-secondary">Account</a>
                        <a href="public/logout.php" class="btn btn-login">Logout</a>
                    <?php else: ?>
                        <a href="public/signup.php" class="btn btn-signin">Sign Up</a>
                        <a href="public/login.php" class="btn btn-login">Log In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <span class="glitch" data-text="THE FORBIDDEN">THE FORBIDDEN</span>
                    <span class="codex-text">CODEX</span>
                    <span class="conduct-text">OF CONDUCT</span>
                </h1>
                <p class="hero-subtitle">Unlock the ancient secrets of digital mastery. Enter a realm where code becomes art and conduct becomes legend.</p>
                <div class="hero-buttons">
                    <a href="#about" class="btn btn-primary">Learn the Ways</a>
                    <a href="#offers" class="btn btn-secondary">Our Offerings</a>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Learn the Ways / About Section -->
    <section id="about" class="learn-hero">
        <div class="learn-overlay"></div>
        <div class="container">
            <div class="learn-content">
                <h2 class="learn-title">Learn the Ways</h2>
                <p class="learn-subtitle">Master timeless principles that turn code into craft. Explore practical rituals, disciplined patterns, and battle‑tested workflows used by expert practitioners.</p>

                <div class="learn-features">
                    <div class="learn-card">
                        <h3 class="learn-card-title">Craftsmanship</h3>
                        <p class="learn-card-text">Readable, resilient, and maintainable systems through clarity, testing, and iteration.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Flow & Focus</h3>
                        <p class="learn-card-text">Rituals for deep work: task slicing, feedback loops, and effective reviews.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Architecture</h3>
                        <p class="learn-card-text">Design with intent—boundaries, contracts, and patterns that scale gracefully.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Delivery</h3>
                        <p class="learn-card-text">Automate, observe, and improve. CI/CD, telemetry, and operational excellence.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Performance</h3>
                        <p class="learn-card-text">Profile, measure, and tune. Ship fast experiences with intent and evidence.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Accessibility</h3>
                        <p class="learn-card-text">Design for everyone: semantics, keyboard flows, contrast, and assistive tech.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Security</h3>
                        <p class="learn-card-text">Threat modeling, hardening, and secure defaults woven through every layer.</p>
                    </div>
                    <div class="learn-card">
                        <h3 class="learn-card-title">Collaboration</h3>
                        <p class="learn-card-text">Clear communication, code reviews, and shared ownership that scales teams.</p>
                    </div>
                </div>

                
            </div>
        </div>
    </section>

    <!-- Our Offers Section -->
    <section id="offers" class="offers">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Forbidden Offerings</h2>
                <p class="section-subtitle">Choose your path to digital enlightenment</p>
            </div>
            
            <div class="offers-grid">
                <div class="offer-card">
                    <div class="offer-icon">
                        <div class="mystical-symbol"></div>
                    </div>
                    <h3 class="offer-title">The Lightning Protocol</h3>
                    <p class="offer-description">Harness the power of rapid development and deployment. Speed beyond mortal comprehension.</p>
                    <ul class="offer-features">
                        <li> Ultra-fast development cycles</li>
                        <li> Real-time collaboration</li>
                        <li> Battle-tested frameworks</li>
                    </ul>
                    <div class="offer-price">From $299/month</div>
                    <a href="#contact" class="btn btn-offer">Begin the Ritual</a>
                </div>

                <div class="offer-card featured">
                    <div class="offer-badge">Most Forbidden</div>
                    <div class="offer-icon">
                        <div class="mystical-symbol"></div>
                    </div>
                    <h3 class="offer-title">The Oracle's Vision</h3>
                    <p class="offer-description">Peer into the future of your digital empire. See what others cannot perceive.</p>
                    <ul class="offer-features">
                        <li> Advanced analytics & insights</li>
                        <li> Predictive algorithms</li>
                        <li> Strategic consultation</li>
                    </ul>
                    <div class="offer-price">From $599/month</div>
                    <a href="#contact" class="btn btn-offer">Gaze into the Future</a>
                </div>

                <div class="offer-card">
                    <div class="offer-icon">
                        <div class="mystical-symbol"></div>
                    </div>
                    <h3 class="offer-title">The Guardian's Shield</h3>
                    <p class="offer-description">Protect your digital realm with impenetrable security. Let no threat breach your defenses.</p>
                    <ul class="offer-features">
                        <li> Military-grade security</li>
                        <li> Advanced encryption</li>
                        <li> 24/7 monitoring</li>
                    </ul>
                    <div class="offer-price">From $399/month</div>
                    <a href="#contact" class="btn btn-offer">Raise the Shield</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">The Forbidden Codex</h3>
                    <p class="footer-description">Where ancient wisdom meets modern technology. Join the ranks of digital mystics.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"></a>
                        <a href="#" class="social-link"></a>
                        <a href="#" class="social-link"></a>
                        <a href="#" class="social-link"></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Sacred Paths</h4>
                    <ul class="footer-links">
                        <li><a href="#offers">Our Offerings</a></li>
                        <li><a href="#about">The Ancient Ways</a></li>
                        <li><a href="#contact">Summon Us</a></li>
                        <li><a href="#testimonials">Mystic Testimonials</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Contact the Keepers</h4>
                    <div class="contact-info">
                        <p> contact@forbiddencodex.com</p>
                        <p> +1 (555) 123-MYSTIC</p>
                        <p> The Digital Citadel, Code Valley</p>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Newsletter of the Ancients</h4>
                    <p>Receive forbidden knowledge directly to your scroll.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Enter your mystical email" required>
                        <button type="submit" class="btn btn-newsletter">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 The Forbidden Codex of Conduct. All rights reserved to the digital mystics.</p>
                    <div class="footer-legal">
                        <a href="#privacy">Privacy Ritual</a>
                        <a href="#terms">Terms of Conduct</a>
                        <a href="#cookies">Cookie Spells</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="public/assets/js/script.js"></script>
</body>
</html>
