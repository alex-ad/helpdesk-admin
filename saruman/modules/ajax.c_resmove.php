<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$data["elemType"] = strlen($data["elemType"])>0 ? $data["elemType"] : "root";
$fnc = "fnc_".$data["elemType"];

$add = new fncResMove();
echo json_encode($add->$fnc($jData));

class fncResMove {
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

    // move service
    public function fnc_srv($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $nextParentId = substr($nextParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $upd = $mysqli->query("UPDATE service SET id_res='$nextParentId' WHERE id='$elemId'");
            $this->result = $upd ?  $this->rnm_result(true, "ИТ-услуга перемещена в другой ИТ-ресурс") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // move role
    public function fnc_rol($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $nextParentId = substr($nextParentId, 4);
        $prevParentId = substr($prevParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $prevRolArr_ = $mysqli->query("SELECT id_srv FROM role WHERE id='$elemId'");
            for ( $prevRolArr=Array(); $row=$prevRolArr_->fetch_assoc(); $prevRolArr[]=$row );
            $prevRolArr = explode(",", $prevRolArr[0]["id_srv"]);
            $nextRol = Array();
            for ( $i=0; $i<sizeOf($prevRolArr); $i++ ) {
                if ( $prevRolArr[$i] !== $prevParentId ) $nextRol[] = $prevRolArr[$i];
            }
            $nextRol[] = $nextParentId;
            ksort($nextRol);
            $nextRol = array_unique($nextRol);
            $nextRol = implode(",", $nextRol);
            $upd = $mysqli->query("UPDATE role SET id_srv='$nextRol' WHERE id='$elemId'");
            $this->result = $upd ?  $this->rnm_result(true, "ИТ-роль перемещена в другую ИТ-услугу") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // move ouop-role
    public function fnc_rup($jData) {
        return $this->fnc_rol($jData);
    }

    // move ouop-cat
    public function fnc_cat($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $nextParentId = substr($nextParentId, 4);
        $prevParentId = substr($prevParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $e = $mysqli->query("SELECT id_ext1 FROM role WHERE id='$nextParentId'");
            for ( $ext=Array(); $row=$e->fetch_assoc(); $ext[]=$row );
            $aext = explode(",", $ext[0]["id_ext1"]);
            $aext[] = $elemId;
            $aext = array_unique($aext);
            sort($aext, SORT_NUMERIC);
            $ext = implode(",", $aext);
            $mysqli->query("UPDATE role SET id_ext1='$ext' WHERE id='$nextParentId'");
            $upd = $mysqli->query("UPDATE role SET id_ext1 = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', id_ext1, ','), ',$elemId,', ',')) WHERE id='$prevParentId'");
            $mysqli->query("UPDATE role SET id_ext1 = '0' WHERE id_ext1 = ''");
            $this->result = $upd ? $this->rnm_result(true, "ОУОП-каталог перемещен", "$id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }
}
?>