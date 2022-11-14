# PHP Account system base
I created this project to serve as a basis for other projects that need an account system. It uses PHP and MySQL.
This system contains the following features:
- Header and footer in a separate file
- Registering accounts
- Logging in and out
- Password reset via email
- Members-only pages
- Account editing

## Usage
You can put the `www` folder on a publicly accessible website and import the `users.sql` file into your database.
The credentials for mysql can be set in `settings.php`. You can also set the minimum session duration here.  
`cron/delete_expired_password_reset_tokens.php` can be ran occasionally, for example every 24 hours, to clean up expired password reset tokens from the database.


If you want a page to members-only you can put the following code at the top of your PHP page:
```php
include_once "includes/user.php";
$user = User::authUser();
```

For a full example page, you can look at `index.php`(Accessible without an account) or `profile.php`(Logged in accounts only)
