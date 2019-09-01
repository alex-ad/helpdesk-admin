<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$fnc = "fnc_".$data["elemType"];

$rnm = new fncStaffRename();
echo json_encode($rnm->$fnc($jData));


class fncStaffRename {

    private $result = Array();

    private function rnm_result($success, $msg) {
        return Array(
            "success"	=> $success,
            "msg"	=> $msg
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

    // rename company
    public function fnc_org($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM company WHERE company='$elemName'");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $upd = $mysqli->query("UPDATE company SET company='$elemName' WHERE id='$elemId'");
                $this->result = $upd ? $this->rnm_result(true, "Организация переименована") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $this->result = $this->rnm_result(false, "Организация с таким именем уже существует");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // rename division
    public function fnc_div($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $elemParentId = substr($elemParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM division WHERE (division='$elemName') AND (company='$elemParentId')");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $upd = $mysqli->query("UPDATE division SET division='$elemName' WHERE id='$elemId'");
                $this->result = $upd ? $this->rnm_result(true, "Подразделение переименовано") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $this->result = $this->rnm_result(false, "Подразделение с таким именем уже существует");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // rename function
    public function fnc_fnc($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $elemParentId = substr($elemParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM function WHERE (func='$elemName') AND (FIND_IN_SET('$elemParentId', division)>0)");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $c = $mysqli->query("SELECT COUNT(*) FROM function WHERE (func='$elemName') AND (not FIND_IN_SET('$elemParentId', division))");
                for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
                if ( $cnt[0]["COUNT(*)"] === "0" ) {// "auditor 1" count=0
                    $upd = $mysqli->query("UPDATE function SET func='$elemName' WHERE id='$elemId'");
                    $this->result = $upd ? $this->rnm_result(true, "Должность переименована") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                } else {
                    $f = $mysqli->query("SELECT id, division FROM function WHERE (func='$elemName') AND (not FIND_IN_SET('$elemParentId', division))");
                    for ( $fnc=Array(); $row=$f->fetch_assoc(); $fnc[]=$row );
                    $id = $fnc[0]['id'];
                    $div = $fnc[0]['division'];
                    $da = explode(',', $div);
                    $da[] = $elemParentId;
                    sort($da);
                    $ds = implode(',', $da);
                    $upd = $mysqli->query("UPDATE function SET division='$ds' WHERE id='$id'");
                    if ( $upd !== false ) {
                        $d = $mysqli->query("SELECT division FROM function WHERE id='$elemId'");
                        for ( $div=Array(); $row=$d->fetch_assoc(); $div[]=$row );
                        if ( strpos($div[0]['division'], ',') === false ) {
                            $del = $mysqli->query("DELETE FROM function WHERE id='$elemId'");
                            $this->result = $del ? $this->rnm_result(true, "Должность переименована") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                        } else {
                            $da = explode(',', $div[0]['division']);
                            $da = array_diff($da, [$elemParentId]);
                            sort($da);
                            $ds = implode(',', $da);
                            $upd = $mysqli->query("UPDATE function SET division='$ds' WHERE id='$elemId'");
                            $this->result = $upd ? $this->rnm_result(true, "Должность переименована") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                        }
                    } else {
                        $this->result = $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                    }
                }
            } else {
                $this->result = $this->rnm_result(false, "Должность с таким именем уже существует");
            }
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