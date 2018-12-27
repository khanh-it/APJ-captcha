<?php
// include composer autoload
require 'vendor/autoload.php';

// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;
use App\EA\Intervention\CaptchaAPJ;

// create an image manager instance with favored driver
$manager = new ImageManager(array('driver' => 'GD'));

// to finally create image instances
// $imgFile = 'https://booking.flypeach.com/rucaptcha';
$imgFile = $_GET['f'] ?? $argv[1];
$imgFile = 'images/' . $imgFile;
$image = $manager->make($imgFile);
//
// 
$captcha = new CaptchaAPJ($image, $manager);
$captcha->crop();
$captcha->removeLine([
    'one_group_only' => true,
    'pixel_count_max' => 3
]);
/* $captcha->removeLine([
    'one_group_only' => true,
    'pixel_count_max' => 3
]); */
// $captcha->crop();
// $images = $captcha->split(); // $image = $images[$_GET['img_idx'] ?? 0];
$images = [$image];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <style>
        body {
            background-color:grey;
            padding: 6px;
        }
        table {
            background-color:white;
            float: left;
            margin-right: 5px;
        }
        table td {font-size:1.4em;padding:0;line-height:1em;}
        table tfoot tr {}
        table tfoot td {
            padding: 3px 0;
            border-top:1px dashed black;
            border-right:1px dashed black;
            line-height:1em;
            font-weight:bold;
            color:blue;
        }
    </style>
</head>
<body>
<img width="300" src="<?php echo $imgFile ?>" />
<br clear="all" />
<?php
foreach ($images as $image) {
    CaptchaAPJ::render($image);
}
?>
</body>
</html>