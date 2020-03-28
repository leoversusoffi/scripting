#!/usr/bin/php
<?php
/* lsb_detection v1.1 */
/* import functions */
require_once("functions.php");

/* check nb arguments */
if(count($argv) != 2){
	echo "This script take only ONE argument.\n";
	echo "\n";
	echo "Type -h for help.\n";
	exit();
}

/* check argument help */
if($argv[1] === "-h" || $argv[1] === "--help"){
	echo "Usage: ".$argv[0]." FILE\n";
	echo "\n";
	echo "FILE:		Take only PNG|BMP|GIF file.\n";
	echo "			BMP 32 bits per pixel not supported (Processed like a 24 bits).\n";
	exit();
}

/* check file exist */
if (!file_exists($argv[1])){
	echo "Unable to find that file : ".$argv[1]."\n";
	exit();
}

/* check right on that file */
$file = fopen($argv[1], "r") or die("Unable to open file : ".$argv[1]."\n");
fclose($file);

/* check extension */
$path = explode("/", $argv[1]);
$filename = $path[(count($path)-1)];
$file = explode(".", $filename);
$name = $file[0];
$ext = $file[(count($file)-1)];
$ext = strtolower($ext);
if($ext !== "png" && $ext !== "bmp" && $ext !== "gif"){
	echo "This script take only PNG|BMP|GIF file.\n";
	echo $ext." given.\n";
	exit();
}

/* check if output dir exist */
if(file_exists($name."_output")){
	echo "Unable to create that dir : ".$name."_output.\n";
	echo $name."_output already exist.\n";
	exit();
}else{
	mkdir($name."_output", 0755);
}

/* get file */
$file = $argv[1];

/* proceed stegano */
if($ext === "png"){
	process_stegano($file, $ext, $name);}
if($ext === "bmp"){
	process_stegano($file, $ext, $name);}
/* if gif, proceed all frame */
if($ext === "gif"){
	system("convert ".$name.".".$ext." ".$name."_output/target.png");
	$files = glob($name."_output/*.png", GLOB_BRACE);
	foreach($files as $file) {
		$filename = explode("/", $file);
		$dir = $filename[0];
		$filename = $filename[1];
		$file = explode(".", $filename);
		$name = $file[0];
		$ext = $file[(count($file)-1)];
		mkdir($dir."/".$name."_output", 0755);
		process_stegano($dir."/".$filename, $ext, $name, $dir."/");
	}
}


