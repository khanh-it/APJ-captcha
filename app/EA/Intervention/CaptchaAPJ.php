<?php

namespace App\EA\Intervention;

/**
 * 
 */
class CaptchaAPJ
{
    /**
     * @var int Coilor white (16777215)
     */
    const COLOR_WHITE = 16777215;

    /**
     * @var int Line's max pixels (3)
     */
    const LINE_MAX_PIXEL = 3;

    /**
     * @var \Intervention\Image\Image
     */
    public $image;

    /**
     * @var int
     */
    protected $imgW;

    /**
     * @var int
     */
    protected $imgH;

    public static function dd()
    {
        echo "<pre>";
        $args = \func_get_args();
        var_dump(...$args);
        echo "</pre>";
        // die();
    }

    /**
     * 
     */
    public function __construct(\Intervention\Image\Image $image)
    {
        $this->image = $image;
        $this->imgW = $image->width();
        $this->imgH = $image->height();
    }

    public static function isColorWhite($colorInt)
    {
        return (static::COLOR_WHITE === $colorInt);
    }

    /**
     * 
     */
    public function lineInfo()
    {
        $pixels = array();
        for ($x = 0; $x < $this->imgW; $x++) {
            $_idx = -1;
            $_last_color = null;
            $pixels[$x] = $pixels[$x] ?? [];
            for ($y = 0; $y < $this->imgH; $y++) {
                $colorInt = $this->image->pickColor($x, $y, 'int');
                //
                if ($_last_color !== $colorInt) {
                    $_idx++;
                }
                $_last_color = $colorInt;
                //
                if (!static::isColorWhite($colorInt)) {
                    //
                    $pixels[$x][$_idx] = $pixels[$x][$_idx] ?? [];
                    //
                    $pixels[$x][$_idx][] = $y;
                }
            }
        }
        //
        return $pixels;
    }

    /**
     * 
     */
    public function removeLine(array $options = array())
    {
        //
        $lineInfo = $this->lineInfo();
        $lastPixels = null;
        $willAddY = 1;
        $setColor = static::COLOR_WHITE;
        $setColor = '#ff0000';
        $setColorUp = '#0000ff';
        $setColorDown = '#008000';
        //
        for ($x = 0; $x < $this->imgW; $x++) {
            $pixels = $lineInfo[$x] ?? [];
            $pixels = array_values($pixels);
            if (is_null($lastPixels)) {
                $lastPixels = $pixels;
            }
            //
            if (empty($pixels)) {

            }
            // 
            else {
                $cnt = count($pixels);
                $_rangeY = [];
                $_rangeYUp = [];
                $_rangeYDown = [];
                foreach ($lastPixels as $group) {
                    if (!empty($group)) {
                        $_rangeY = array_merge($_rangeY, $group);
                        $yUp = min($group) - $willAddY;
                        $yDown = max($group) + $willAddY;
                        if (!in_array($yUp, $_rangeY)) {
                            $_rangeYUp[] = $yUp;
                        }
                        if (!in_array($yDown, $_rangeY)) {
                            $_rangeYDown[] = $yDown;
                        }
                    }                    
                } unset($yUp, $yDown);
                // static::dd($_rangeY); die();
                $_lastPixels = [];
                if (!empty($pixels)) {
                    if (41 === $x) {
                        static::dd($x, $pixels, $_rangeY, $_rangeYUp, $_rangeYDown, $lastPixels);
                    }
                    $inArrCnt = 0;
                    // Go center
                    foreach ($pixels as $groupIdx => $group) {
                    // for ($groupIdx = count($pixels) - 1; $groupIdx >= 0; $groupIdx--) { $group = $pixels[$groupIdx]; $pixelCnt = count($group);
                        foreach ($group as $y) {
                            if (\in_array($y, $_rangeY)) {
                                ++$inArrCnt;
                                if ($inArrCnt > static::LINE_MAX_PIXEL) {
                                    break;
                                }
                                $_lastPixels[$groupIdx] = $_lastPixels[$groupIdx] ?? [];
                                $_lastPixels[$groupIdx][] = $y;
                                $this->image->pixel($setColor, $x, $y);
                            }
                        }
                    }
                    // Go up
                    foreach ($pixels as $groupIdx => $group) {
                        foreach ($group as $y) {
                            if (\in_array($y, $_rangeYUp)) {
                                ++$inArrCnt;
                                if ($inArrCnt > static::LINE_MAX_PIXEL) {
                                    break;
                                }
                                $_lastPixels[$groupIdx] = $_lastPixels[$groupIdx] ?? [];
                                $_lastPixels[$groupIdx][] = $y;
                                $this->image->pixel($setColorUp, $x, $y);
                            }
                        }
                    }
                    // Go down
                    foreach ($pixels as $groupIdx => $group) {
                        foreach ($group as $y) {
                            if (\in_array($y, $_rangeYDown)) {
                                ++$inArrCnt;
                                if ($inArrCnt > static::LINE_MAX_PIXEL) {
                                    break;
                                }
                                $_lastPixels[$groupIdx] = $_lastPixels[$groupIdx] ?? [];
                                $_lastPixels[$groupIdx][] = $y;
                                $this->image->pixel($setColorDown, $x, $y);
                            }
                        }
                    }
                }
                $lastPixels = !empty($_lastPixels) ? $_lastPixels : $lastPixels;
            }
            //
            // $lastPixels = empty($pixels) ? $lastPixels : $pixels;
            //
            if ($x >= 80) {
                // break;
            }
        }
        return $this;
    }

