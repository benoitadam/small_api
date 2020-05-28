<?php

// .../upload -> img/source/[id]

// .../source/[id]
// .../sd/[id].[jpg/gif/png] -> .../720x576/[id].[jpg/gif/png]
// .../hd/[id].[jpg/gif/png] -> .../1920x1080/[id].[jpg/gif/png]
// .../4k/[id].[jpg/gif/png] -> .../3840x2160/[id].[jpg/gif/png]



// img/[id]/[*]_w300h600c.jpg

//

function img_support($ext) {
  switch ($ext) {
    case 'jpg':
    case 'jpeg':
      if (imagetypes() & IMG_JPG) return true;
      break;
    case 'gif':
      if (imagetypes() & IMG_GIF) return true;
      break;
    case 'png':
      if (imagetypes() & IMG_PNG) return true;
      break;
  }
  return false;
}

function return_img_file($img_file) {
  header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
  header('Content-Length: '.filesize($img_file), true);
  header('Content-Type: '.mime_content_type($img_file), true);
  header('Pragma: no-cache', true);
  readfile($img_file);
  exit;
}

$img_file = $data_dir.'/'.$uri;
$img_ext = strtolower(pathinfo($img_file, PATHINFO_EXTENSION));
if (img_support($img_ext)) {
  if (is_file($img_file)) return_img_file($img_file);
  $id =  $img_file
  if ()
}

// $media_dir = $data_dir.'/media';
// $media_dump_dir = $dump_dir.'/media';

function get_input($name, $default_value=null) {
  global $input;
  return (is_object($input) && isset($input->{$name})) ? $input->{$name} : isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default_value;
}

function get_input_number($name, $default_value=null) {
  $value = get_input($name, $default_value);
  return empty($value) ? $default_value : floatval($value);
}

if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {

}

$target_ext = get_input('ext', null);
if ($target_ext === null) {
  $target_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
}

$target_name = create_id().'.'.$target_ext;
$target_file = $data_dir.'/'.$service.'/'.$target_name;
$source_file = $_FILES['file']['tmp_name'];

$target_width = get_input_number('width', 0);
$target_height = get_input_number('height', 0);


function img_format($src_file, $dst_file, $dst_w, $dst_h) {

  // Check support for destination extension
  $dst_ext = strtolower(pathinfo($dst_file, PATHINFO_EXTENSION));
  if (!img_support($dst_ext)) return array('error' => 'image_not_supported', 'var' => 'dst_file');
  
  // Check if image file is a actual image or fake image
  $src_info = getimagesize($src_file);
  if ($src_info === false) return array('error' => 'image_not_supported', 'var' => 'src_file');

  // Check support for source extension
  $src_ext = substr($src_info['mime'], 6); // 'image/jpg' -> 'jpg'
  if (!img_support($src_ext)) return array('error' => 'image_not_supported', 'var' => 'src_info');

  if ($dst_w && $dst_h && $dst_ext === $src_ext) return array('success' => true, 'update' => false);

  // Create target directory
  $dst_dir = dirname($dst_file);
  if (!is_dir($dst_dir)) mkdir($dst_dir, 0770, true);
  
    $src_image = null;
    switch ($src_ext) {
      case 'jpg':
      case 'jpeg':
        $src_image = imagecreatefromjpeg($src_file, 90);
        break;
      case 'gif':
        $src_image = @imagecreatefromgif($src_file);
        break;
      case 'png':
        $src_image = @imagecreatefrompng($src_file);
        break;
    }

    if ($dst_w || $dst_h) {
      $src_w = imagesx($src_image);
      $src_h = imagesy($src_image);

      if ($dst_w <= 0) $dst_w = $src_w / $src_h * $dst_h;
      if ($dst_h <= 0) $dst_h = $src_h / $src_w * $dst_w;

      $dst_w = round($dst_w);
      $dst_h = round($dst_h);

      $dst_image = imagecreatetruecolor($dst_w, $dst_h);
      // resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h
      imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
      imagedestroy($src_image);
      $src_image = $dst_image;
    }

    switch($target_ext) {
      case 'jpg':
      case 'jpeg':
        imagejpeg($src_image, $dst_file);
	      break;
      case 'gif':
	      imagegif($src_image, $dst_file);
        break;
      case 'png':
	      imagepng($src_image, $dst_file);
        break;
    }

    imagedestroy($src_image);
  }
}

