<?php
$title = "Base account system - Logout";
include "includes/header.php";

/* Destroy the session */
session_start();
$_SESSION = array();
session_destroy();
?>
You were logged succesfully logged out.<br>
You will now be redirected back to the home page.<br>
<a href="index.php">Home</a>
<script>
    setTimeout(function() {
        window.location.href = "index.php";
    }, 3000);
</script>
<?php
include "includes/footer.php";
?>