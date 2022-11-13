<?php
include_once "includes/user.php";
include_once "includes/database.php";

$user = User::authUser();
$db = new Database();

$username = $user->username;
$email = $user->email;
$editProfileError = "";
/* Process update form if any was submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /* Detect which form was submitted */
    if (isset($_POST['username'])) {
        /* Update username */
        $username = $_POST['username'];
        if (strlen($username) > 32) {
            /* Username is too long */
            $editProfileError = "Username is too long.";
        } else if ($username == "") {
            /* Username is empty */
            $editProfileError = "Username cannot be empty.";
        } else {
            try {
                $user->setUsername($db, $username);
            } catch (usernameAlreadyExistsException $e) {
                $editProfileError = "Username already exists.";
            }
        }
    } else if (isset($_POST['password'])) {
        /* Update password */
        if (strlen($_POST['password']) < 8) {
            /* Password is too short */
            $editProfileError = "Password must be at least 8 characters long.";
        } else {
            $user->setPassword($db, $_POST['password']);
        }
    } else if (isset($_POST['email'])) {
        /* Update email */
        $email = $_POST['email'];

        if (strlen($email) > 128) {
            /* Email is too long */
            $editProfileError = "Email is too long.";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            /* Email is invalid */
            $editProfileError = "Invalid email address.";
        } else {
            try {
                $user->setEmail($db, $email);
            } catch (emailAlreadyExistsException $e) {
                $editProfileError = "Email already in use.";
            }
        }
    } else if (isset($_POST['first_name'])) {
        /* Update name */
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        if (strlen($first_name) > 256 || strlen($last_name) > 256) {
            /* Name is too long */
            $editProfileError = "Name is too long.";
        } else if ($first_name == "" || $last_name == "") {
            /* A name is empty */
            $editProfileError = "Name cannot be empty.";
        } else {
            $user->setName($db, $first_name, $last_name);
        }
    }
}


$title = "Base account system";
include "includes/header.php";
?>
<h1>Profile page</h1>
Welcome <?php echo $user->first_name; ?>!<br>
<a href="logout.php">Logout</a><br>

<h2>Edit profile</h2>
<?php echo $editProfileError; ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?php echo $username; ?>" maxlength="32" required>
    <input type="submit" value="Update">
</form>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="password">Password:</label>
    <input type="password" name="password" required>
    <input type="submit" value="Update">
</form>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo $email; ?>" maxlength="128" required>
    <input type="submit" value="Update">
</form>
<br>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="first_name">First name:</label>
    <input type="text" name="first_name" value="<?php echo $user->first_name; ?>" maxlength="256" required>
    <br>
    <label for="last_name">Last name:</label>
    <input type="text" name="last_name" value="<?php echo $user->last_name; ?>" maxlength="256" required>
    <br>
    <input type="submit" value="Update">
</form>
<?php
include "includes/footer.php";
?>