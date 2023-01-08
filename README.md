# forwarders_ui
UI for managing email forwarders

TODO:
1) Allow for modifying forwarder expiration dates (possibly via overwrites)
2) Verify ownership rules before adding a forwarder (no valias, no ralias/ealias owned by someone else)
3) Add presubmit exim validation stages to prevent regressions
4) Allow for modifying destination emails (this will require us to track owners separately)
5) Add postsubmit exim validation to ensure nothing went wrong
6) Improve documentation here.


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
define('VALIASES', '<Fill in server valiases file location>');
define('EXIM', '<Fill in server exim binary>');

?>
```