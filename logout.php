<?php
session_start();
session_unset();
session_destroy();
header("Location: welcome.html"); // Redirect to welcome page
exit();
?>
