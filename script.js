const fireplace = document.getElementById("fireplace");
const toggleFireBtn = document.getElementById("toggleFire");
const toggleSnowBtn = document.getElementById("toggleSnow");
const toggleCandlesBtn = document.getElementById("toggleCandles");
const toggleTreeLightsBtn = document.getElementById("toggleTreeLights");
const toggleThemeBtn = document.getElementById("toggleTheme");
const snowIntensityBtn = document.getElementById("snowIntensity");
const candleRow = document.getElementById("candleRow");
const treeContainer = document.getElementById("treeContainer");
const playBtn = document.getElementById("playBtn");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

const fireStatus = document.getElementById("fireStatus");
const candleStatus = document.getElementById("candleStatus");
const snowStatus = document.getElementById("snowStatus");
const treeStatus = document.getElementById("treeStatus");

// ============================================
// CORE TOGGLE FUNCTIONS
// ============================================

function toggleFire() {
    fireplace.classList.toggle("is-off");
    fireStatus.textContent = fireplace.classList.contains("is-off") ? "OFF" : "ON";
    showToast(fireplace.classList.contains("is-off") ? "🔥 Fire extinguished" : "🔥 Fire is crackling!");
}

function toggleSnow() {
    document.body.classList.toggle("no-snow");
    snowStatus.textContent = document.body.classList.contains("no-snow") ? "OFF" : "ON";
}

function toggleCandles() {
    candleRow.classList.toggle("candles-off");
    candleStatus.textContent = candleRow.classList.contains("candles-off") ? "OFF" : "ON";
}

function toggleTreeLights() {
    treeContainer.classList.toggle("lights-off");
    treeStatus.textContent = treeContainer.classList.contains("lights-off") ? "OFF" : "ON";
    if (!treeContainer.classList.contains("lights-off")) {
        createSparkles(treeContainer);
    }
}

fireplace.addEventListener("click", toggleFire);
toggleFireBtn.addEventListener("click", toggleFire);
toggleSnowBtn.addEventListener("click", toggleSnow);
toggleCandlesBtn.addEventListener("click", toggleCandles);
toggleTreeLightsBtn.addEventListener("click", toggleTreeLights);
candleRow.addEventListener("click", toggleCandles);
treeContainer.addEventListener("click", toggleTreeLights);

// ============================================
// THEME SWITCHER
// ============================================

const themes = ['', 'theme-warm', 'theme-cool', 'theme-nature', 'theme-purple'];
let currentTheme = 0;

toggleThemeBtn.addEventListener("click", () => {
    document.body.classList.remove(themes[currentTheme]);
    currentTheme = (currentTheme + 1) % themes.length;
    if (themes[currentTheme]) {
        document.body.classList.add(themes[currentTheme]);
    }
    const themeNames = ['Default', 'Warm Red', 'Cool Blue', 'Nature Green', 'Royal Purple'];
    showToast(`🎨 Theme: ${themeNames[currentTheme]}`);
});

// ============================================
// SNOW INTENSITY CONTROL
// ============================================

const snowLevels = ['', 'snow-light', 'snow-heavy', 'snow-blizzard'];
let currentSnowLevel = 0;

snowIntensityBtn.addEventListener("click", () => {
    document.body.classList.remove(snowLevels[currentSnowLevel]);
    currentSnowLevel = (currentSnowLevel + 1) % snowLevels.length;
    if (snowLevels[currentSnowLevel]) {
        document.body.classList.add(snowLevels[currentSnowLevel]);
    }
    const snowNames = ['Normal', 'Light', 'Heavy', 'Blizzard'];
    showToast(`🌨️ Snow: ${snowNames[currentSnowLevel]}`);
});

// ============================================
// MUSIC PLAYER WITH PLAYLIST
// ============================================

const playlist = [
    { name: "Winter Wonderland", artist: "Holiday Classics", emoji: "🎄" },
    { name: "Jingle Bells", artist: "Christmas Orchestra", emoji: "🔔" },
    { name: "Silent Night", artist: "Peaceful Melodies", emoji: "🌙" },
    { name: "Let It Snow", artist: "Winter Jazz", emoji: "❄️" },
    { name: "Deck the Halls", artist: "Festive Band", emoji: "🎉" }
];

let currentTrack = 0;
let isPlaying = false;
let progress = 0;
let progressInterval;

function updateTrackDisplay() {
    document.getElementById("trackName").textContent = playlist[currentTrack].name;
    document.getElementById("trackArtist").textContent = playlist[currentTrack].artist;
    document.getElementById("albumArt").textContent = playlist[currentTrack].emoji;
}

function startProgress() {
    progressInterval = setInterval(() => {
        progress += 0.5;
        if (progress >= 100) {
            nextTrack();
        }
        document.getElementById("progressFill").style.width = progress + "%";
    }, 150);
}

function stopProgress() {
    clearInterval(progressInterval);
}

