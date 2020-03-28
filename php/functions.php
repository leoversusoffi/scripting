<?php
function imagecreatefrom_bmp($filename)
{

    //Ouverture du fichier en mode binaire
    if (! $f1 = fopen($filename,"rb")) return FALSE;

    //1 : Chargement des entetes FICHIER
    $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
    if ($FILE['file_type'] != 19778) return FALSE;

    //2 : Chargement des entetes BMP
    $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
           '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
           '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
    $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);

    //add fix
    $header_fix = $BMP['header_size'];
    //fin add fix

    if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];

    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
    $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
    $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
    $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
    $BMP['decal'] = 4-(4*$BMP['decal']);

    if ($BMP['decal'] == 4) $BMP['decal'] = 0;

    //header fix
    //Fermeture du fichier
    fclose($f1);
    //Ouverture du fichier en mode binaire
    if (! $f1 = fopen($filename,"rb")) return FALSE;
    fread($f1,14);
    fread($f1,$header_fix);
    //fin header fix

    //3 : Chargement des couleurs de la palette
    $PALETTE = array();
    if ($BMP['colors'] < 16777216)
    {
      $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
    }

    //4 : Creation de l'image
    $IMG = fread($f1,$BMP['size_bitmap']);
    $VIDE = chr(0);

    $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
    //add alpha
    if ($BMP['bits_per_pixel'] == 32){imagealphablending($res, false);imagesavealpha($res, true);}
    //fin add alpha
    $P = 0;
    $Y = $BMP['height']-1;
    while ($Y >= 0)
    {
      $X=0;
      while ($X < $BMP['width'])
      {
        //Add 32 bits per pixel
        if ($BMP['bits_per_pixel'] == 32)
        {
          $COLOR_b = unpack("v",substr($IMG,$P,1).$VIDE);
          $COLOR_g = unpack("v",substr($IMG,$P+1,1).$VIDE);
          $COLOR_r = unpack("v",substr($IMG,$P+2,1).$VIDE);
          $COLOR_a = unpack("v",substr($IMG,$P+3,1).$VIDE);

          $COLOR = array(1 => imagecolorallocatealpha($res, $COLOR_r[1], $COLOR_g[1], $COLOR_b[1], round($COLOR_a[1]/2,0, PHP_ROUND_HALF_DOWN)));
          //$COLOR = array(1 => imagecolorallocatealpha($res, $COLOR_r[1], $COLOR_g[1], $COLOR_b[1], 75/*$COLOR_a[1]*/));
        }
        else/*Fin 32 bits per pixel*/if ($BMP['bits_per_pixel'] == 24)
        {
          $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
        }
        elseif ($BMP['bits_per_pixel'] == 16)
        {

          $COLOR = unpack("v",substr($IMG,$P,2));
          $blue = ($COLOR[1] & 0x001f) << 3;
          $green = ($COLOR[1] & 0x07e0) >> 3;
          $red = ($COLOR[1] & 0xf800) >> 8;
          $COLOR[1] = $red * 65536 + $green * 256 + $blue;

        }
        elseif ($BMP['bits_per_pixel'] == 8)
        {
          $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
        }
        elseif ($BMP['bits_per_pixel'] == 4)
        {
          $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
          if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
        }
        elseif ($BMP['bits_per_pixel'] == 1)
        {
          $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
          if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
          elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
          elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
          elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
          elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
          elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
          elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
          elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
          $COLOR[1] = $PALETTE[$COLOR[1]+1];
        }
        else
          return FALSE;

        imagesetpixel($res,$X,$Y,$COLOR[1]);
        $X++;
        $P += $BMP['bytes_per_pixel'];
      }

      $Y--;
      $P+=$BMP['decal'];
    }

    //Fermeture du fichier
    fclose($f1);

    return $res;
}


function GD2BMPstring(&$gd_image)
  {
    $imageX = ImageSX($gd_image);
    $imageY = ImageSY($gd_image);

    $BMP = '';
    for ($y = ($imageY - 1); $y >= 0; $y--) {
      $thisline = '';
      for ($x = 0; $x < $imageX; $x++) {
        $argb = GetPixelColor($gd_image, $x, $y);
        $thisline .= chr($argb['blue']).chr($argb['green']).chr($argb['red'])/*Add alpha*/.chr($argb['alpha']/*Fin Add alpha*/);
      }
      while (strlen($thisline) % 4) {
        $thisline .= "\x00";
      }
      $BMP .= $thisline;
    }

    $bmpSize = strlen($BMP) + 14 + 40;
    // BITMAPFILEHEADER [14 bytes] - http://msdn.microsoft.com/library/en-us/gdi/bitmaps_62uq.asp
    $BITMAPFILEHEADER  = 'BM';                                    // WORD    bfType;
    $BITMAPFILEHEADER .= LittleEndian2String($bmpSize, 4); // DWORD   bfSize;
    $BITMAPFILEHEADER .= LittleEndian2String(       0, 2); // WORD    bfReserved1;
    $BITMAPFILEHEADER .= LittleEndian2String(       0, 2); // WORD    bfReserved2;
    $BITMAPFILEHEADER .= LittleEndian2String(      54, 4); // DWORD   bfOffBits;

    // BITMAPINFOHEADER - [40 bytes] http://msdn.microsoft.com/library/en-us/gdi/bitmaps_1rw2.asp
    $BITMAPINFOHEADER  = LittleEndian2String(      40, 4); // DWORD  biSize;
    $BITMAPINFOHEADER .= LittleEndian2String( $imageX, 4); // LONG   biWidth;
    $BITMAPINFOHEADER .= LittleEndian2String( $imageY, 4); // LONG   biHeight;
    $BITMAPINFOHEADER .= LittleEndian2String(       1, 2); // WORD   biPlanes;
    $BITMAPINFOHEADER .= LittleEndian2String(/*L*/ 32, 2); // WORD   biBitCount;
    $BITMAPINFOHEADER .= LittleEndian2String(       0, 4); // DWORD  biCompression;
    $BITMAPINFOHEADER .= LittleEndian2String(       0, 4); // DWORD  biSizeImage;
    $BITMAPINFOHEADER .= LittleEndian2String(    2835, 4); // LONG   biXPelsPerMeter;
    $BITMAPINFOHEADER .= LittleEndian2String(    2835, 4); // LONG   biYPelsPerMeter;
    $BITMAPINFOHEADER .= LittleEndian2String(       0, 4); // DWORD  biClrUsed;
    $BITMAPINFOHEADER .= LittleEndian2String(       0, 4); // DWORD  biClrImportant;

    return $BITMAPFILEHEADER.$BITMAPINFOHEADER.$BMP;
  }


  function LittleEndian2String($number, $minbytes=1)
  {
    $intstring = '';
    while ($number > 0) {
      $intstring = $intstring.chr($number & 255);
      $number >>= 8;
    }
    return str_pad($intstring, $minbytes, "\x00", STR_PAD_RIGHT);
  }

  function GetPixelColor(&$img, $x, $y)
  {
    if (!is_resource($img)) {
      return false;
    }
    return @ImageColorsForIndex($img, @ImageColorAt($img, $x, $y));
  }
?>
