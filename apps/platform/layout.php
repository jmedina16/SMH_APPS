<?php

class layout {

    protected $pid;
    protected $action;
    private $_link;

    public function __construct() {
        $this->pid = isset($_GET['pid']) ? $_GET['pid'] : (isset($_POST['pid']) ? $_POST['pid'] : null);
        $this->action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : null);
        $this->_link = @mysqli_connect("127.0.0.1", "smh_mngmt", "*AC54418D19B5CA7E6195A83CBA66B843ED7CC16C", "smh_management", 3307) or die('Unable to establish a DB connection');
    }

    //run ppv api
    public function run() {
        switch ($this->action) {
            case "update_layout":
                $this->update_layout();
                break;
            case "get_layout":
                $this->get_layout();
                break;
            default:
                echo "Action not found!";
        }
    }

    public function update_layout() {
        $success = array('success' => false);
        if (!$this->wl_check_init_setup($this->pid)) {
            if ($this->wl_init_setup($this->pid)) {
                if ($this->update_layout_config($this->pid)) {
                    $success = array('success' => true);
                } else {
                    $success = array('success' => false);
                }
            } else {
                $success = array('success' => false);
            }
        } else {
            if ($this->update_layout_config($this->pid)) {
                $success = array('success' => true);
            } else {
                $success = array('success' => false);
            }
        }

        echo json_encode($success);
    }

    public function wl_check_init_setup($pid) {
        $success = false;
        $query = "SELECT * FROM `white_label_config` " .
                "WHERE partner_id = " . $pid;
        $qresult = mysqli_query($this->_link, $query) or die('Query failed: ' . $query . " " . mysqli_error());
        $rowcount = mysqli_num_rows($qresult);
        if ($rowcount > 0) {
            $success = true;
        }
        return $success;
    }

    public function wl_init_setup($pid) {
        $success = false;
        $query = "INSERT INTO `white_label_config` " .
                "(partner_id,updated_at) VALUES ('" . $pid . "','" . date("Y-m-d h:i:s") . "')";
        $qresult = mysqli_query($this->_link, $query) or die('Query failed: ' . $query . " " . mysqli_error());

        if ($qresult) {
            $success = true;
        }
        return $success;
    }

    public function update_layout_config($pid) {
        $success = false;
        $query = "UPDATE `white_label_config` SET " .
                "layout_top_settings = " . (($_POST['layout_top_settings'] == 'true') ? 1 : 0) . ", " .
                "top_nav_bgcolor = '" . $_POST['top_nav_bgcolor'] . "', " .
                "top_nav_fontcolor = '" . $_POST['top_nav_fontcolor'] . "', " .
                "layout_logo_image = " . (($_POST['layout_logo_image'] == 'true') ? 1 : 0) . ", " .
                "layout_logoid = '" . $_POST['layout_logoid'] . "', " .
                "layout_logo_text = " . (($_POST['layout_logo_text'] == 'true') ? 1 : 0) . ", " .
                "logo_font_size = " . $_POST['logo_font_size'] . ", " .
                "layout_side_settings = " . (($_POST['layout_side_settings'] == 'true') ? 1 : 0) . ", " .
                "side_nav_bgcolor = '" . $_POST['side_nav_bgcolor'] . "', " .
                "side_nav_fontcolor = '" . $_POST['side_nav_fontcolor'] . "', " .
                "side_nav_sub_bgcolor = '" . $_POST['side_nav_sub_bgcolor'] . "', " .
                "side_nav_sub_fontcolor = '" . $_POST['side_nav_sub_fontcolor'] . "', " .
                "updated_at = '" . date("Y-m-d h:i:s") . "' " .
                "WHERE partner_id = " . $pid;
        $result = mysqli_query($this->_link, $query) or die('Query failed: ' . $query . " " . mysqli_error());
        if ($result) {
            $success = true;
        }
        return $success;
    }

    public function get_layout() {
        $result = array();
        $query = "SELECT * FROM `white_label_config` " .
                "WHERE partner_id = " . $this->pid;
        $qresult = mysqli_query($this->_link, $query) or die('Query failed: ' . $query . " " . mysqli_error());
        while ($row = $qresult->fetch_array(MYSQL_ASSOC)) {
            $result[] = $row;
        }
        echo json_encode($result);
    }

}

header('Content-Type: application/json');
$layout = new layout();
$layout->run();
?>