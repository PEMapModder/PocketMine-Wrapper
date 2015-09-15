<?php

function console($msg, $ret = null, $eol = true){
  echo date("[H:i:s] ") . $msg . ($eol ? PHP_EOL : "");
  return $ret;
}
