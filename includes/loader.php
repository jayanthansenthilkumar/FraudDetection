<div class="page-loader" id="pageLoader">
    <div class="loader-content">
        <div class="shield-icon">
            <i class="ri-shield-flash-line"></i>
        </div>
        <div class="loader-bar">
            <div class="loader-progress"></div>
        </div>
        <p class="loader-text">Securing your session...</p>
    </div>
</div>

<style>
.page-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0a0e17 0%, #131a2b 50%, #0d1117 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

.page-loader.loaded {
    opacity: 0;
    visibility: hidden;
}

.loader-content {
    text-align: center;
}

.shield-icon {
    font-size: 3rem;
    color: #D97706;
    animation: pulse-shield 1.5s infinite;
    margin-bottom: 1.5rem;
}

@keyframes pulse-shield {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: 0.7; }
}

.loader-bar {
    width: 200px;
    height: 3px;
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
    overflow: hidden;
    margin: 0 auto 1rem;
}

.loader-progress {
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, #D97706, #F59E0B);
    border-radius: 3px;
    animation: load-progress 1.5s ease-in-out forwards;
}

@keyframes load-progress {
    0% { width: 0%; }
    100% { width: 100%; }
}

.loader-text {
    color: rgba(255,255,255,0.5);
    font-size: 0.85rem;
    letter-spacing: 1px;
}
</style>

<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('pageLoader').classList.add('loaded');
    }, 1600);
});
</script>
