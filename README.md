# forwarders_ui
UI for managing email forwarders

# Installation
The following file should be added at `private/installation.php` to make this site work.  Each parameter needs to be defined according to your server configuration.  We expect a database with a users table that contains columns for unique emails and hashed passwords of type VARCHAR(255).

```
<?php

define('DB_HOST', 'localhost');
define('DB_USER', '<Fill in database user>');
define('DB_PASSWORD', '<Fill in database password>');
define('DB_DATABASE', '<Fill in database name>');
define('DB_USERS_TABLE', '<Fill in users table>');
define('EALIASES', '<Fill in server ealiases file location>');

?>
```