    /**
     * 
     */
    public function removeLine1st(array $options = array())
    {
        //
        $oneGroupOnly = ($options['one_group_only'] ?? null);
        $pixelCntMax = ($options['pixel_count_max'] ?? 2);
        //
        $lineInfo = $this->lineInfo();
        //
        foreach ($lineInfo as $x => $pixels) {
            $cnt = count($pixels);
            // @TODO: Only have 1 group of colors white per line.
            if (true === $oneGroupOnly) {
                // static::dd($x, $cnt); // die();
                if (1 != $cnt) {
                    continue;
                }
            }
            foreach ($pixels as $groupIdx => $group) {
                $pixelCnt = count($group);
                if (1 <= $pixelCnt && $pixelCnt <= $pixelCntMax) {
                    foreach ($group as $y) {
                        $this->image->pixel(static::COLOR_WHITE, $x, $y);
                    }
                    continue;
                }
            }
        }
        //
        return $pixels;
    }

    public function coordinatesInfo()
    {
        $pixels = array();
        //
        $topLeft = null;
        $topRight = null;
        $bottomLeft = null;
        $bottomRight = null;
        //
        $nonEmptyColLeft = null;
        $nonEmptyColRight = null;
        $nonEmptyLineTop = null;
        $nonEmptyLineBottom = null;
        //
        for ($x = 0; $x < $this->imgW; $x++) {
            for ($y = 0; $y < $this->imgH; $y++) {
                //
                if (is_null($nonEmptyLineTop)) {
                    if (!$this->isLineEmpty($y)) {
                        $nonEmptyLineTop = $y;
                    }
                }
                //
                if (is_null($nonEmptyLineBottom)) {
                    if (!$this->isLineEmpty($_y = ($this->imgH - 1 - $y))) {
                        $nonEmptyLineBottom = $_y;
                    }
                }
                // Top Left
                if (!$topLeft) {
                    $tlColorInt = $this->image->pickColor($_x = $x, $_y = $y, 'int');
                    if (!static::isColorWhite($tlColorInt)) {
                        $topLeft = [$_x, $_y];
                    }
                }
                // Top Right
                if (!$topRight) {
                    $trColorInt = $this->image->pickColor($_x = ($this->imgW - 1 - $x), $_y = $y, 'int');
                    if (!static::isColorWhite($trColorInt)) {
                        $topRight = [$_x, $_y];
                    }
                }
                // Bottom Left
                if (!$bottomLeft) {
                    $blColorInt = $this->image->pickColor($_x = $x, $_y = ($this->imgH - 1 - $y), 'int');
                    if (!static::isColorWhite($blColorInt)) {
                        $bottomLeft = [$_x, $_y];
                    }
                }
                // Bottom Right
                if (!$bottomRight) {
                    $brColorInt = $this->image->pickColor($_x = ($this->imgW - 1 - $x), $_y = ($this->imgH - 1 - $y), 'int');
                    if (!static::isColorWhite($brColorInt)) {
                        $bottomRight = [$_x, $_y];
                    }
                }
                //
                if ($topLeft && $topRight && $bottomLeft && $bottomRight) {
                    break;
                }
            }
            //
            if (is_null($nonEmptyColLeft)) {
                if (!$this->isColEmpty($x)) {
                    $nonEmptyColLeft = $x;
                }
            }
            //
            if (is_null($nonEmptyColRight)) {
                if (!$this->isColEmpty($_x = ($this->imgW - 1 - $x))) {
                    $nonEmptyColRight = $_x;
                }
            }
        }
        //
        $return = [
            'width' => $this->imgW,
            'height' => $this->imgH,
            'non_empty_line' => [$nonEmptyLineTop, $nonEmptyLineBottom],
            'non_empty_col' => [$nonEmptyColLeft, $nonEmptyColRight],
            'tl' => $topLeft,
            'tr' => $topRight,
            'bl' => $bottomLeft,
            'br' => $bottomRight,
            'avail_width' => max($topRight[0] - $topLeft[0], $bottomRight[0] - $bottomLeft[0]),
            'avail_height' => max($bottomLeft[1] - $topLeft[1], $bottomRight[1] - $topRight[1])
        ];
        return $return;
        echo '<pre>';
        print_r($return);
        exit();
    }

