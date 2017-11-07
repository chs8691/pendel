<?php
/*
 * TODO Media settings 'Organize my uploads into month...' must be enabled.
 */
require_once( 'configuration.php' );

require_once( 'tiles.php' );

/* Entry point. To embedd, page must contain the command string [pendel: ...]
 * string $page - String with page content.
 * Returns true, if pendel processed, otherwise false.
 */

function init_config($page) {
//    echo "init_config() for $page <br>";

    preg_match('/.*\[pendel:(.*)\].*/', $page, $res);
    if (is_null($res[0])) {
        echo 'leaving init_config() <br>';
        return false;
    }
    preg_match_all('/.*(\w+)=\"(.*)\".*/U', $res[1], $parts);
    $params = array();
    for ($i = 0; $i < count($parts[1]); $i++) {
        $params[$parts[1][$i]] = $parts[2][$i];
    }
//    echo 'calling set_config <br>';
    set_config($params);
    return true;
}

// Calculate configuration, save to database and return configuration as array.
// params: $px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat
function set_config($params) {
//    echo '5 set_config: <br>';
    $configDb = new ConfigurationFacade();
    $configDb->init_table();
    $configDb->modifyItem($params['x'], $params['y'], $params['tile'], $params['tile'], $params['start_lon'], $params['start_lat'], $params['end_lon'], $params['end_lat']);
//    echo 'END set_config: <br>';
}

function init_tiles($dir, $config) {
    tiles_refresh_db($dir, $config);
}

function hook_css() {
    $config = get_config();
    $angle = $config->tilespace * 2;
//
//    echo "imagesize=$config->imagesize <br>";
//    echo "tilesize=$config->tilesize <br>";
//    echo "mid=$mid <br>";
    ?> <!-- Closing the PHP here -->
    <style>
        #svg-tile-rect {
            fill:rgb(200,200,200);}

        #svg-tile-group:hover {
            fill: #00cc00 ;
            animation: shake 0.2s ;
        }
        @keyframes shake {
            25% {
                transform: rotate(-5deg) ;
            }
            75% {
                transform: rotate(+5deg) ;
            }
        </style>
        <?php
        /*
          100% {
          transform: rotate(+5deg);
          }
         *
         */
        //Opening the PHP tag again
    }

    function hook_body($url) {
        hook_css();
        $config = get_config();
//        echo 'test: ' . $config->test . '<br>';
//        echo 'hook_body: ' . $url . '<br>';
//$px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat
// start: left, top
// end: right, bottom
// (0.0) ---------------------> x, lon   CANVAS
//   |<---->                ^            SPACE (x and y)
//   |                      |
//   |                      v
//   |      0---------------+               TILE
//   |      |               |
//   |      |  0---------+  |               IMAGE
//   |      |  |         |  |
//   |      |  |    0    |  |               Middle
//   |      |  |         |  |
//   |      |  +---------+  |
//   |      |               |
//   |      +---------------+
//   |
//   v                                   . Canvas (xmax,ymax)
//  y, lat
        $tiles = tiles_read(1);
        $canvasheight = $config->px_y + $config->tilespace;
        $canvaswidth = $config->px_x + $config->tilespace;
        $offseti = $config->imagesize / 2;
        $offsett = $config->tilesize / 2;
        ?>
        <svg  width="90%"  viewBox="0 0 <?php echo $canvaswidth ?> <?php echo $canvasheight; ?>">
        <filter id="f1">
            <feGaussianBlur stdDeviation="4" />
        </filter>
        <rect width="<?php echo $canvaswidth; ?>" height="<?php echo $canvasheight; ?>" style="fill:rgb(240,240,240);" />
        <?php
        foreach ($tiles as $tile) {
            $midx = ($tile->pos_x * ($config->tilesize + $config->tilespace)) + $config->tilespace + $offsett;
            $midy = ($tile->pos_y * ($config->tilesize + $config->tilespace)) + $config->tilespace + $offsett;
            $src = $url . "/" . $tile->thumb_file;
            echo "<g transform=\"translate($midx,$midy)\">";
            echo "<g id=\"svg-tile-group\"> ";
            echo "<rect id=\"svg-tile-rect\" y=\"-$offsett\" x=\"-$offsett\" width=\"$config->tilesize\" height=\"$config->tilesize\" filter=\"url(#f1)\" />";
            echo "<image xlink:href=\"$src\" y=\"-$offseti\" x=\"-$offseti\" width=\"$config->imagesize\" height=\"$config->imagesize\"  />";
            echo "</g>";
            echo "</g>";
        }
        ?>
        </svg>
        <br>
        <?php
        $t = time();
        echo(date("H:i:s"));
    }

    /*
     *            echo "<rect id=\"svg-tile-rect\" y=\"$framey\" x=\"$framex\" width=\"$config->tilesize\" height=\"$config->tilesize\" filter=\"url(#f1)\" />";
      echo "<image xlink:href=\"$src\" y=\"$posy\" x=\"$posx\" width=\"$config->imagesize\" height=\"$config->imagesize\"  />";

     */
    /* Return configuration as array plus calculated stuff.
     *
     * Calculated values
     * -----------------
     * tilespace : space between two tiles and frame space
     * tilesize : use this instead of tile_x and tile_y
     * imagesize : x and y size of the image
     *
     * Database values (user input)
     * ---------------
     * tile_x/tile_y : pixel size of the whole tile plus space
     * px_x/px_y : Pixel size of the canvas
     * start_lon/start_lat : //location in 0,0
     * end_lon/end_lat : //location in x,y
     */

    function get_config() {
        $configDb = new ConfigurationFacade();
        $config = $configDb->get();
        $config->tilespace = $config->tile_x * 4 / 100;
        $config->tilesize = $config->tile_x - $config->tilespace;
        $config->imagesize = $config->tilesize - (2 * $config->tilespace);

        return $config;
    }
