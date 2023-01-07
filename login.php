<?php

session_start();
if(isset($_SESSION['email'])) {
  header("Location: index.php");
}

$email = strtolower($_POST['email']);
$password = $_POST['password'];
$change_password = $_POST['change_password'] == 'true';
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

function verify_login(&$errors) {
  global $email, $password, $mysqli;

  $result = $mysqli->query(sprintf(
    "SELECT password FROM ".DB_USERS_TABLE." WHERE email = '%s'",
    $mysqli->real_escape_string($email)));
  if(!$result or $result->num_rows == 0 ||
    !password_verify($email.$password, $result->fetch_assoc()['password'])) {
    array_push($errors, 'Invalid email or password!');
    return false;
  }
  return true;
}

function change_password(&$errors) {
  global $email, $new_password, $confirm_password, $mysqli;
  if($new_password != $confirm_password) {
    array_push($errors, 'New passwords don\'t match!');
    return;
  }
  $mysqli->query(sprintf(
    "UPDATE ".DB_USERS_TABLE." SET password='%s' WHERE email = '%s'",
    password_hash($email.$new_password, PASSWORD_DEFAULT),
    $mysqli->real_escape_string($email)));
  if($mysqli->affected_rows != 1) {
    array_push($errors, 'Failed to update password!');
  }
}


$errors = array();
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once('mysql.php');

  if(verify_login($errors)) {
    if($change_password) {
      change_password($errors);
    } else if(empty($errors)) {
      $_SESSION['email'] = $email;
      header("Location: index.php");
    }
  }
}

?>
<html>
<head>
  <script src="jquery-3.6.3.min.js"></script>
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <form id="login-form" method="post" action="">
    <fieldset>
      <legend>Login</legend>
      <div id="login-form-container">
        <p>
          <label for="login-email">Email:</label>
          <input type="email" placeholder="Enter Email" name="email">
        </p>
        <p>
          <label for=login-password>Password:</label>
          <input type="password" placeholder="Enter Password" name="password">
        </p>
        <p id="change-password-enable">
          <a href="#">Change Password</a>
          <input type="hidden" name="change_password" value="false">
        </p>
        <p class="hidden change-password-element">
          <label for=login-password>New Password:</label>
          <input type="password" placeholder="Enter New Password" name="new_password">
        </p>
        <p class="hidden change-password-element">
          <label for=login-password>Confirm:</label>
          <input type="password" placeholder="Confirm New Password" name="confirm_password">
        </p>
<?php
  foreach($errors as $error) {
    echo '<p class="error">'.$error.'</p>';
  }
?>
        <p>
          <input id="login-submit" type="submit" value="Login">
        </p>
      </div>
    </fieldset>
  </form>
  <script type="application/javascript">
    $(document).ready(function() {
      $('#change-password-enable a').click((e) => {
        $(e.target).parent().addClass('hidden');
        $('#change-password-enable input').val("true");
        $('.change-password-element').removeClass('hidden');
        e.preventDefault();
        return false;
      });
      $('.change-password-element input').keyup((e) => {
        var match = $('.change-password-element input').toArray().every((input) => {
          return $(e.target).val() == $(input).val();
        });
        if(match) {
          $('.change-password-element input').removeClass('invalid');
          $('#login-submit').attr('disabled', false);
        } else {
          $('.change-password-element input').addClass('invalid');
          $('#login-submit').attr('disabled', true);
        }
      });
    });
  </script>
 </body>
</html>