    public function isLineEmpty($line)
    {
        $empty = true;
        for ($x = 0; $x < $this->imgW; $x++) {
            $colorInt = $this->image->pickColor($x, $line, 'int');
            if (!static::isColorWhite($colorInt)) {
                $empty = false;
                break;
            }
        }
        return $empty;
    }

    public function isColEmpty($col)
    {
        $empty = true;
        for ($y = 0; $y < $this->imgH; $y++) {
            $colorInt = $this->image->pickColor($col, $y, 'int');
            if (!static::isColorWhite($colorInt)) {
                $empty = false;
                break;
            }
        }
        return $empty;
    }

    /**
     * 
     */
    public function crop()
    {
        $cords = $this->coordinatesInfo();
        $padding = 0;
        $this->image->crop(
            ($cords['non_empty_col'][1] - $cords['non_empty_col'][0]) + $padding
            , ($this->imgH = ($cords['non_empty_line'][1] - $cords['non_empty_line'][0])) + $padding
            , $cords['non_empty_col'][0] - $padding
            , $cords['non_empty_line'][0] - $padding
        );
        $this->imgW = $this->image->width();
        $this->imgH = $this->image->height();
        return $this;
    }

    /**
     * 
     */
    public function split()
    {
        $MAX_CHARS = 7; // total chars in image
        $fontSize = 23; //px
        // 
        $lineInfo = $this->lineInfo();
        //
        $images = [];
        //
        $_minX = 0;
        $minX = null;
        $limitCnt = 0;
        $threhold = 2/* @TODO: */;
        for ($chars = 0; $chars < $MAX_CHARS; $chars++) {
            //
            $first = $fontSize * ($chars + 1);
            $start = $minX;
            if (is_null($start)) {
                $start = $first - ($_tmp = (intval($fontSize / 2) + $threhold));
            } else {
                $start += ($_tmp = (intval($fontSize / 2) - $threhold));
            }
            $end = $first;
            
            //
            $image = clone $this->image;
            $captcha = new static($image);
            //
            $minX = is_null($minX) ? $first : $minX;
            $minX = $first;
            $lineXClrWhiteCntMin = PHP_INT_MAX;
            for ($x = $start; $x < $end; $x++) {
                // line info at 'x'
                $lineXInf = $lineInfo[$x] ?? null;
                if (is_null($lineXInf)) {
                    break;
                }
                // number of colors white at 'x'
                $lineXClrWhiteCnt = array_sum(array_map(function($var) {
                    return count($var);
                }, $lineXInf));
                if (($lineXClrWhiteCnt >= $limitCnt) && ($lineXClrWhiteCnt <= $lineXClrWhiteCntMin)) {
                    $lineXClrWhiteCntMin = $lineXClrWhiteCnt;
                    // static::dd($lineXClrWhiteCntMin);
                    // $minX = $x;
                }
            }
            $_width = $minX - $_minX;
            static::dd("first: {$first}, start: {$start}, end: {$end}, minX: {$minX}, tmp: {$_tmp}, width: {$_width}.") . PHP_EOL;
            // continue;
            $image->crop(
                $_width // width
                , $image->height() // height
                , $_minX // x offset
                , 0 // y offset
            );
            $images[] = $image;
            $_minX = $minX;
        }
        return $images;
    }

    /**
     * 
     * @var \Intervention\Image\Image $image
     * @return string
     */
    public static function render(\Intervention\Image\Image $image)
    {
        \extract(compact(
            'image'
        ));
        require(__DIR__ . '/../../resources/views/intervention/captcha.phtml');
    }
}