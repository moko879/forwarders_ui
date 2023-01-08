<?php

require_once('check_login.php');
require_once('exim.php');
require_once('mysql.php');
require_once('private/installation.php');

# TODO: sanitize these to prevent injection
$forwarder = $_POST['forwarder'];
$expiration = $_POST['expiration'] ? strtotime($_POST['expiration']) : 'N/A';

# TODO: Add validation via exim
# 1. The forwarder should not exist, or should be owned by to current owner
# 2. The forwarder should not exist as an explicit valias, since those will take precedent
# 3. All our exim tests should continue to work before adjusting the config
# 4. Post-commit test should make sure the forwarder works

# Add the forwarder to the config file.
$fp = fopen(EALIASES.'/kruskal.net', 'a');
fwrite($fp, "{$forwarder}: {$login} {$expiration} \n");

echo(json_encode([
  'success' => true,
  'destination' => $login,
  'forwarder' => $forwarder,
  'expiration' => $expiration,
]));

?>