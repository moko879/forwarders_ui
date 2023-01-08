<?php

require_once('private/installation.php');

function _get_forwarder_info($forwarder) {
  return @shell_exec(EXIM.' -bt '.$forwarder.' 2>&1');
}

function _exim_capture($line, &$matches, $forwarder = null, $destination = null, $expiration = null) {
  $forwarder = isset($forwarder) ? $forwarder : '([^: ]+)';
  $destination = isset($destination) ? $destination : '([^ ]+)';
  $expiration = isset($expiration) ? $expiration : '([a-zA-Z0-9/]*)';
  return preg_match("|^[ ]*{$forwarder}[ ]*:[ ]*{$destination}[ ]*{$expiration}|i",
    $line, $matches);
}

function _find_owner($file, $forwarder) {
  $file_data = file($file);
  foreach($file_data as $line) {
    if(!_exim_capture($line, $matches)) continue;
    if(strcasecmp($matches[1], $forwarder) == 0) return $matches[2];
  }
  return null;
}
  
function forwarder_owned_by($forwarder, $owner) {
  $info = _get_forwarder_info($forwarder);
  if (stripos($info, "<-- $owner") !== false) {
    // This is a valid email owned by user.
    return true;
  }
  if (stripos($info, "$forwarder is undeliverable") === false) {
    // This undeliverable, which means it is owned by someone else.
    return false;
  }
  // This is an undeliverable email, meaning it is either unowned or expired.
  return _find_owner(EALIASES.'/kruskal.net', $forwarder) == $owner;
}

function remove_forwarder($forwarder, $destination, $expiration = null) {
  $file = file(EALIASES.'/kruskal.net');
  $found = false;
  foreach($file as $index => $line) {
    if(_exim_capture($line, $matches, $forwarder, $destination, $expiration)) {
      unset($file[$index]);
      $found = true;
    }
  }
  if(!$found) return false;
  
  file_put_contents(EALIASES.'/kruskal.net', implode("", $file));
  return true;
}

?>