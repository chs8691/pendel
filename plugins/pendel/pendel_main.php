<?php
/*
 * TODO Media settings 'Organize my uploads into month...' must be enabled.
 */
require_once( 'configuration.php' );

require_once( 'tiles.php' );

/* Extracts the name (id) of the pendel [pendel: ...]
 * string $page - String with pendel page content.
 * Returns String with id or false
 */

function extract_pendel_id($page) {
//    echo "6 extract_pendel_id() for $page <br>";
//
    //id must have a space before
    preg_match('/.*\[pendel:.* id=\"(.*)\".*\].*/U', $page, $parts);

//    echo("extract_pendel_id 0 = " . $parts[0] . '.<br>'); Shows all
//    echo("extract_pendel_id 1 = " . $parts[1] . '.<br>'); Shows id

    $id = $parts[1];
    if ($id == NULL || strlen($id) == 0) {
        trigger_error("Pendel configuration: missing id, e.g. 'id=abc'.", E_USER_WARNING);
        return false;
    }
    trigger_error("Found page with pendel id=$id", E_USER_NOTICE);
    return $id;
}

/* Checks, if content has pendel entry [pendel: ...]
 * string $page - String with page content.
 * Returns true, if pendel command found, otherwise false.
 */

function is_pendel_content($page) {
//    echo "init_config() for $page <br>";

    $ret = preg_match('/.*\[pendel:(.*)\].*/', $page);
    return !(is_null($ret) or $ret == 0);
}

/* Entry point. Should only be called for pandel relevant content. So, first
 * call is_pendel_content().
 * string $page - String with page content.
 *
 */

function init_config($page) {
//    echo "1 init_config() for $page <br>";

    preg_match('/.*\[pendel:(.*)\].*/', $page, $res);
    if (is_null($res[0])) {
        trigger_error('Seems to be no pendel configuraturion in page, leaving init_config()', E_USER_WARNING);
        return;
    }

    preg_match_all('/.*(\w+)=\"(.*)\".*/U', $res[1], $parts);
    $params = array();
    for ($i = 0; $i < count($parts[1]); $i++) {
        $params[$parts[1][$i]] = $parts[2][$i];
    }
//    echo 'calling set_config <br>';
    set_config($params);
}

// Calculate configuration, save to database and return configuration as array.
// params: $px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat
function set_config($params) {
//    echo '5 set_config: <br>';
    $configDb = new ConfigurationFacade($params['id']);
    $configDb->modifyItem($params['x'], $params['y'], $params['tile'], $params['tile'], $params['start_lon'], $params['start_lat'], $params['end_lon'], $params['end_lat']);
//    echo 'END set_config: <br>';
}

/*
 * To be called, when the plugin will be activated.
 */

function install() {
    (new ConfigurationFacade())->init_table();
    (new TileFacade())->initTable();
    trigger_error("Pendel deinstalled", E_USER_NOTICE);
}

/*
 * To be called, when the plugin will be uninstalled or deactivated.
 */

function deinstall() {
    (new ConfigurationFacade())->dropTable();
    (new TileFacade())->dropTable();
    trigger_error("Pendel deinstalled", E_USER_NOTICE);
}

function init_tiles($dir, $config) {
    tiles_refresh_db($dir, $config);
}

function hook_body($url) {
//    hook_css();
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
    // Show last canvas nr
    $actualNr = $config->canvas_nr;
    $tiles = tiles_read($actualNr);
    $canvasheight = $config->px_y + $config->tilespace;
    $canvaswidth = $config->px_x + $config->tilespace;
    $offseti = $config->imagesize / 2;
    $offsett = $config->tilesize / 2;
    ?>
    <!-- Trigger/Open The Modal -->
    <!--<button id="myBtn">Open Modal</button>-->

    <!-- The Modal -->
    <div id="pendelModal" class="modal">

        <!-- Modal content -->
        <div id="pendelModalContent" class="modal-content">
            <span class="close">&times;</span>
            <img   id="pendelModalImg">

            <div class="viewer">
                <div  id="viewer-title"></div>
                <div  id="viewer-subtitle" class="viewer-subtitle">
                </div>

            </div>


        </div>
    </div>
    </div>


    <div id="svg-section">
        <svg id="canvas" width="90%"  viewBox="0 0 <?php echo $canvaswidth ?> <?php echo $canvasheight; ?>">
        <filter id="f1">
            <feGaussianBlur stdDeviation="4" />
        </filter>
        <rect width="<?php echo $canvaswidth; ?>" height="<?php echo $canvasheight; ?>" id="canvaspaper"  />
        <?php
        foreach ($tiles as $tile) {
            $midx = ($tile->pos_x * ($config->tilesize + $config->tilespace)) + $config->tilespace + $offsett;
            $midy = ($tile->pos_y * ($config->tilesize + $config->tilespace)) + $config->tilespace + $offsett;
            $src = $url . "/" . $tile->thumb_file;
            $img = $url . "/" . $tile->image_file;
            $title = preg_replace("/\r?\n/", "\\n", addslashes($tile->title));
            $description = preg_replace("/\r?\n/", "\\n", addslashes($tile->description));
            trigger_error("hook_body: sanitized file $tile->image_file : >>>$title<<<>>>$description<<<", E_USER_NOTICE);

            echo "<g transform=\"translate($midx,$midy)\" onclick=\"onTileClicked('$img', '$title', '$description','$tile->lat','$tile->lon')\">";
            echo "<g id=\"svg-tile-group\"> ";
            echo "<rect id=\"svg-tile-rect\" y=\"-$offsett\" x=\"-$offsett\" width=\"$config->tilesize\" height=\"$config->tilesize\" filter=\"url(#f1)\" />";
            echo "<image xlink:href=\"$src\" y=\"-$offseti\" x=\"-$offseti\" width=\"$config->imagesize\" height=\"$config->imagesize\"  />";
            echo "</g>";
            echo "</g>";
        }
        ?>
        </svg>

    </div>
    <div class="infoLine">
        <span id="pendelActualNr"><?php echo $actualNr; ?></span>/<span id="pendelNr"><?php echo $config->canvas_nr; ?></span>
        <span id="msg">4</span>
    </div>


    <br>

    <?php
//    $t = time();
//    echo date("H:i:s") . "<br>";
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
    $configDb = new ConfigurationFacade($GLOBALS['pendel_id']);
    $config = $configDb->get();
    $config->tilespace = $config->tile_x * 4 / 100;
    $config->tilesize = $config->tile_x - $config->tilespace;
    $config->imagesize = $config->tilesize - (2 * $config->tilespace);

    return $config;
}
