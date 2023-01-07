<?php

putenv("PATH=" .$_ENV["PATH"]. ':/opt/homebrew/bin');

function get_addresses($email) {
  $result = shell_exec('exim -bt '.$email.' 2>&1');
  return $result;
}

?>