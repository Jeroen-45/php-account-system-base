<?php
include_once "user.php";


class usernameAlreadyExistsException extends Exception {
    public function errorMessage() {
        return "Username already exists.";
    }
}

class emailAlreadyExistsException extends Exception {
    public function errorMessage() {
        return "Email adress already in use.";
    }
}


class Database {
    protected PDO $pdo;


    public function __construct() {
        /* Get settings from settings file */
        $settings = include("settings.php");

        /* Connect to the database */
        $dsn = "mysql:host=$settings->mysql_host;dbname=$settings->mysql_db;charset=$settings->mysql_charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $settings->mysql_user, $settings->mysql_pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /* Gets the user with the given username from the database.
     * Returns a User object if the user exists, null otherwise. */
    public function getUser(string $username): ?User {
        /* Search the database for the user */
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        /* If the user exists, return a new User object. Return null otherwise. */
        if ($user) {
            return new User(intval($user['id']), $user['username'], $user['password_hash'], $user['email'],
                            $user['first_name'], $user['last_name'], intval($user['privilege_level']));
        } else {
            return null;
        }
    }

    /* Gets the user with the given email from the database.
     * Returns a User object if the user exists, null otherwise. */
    public function getUserByEmail(string $email): ?User {
        /* Search the database for the user */
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        /* If the user exists, return a new User object. Return null otherwise. */
        if ($user) {
            return new User(intval($user['id']), $user['username'], $user['password_hash'], $user['email'],
                            $user['first_name'], $user['last_name'], intval($user['privilege_level']));
        } else {
            return null;
        }
    }

    /* Attempts to create a user with the given data. Returns new user object if successful, null otherwise. */
    public function createUser(string $username, string $password_hash, string $email,
                               string $first_name, string $last_name, int $privilege_level = 1): ?User {
        /* Check if the username is already taken */
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetchColumn()) {
            throw new usernameAlreadyExistsException();
            return null;
        }

        /* Check if the email is already taken */
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn()) {
            throw new emailAlreadyExistsException();
            return null;
        }

        /* Insert the user into the database */
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password_hash, email, first_name, last_name, privilege_level)
                                     VALUES (:username, :password_hash, :email, :first_name, :last_name, :privilege_level)");
        $stmt->execute(['username' => $username, 'password_hash' => $password_hash, 'email' => $email,
                        'first_name' => $first_name, 'last_name' => $last_name, 'privilege_level' => $privilege_level]);

        /* Return the new user object */
        return new User($this->pdo->lastInsertId(), $username, $password_hash,
                        $email, $first_name, $last_name, $privilege_level);
    }

    /* Updates username of the user with the given id. */
    public function updateUserUsername(int $id, string $username): void {
        /* Check if the username is already taken */
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetchColumn()) {
            throw new usernameAlreadyExistsException();
            return;
        }

        /* Update the username */
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username WHERE id = :id");
        $stmt->execute(['username' => $username, 'id' => $id]);
    }

    /* Updates password of the user with the given id. */
    public function updateUserPassword(int $id, string $password_hash): void {
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $stmt->execute(['password_hash' => $password_hash, 'id' => $id]);
    }

    /* Updates email of the user with the given id. */
    public function updateUserEmail(int $id, string $email): void {
        /* Check if the email is already taken */
        $stmt = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn()) {
            throw new emailAlreadyExistsException();
            return;
        }

        /* Update the email */
        $stmt = $this->pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
        $stmt->execute(['email' => $email, 'id' => $id]);
    }

    /* Updates name of the user with the given id. */
    public function updateUserName(int $id, string $first_name, string $last_name): void {
        $stmt = $this->pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name WHERE id = :id");
        $stmt->execute(['first_name' => $first_name, 'last_name' => $last_name, 'id' => $id]);
    }

    /* Gets the user id for the given password reset token.
     * Returns the user id if the token exists and hasn't expired, null otherwise. */
    public function getUserIdForPasswordResetToken(string $token): ?int {
        /* Search the database for the token */
        $stmt = $this->pdo->prepare("SELECT user_id
                                     FROM password_reset_tokens
                                     WHERE token = :token
                                        AND creation_timestamp > NOW() - INTERVAL 1 HOUR
                                     LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user_id = $stmt->fetchColumn();

        /* If the token exists, return the associated user id. Return null otherwise. */
        if ($user_id) {
            return intval($user_id);
        } else {
            return null;
        }
    }

    /* Inserts a password reset token into the database for the given user.
     * Returns the newly generated token. */
    public function createPasswordResetToken(int $user_id): string {
        /* Generate cryptographically random password reset token */
        $token = bin2hex(random_bytes(64));

        /* Insert the token into the database */
        $stmt = $this->pdo->prepare("INSERT INTO password_reset_tokens (token, user_id) VALUES (:token, :user_id)");
        $stmt->execute(['token' => $token, 'user_id' => $user_id]);

        /* Return the token */
        return $token;
    }

    /* Deletes the password reset tokens for a given user id from the database. */
    public function deletePasswordResetTokens(int $user_id): void {
        $stmt = $this->pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
    }

    /* Deletes all expired password reset tokens from the database. */
    public function deleteExpiredPasswordResetTokens(): void {
        $stmt = $this->pdo->prepare("DELETE FROM password_reset_tokens WHERE creation_timestamp < NOW() - INTERVAL 1 HOUR");
        $stmt->execute();
    }
}
