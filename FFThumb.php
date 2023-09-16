<?php
ini_set("display_errors", "on");

define("LOCK_FILE", "ffthumb.lock");
define("THUMB_WIDTH", "360");
define("THUMB_HEIGHT", "180");
define("IMAGE_EXTENSION", "png");
define("SCREEN_QUALITY", "100");
define("THUMB_QUALITY", "100");
define("FFMPEG_PATH", "ffmpeg");
define("VIDEO_OFFSET", "120");

$vid = realpath($_GET["vid"]);
$scr = __DIR__ . "/scrs/" . md5($vid) . "." . IMAGE_EXTENSION;
$thb =
    __DIR__ .
    "/thbs/" .
    THUMB_WIDTH .
    "X" .
    THUMB_HEIGHT .
    "_" .
    md5($vid) .
    "." .
    IMAGE_EXTENSION;

if (!file_exists($thb)) {
    while (file_exists(LOCK_FILE)) {
        sleep(1);
    }

    file_put_contents(LOCK_FILE, time());

    $vidshell = escapeshellarg($vid);
    $scrshell = escapeshellarg($scr);

    $cmd =
        FFMPEG_PATH .
        " -ss " .
        VIDEO_OFFSET .
        " -i " .
        $vidshell .
        " -q:v -2 -crf:v -2 " .
        $scrshell;
    //exit;
    if (!file_exists($scr)) {
        shell_exec($cmd);

        usleep(500);
    }

    $cmd =
        FFMPEG_PATH .
        " -ss 1 -i " .
        $vidshell .
        " -q:v -2 -crf:v -2 " .
        $scrshell;
    //exit;
    if (!file_exists($scr)) {
        shell_exec($cmd);
        usleep(500);
    }
}
header("Content-type: image/png");
//$image = readfile(__DIR__."/error.png");
if (!file_exists($thb)) {
    // Create a new object
    try {
        $image = new Imagick($scr);

        // Crop and resize the image
        $image->cropThumbnailImage(THUMB_WIDTH, THUMB_HEIGHT);

        // Remove the canvas using the line below
        // if the image is a .gif file:
        // $image->setImagePage(0, 0, 0, 0);

        $image->writeImage($thb);
    } catch (Exception $e) {
    }
} else {
    $image = readfile($thb);
}

// Display resulting image:
echo $image;

unlink(LOCK_FILE);
