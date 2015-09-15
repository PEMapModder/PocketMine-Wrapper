<?php

function console($msg, $ret = null){
  echo date("[H:i:s] ") . $msg . PHP_EOL;
  return $ret;
}