function process_stegano($file, $ext, $name, $dir=""){
  if($ext === "png"){
    if(!$im = imagecreatefrompng($file)){echo "Unable to imagecreatefrompng.\n";exit();}}
  if($ext === "bmp"){
    if(!$im = imagecreatefrom_bmp($file)){echo "Unable to imagecreatefrom_bmp.\n";exit();}}


  list($width, $height, $type, $attr) = getimagesize($file);
  $i=0;$j=0;
  $dump_alpha = "";
  $dump_r = "";
  $dump_g = "";
  $dump_b = "";
  $dump_rgb = "";

  $img_rgb_detection = imagecreatetruecolor($width,$height);
  $img_r_detection = imagecreatetruecolor($width,$height);
  $img_g_detection = imagecreatetruecolor($width,$height);
  $img_b_detection = imagecreatetruecolor($width,$height);
  $img_alpha_detection = imagecreatetruecolor($width,$height);
  $img_withoutalpha = imagecreatetruecolor($width,$height);

  while($j <= ($height-1)){
    while($i <= ($width-1)){
      $rgb = imagecolorat($im, $i, $j);
      $cols = imagecolorsforindex($im, $rgb);
      $r = $cols['red'];
      $g = $cols['green'];
      $b = $cols['blue'];
      $a = $cols['alpha'];

      /*echo $r."\n";
      echo $g."\n";
      echo $b."\n";
      echo $a."\n";*/

      /* rgb_detection */
      if($r%2){$red=0;}else{$red=255;}
      if($g%2){$green=0;}else{$green=255;}
      if($b%2){$blue=0;}else{$blue=255;}
      $color = imagecolorallocate($img_rgb_detection, $red, $green, $blue);
      imagesetpixel($img_rgb_detection, $i ,$j , $color);

      /* r_detection */
      $color = imagecolorallocate($img_r_detection, $red, 0, 0);
      imagesetpixel($img_r_detection, $i ,$j , $color);

      /* g_detection */
      $color = imagecolorallocate($img_g_detection, 0, $green, 0);
      imagesetpixel($img_g_detection, $i ,$j , $color);

      /* b_detection */
      $color = imagecolorallocate($img_b_detection, 0, 0, $blue);
      imagesetpixel($img_b_detection, $i ,$j , $color);

      /* alpha_detection */
      if($a%2){$red=0;$green=255;}else{$red=255;$green=0;}
      $color = imagecolorallocate($img_alpha_detection, $red, $green, 0);
      imagesetpixel($img_alpha_detection, $i ,$j , $color);

      /* without alpha */
      $color = imagecolorallocate($img_withoutalpha, $r, $g, $b);
      imagesetpixel($img_withoutalpha, $i ,$j , $color);
      
      /* dump alpha */
      $dump_alpha .= $a." ";

      /* dump r */
      $dump_r .= $r." ";

      /* dump g */
      $dump_g .= $g." ";

      /* dump b */
      $dump_b .= $b." ";

      /* dump rgb */
      $dump_rgb .= "(".$r."|".$g."|".$b.") ";

      $i++;
    }
    $dump_alpha .= "\n";
    $dump_r .= "\n";
    $dump_g .= "\n";
    $dump_b .= "\n";
    $dump_rgb .= "\n";
    $i=0;
    $j++;
  }

  /* create images */
  if($ext === "png"){
    imagepng($img_rgb_detection, $dir.$name."_output/".$name."_rgb_detection.".$ext);
    imagepng($img_r_detection, $dir.$name."_output/".$name."_r_detection.".$ext);
    imagepng($img_g_detection, $dir.$name."_output/".$name."_g_detection.".$ext);
    imagepng($img_b_detection, $dir.$name."_output/".$name."_b_detection.".$ext);
    imagepng($img_alpha_detection, $dir.$name."_output/".$name."_alpha_detection.".$ext);
    imagepng($img_withoutalpha, $dir.$name."_output/".$name."_withoutalpha.".$ext);}
  if($ext === "bmp"){
    file_put_contents($dir.$name."_output/".$name."_rgb_detection.".$ext, GD2BMPstring($img_rgb_detection));
    file_put_contents($dir.$name."_output/".$name."_r_detection.".$ext, GD2BMPstring($img_r_detection));
    file_put_contents($dir.$name."_output/".$name."_g_detection.".$ext, GD2BMPstring($img_g_detection));
    file_put_contents($dir.$name."_output/".$name."_b_detection.".$ext, GD2BMPstring($img_b_detection));
    file_put_contents($dir.$name."_output/".$name."_alpha_detection.".$ext, GD2BMPstring($img_alpha_detection));
    file_put_contents($dir.$name."_output/".$name."_withoutalpha.".$ext, GD2BMPstring($img_withoutalpha));}
  if($ext === "gif"){
    imagegif($img_rgb_detection, $dir.$name."_output/".$name."_rgb_detection.".$ext);
    imagegif($img_r_detection, $dir.$name."_output/".$name."_r_detection.".$ext);
    imagegif($img_g_detection, $dir.$name."_output/".$name."_g_detection.".$ext);
    imagegif($img_b_detection, $dir.$name."_output/".$name."_b_detection.".$ext);
    imagegif($img_alpha_detection, $dir.$name."_output/".$name."_alpha_detection.".$ext);
    imagegif($img_withoutalpha, $dir.$name."_output/".$name."_withoutalpha.".$ext);}



  /* write dump alpha */
  $file_dump_alpha = fopen($dir.$name."_output/".$name."_dump_alpha", "w") or die("Unable to open file ".$dir.$name."_output/".$name."_dump_alpha");
  fwrite($file_dump_alpha, $dump_alpha);
  fclose($file_dump_alpha);

  /* write dump r */
  $file_dump_r = fopen($dir.$name."_output/".$name."_dump_r", "w") or die("Unable to open file ".$dir.$name."_output/".$name."_dump_r");
  fwrite($file_dump_r, $dump_r);
  fclose($file_dump_r);

  /* write dump g */
  $file_dump_g = fopen($dir.$name."_output/".$name."_dump_g", "w") or die("Unable to open file ".$dir.$name."_output/".$name."_dump_g");
  fwrite($file_dump_g, $dump_g);
  fclose($file_dump_g);

  /* write dump b */
  $file_dump_b = fopen($dir.$name."_output/".$name."_dump_b", "w") or die("Unable to open file ".$dir.$name."_output/".$name."_dump_b");
  fwrite($file_dump_b, $dump_b);
  fclose($file_dump_b);

  /* write dump rgb */
  $file_dump_rgb = fopen($dir.$name."_output/".$name."_dump_rgb", "w") or die("Unable to open file ".$dir.$name."_output/".$name."_dump_rgb");
  fwrite($file_dump_rgb, $dump_rgb);
  fclose($file_dump_rgb);
}
?>