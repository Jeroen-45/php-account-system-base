<?php
include_once "includes/user.php";
include_once "includes/database.php";

$title = "Base account system - Reset password";
include "includes/header.php";


/* Check if the token is set */
if (!isset($_GET['token'])) {
    /* Token is not set, redirect to homepage */
    header("Location: index.php");
    exit();
}

/* Check if user is already logged in */
$user = User::authUser(0);
if ($user && $user->privilege_level >= 1) {
    /* User is already logged in, log out the current user first */
    $_SESSION = array();
    session_destroy();
}

/* Check if the token is valid */
$db = new Database();
$userId = $db->getUserIdForPasswordResetToken($_GET['token']);

/* Process new password form if it was submitted and the token is valid */
$passwordError = "";
if ($userId && $_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];

    /* Check if the password is valid */
    if (strlen($password) < 8) {
        /* Password is too short, show an error message */
        $passwordError = "Password must be at least 8 characters long.";
    } else {
        /* Password is valid, hash it and update it in the database */
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $db->updateUserPassword($userId, $password_hash);

        /* Delete all reset tokens for this user from the database */
        $db->deletePasswordResetTokens($userId);

        /* Redirect to the login page */
        header("Location: login.php");
        exit();
    }
}
?>
<h1>Set new password</h1>
<?php if (!$userId) { ?>
    The url used is invalid. It likely expired.<br>
    <a href="forgot_password.php">Try again</a>
<?php } else { ?>
    <?php echo $passwordError; ?>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . "?token=" . $_GET['token']);?>"
          method="post" autocomplete="off">
        <label for="password">New password:</label>
        <input type="password" name="password" required>
        <br>
        <input type="submit" value="Save">
    </form>
<?php } ?>
<?php
include "includes/footer.php";
?>