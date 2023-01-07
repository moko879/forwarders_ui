<?php

require_once('private/installation.php');

$file = file_get_contents(EALIASES.'/kruskal.net');
$forwarders = explode(PHP_EOL, $file);
foreach($forwarders as $forwarder) {
  if(!$forwarder) continue;
  $data = explode(' ', $forwarder);
  $output[] = [
    "forwarder" => substr($data[0], 0, strlen($data[0])-1),
    "destination" => $data[1],
    "expiration" => $data[2]
  ];
}

echo(json_encode($output));

?>