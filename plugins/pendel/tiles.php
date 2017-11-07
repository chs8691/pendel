<?php

require_once( 'globals.php' );

class TileFacade {

    private function dropTable() {
//        echo "dropTable() <br>";
        $res = $GLOBALS['wpdb']->query(
                "DROP TABLE IF EXISTS $this->table_name;");
//        echo "Dropped $res tables.<br>";
    }

    public function init() {
        $this->dropTable();
        $this->createTable();
    }

    private function createTable() {
//        echo "createTable() <br>";
        $charset_collate = $GLOBALS['wpdb']->get_charset_collate();

        $res = $GLOBALS['wpdb']->query(
                "CREATE TABLE IF NOT EXISTS $this->table_name ( " .
                "id int(1) NOT NULL AUTO_INCREMENT, " .
                "nr INT(5), " . // canvas's number
                "pos_x INT(5), " . // x position on canvas
                "pos_y INT(5), " . // y position on canvas
                "image_file VARCHAR(50), " . // file name
                "thumb_file VARCHAR(50), " . // file name
                "gps_file VARCHAR(50), " . // file name
                "reg_date timestamp, " .
                "PRIMARY KEY  (id) ) $charset_collate;"
        );
//        echo "Created $res tables.<br>";
    }

    /*
     * Insert single tile item.
     */

    public function insertItem($nr, $pos_x, $pos_y, $image_file, $thumb_file, $gps_file) {
        $ret = $GLOBALS['wpdb']->insert(
                $this->table_name, array(
            'nr' => $nr,
            'pos_x' => $pos_x,
            'pos_y' => $pos_y,
            'image_file' => $image_file,
            'thumb_file' => $thumb_file,
            'gps_file' => $gps_file
                )
        );
        if ($ret == FALSE) {
            echo "Error inserting into table $this->table_name <br>";
        }
//        else {
//            echo "Inserted $ret rows image_file = $image_file into $this->table_name <br>";
//        }
    }

    /*
     * Returns array with all tiles for the particular number.
     * If nothing found, returns NULL.
     */

    public function getByNr($nr) {
//        echo "getByNr() for $nr <br>";
//        $data = array();
//        $index = 0;
        $results = $GLOBALS['wpdb']->get_results("SELECT * FROM $this->table_name WHERE nr=$nr");
//        foreach ($results as $row) {
//            echo " file=$row->image_file <br>";
//            $ret = array(
//                "id" => $row->id,
//                "px_x" => $row->px_x,
//                "px_y" => $row->px_y,
//                "image_file" => $row->image_file,
//                "thumb_file" => $row->thumb_file,
//                "gps_file" => $row->gps_file,
//                "reg_date" => $row->reg_date);
//            $data[$index++] = $ow;
//        }
        if (count($results) > 0) {
            return $results;
        } else {
            echo 'Nothing found in table ' . $this->table_name . '! <br>';
            return NULL;
        }

//        $sql = "SELECT * FROM " . $this->table_name . " WHERE nr=" . $nr;
//        $result = $this->conn->query($sql);
//        $data = array();
//
//        // There should only be one entry
//        if ($result->num_rows > 0) {
//            $index = 0;
//            while ($row = $result->fetch_assoc()) {
//                $ret = array(
//                    "id" => $row["id"],
//                    "pos_x" => $row["pos_x"],
//                    "pos_y" => $row["pos_y"],
//                    "image_file" => $row["image_file"],
//                    "thumb_file" => $row["thumb_file"],
//                    "gps_file" => $row["gps_file"],
//                    "reg_date" => $row["reg_date"]);
//                $data[$index++] = $ret;
//            }
//            return $data;
//        }
//        echo 'Tiles for level ' . $nr . ' not found in table ' . $this->table_name . '! <br>';
//        return NULL;
    }

    public function TileFacade() {
        $prefix = $GLOBALS['wpdb']->prefix;
//        echo "ConfigurationFacade() prefix= $prefix <br>";
        $this->table_name = $prefix . "pendel_tile";
    }

