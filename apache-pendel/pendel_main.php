<?php

//require_once ( 'configuration.php' );
//require_once ( 'tiles.php' );

/* Entry point. To embedd, page must contain the command string [pendel: ...]
 * string $page - String with page content.
 */

function init_config($page) {
    echo "init_config: $page";
    return;

    preg_match('/.*\[pendel:(.*)\].*/', $page, $res);
    if (is_null($res[0])) {
        return;
    }
    preg_match_all('/.*(\w+)=\"(.*)\".*/U', $res[1], $parts);
//    echo var_dump($parts[1]) . '<br>';
    $params = array();
    for ($i = 0; $i < count($parts[1]); $i++) {
        $params[$parts[1][$i]] = $parts[2][$i];
    }
//    echo var_dump($params) . '<br>';
    return set_config($params);
}

/*
// Calculate configuration, save to database and return configuration as array.
// params: $px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat
function set_config($params) {
//    echo 'set_config: ' . $params['x'] . '<br>';
    $configDb = new ConfigurationFacade();
    $configDb->init_table();
    $configDb->modifyItem($params['x'], $params['y'], $params['tile'], $params['tile'], $params['start_lon'], $params['start_lat'], $params['end_lon'], $params['end_lat']);
    $configDb->close();
}

// Return configuration as array.
function get_config() {
    $configDb = new ConfigurationFacade();
    $config = $configDb->getAll();
    $configDb->close();
    return $config;
}

function init_tiles($dir, $config) {
    tiles_refresh_db($dir, $config);
}

function hook_css() {
    $config = get_config();
//    echo 'hook_css: ' . var_dump($config) . '<br>';
    ?> <!-- Closing the PHP here -->
    <style>

        h1 { color: green; }
        #box1 {
            background: khaki;
            position: absolute;
            width: <?php echo $config['px_x']; ?>px;
            height: <?php echo $config['px_y']; ?>px;
        }
        img.thumb {
            position: absolute;
            border-radius: 10px;
            box-shadow: 3px 3px 3px lightgrey;
            width: <?php echo $config['tile_x'] - 5; ?>px;
            height: <?php echo $config['tile_x'] - 5; ?>px;
            margin: 5px;
        }
        img:hover{
            animation: shake 0.2s;
            box-shadow: 3px 3px 3px  gray;
        }
        @keyframes shake {
            0% {
                transform: rotate(-5deg);
            }
            100% {
                transform: rotate(+5deg);
            }
        </style>
        <?php
        //Opening the PHP tag again
    }

    function hook_body($dir) {
        $config = get_config();
//echo 'start: ' . $config['id'] . '<br>';
//$px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat
// start: left, top
// end: right, bottom
// (0.0) ---------------------> x, lon
//   |
//   |
//   |
//   |
//   |
//   v                       . (xmax,ymax)
//  y, lat
        init_tiles($dir, $config);
        $tiles = tiles_read(1);
        ?>

        <div id = "box1">
            <?php
            for ($x = 0; $x < count($tiles); $x++) {
                echo "<img  class=\"thumb\" src=\"" . $dir . "/" . $tiles[$x]['thumb_file'] . "\" alt=\"\"" .
                " style=top:" . ($tiles[$x]['pos_y'] * $config['tile_y']) . "px;left:" . ($tiles[$x]['pos_x'] * $config['tile_x']) . "px;\" />";
            }
            ?>
        </div>
        <?php
    }
*/