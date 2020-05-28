<?php

$result = new stdClass;

if (is_dir($exec_dir)) {
    $files = scandir($exec_dir);
    foreach ($files as $f) {
        if (substr($f, -4) === '.php') {
            $name = substr($f, 0, -4);
            $result->{$name} = array('updated' => null, 'type' => 'exec');
        }
    }
}

if (is_dir($data_dir)) {
    $files = scandir($data_dir);
    foreach ($files as $f) {
        if (substr($f, -5) === '.json') {
            $name = substr($f, 0, -5);
            $file = $data_dir.'/'.$f;
            $updated = get_file_updated($file);
            $result->{$name} = array('updated' => $updated, 'type' => 'data');
        }
    }
}

response(true, array($service), $now, $result);