    public function deleteItem($id) {
        $sql = "DELETE FROM " . $this->table_name .
                " WHERE id='" . $id . "'";

        if ($this->conn->query($sql) === TRUE) {
//            echo "Record deleted, if exists!<br>";
        } else {
            echo "Error deleting: " . $this->conn->error . "<br>";
        }
    }

}

// Rebuild all tile items in database. For every image, there must be two files.
// First the original image and second the thumbnail image with same file name
// plus prefix 'tile_'.
// Returns nothing
function tiles_refresh_db($directory, $config) {

    $allowdTypes = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
    $db = new TileFacade();
    $db->init();

//    echo 'tile_refresh_db: scanning =' . $directory . '<br>';
    $filenames = scandir($directory);
//    foreach( $filenames as $f){
//    echo 'tile_refresh_db: file =' . $f . '<br>';
//    }

    $files = new DirectoryIterator($directory);
//    echo var_dump($filenames);
    $gps = read_gps_from_file($directory);

    foreach (new DirectoryIterator($directory) as $tilefile) {
        if ($tilefile->isDot())
            continue;

//        echo var_dump($tilefile->getFilename()) . '<br>';
        if ($tilefile->getFilename() === 'gps.csv') {
            continue;
        }

        if ($tilefile->isFile() and ( strpos($tilefile->getFilename(), 'tile_') === 0)) {
            $image = getImageForTile($files, $tilefile);
            $imagename = $image->getFilename();

            $type = exif_imagetype($image->getPathname());
            if (in_array($type, $allowdTypes)) {
//                $exifs = read_gps_location($image->getPathname());
//                echo $image->getFilename() . ": " . var_dump($exifs);
            } else {
                echo "No image: " . $image->getFilename() . '<br>';
                continue;
            }
//            echo $image->getFilename() . '<br>';
//          Nr of tiles of the canvas.
            $tiles_x = round($config->px_x / $config->tile_x);
            $tiles_y = round($config->px_y / $config->tile_y);


            // Now let's calculate the canvas place by $exifs lat and lng
            // tiles_* - 1: indexing starts with 0!
            $pos_x = floor(($tiles_x - 1 ) / ($config->end_lon - $config->start_lon) * ($gps[$image->getFilename()]['lon'] - $config->start_lon));
//            $pos_y = round($tiles_y / ($config->end_lat'] - $config->start_lat'] ) * ( $config->start_lat'] - $gps[$image->getFilename()]['lat'] ));
            $pos_y = floor(($tiles_y - 1) * ( $config->start_lat - $gps[$image->getFilename()]['lat'] ) / ($config->start_lat - $config->end_lat ));
//            echo "$imagename $tiles [x,y]: [$tiles_x,$tiles_y] , pos[x,y]: $pos_x, $pos_y] config->px_x,y: [$config->px_x,$config->px_y] <br>";
//            //Set images outside canvas to a border place
            if ($pos_x >= $tiles_x) {
                $pos_x = $tiles_x - 1;
            }
            if ($pos_y >= $tiles_y) {
                $pos_y = $tiles_y - 1;
            }
            if ($pos_x < 0) {
                $pos_x = 0;
            }
            if ($pos_y < 0) {
                $pos_y = 0;
            }
//            echo $image->getFilename() . ' ' . $tilefile . '=' . $pos_x . ', ' . $pos_y . '<br>';
            $db->insertItem(1, $pos_x, $pos_y, $image->getFilename(), $tilefile, NULL);
        }
    }
}

// Returns a file for the corresponding image for the given tile file,
// or, if not found, false.
function getImageForTile($files, $tile) {
    $name = substr($tile, strlen('tile_'));
    foreach ($files as $file) {
        if ($file->getFilename() === $name) {
            return $file;
        }
    }
    return false;
}

// Returns array with tiles for a specific nr
function tiles_read($nr) {
    $db = new TileFacade();
    $ret = $db->getByNr($nr);
//    echo var_dump($ret) . '<br>';
    return $ret;
}

