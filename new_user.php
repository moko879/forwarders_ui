<?php

echo(password_hash($_GET['email'].$_GET['password'], PASSWORD_DEFAULT));

?>