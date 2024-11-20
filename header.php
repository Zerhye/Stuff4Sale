<script>
let sessionTimeout;

document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') {
        // Start a timeout to end the session after 3 seconds if the page is closed
        sessionTimeout = setTimeout(() => {
            navigator.sendBeacon('logout.php');
        }, 10000); // Adjust the delay as needed (e.g., 3000ms = 3 seconds)
    } else {
        // If the user returns quickly (i.e., navigated to another page), clear the timeout
        clearTimeout(sessionTimeout);
    }
});
</script>
