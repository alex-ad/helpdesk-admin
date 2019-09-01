<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$data["elemType"] = strlen($data["elemType"])>0 ? $data["elemType"] : "root";
$fnc = "fnc_".$data["elemType"];

$add = new fncStaffDel();
echo json_encode($add->$fnc($jData));


class fncStaffDel {
    private $result = Array();

    private function rnm_result($success, $msg, $elemId="") {
        return Array(
            "success"	=> $success,
            "msg"	=> $msg,
            "elemId"	=> $elemId
        );
    }

    // db connecting
    private function db_connect() {
        $mysqli_ = new mysqli(db::host, db::user, db::password, db::base);

        if ( $mysqli_->connect_error ) {
            $this->result = $mysqli_->connect_errno . " : " . $mysqli_->connect_error;
            return false;
        }
        $mysqli_->set_charset("utf8");
        return $mysqli_;
    }

    // delete company
    public function fnc_org($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $c = $mysqli->query("SELECT COUNT(*) FROM division WHERE company='$elemId'");
            for ($cnt = Array(); $row = $c->fetch_assoc(); $cnt[] = $row);
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $mysqli->query("DELETE FROM company WHERE id='$elemId'");
                $this->result = $this->rnm_result(true, "Организация удалена");
            } else {
                $d = $mysqli->query("SELECT id FROM division WHERE company='$elemId'");
                for ($div = Array(); $row = $d->fetch_assoc(); $div[] = $row);
                for ( $i=0; $i<sizeOf($div); $i++ ) {
                    $div_id = $div[$i]["id"];
                    $mysqli->query("DELETE FROM function WHERE division='$div_id'");
                    $upd = $mysqli->query("UPDATE function SET division = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', division, ','), ',$div_id,', ',')) WHERE FIND_IN_SET('$div_id', division)");
                    if ( $upd !== true ) {
                        $this->result = $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                        $mysqli->close();
                        return $this->result;
                    }
                }
                $mysqli->query("DELETE FROM division WHERE company='$elemId'");
                $del = $mysqli->query("DELETE FROM company WHERE id='$elemId'");
                $this->result = $del ? $this->rnm_result(true, "Организация удалена со всеми вложенными непривязанными элементами") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // delete division
    public function fnc_div($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $mysqli->query("DELETE FROM function WHERE division='$elemId'");
            $upd = $mysqli->query("UPDATE function SET division = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', division, ','), ',$elemId,', ',')) WHERE FIND_IN_SET('$elemId', division)");
            $mysqli->query("DELETE FROM division WHERE id='$elemId'");
            $this->result = $upd ? $this->rnm_result(true, "Подразделение удалено со всеми непривязанными должностями") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // delete function
    public function fnc_fnc($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $elemParentId = substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $mysqli->query("DELETE FROM function WHERE (division='$elemParentId') AND (id='$elemId')");
            //$upd = $mysqli->query("UPDATE function SET division = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', division, ','), ',$elemParentId,', ',')) WHERE FIND_IN_SET('$elemParentId', division)");
            $upd = $mysqli->query("UPDATE function SET division = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', division, ','), ',$elemParentId,', ',')) WHERE id='$elemId'");
            $this->result = $upd ? $this->rnm_result(true, "Должность удалена с учетом привязки к другим подразделениям") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }
}
?>