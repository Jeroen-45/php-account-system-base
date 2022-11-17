<?php
include_once "database.php";


class User {
    public int          $id;
    public string       $username;
    protected string    $password_hash;
    public string       $email;
    public string       $first_name;
    public string       $last_name;
    public int          $privilege_level;


    public function __construct(int $id, string $username, string $password_hash, string $email,
                                string $first_name, string $last_name, int $privilege_level) {
        $this->id = $id;
        $this->username = $username;
        $this->password_hash = $password_hash;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->privilege_level = $privilege_level;
    }

    /* Checks if a user is logged in (authLevel > 0) and if the user has the required privilege level.
     * Returns the user object if the user is logged in and has the required privilege level.
     * Redirects to the login page if the user is not logged in but the required privilege level is > 0.
     * Returns null otherwise. */
    public static function authUser(int $privilege_level = 1): ?User {
        /* Start / resume session */
        if (session_status() === PHP_SESSION_NONE) {
            $settings = require "settings.php";
            ini_set('session.gc_maxlifetime', $settings->session_timeout);
            session_set_cookie_params($settings->session_timeout);
            session_start();
        }

        /* Check if a user session exists and if the user has the required privilege level */
        if (isset($_SESSION['user']) && $_SESSION['user']->privilege_level >= $privilege_level) {
            return $_SESSION['user'];
        } else if ($privilege_level > 0) {
            /* If the user does not have the required privilege level and should be logged in,
             * redirect to the login page */
            header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            exit();
        } else {
            return null;
        }
    }

    /* Checks if the given password is correct. Returns true if it is, false otherwise. */
    public function checkPassword(string $password): bool {
        return password_verify($password, $this->password_hash);
    }

    /* Sets the username of the user to the given value. */
    public function setUsername(Database $db, string $username): void {
        $db->updateUserUsername($this->id, $username);
        $this->username = $username;
    }

    /* Sets the password of the user to the given value. */
    public function setPassword(Database $db, string $new_password): void {
        /* Hash the new password */
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        /* Update the password in the database */
        $db->updateUserPassword($this->id, $new_password_hash);

        /* Update self */
        $this->password_hash = $new_password_hash;
    }

    /* Sets the email of the user to the given value. */
    public function setEmail(Database $db, string $email): void {
        $db->updateUserEmail($this->id, $email);
        $this->email = $email;
    }

    /* Sets the first aand last name of the user to the given value. */
    public function setName(Database $db, string $first_name, string $last_name): void {
        $db->updateUserName($this->id, $first_name, $last_name);
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }
}