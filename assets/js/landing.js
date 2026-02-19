/* ========================================
   FraudShield - Landing Page Scripts
   ======================================== */

// ---- Particle Canvas Background ----
(function() {
    var canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var particles = [];
    var mouseX = 0, mouseY = 0;

    function resize() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    canvas.addEventListener('mousemove', function(e) {
        var rect = canvas.getBoundingClientRect();
        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
    });

    function Particle() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.vx = (Math.random() - 0.5) * 0.5;
        this.vy = (Math.random() - 0.5) * 0.5;
        this.radius = Math.random() * 2 + 0.5;
        this.alpha = Math.random() * 0.5 + 0.1;
    }

    for (var i = 0; i < 80; i++) {
        particles.push(new Particle());
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            p.x += p.vx;
            p.y += p.vy;
            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(217, 119, 6, ' + p.alpha + ')';
            ctx.fill();

            // Draw lines between nearby particles
            for (var j = i + 1; j < particles.length; j++) {
                var p2 = particles[j];
                var dx = p.x - p2.x;
                var dy = p.y - p2.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 120) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.strokeStyle = 'rgba(217, 119, 6, ' + (0.1 * (1 - dist / 120)) + ')';
                    ctx.stroke();
                }
            }

            // Mouse interaction
            var mdx = p.x - mouseX;
            var mdy = p.y - mouseY;
            var mdist = Math.sqrt(mdx * mdx + mdy * mdy);
            if (mdist < 150) {
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(mouseX, mouseY);
                ctx.strokeStyle = 'rgba(217, 119, 6, ' + (0.15 * (1 - mdist / 150)) + ')';
                ctx.stroke();
            }
        }

        requestAnimationFrame(animate);
    }

    animate();
})();

// ---- FAQ Toggle ----
function toggleFaq(el) {
    var item = el.parentElement;
    var wasActive = item.classList.contains('active');
    // Close all
    document.querySelectorAll('.faq-item').forEach(function(faq) {
        faq.classList.remove('active');
    });
    // Toggle clicked
    if (!wasActive) {
        item.classList.add('active');
    }
}

// ---- Stat Counter Animation ----
(function() {
    var counters = document.querySelectorAll('.counter-value');
    if (!counters.length) return;

    var observed = false;
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting && !observed) {
                observed = true;
                counters.forEach(function(counter) {
                    var target = parseInt(counter.getAttribute('data-target'));
                    var suffix = counter.getAttribute('data-suffix') || '';
                    var duration = 2000;
                    var start = 0;
                    var startTime = null;

                    function step(timestamp) {
                        if (!startTime) startTime = timestamp;
                        var progress = Math.min((timestamp - startTime) / duration, 1);
                        var eased = 1 - Math.pow(1 - progress, 3); // ease out cubic
                        var current = Math.floor(eased * target);
                        counter.textContent = current.toLocaleString() + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(step);
                        } else {
                            counter.textContent = target.toLocaleString() + suffix;
                        }
                    }

                    requestAnimationFrame(step);
                });
            }
        });
    }, { threshold: 0.3 });

    var statsSection = document.querySelector('.stats-strip');
    if (statsSection) observer.observe(statsSection);
})();

// ---- Smooth Scroll for Nav Links ----
document.querySelectorAll('a[href^="#"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        var targetId = this.getAttribute('href');
        if (targetId === '#') return;
        var target = document.querySelector(targetId);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ---- Mobile Nav Toggle ----
function toggleNav() {
    var navLinks = document.querySelector('.nav-links');
    if (navLinks) navLinks.classList.toggle('open');
}

// ---- Navbar Scroll Effect ----
(function() {
    var navbar = document.querySelector('.navbar');
    if (!navbar) return;
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
})();
