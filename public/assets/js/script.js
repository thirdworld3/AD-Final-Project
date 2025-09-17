// The Forbidden Codex - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    var links = document.querySelectorAll('a[href^="#"]');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }

    // Glitch effect for hero title
    var glitchElement = document.querySelector('.glitch');
    if (glitchElement) {
        setInterval(function() {
            glitchElement.classList.add('glitch-active');
            setTimeout(function() {
                glitchElement.classList.remove('glitch-active');
            }, 200);
        }, 3000);
    }

    // Newsletter form submission
    var newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var email = this.querySelector('input[type="email"]').value;
            if (email) {
                alert('Thank you for subscribing to the Forbidden Codex newsletter!');
                this.reset();
            }
        });
    }

    // Add mystical hover effects to cards
    var cards = document.querySelectorAll('.learn-card, .offer-card');
    for (var j = 0; j < cards.length; j++) {
        cards[j].addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 10px 30px rgba(157, 153, 153, 0.3)';
        });
        
        cards[j].addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.3)';
        });
    }

    // Scroll indicator animation
    var scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        window.addEventListener('scroll', function() {
            var scrolled = window.pageYOffset;
            var rate = scrolled * -0.5;
            scrollIndicator.style.transform = 'translateY(' + rate + 'px)';
            
            if (scrolled > 100) {
                scrollIndicator.style.opacity = '0';
            } else {
                scrollIndicator.style.opacity = '1';
            }
        });
    }

    // Form validation for auth forms
    var authForms = document.querySelectorAll('.auth-form');
    for (var k = 0; k < authForms.length; k++) {
        authForms[k].addEventListener('submit', function(e) {
            var password = this.querySelector('input[name="password"]');
            var confirmPassword = this.querySelector('input[name="confirm_password"]');
            
            if (confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password && password.value.length < 3) {
                e.preventDefault();
                alert('Password must be at least 3 characters long!');
                return false;
            }
        });
    }

    // Update cart count in navigation
    function updateCartCount() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'cart/simple_count.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var count = xhr.responseText.trim();
                var cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = count;
                }
            }
        };
        xhr.send();
    }

    // Show notification messages
    function showNotification(message, type) {
        if (!type) type = 'info';
        
        var notification = document.createElement('div');
        notification.className = 'notification notification-' + type;
        notification.textContent = message;
        
        var bgColor = '#2196F3';
        if (type === 'success') bgColor = '#4CAF50';
        if (type === 'error') bgColor = '#f44336';
        
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 20px';
        notification.style.background = bgColor;
        notification.style.color = 'white';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = '10000';
        
        document.body.appendChild(notification);
        
        setTimeout(function() {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 3000);
    }

    // Shopping cart functionality
    window.addToCart = function(productId, quantity) {
        if (!quantity) quantity = 1;
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'cart/simple_add.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText.trim();
                
                if (response === 'SUCCESS') {
                    updateCartCount();
                    showNotification('Product added to cart!', 'success');
                } else if (response === 'LOGIN_REQUIRED') {
                    showNotification('Please log in to add items to cart', 'error');
                } else if (response === 'PRODUCT_NOT_FOUND') {
                    showNotification('Product not found', 'error');
                } else if (response === 'INSUFFICIENT_STOCK') {
                    showNotification('Insufficient stock available', 'error');
                } else if (response === 'EXCEEDS_STOCK') {
                    showNotification('Cannot add more items than available stock', 'error');
                } else {
                    showNotification('Failed to add product to cart', 'error');
                }
            }
        };
        
        var requestData = 'product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity);
        xhr.send(requestData);
    };

    // Initialize cart count on page load
    if (document.querySelector('.cart-count')) {
        updateCartCount();
    }
});

// Add CSS animations
var style = document.createElement('style');
style.textContent = '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } } .glitch-active { animation: glitch 0.3s; } @keyframes glitch { 0% { transform: translate(0); } 20% { transform: translate(-2px, 2px); } 40% { transform: translate(-2px, -2px); } 60% { transform: translate(2px, 2px); } 80% { transform: translate(2px, -2px); } 100% { transform: translate(0); } }';
document.head.appendChild(style);
