<?php

require_once('mysql.php');

function check_login() {
  global $mysqli;
  
  $email = strtolower($_POST['email']);
  $password = $_POST['password'];

  $result = $mysqli->query(sprintf(
    "SELECT password FROM user WHERE email = '%s'",
    $mysqli->real_escape_string($email)));
  if(!$result or $result->num_rows == 0) {
    return false;
  }
  return password_verify($email.$password, $result->fetch_assoc()['password']);
}

?>