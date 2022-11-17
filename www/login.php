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

$username = "";
$loginError = "";
/* Process login form if it was submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    /* Get the user from the database */
    $db = new Database();
    $user = $db->getUser($username);

    /* Check if the user exists and if the password is correct */
    if ($user && $user->checkPassword($password)) {
        /* Password is correct, start a new session and redirect to the user page */
        $_SESSION['user'] = $user;

        /* Get redirect url or default and redirect */
        $redirect_url = $_GET['redirect'] ?? "profile.php";
        header("Location: $redirect_url");
        exit();
    } else {
        /* Username or password is incorrect, show an error message */
        $loginError = "Incorrect username or password.";
    }
}
?>
<h1>Login</h1>
<?php echo $loginError; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]
                         . (isset($_GET['redirect']) ? "?redirect=" . urlencode($_GET['redirect']) : "")); ?>"
      method="post" autocomplete="off">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?php echo $username; ?>" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" name="password" required>
    <br>
    <input type="submit" value="Login">
</form>
<a href="forgot_password.php">Forgot password?</a>
<?php
include "includes/footer.php";
?>