if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {

  $target_ext = get_input('ext', null);
  if ($target_ext === null) {
    $target_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
  }
  
  $target_name = create_id().'.'.$target_ext;
  $target_file = $data_dir.'/'.$service.'/'.$target_name;
  $source_file = $_FILES['file']['tmp_name'];

  $target_dir = dirname($target_file);
  if (!is_dir($target_dir)) mkdir($target_dir, 0770, true);

  $target_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
  if (!img_support($target_ext)) response(false, $fullpath, $now, 'no_supported_target:'.$target_ext);

  $is_convert = false;

  // Check if image file is a actual image or fake image
  $source_info = getimagesize($source_file);
  if ($source_info === false) response(false, $fullpath, $now, 'no_supported_source');

  $source_ext = $source_info === false ? null : substr($source_info['mime'], 6);
  if (!img_support($source_ext)) response(false, $fullpath, $now, 'no_supported_source:'.$source_ext);

  $target_width = get_input_number('width', 0);
  $target_height = get_input_number('height', 0);

  var_dump($target_width, $target_height, $target_ext, $source_ext);

  if ($target_width > 0 || $target_height > 0 || $target_ext !== $source_ext) {

    $image = null;
    switch ($source_ext) {
      case 'jpg':
      case 'jpeg':
        $image = imagecreatefromjpeg($filename);
        break;
      case 'gif':
        $image = @imagecreatefromgif($filename);
        break;
      case 'png':
        $image = @imagecreatefrompng($filename);
        break;
      default:
        response(false, $fullpath, $now, 'no_supported_source:'.$source_ext);
    }

    if ($target_width > 0 || $target_height > 0) {
      $source_width = imagesx($image);
      $source_height = imagesy($image);

      var_dump($source_width, $source_height);

      if ($target_width <= 0) $target_width = $source_width / $source_height * $target_height;
      if ($target_height <= 0) $target_height = $source_height / $source_width * $target_width;

      $target_width = round($target_width);
      $target_height = round($target_height);

      var_dump($target_width, $target_height);

      $new_image = imagecreatetruecolor($target_width, $target_height);
      imagecopyresampled($new_image, $image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
      imagedestroy($image);
      $image = $new_image;
    }

    switch($target_ext) {
      case 'jpg':
      case 'jpeg':
        imagejpeg($image, $target_file, 90);
	      break;
      case 'gif':
	      imagegif($image, $target_file);
        break;
      case 'png':
	      imagepng($image, $target_file);
        break;
    }

    imagedestroy($image);
  }
  else if (!move_uploaded_file($source_file, $target_file)) {
    response(false, $fullpath, $now, 'no_supported_source');
  }

  response(true, $fullpath, $now, array(
    'url' => path_to_url($service.'/'.$target_name)
  ));
}

response(false, $fullpath, $now, null);

// // media (image, doc, json...)
// if (!empty($ext)) {
//     if ($method === 'GET') {
//       $mediaFile = $dataDir.'/'.$uri;
//       if (is_file($mediaFile)) {
//         header('Content-Type: '.mime_content_type($mediaFile), true);
//         readfile($mediaFile);
//       }
//     }
//     exit;
//   }

  
  
// $dirPath = array_slice($path, 1);
// $dirUri = join('/', $dirPath);
// $dir = $dataDir.'/'.$dirUri;

// if (is_dir($dir)) {
//     $scan = scandir($dir);
//     if ($scan[1] === '..') $scan = array_slice($scan, 2);

//     $result = new stdClass;
//     $result->dir = $dir;

//     foreach ($scan as $name) {
//         $file = $dir.'/'.$name;
//         $updated = date('c',filemtime($file));
//         $ext = pathinfo($name, PATHINFO_EXTENSION);
//         if ($ext === 'php') {
//             if (count($dirPath) === 0) {
//                 $name = substr($name, 0, -4);
//                 $result->{$name} = array('type' => 'service', 'updated' => $updated);
//             }
//             continue;
//         }
//         if (count($dirPath) === 0 && $ext === 'json') {
//             $name = substr($name, 0, -5);
//             $result->{$name} = array('type' => 'collection', 'updated' => $updated);
//             continue;
//         }
//         if (is_dir($file)) {
//             $result->{$name} = array('type' => 'folder', 'updated' => $updated);
//             continue;
//         }
//         $result->{$name} = array('type' => 'file', 'updated' => $updated);
//     }

//     response(true, $path, $now, $result);
// }

// response(false, $path, $now, 'not_dir:'.join('/',$dirPath));