function nextTrack() {
    currentTrack = (currentTrack + 1) % playlist.length;
    progress = 0;
    updateTrackDisplay();
}

function prevTrack() {
    currentTrack = (currentTrack - 1 + playlist.length) % playlist.length;
    progress = 0;
    updateTrackDisplay();
}

playBtn.addEventListener("click", () => {
    isPlaying = !isPlaying;
    playBtn.textContent = isPlaying ? "⏸️" : "▶️";
    document.getElementById("albumArt").classList.toggle("playing", isPlaying);
    if (isPlaying) {
        startProgress();
    } else {
        stopProgress();
    }
});

prevBtn.addEventListener("click", () => {
    prevTrack();
    if (isPlaying) {
        stopProgress();
        startProgress();
    }
});

nextBtn.addEventListener("click", () => {
    nextTrack();
    if (isPlaying) {
        stopProgress();
        startProgress();
    }
});

// ============================================
// HOLIDAY GREETINGS
// ============================================

const greetings = [
    "Wishing you a magical holiday season filled with joy and warmth! ✨",
    "May your days be merry and bright! 🌟",
    "Sending warm wishes for a cozy winter! 🔥",
    "May the spirit of the season fill your heart! ❤️",
    "Here's to hot cocoa and happy memories! ☕",
    "Wishing you peace, love, and snowflakes! ❄️",
    "May your holidays sparkle with joy! ✨",
    "Cheers to family, friends, and festive fun! 🎉",
    "Wishing you warmth in every winter moment! 🧣",
    "May your heart be light and your sweaters ugly! 🎄"
];

document.getElementById("newGreeting").addEventListener("click", () => {
    const greetingEl = document.getElementById("greetingText");
    greetingEl.style.opacity = "0";
    setTimeout(() => {
        const randomGreeting = greetings[Math.floor(Math.random() * greetings.length)];
        greetingEl.textContent = randomGreeting;
        greetingEl.style.opacity = "1";
    }, 300);
});

// ============================================
// WISHLIST
// ============================================

const wishlist = [];
const wishlistEl = document.getElementById("wishlist");
const wishInput = document.getElementById("wishInput");

function renderWishlist() {
    wishlistEl.innerHTML = wishlist.map((wish, index) => `
        <li>
            <span>🎁 ${wish}</span>
            <span class="wish-delete" data-index="${index}">❌</span>
        </li>
    `).join('');
}

document.getElementById("addWish").addEventListener("click", addWish);
wishInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") addWish();
});

function addWish() {
    const wish = wishInput.value.trim();
    if (wish && wishlist.length < 10) {
        wishlist.push(wish);
        wishInput.value = "";
        renderWishlist();
        showToast("🎁 Wish added!");
    } else if (wishlist.length >= 10) {
        showToast("📋 Wishlist is full!", "info");
    }
}

wishlistEl.addEventListener("click", (e) => {
    if (e.target.classList.contains("wish-delete")) {
        const index = parseInt(e.target.dataset.index);
        wishlist.splice(index, 1);
        renderWishlist();
    }
});

// ============================================
// DAILY WINTER TIPS
// ============================================

const winterTips = [
    "Layer up! Wearing multiple thin layers keeps you warmer than one thick layer. 🧥",
    "Keep a cozy blanket nearby for instant warmth! 🛋️",
    "Hot drinks warm you from the inside out! Try some cocoa! ☕",
    "Don't forget to moisturize - winter air is dry! 💧",
    "Wool socks are your best friend in cold weather! 🧦",
    "A warm hat prevents 40% of body heat loss! 🎩",
    "Take breaks to enjoy the beautiful snow outside! ❄️",
    "Candlelight creates the coziest atmosphere! 🕯️",
    "Reading by the fire is peak winter comfort! 📚",
    "Share warmth - hug someone you love! 🤗"
];

document.getElementById("newTip").addEventListener("click", () => {
    const tipEl = document.getElementById("dailyTip");
    tipEl.style.opacity = "0";
    setTimeout(() => {
        const randomTip = winterTips[Math.floor(Math.random() * winterTips.length)];
        tipEl.textContent = randomTip;
        tipEl.style.opacity = "1";
    }, 300);
});

// ============================================
// SANTA TRACKER
// ============================================

const santaLocations = [
    "North Pole Workshop",
    "Reindeer Stables",
    "Gift Wrapping Station",
    "Sleigh Maintenance",
    "Elf Training Center",
    "Cookie Testing Lab",
    "Naughty/Nice List Office"
];

function updateSantaTracker() {
    const now = new Date();
    const christmas = new Date(now.getFullYear(), 11, 25);
    if (now > christmas) {
        christmas.setFullYear(christmas.getFullYear() + 1);
    }
    
    const totalDays = 365;
    const daysUntil = Math.floor((christmas - now) / (1000 * 60 * 60 * 24));
    const progress = ((totalDays - daysUntil) / totalDays) * 100;
    
    document.getElementById("santaProgress").style.width = Math.min(progress, 100) + "%";
    
    // Randomly update location every few seconds
    const locationIndex = Math.floor((Date.now() / 10000) % santaLocations.length);
    document.getElementById("santaLocation").textContent = santaLocations[locationIndex];
}