function read_gps_from_file($directory) {
    $ret = array();

    $myfile = fopen("$directory/gps.csv", "r") or die("Unable to open file!");
    $first = true;
    while (!feof($myfile)) {
        if ($first) {
            $first = false;
            fgets($myfile);
            continue;
        }
        list($filename, $lat, $lon) = explode(',', fgets($myfile));
        $filename = substr($filename, 2);
        $res = array();
        preg_match('/"(\d+\.\d+) \w/', $lat, $res);
        $lat = $res[1];
        $res = array();
        preg_match('/(\d+\.\d+) \w"/', $lon, $res);
        $lon = $res[1];
        if (strlen($filename) > 0) {
//        echo $filename . ' ' . $lat . '  ' . $lon . '<br>';
            $gps = array();
            $gps['lon'] = $lon;
            $gps['lat'] = $lat;
            $ret[$filename] = $gps;
        }
    }
    fclose($myfile);

//    echo var_dump($ret) . '<br>';
    return $ret;
}

// Returns array with 'lon' and 'lat'. If no exif location found, both values will be '0'.
function read_gps_location($file) {
    if (is_file($file)) {

//      PHP bug https://bugs.php.net/bug.php?id=66443
//        return array(
//            'lat' => '50.1353488',
//            'lon' => '8.8760448'
//        );
//      END

        try {
            $info = exif_read_data($file, 'ANY_TAG');
            echo $file . ' info=' . var_dump($info['GPSLatitude']) . '<br>';
            if (!$info) {
                return array(
                    'lat' => '0',
                    'lon' => '0'
                );
            }

            if (isset($info['GPSLatitude']) && isset($info['GPSLongitude']) &&
                    isset($info['GPSLatitudeRef']) && isset($info['GPSLongitudeRef']) &&
                    in_array($info['GPSLatitudeRef'], array('E', 'W', 'N', 'S')) && in_array($info['GPSLongitudeRef'], array('E', 'W', 'N', 'S'))) {

                $GPSLatitudeRef = strtolower(trim($info['GPSLatitudeRef']));
                $GPSLongitudeRef = strtolower(trim($info['GPSLongitudeRef']));

                $lat_degrees_a = explode('/', $info['GPSLatitude'][0]);
                $lat_minutes_a = explode('/', $info['GPSLatitude'][1]);
                $lat_seconds_a = explode('/', $info['GPSLatitude'][2]);
                $lng_degrees_a = explode('/', $info['GPSLongitude'][0]);
                $lng_minutes_a = explode('/', $info['GPSLongitude'][1]);
                $lng_seconds_a = explode('/', $info['GPSLongitude'][2]);

                $lat_degrees = $lat_degrees_a[0] / $lat_degrees_a[1];
                $lat_minutes = $lat_minutes_a[0] / $lat_minutes_a[1];
                $lat_seconds = $lat_seconds_a[0] / $lat_seconds_a[1];
                $lng_degrees = $lng_degrees_a[0] / $lng_degrees_a[1];
                $lng_minutes = $lng_minutes_a[0] / $lng_minutes_a[1];
                $lng_seconds = $lng_seconds_a[0] / $lng_seconds_a[1];

                $lat = (float) $lat_degrees + ((($lat_minutes * 60) + ($lat_seconds)) / 3600);
                $lng = (float) $lng_degrees + ((($lng_minutes * 60) + ($lng_seconds)) / 3600);

                //If the latitude is South, make it negative.
                //If the longitude is west, make it negative
                $GPSLatitudeRef == 's' ? $lat *= -1 : '';
                $GPSLongitudeRef == 'w' ? $lng *= -1 : '';

                return array(
                    'lat' => $lat,
                    'lng' => $lng
                );
            }
        } catch (Exception $ex) {
            echo 'GPS not found: ' . $ex->getMessage() . '<br>';
        }
    }
    return array(
        'lat' => '0',
        'lon' => '0'
    );
}
