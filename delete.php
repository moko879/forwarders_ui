<?php

require_once('private/installation.php');
require_once('exim.php');
require_once('mysql.php');
require_once('login.php');

check_login() or die(json_encode([
  'error' => 'Invalid email or password:'.$_POST['email'].' '.$_POST['password']
]));

# TODO: sanitize these to prevent injection
$destination = $_POST['destination'];
$forwarder = $_POST['forwarder'];
$expiration = $_POST['expiration'];// ? strtotime($_POST['expiration']) : 'N/A';

# TODO: Add validation via exim
# 1. All our exim tests should continue to work before adjusting the config
# 2. Post-commit test should make sure the forwarder works

# Remove the forwarder to the config file.
$file = file(EALIASES.'/kruskal.net');
foreach($file as $index => $line) {
  if(strcasecmp(trim($line), "{$forwarder}: {$destination} {$expiration}") == 0) {
    unset($file[$index]);
  }
}
file_put_contents(EALIASES.'/kruskal.net', implode("", $file));

echo(json_encode([
  'success' => true,
  'destination' => $destination,
  'forwarder' => $forwarder,
  'expiration' => $expiration,
]));

?>