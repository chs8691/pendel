<?php

class ConfigurationFacade {

    function init_table() {
        $this->dropTable();
        $this->createTable();
    }

    function dropTable() {
//        echo "dropTable() <br>";
        $res = $GLOBALS['wpdb']->query(
                "DROP TABLE IF EXISTS $this->table_name;");
//        echo "Dropped $res tables.<br>";
    }

    private function createTable() {
//        echo "createTable() <br>";
        $charset_collate = $GLOBALS['wpdb']->get_charset_collate();

        $res = $GLOBALS['wpdb']->query(
                "CREATE TABLE IF NOT EXISTS $this->table_name ( " .
                "id varchar(3) NOT NULL, " . //Unique name
                "px_x varchar(10), " . // pixel size
                "px_y varchar(10), " . // pixel size
                "tile_x varchar(5), " . // tile size in pixel
                "tile_y varchar(5), " . // tile size in pixel
                "start_lon varchar(20), " . //location in 0,0
                "start_lat varchar(20), " .
                "end_lon varchar(20), " . //location in x,y
                "end_lat varchar(20), " .
                "canvas_nr INT(5), " . // newest canvas's number
                "reg_date timestamp, " .
                "PRIMARY KEY  (id) ) $charset_collate;"
        );
//        echo "Created $res tables.<br>";
    }

    /**
     * There are two different usages: For creating table, $id isn't used.
     * For read/write access, the $id is obligatory
     * @param type $id, can be null for initializing tables
     */
    function __construct($id = NULL) {
        $this->id = $id;
        $prefix = $GLOBALS['wpdb']->prefix;
//        echo "ConfigurationFacade() prefix= $prefix <br>";
        $this->table_name = $prefix . "pendel_config";
    }

    /*
     * Checks, if Configuration exists. Returns true, if there is an configuration,
     * otherwise false.
     */

    public function exists() {
        $rowcount = $GLOBALS['wpdb']->get_var("SELECT count(*) FROM $this->table_name where id='$this->id'");
        if ($rowcount == 0) {
//            echo "No configuration found. <br>";
            return false;
        }
//        echo "Configuration exists. <br>";
        return true;

//        if (is_null($GLOBALS['wpdb']->get_var("SHOW TABLES LIKE '$this->table_name'"))) {
//            echo "exists $this->table_name: true <br>";
//            return false;
//        } else {
//            echo "exists $this->table_name: true <br>";
//            return true;
//        }
    }

    /*
     * Returns single item with all rows as class instance with column names as keys.
     * In error case, returns false.
     */

    public function get() {
//        echo "config.getAll() <br>";
        $ret = $GLOBALS['wpdb']->get_row("SELECT * FROM $this->table_name WHERE id='$this->id'");
        if ($ret == NULL) {
            trigger_error("Nothing found in table $this->table_name for id=$this->id", E_USER_WARNING);
            return false;
        }
//        echo "config.getAll() found id=" . $ret->id . " <br>";


        return $ret;
//        $data = array();
//        $results = $GLOBALS['wpdb']->get_results("SELECT * FROM $this->table_name");
//        foreach ($results as $row) {
//            $ret = array(
//                "id" => $row->id,
//                "px_x" => $row->px_x,
//                "px_y" => $row->px_y,
//                "tile_x" => $row->tile_x,
//                "tile_y" => $row->tile_y,
//                "start_lon" => $row->start_lon,
//                "start_lat" => $row->start_lat,
//                "end_lon" => $row->end_lon,
//                "end_lat" => $row->end_lat,
//                "reg_date" => $row->reg_date);
//            $data[$index++] = $ret;
//        }
//        if (count($data) > 0) {
//            return $data;
//        } else {
//            echo 'Nothing found in table ' . $this->table_name . '! <br>';
//            return NULL;
//        }
    }

    function deleteItem() {
//        echo "deleteItem() <br>";

        $ret = $GLOBALS['wpdb']->delete($this->table_name, array('id' => $this->id));
//        if ($ret == FALSE) {
//            echo "Error deleting id = $id <br>";
//        } else {
//            echo "Deleted $ret rows with id = $id <br>";
//        }
    }

    /**
     * Modify just the field canvas nr for the actual (latest) canvas nr.
     * The config item must exists
     */
        function setCanvasNr($canvas_nr) {
//        echo "2 modifyItem() <br>";
        // There is just upt to one entry, it has always id 1.
        $item = $this->get();
        $this->deleteItem();

        $ret = $GLOBALS['wpdb']->insert(
                $this->table_name, array(
            'id' => $this->id,
            'px_x' => $item->px_x,
            'px_y' => $item->px_y,
            'tile_x' => $item->tile_x,
            'tile_y' => $item->tile_y,
            'start_lon' => $item->start_lon,
            'start_lat' => $item->start_lat,
            'end_lon' => $item->end_lon,
            'end_lat' => $item->end_lat,
            'canvas_nr' => $canvas_nr
                )
        );
        if ($ret == FALSE) {
            echo "Error updating canvas_nr for item id=$this->id into table $this->table_name <br>";
        }
//        else {
//            echo "Inserted $ret rows with id = $id into $this->table_name <br>";
//        }
    }

    function modifyItem($px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat) {
//        echo "2 modifyItem() <br>";
        // There is just upt to one entry, it has always id 1.
        $this->deleteItem();

        $ret = $GLOBALS['wpdb']->insert(
                $this->table_name, array(
            'id' => $this->id,
            'px_x' => $px_x,
            'px_y' => $px_y,
            'tile_x' => $tile_x,
            'tile_y' => $tile_y,
            'start_lon' => $start_lon,
            'start_lat' => $start_lat,
            'end_lon' => $end_lon,
            'end_lat' => $end_lat
//            'canvas_nr' =>         // Can only be set with setCanvasNr
                )
        );
        if ($ret == FALSE) {
            echo "Error inserting item id=$this->id into table $this->table_name <br>";
        }
//        else {
//            echo "Inserted $ret rows with id = $id into $this->table_name <br>";
//        }
    }

}
