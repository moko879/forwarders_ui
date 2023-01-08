<?php

require_once('private/installation.php');

function _get_forwarder_info($forwarder) {
  return @shell_exec(EXIM.' -bt '.$forwarder.' 2>&1');
}

function _find_owner($file, $forwarder) {
  $file_data = file($file);
  foreach($file_data as $line) {
    if(!preg_match('/^[ ]*([^: ]+):[ ]*([^ ]+)/', strtolower($line), $matches)) continue;
    //return implode(',',$matches);
    if($matches[1] == $forwarder) return $matches[2];
  }
  return null;
}
  
function forwarder_owned_by($forwarder, $owner) {
  $forwarder = strtolower($forwarder);
  $owner = strtolower($owner);
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

?>