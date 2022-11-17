<?php
include_once "includes/user.php";
include_once "includes/database.php";

$title = "Base account system - Register";
include "includes/header.php";


/* Check if user is already logged in */
$user = User::authUser(0);
if ($user && $user->privilege_level >= 1) {
    /* User is already logged in, redirect to the user page */
    header("Location: profile.php");
    exit();
}

$username = $password = $email = $first_name = $last_name = "";
$registerError = "";
/* Process registration form if it was submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);

    /* Check if input is valid */
    if (strlen($username) > 32 || strlen($email) > 128 ||
        strlen($first_name) > 256 || strlen($last_name) > 256) {
        /* Input is too long */
        $registerError = "Input is too long.";
    } else if ($username == "" || $first_name == "" || $last_name == "") {
        /* Input is empty */
        $registerError = "Input cannot be empty.";
    } else if (strlen($password) < 8) {
        /* Password is too short */
        $registerError = "Password must be at least 8 characters long.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        /* Email is invalid */
        $registerError = "Invalid email address.";
    } else {
        /* Hash the password */
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        /* Create new user and log it in if no exception is thrown */
        try {
            $db = new Database();
            $new_user = $db->createUser($username, $password_hash, $email, $first_name, $last_name);
            $_SESSION['user'] = $new_user;
            header("Location: profile.php");
            exit();
        } catch (usernameAlreadyExistsException $e) {
            $registerError = "Username already exists.";
        } catch (emailAlreadyExistsException $e) {
            $registerError = "Email already in use.";
        }
    }
}
?>
<h1>Register</h1>
<?php echo $registerError; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?php echo $username; ?>" maxlength="32" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" name="password" value="<?php echo $password; ?>" required>
    <br>
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo $email; ?>" maxlength="128" required>
    <br>
    <label for="first_name">First name:</label>
    <input type="text" name="first_name" value="<?php echo $first_name; ?>" maxlength="256" required>
    <br>
    <label for="last_name">Last name:</label>
    <input type="text" name="last_name" value="<?php echo $last_name; ?>" maxlength="256" required>
    <br>
    <input type="submit" value="Register">
</form>
<?php
include "includes/footer.php";
?>