setInterval(updateSantaTracker, 5000);
updateSantaTracker();

// ============================================
// MOOD SELECTOR
// ============================================

const moodMessages = {
    cozy: "🛋️ Curl up with a warm blanket and enjoy!",
    festive: "🎊 Let's celebrate the season!",
    peaceful: "🕊️ Take a deep breath and relax...",
    excited: "⚡ The holidays are almost here!"
};

document.querySelectorAll(".mood-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".mood-btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        const mood = btn.dataset.mood;
        document.getElementById("moodDisplay").textContent = moodMessages[mood];
        
        if (mood === "festive") {
            createConfetti();
        }
    });
});

// ============================================
// SOUND BUTTONS
// ============================================

document.querySelectorAll(".sound-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        btn.classList.toggle("active");
    });
});

// ============================================
// QUICK ACTIONS
// ============================================

document.getElementById("cozyMode").addEventListener("click", () => {
    if (!fireplace.classList.contains("is-off")) toggleFire();
    if (!candleRow.classList.contains("candles-off")) toggleCandles();
    if (!treeContainer.classList.contains("lights-off")) toggleTreeLights();
    if (!document.body.classList.contains("no-snow")) toggleSnow();
    showToast("🌙 Cozy mode activated - everything dimmed");
});

document.getElementById("partyMode").addEventListener("click", () => {
    if (fireplace.classList.contains("is-off")) toggleFire();
    if (candleRow.classList.contains("candles-off")) toggleCandles();
    if (treeContainer.classList.contains("lights-off")) toggleTreeLights();
    if (document.body.classList.contains("no-snow")) toggleSnow();
    createConfetti();
    showToast("🎉 Party mode activated!");
});

document.getElementById("focusMode").addEventListener("click", () => {
    if (!document.body.classList.contains("no-snow")) toggleSnow();
    showToast("🧘 Focus mode - snow disabled for concentration");
});

document.getElementById("resetAll").addEventListener("click", () => {
    if (fireplace.classList.contains("is-off")) toggleFire();
    if (candleRow.classList.contains("candles-off")) toggleCandles();
    if (treeContainer.classList.contains("lights-off")) toggleTreeLights();
    if (document.body.classList.contains("no-snow")) toggleSnow();
    
    // Reset themes and snow levels
    document.body.classList.remove(...themes, ...snowLevels);
    currentTheme = 0;
    currentSnowLevel = 0;
    
    showToast("🔄 Everything reset to default!");
});

// ============================================
// COUNTDOWN
// ============================================

function updateCountdown() {
    const now = new Date();
    const christmas = new Date(now.getFullYear(), 11, 25);
    if (now > christmas) {
        christmas.setFullYear(christmas.getFullYear() + 1);
    }

    const diff = christmas - now;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    document.getElementById("days").textContent = String(days).padStart(2, "0");
    document.getElementById("hours").textContent = String(hours).padStart(2, "0");
    document.getElementById("minutes").textContent = String(minutes).padStart(2, "0");
    document.getElementById("seconds").textContent = String(seconds).padStart(2, "0");
}

updateCountdown();
setInterval(updateCountdown, 1000);

// ============================================
// VISUAL EFFECTS
// ============================================

function createConfetti() {
    const colors = ['#f97316', '#ef4444', '#22c55e', '#3b82f6', '#a855f7', '#fbbf24'];
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 3000);
        }, i * 30);
    }
}

function createSparkles(container) {
    for (let i = 0; i < 10; i++) {
        setTimeout(() => {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.left = Math.random() * 100 + '%';
            sparkle.style.top = Math.random() * 100 + '%';
            container.appendChild(sparkle);
            setTimeout(() => sparkle.remove(), 1000);
        }, i * 100);
    }
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================

function showToast(message, type = 'success') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after delay
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 2500);
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

document.addEventListener('keydown', (e) => {
    if (e.target.tagName === 'INPUT') return;
    
    switch(e.key.toLowerCase()) {
        case 'f': toggleFire(); break;
        case 's': toggleSnow(); break;
        case 'c': toggleCandles(); break;
        case 't': toggleTreeLights(); break;
        case 'p': playBtn.click(); break;
        case 'arrowright': nextBtn.click(); break;
        case 'arrowleft': prevBtn.click(); break;
    }
});

// Show keyboard shortcuts hint
console.log(`
🎄 Keyboard Shortcuts:
F - Toggle Fire
S - Toggle Snow  
C - Toggle Candles
T - Toggle Tree Lights
P - Play/Pause Music
← → - Previous/Next Track
`);

// Welcome message
setTimeout(() => {
    showToast("❄️ Welcome to Winter Wonderland!", "info");
}, 1000);