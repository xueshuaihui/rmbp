<?php

/**
 * Created by PhpStorm.
 * User: djunny
 * Date: 2015/8/6
 * Time: 0:58
 */
include ROOT_PATH . 'control/api_control.class.php';

class mapi_control extends api_control {

    function __construct(&$conf) {
        parent::__construct($conf);
    }

    /**
     * upload user_avatar
     */
    function on_user_avatar() {
        $is_auth = $this->U->init($this->conf, false);
        if (!$is_auth) {
            $this->_message('login_first');
        }

        $user_id = $this->U->user_id;

        $avatar_file = avatar($user_id, '', 0);
        $avatar_dir = dirname($avatar_file);
        $static_dir = $this->conf['static_dir'];
        !is_dir($static_dir . $avatar_dir) && mkdir($static_dir . $avatar_dir, 0777, 1);

        list(,, $type) = getimagesize($_POST["imageSource"]);
        $viewPortW = $_POST["viewPortW"];
        $viewPortH = $_POST["viewPortH"];
        $pWidth = $_POST["imageW"];
        $pHeight = $_POST["imageH"];
        $image_types = array(
            1 => 'gif',
            2 => 'jpg',
            3 => 'png',
        );
        $ext = $image_types[$type];
        $function = return_correct_function($ext);
        $image = $function($_POST["imageSource"]);
        $width = imagesx($image);
        $height = imagesy($image);
        // Resample
        $image_p = imagecreatetruecolor($pWidth, $pHeight);
        set_transparency($image, $image_p, $ext);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $pWidth, $pHeight, $width, $height);
        imagedestroy($image);
        $widthR = imagesx($image_p);
        $hegihtR = imagesy($image_p);
        $selectorX = $_POST["selectorX"];
        $selectorY = $_POST["selectorY"];
        if ($_POST["imageRotate"]) {
            $angle = 360 - $_POST["imageRotate"];
            $image_p = imagerotate($image_p, $angle, 0);

            $pWidth = imagesx($image_p);
            $pHeight = imagesy($image_p);

            //print $pWidth."---".$pHeight;
            $diffW = abs($pWidth - $widthR) / 2;
            $diffH = abs($pHeight - $hegihtR) / 2;
            $_POST["imageX"] = ($pWidth > $widthR ? $_POST["imageX"] - $diffW : $_POST["imageX"] + $diffW);
            $_POST["imageY"] = ($pHeight > $hegihtR ? $_POST["imageY"] - $diffH : $_POST["imageY"] + $diffH);
        }


        $dst_x = $src_x = $dst_y = $src_y = 0;

        if ($_POST["imageX"] > 0) {
            $dst_x = abs($_POST["imageX"]);
        } else {
            $src_x = abs($_POST["imageX"]);
        }
        if ($_POST["imageY"] > 0) {
            $dst_y = abs($_POST["imageY"]);
        } else {
            $src_y = abs($_POST["imageY"]);
        }

        $viewport = imagecreatetruecolor($_POST["viewPortW"], $_POST["viewPortH"]);
        set_transparency($image_p, $viewport, $ext);

        imagecopy($viewport, $image_p, $dst_x, $dst_y, $src_x, $src_y, $pWidth, $pHeight);
        imagedestroy($image_p);


        $selector = imagecreatetruecolor($_POST["selectorW"], $_POST["selectorH"]);
        set_transparency($viewport, $selector, $ext);
        imagecopy($selector, $viewport, 0, 0, $selectorX, $selectorY, $_POST["viewPortW"], $_POST["viewPortH"]);



        parse_image('jpg', $selector, $this->conf['static_dir'].$avatar_file);

        imagedestroy($viewport);

        $avatar = base64_encode(file_get_contents($this->conf['static_dir'].$avatar_file));


        $attachment = $this->attachment->upload_base64_image($avatar,
            $user_id, 'user', $user_id,
            'avatar', $avatar_file, '', 1);
        echo $this->conf['static_url'] . $avatar_file;
        exit;

    }

}


function determine_image_scale($sourceWidth, $sourceHeight, $targetWidth, $targetHeight) {
    $scalex = $targetWidth / $sourceWidth;
    $scaley = $targetHeight / $sourceHeight;
    return min($scalex, $scaley);
}

function return_correct_function($ext) {
    $function = "";
    switch ($ext) {
        case "png":
            $function = "imagecreatefrompng";
        break;
        case "jpeg":
            $function = "imagecreatefromjpeg";
        break;
        case "jpg":
            $function = "imagecreatefromjpeg";
        break;
        case "gif":
            $function = "imagecreatefromgif";
        break;
    }
    return $function;
}

function parse_image($ext, $img, $file = null) {
    switch ($ext) {
        case "png":
            imagepng($img, ($file != null ? $file : ''));
        break;
        case "jpeg":
        case "jpg":
            imagejpeg($img, ($file ? $file : ''), 90);
        break;
        case "gif":
            imagegif($img, ($file ? $file : ''));
        break;
    }
}

function set_transparency($imgSrc, $imgDest, $ext) {

    if ($ext == "png" || $ext == "gif") {
        $trnprt_indx = imagecolortransparent($imgSrc);
        // If we have a specific transparent color
        if ($trnprt_indx >= 0) {
            // Get the original image's transparent color's RGB values
            $trnprt_color = imagecolorsforindex($imgSrc, $trnprt_indx);
            // Allocate the same color in the new image resource
            $trnprt_indx = imagecolorallocate($imgDest, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
            // Completely fill the background of the new image with allocated color.
            imagefill($imgDest, 0, 0, $trnprt_indx);
            // Set the background color for new image to transparent
            imagecolortransparent($imgDest, $trnprt_indx);
        } // Always make a transparent background color for PNGs that don't have one allocated already
        elseif ($ext == "png") {
            // Turn off transparency blending (temporarily)
            imagealphablending($imgDest, true);
            // Create a new transparent color for image
            $color = imagecolorallocatealpha($imgDest, 0, 0, 0, 127);
            // Completely fill the background of the new image with allocated color.
            imagefill($imgDest, 0, 0, $color);
            // Restore transparency blending
            imagesavealpha($imgDest, true);
        }

    }
}