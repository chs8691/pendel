<?php
require_once 'pendel_main.php';
$configstr = '[pendel: x="1000", y="400", tile="30", ' .
        'start_lon="8.615272", start_lat="50.2031599",' .
        'end_lon="9.1137766", end_lat="50.0544646"]';
init_config($configstr);
// start: left, top
// end: right, bottom
// (0.0) ---------------------> x, lon
//   |
//   |
//   |
//   |
//   |
//   v                       . (xmax,ymax)
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <?php hook_css(); ?>
    </head>
    <body>
        <?php hook_body('uploads'); ?>
        <!--        <div id = "box1"> -->
        <!-- ?php
        for ($x = 0; $x < count($tiles); $x++) {
            echo "<img  class=\"thumb\" src=\"" . $dir . "/" . $tiles[$x]['thumb_file'] . "\" alt=\"\"" .
            " style=top:" . ($tiles[$x]['pos_y'] * $config['tile_y']) . "px;left:" . ($tiles[$x]['pos_x'] * $config['tile_x']) . "px;\" />";
        }
        ? -->
        <!-- </div>-->
    </body>

</html>
