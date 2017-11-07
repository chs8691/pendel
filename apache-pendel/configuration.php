<?php

//require_once( 'globals.php' );

class ConfigurationFacade {

    function init_table() {
        $this->dropTable();
        $this->createTable();
    }

    function dropTable() {
        $sql = "DROP TABLE IF EXISTS " . $this->table_name;
        if ($this->conn->query($sql) === TRUE) {
//            echo "Table " . $this->table_name . ": dropped<br>";
        } else {
            echo "Error dropping table: " . $this->conn->error . "<br>";
        }
    }

    function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY, " .
                "px_x VARCHAR(10), " . // pixel size
                "px_y VARCHAR(10), " . // pixel size
                "tile_x VARCHAR(5), " . // tile size in pixel
                "tile_y VARCHAR(5), " . // tile size in pixel
                "start_lon VARCHAR(20), " . //location in 0,0
                "start_lat VARCHAR(20), " .
                "end_lon VARCHAR(20), " . //location in x,y
                "end_lat VARCHAR(20), " .
                "reg_date TIMESTAMP)";
        if ($this->conn->query($sql) === TRUE) {
//            echo "Table " . $this->table_name . ": ok<br>";
        } else {
            echo "Error creating table: " . $this->conn->error . "<br>";
        }
    }

    function ConfigurationFacade() {
        $this->table_name = "php5_" . "pendel_config";
//        $this->conn = new mysqli("localhost:3307", "root", "sonntag17",  "bitnami_wordpress");
        $this->conn = new mysqli(Constants::dbUrl, Constants::dbUser, Constants::dbPassword, Constants::dbName);

        if ($this->conn->connect_error) {
            die("Connection failed to " . Constants::dbUrl . ': ' . $this->conn->connect_error);
        }
    }

    function close() {
        $this->conn->close();
    }

    function getAll() {
        $sql = "SELECT * FROM " . $this->table_name;
        $result = $this->conn->query($sql);

        // There should only be one entry
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row) {
                $config = array(
                    "id" => $row["id"],
                    "px_x" => $row["px_x"],
                    "px_y" => $row["px_y"],
                    "tile_x" => $row["tile_x"],
                    "tile_y" => $row["tile_y"],
                    "start_lon" => $row["start_lon"],
                    "start_lat" => $row["start_lat"],
                    "end_lon" => $row["end_lon"],
                    "end_lat" => $row["end_lat"],
                    "reg_date" => $row["reg_date"]);
            }
        }
//        echo 'getAll: ' . $config['id'] . '<br>';
        return $config;
    }

    function deleteItem($id) {
        $sql = "DELETE FROM " . $this->table_name .
                " WHERE id='" . $id . "'";

        if ($this->conn->query($sql) === TRUE) {
//            echo "Record deleted, if exists!<br>";
        } else {
            echo "Error deleting: " . $this->conn->error . "<br>";
        }
    }

    function modifyItem($px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat) {

        $this->deleteItem(1);

        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name .
                " (px_x, px_y, tile_x, tile_y, start_lon, start_lat, end_lon, end_lat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat);

        if ($stmt->execute() === TRUE) {
//            echo "New record created successfully with ID=" .
            $this->conn->insert_id . "<br>";
        } else {
            echo "Error inserting: " . $this->conn->error . "<br>";
        }
        $stmt->close();
    }

}
