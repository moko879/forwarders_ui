<?php

session_start();

isset($_SESSION['email']) or die(json_encode([
  'error' => 'Invalid email or password:'.$_POST['email'].' '.$_POST['password']
]));

$login = $_SESSION['email'];

?>