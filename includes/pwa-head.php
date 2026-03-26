<!-- PWA Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#00D9FF">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="QuantrazGG">

<!-- iOS Icons -->
<link rel="apple-touch-icon" href="/images/icons/icon-152.png">
<link rel="apple-touch-icon" sizes="152x152" href="/images/icons/icon-152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-192.png">
<link rel="apple-touch-icon" sizes="167x167" href="/images/icons/icon-192.png">

<!-- Splash Screens (iOS) -->
<link rel="apple-touch-startup-image" href="/images/splash/splash-640x1136.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="/images/splash/splash-750x1334.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="/images/splash/splash-1242x2208.png" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)">
<link rel="apple-touch-startup-image" href="/images/splash/splash-1125x2436.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">

<!-- Windows Tiles -->
<meta name="msapplication-TileColor" content="#1A1A2E">
<meta name="msapplication-TileImage" content="/images/icons/icon-144.png">

<!-- Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered:', registration.scope);
            })
            .catch(error => {
                console.log('SW registration failed:', error);
            });
    });
}
</script>
