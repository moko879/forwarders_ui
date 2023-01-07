<?php

session_start();
if(isset($_SESSION['email'])) {
  header("Location: index.php");
}

$errors = array();
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once('mysql.php');

  $email = strtolower($_POST['email']);
  $password = $_POST['password'];

  $result = $mysqli->query(sprintf(
    "SELECT password FROM ".DB_USERS_TABLE." WHERE email = '%s'",
    $mysqli->real_escape_string($email)));

  if(!$result or $result->num_rows == 0 ||
    !password_verify($email.$password, $result->fetch_assoc()['password'])) {
    array_push($errors, 'Invalid email or password!');
  } else {
    $_SESSION['email'] = $email;
    header("Location: index.php");
  }
}

?>

<head>
  <link rel="stylesheet" href="main.css">
</head>
<body>
  <div id="login-form">
    <form method="post" action="">
      <p>
        <label for="login-email">Email:</label>
        <input type="email" id="login-email" placeholder="Enter Email" name="email">
      </p>
      <p>
        <label for=login-password>Password:</label>
        <input type="password" id="login-password" placeholder="Enter Password" name="password">
      </p>
      <p>
        <input type="submit" value="Login">
      </p>
<?php
  foreach($errors as $error) {
    echo '<p class="error">'.$error.'</p>';
  }
?>
    </form>
  </div>
</body>