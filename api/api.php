<?php

$data_dir = __DIR__.'/data';
$dump_dir = __DIR__.'/dump';
$exec_dir = __DIR__.'/exec';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

function get_file_updated($file) {
  return date('c',filemtime($file));
}

function result_json($success, $fullpath, $updated, $data) {
  $result = array('success' => $success, 'path' => join('/', $fullpath), 'updated' => $updated, 'data' => $data);
  return json_encode($result, JSON_PRETTY_PRINT);
}

function response($success, $fullpath, $updated, $data) {
  header('Content-Type: application/json', true);
  print result_json($success, $fullpath, $updated, $data);
  exit;
}

function create_id($time=null, $random=null, $counter=null) {
  if ($time === null) $time = time();
  if ($random === null) $random = random_bytes(5);
  if ($counter === null) $counter = random_bytes(3);
  return dechex($time).bin2hex($random).bin2hex($counter);
}

function create_dir($dir) {
  mkdir($dir, 0770, true);
}

function dump_file($data_file, $dump_dir) {
  if (is_file($data_file)) {
    $day_dir = $dump_dir.'/'.date('Ymd');
    if (!is_dir($day_dir)) create_dir($day_dir);
    $dump = $day_dir.'/'.date('Gi').'_'.basename($data_file);
    rename($data_file, $dump);
  }
}

function path_to_url($path) {
  $path = trim(is_array($path) ? join('/', $path) : $path, '/');
  $script_name = trim(substr($_SERVER['SCRIPT_NAME'], 0, -4), '/');
  return (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER[HTTP_HOST].'/'.$script_name.'/'.$path;
}

// get method
$method = $_SERVER['REQUEST_METHOD'];
$uri = trim($_SERVER['PATH_INFO'], '/');
$fullpath = explode('/', $uri);
$path = array_slice($fullpath, 1);
$id = end($fullpath);
$now = date('c');
$service = $fullpath[0];

// get body data
$input = file_get_contents("php://input");
$input = empty($input) ? null : json_decode($input);

// php
$php_file = $exec_dir.'/'.trim($service).'.php';
if (is_file($php_file)) include_once($php_file);

// data
$data_file = $data_dir.'/'.$service.'.json';

// return file
// if ($method === 'GET' && count($path) === 0) {
//   if (!is_file($data_file)) response(true, $fullpath, $now, null);
//   header('Content-Type: application/json', true);
//   readfile($data_file);
//   exit;
// }

$isNewPath = !is_file($data_file);
$data = $isNewPath ? new stdClass : json_decode(file_get_contents($data_file));

if (!is_object($data)) $data = new stdClass;
$updated = $isNewPath ? $now : get_file_updated($data_file);
// isset($data->updated) ? $data->updated : $now;
// $data = isset($data->data) ? $data->data : new stdClass;

$parent = null;
$node = &$data;
foreach($path as $p) {
  $parent = &$node;
  if (!isset($node->{$p}) || !is_object($node->{$p})) {
    $isNewPath = true;
    $node->{$p} = new stdClass;
  }
  $node = &$node->{$p};
}

switch($method) {
  case 'GET': // read
    if ($isNewPath) {
      http_response_code(404); // Not Found
      response(false, $fullpath, $updated, null);
    }
    response(true, $fullpath, $updated, $node);
    break;
  case 'PATCH': // update
    if (is_object($input)) {
      foreach ($input as $k => &$v) $node->{$k} = &$v;
    }
    else if (is_object($parent)) $parent->{$id} = $input;
    else if (count($fullpath) === 1) $data = $input;
    break;
  case 'DELETE': // remove
    if (is_object($parent)) {
      if (!isset($parent->{$id})) {
        http_response_code(404); // Not Found
        response(false, $fullpath, $updated, null);
      }
      unset($parent->{$id});
      $node = null;
    }
    else if (count($fullpath) === 1) $data = null;
    break;
  case 'POST': // create
    $id = create_id();
    array_push($fullpath, $id);
    $parent = &$node;
    if (!isset($parent->{$id})) $parent->{$id} = new stdClass;
    $parent->{$id} = $node = &$input;
    break;
  case 'PUT': // replace
    if (is_object($input)) {
      $parent->{$id} = &$input;
      $node = &$parent->{$id};
    }
    else if (is_object($parent)) $parent->{$id} = $input;
    else if (count($fullpath) === 1) $data = $input;
    break;
  default: // other
    http_response_code(405); // Method Not Allowed
    response(false, $fullpath, $updated, null);
    break;
}

// create dump
dump_file($data_file, $dump_dir);

if (!is_dir($data_dir)) mkdir($data_dir, 0770, true);

// add update to data file
file_put_contents($data_file, result_json(true, array($service), $now, $data));
response(true, $fullpath, $now, $node);