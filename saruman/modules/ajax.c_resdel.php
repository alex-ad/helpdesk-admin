<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$data["elemType"] = strlen($data["elemType"])>0 ? $data["elemType"] : "root";
$fnc = "fnc_".$data["elemType"];

$add = new fncResDel();
echo json_encode($add->$fnc($jData));


class fncResDel {
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

    // delete resource
    public function fnc_res($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $c = $mysqli->query("SELECT COUNT(*) FROM service WHERE id_res='$elemId'");
            for ($cnt = Array(); $row = $c->fetch_assoc(); $cnt[] = $row);
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $mysqli->query("DELETE FROM resource WHERE id='$elemId'");
                $this->result = $this->rnm_result(true, "ИТ-ресурс удален");
                $mysqli->close();
                return $this->result;
            } else {
                $s = $mysqli->query("SELECT id FROM service WHERE id_res='$elemId'");
                for ($srv = Array(); $row = $s->fetch_assoc(); $srv[] = $row);
                for ( $i=0; $i<sizeOf($srv); $i++ ) {
                    $srv_id = $srv[$i]["id"];
                    $mysqli->query("DELETE FROM role WHERE id_srv='$srv_id'");
                    $upd = $mysqli->query("UPDATE role SET id_srv = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', id_srv, ','), ',$srv_id,', ',')) WHERE FIND_IN_SET('$srv_id', id_srv)");
                    if ( $upd !== true ) {
                        $this->result = $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                        $mysqli->close();
                        return $this->result;
                    }
                }
                $mysqli->query("DELETE FROM service WHERE id_res='$elemId'");
                $del = $mysqli->query("DELETE FROM resource WHERE id='$elemId'");
                $this->result = $del ? $this->rnm_result(true, "ИТ-ресурс удален со всеми вложенными непривязанными элементами") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                $mysqli->close();
                return $this->result;
            }
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // delete service
    public function fnc_srv($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $mysqli->query("DELETE FROM role WHERE id_srv='$elemId'");
            $mysqli->query("UPDATE role SET id_srv = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', id_srv, ','), ',$elemId,', ',')) WHERE FIND_IN_SET('$elemId', id_srv)");
            $del = $mysqli->query("DELETE FROM service WHERE id='$elemId'");
            $this->result = $del ? $this->rnm_result(true, "ИТ-услуга удалена со всеми вложенными непривязанными элементами") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // delete role
    public function fnc_rol($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemParentId = substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $mysqli->query("DELETE FROM role WHERE (id_srv='$elemParentId') AND (name='$elemName')");
            $upd = $mysqli->query("UPDATE role SET id_srv = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', id_srv, ','), ',$elemParentId,', ',')) WHERE (FIND_IN_SET('$elemParentId', id_srv)) AND (name='$elemName')");
            $this->result = $upd ? $this->rnm_result(true, "ИТ-роль удалена с учетом привязки к другим ИТ-услугам") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // delete ouop role
    public function fnc_rup($jData) {
        return $this->fnc_rol($jData);
    }

    // delete ouop-cat
    public function fnc_cat($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $mysqli->query("UPDATE role SET id_ext1 = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', id_ext1, ','), ',$elemId,', ',')) WHERE FIND_IN_SET('$elemId', id_ext1)");
            $upd = $mysqli->query("UPDATE role SET id_ext1 = '0' WHERE id_ext1 = ''");
            $this->result = $upd ? $this->rnm_result(true, "ОУОП-каталог удален из текущей ИТ-роли") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
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