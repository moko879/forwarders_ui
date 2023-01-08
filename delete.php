<?php

require_once('check_login.php');
require_once('exim.php');

# TODO: sanitize these to prevent injection
$destination = strtolower($_POST['destination']);
$forwarder = strtolower($_POST['forwarder']);
$expiration = strtolower($_POST['expiration']);

# TODO: Add validation via exim
# 1. All our exim tests should continue to work before adjusting the config
# 2. Post-commit test should make sure the forwarder works

forwarder_owned_by($forwarder, $login) or die(json_encode([
  'error' => "You do not own $forwarder!"
]));

# Remove the forwarder to the config file.
remove_forwarder($forwarder, $destination, $expiration) or die(json_encode([
  'error' => "Internal error trying to delete $forwarder!"
]));

echo(json_encode([
  'success' => true,
  'destination' => $destination,
  'forwarder' => $forwarder,
  'expiration' => $expiration,
]));

?>