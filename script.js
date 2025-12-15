const fireplace = document.getElementById("fireplace");
const toggleFireBtn = document.getElementById("toggleFire");
const toggleSnowBtn = document.getElementById("toggleSnow");
const toggleCandlesBtn = document.getElementById("toggleCandles");
const toggleTreeLightsBtn = document.getElementById("toggleTreeLights");
const candleRow = document.getElementById("candleRow");
const treeContainer = document.getElementById("treeContainer");
const playBtn = document.getElementById("playBtn");

const fireStatus = document.getElementById("fireStatus");
const candleStatus = document.getElementById("candleStatus");
const snowStatus = document.getElementById("snowStatus");
const treeStatus = document.getElementById("treeStatus");

function toggleFire() {
    fireplace.classList.toggle("is-off");
    fireStatus.textContent = fireplace.classList.contains("is-off") ? "OFF" : "ON";
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
}

fireplace.addEventListener("click", toggleFire);
toggleFireBtn.addEventListener("click", toggleFire);
toggleSnowBtn.addEventListener("click", toggleSnow);
toggleCandlesBtn.addEventListener("click", toggleCandles);
toggleTreeLightsBtn.addEventListener("click", toggleTreeLights);
candleRow.addEventListener("click", toggleCandles);
treeContainer.addEventListener("click", toggleTreeLights);

let isPlaying = false;
playBtn.addEventListener("click", () => {
    isPlaying = !isPlaying;
    playBtn.textContent = isPlaying ? "⏸️" : "▶️";
});

document.querySelectorAll(".sound-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        btn.classList.toggle("active");
    });
});

document.getElementById("cozyMode").addEventListener("click", () => {
    if (!fireplace.classList.contains("is-off")) toggleFire();
    if (!candleRow.classList.contains("candles-off")) toggleCandles();
    if (!treeContainer.classList.contains("lights-off")) toggleTreeLights();
    if (!document.body.classList.contains("no-snow")) toggleSnow();
});

document.getElementById("partyMode").addEventListener("click", () => {
    if (fireplace.classList.contains("is-off")) toggleFire();
    if (candleRow.classList.contains("candles-off")) toggleCandles();
    if (treeContainer.classList.contains("lights-off")) toggleTreeLights();
    if (document.body.classList.contains("no-snow")) toggleSnow();
});

document.getElementById("focusMode").addEventListener("click", () => {
    if (!document.body.classList.contains("no-snow")) toggleSnow();
});

document.getElementById("resetAll").addEventListener("click", () => {
    if (fireplace.classList.contains("is-off")) toggleFire();
    if (candleRow.classList.contains("candles-off")) toggleCandles();
    if (treeContainer.classList.contains("lights-off")) toggleTreeLights();
    if (document.body.classList.contains("no-snow")) toggleSnow();
});

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