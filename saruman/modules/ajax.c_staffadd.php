<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$fnc = "fnc_".$data["elemType"];

$add = new fncStaffAdd();
echo json_encode($add->$fnc($jData));


class fncStaffAdd {
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

    // add new company
    public function fnc_org($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM company WHERE company='$elemName'");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $ins = $mysqli->query("INSERT INTO company (company, visible) VALUES ('$elemName', '1')");
                $this->result = $ins ?  $this->rnm_result(true, "Организация добавлена", "$mysqli->insert_id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
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

    // add new division
    public function fnc_div($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = ( $elemParentId === "" ) ? substr($elemId,4) : substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM division WHERE (division='$elemName') AND (company='$elemId')");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $ins = $mysqli->query("INSERT INTO division (division, company) VALUES ('$elemName', '$elemId')");
                $this->result = $ins ?  $this->rnm_result(true, "Подразделение добавлено", "$mysqli->insert_id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
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

    // add new function
    public function fnc_fnc($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = ( substr($elemParentId,0,3) === 'div' ) ? substr($elemParentId,4) : substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM function WHERE (func='$elemName') AND (FIND_IN_SET('$elemId', division)>0)");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $c = $mysqli->query("SELECT COUNT(*) FROM function WHERE func='$elemName'");
                for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
                if ( $cnt[0]["COUNT(*)"] === "0" ) {
                    $ins = $mysqli->query("INSERT INTO function (func, division) VALUES ('$elemName', '$elemId')");
                    $this->result = $ins ?  $this->rnm_result(true, "Должность добавлена", "$mysqli->insert_id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                } else {
                    $d = $mysqli->query("SELECT id, division FROM function WHERE func='$elemName'");
                    for ( $div=Array(); $row=$d->fetch_assoc(); $div[]=$row );
                    $adiv = explode(",", $div[0]["division"]);
                    $id = $div[0]["id"];
                    $adiv[] = $elemId;
                    $adiv = array_unique($adiv);
                    sort($adiv, SORT_NUMERIC);
                    $div = implode(",", $adiv);
                    $upd = $mysqli->query("UPDATE function SET division='$div' WHERE id='$id'");
                    $this->result = $upd ? $this->rnm_result(true, "Должность добавлена", "$id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
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