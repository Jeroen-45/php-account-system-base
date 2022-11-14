<?php
include_once "includes/user.php";
include_once "includes/database.php";

$title = "Base account system - Login";
include "includes/header.php";


/* Check if user is already logged in */
$user = User::authUser(0);
if ($user && $user->privilege_level >= 1) {
    /* User is already logged in, redirect to the user page */
    header("Location: profile.php");
    exit();
}

/* Process forgot password form if it was submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    /* Get the user from the database */
    $db = new Database();
    $user = $db->getUserByEmail($email);

    /* Check if the user exists */
    if ($user) {
        /* Generate token and save it to the database */
        $token = $db->createPasswordResetToken($user->id);

        /* Send email with password reset link */
        $to      = $user->email;
        $subject = 'Password reset';
        $message = 'Visit the following link to reset your password: http://localhost/reset_password.php?token=' . $token;
        $headers = array(
            'From' => 'no-reply@example.com',
            'Reply-To' => 'support@example.com',
            'X-Mailer' => 'PHP/' . phpversion()
        );
        mail($to, $subject, $message, $headers);
    }
}
?>
<h1>Request password reset</h1>
<?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
    If an account with the given email address exists, an email with a password reset link has been sent.<br>
    <a href="login.php">Back to login</a>
<?php } else { ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
        <label for="username">Account email:</label>
        <input type="text" name="email" required>
        <br>
        <input type="submit" value="Send password request link">
    </form>
<?php } ?>
<?php
include "includes/footer.php";
?>