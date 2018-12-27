<?php
require_once(__DIR__ . '/grafika/src/autoloader.php');

use Grafika\Grafika; // Import package

$editor = Grafika::createEditor(); // Create the best available editor

$editor->open( $image, __DIR__ . '/images/1.gif');

// $filter = Grafika::createFilter('Grayscale'); // Create filter object depending on available editor
// $filter = Grafika::createFilter('Sobel'); // Create filter object depending on available editor
// $filter = Grafika::createFilter('Sharpen', 1);
// $filter = Grafika::createFilter('Pixelate', 2);
// $filter = Grafika::createFilter('Dither', 'diffusion');
$filter = Grafika::createFilter('Dither', 'ordered');
$editor->apply( $image, $filter ); // Apply it to an image 

header('Content-type: image/png'); // Tell the browser we're sending a png image
$image->blob('PNG'); // Output raw binary png format