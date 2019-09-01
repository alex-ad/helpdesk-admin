<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$fnc = "fnc_".$data["elemType"];

$add = new fncResAdd();
echo json_encode($add->$fnc($jData));


class fncResAdd {
    private $result = Array();

    private function rnm_result($success, $msg, $elemId="", $elemName="") {
        return Array(
            "success"	=> $success,
            "msg"	=> $msg,
            "elemId"	=> $elemId,
            "elemName"	=> $elemName
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

    // add new service
    public function fnc_srv($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = ( $elemParentId === "" ) ? substr($elemId,4) : substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM service WHERE (name='$elemName') AND (id_res='$elemId')");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $ins = $mysqli->query("INSERT INTO service (name, id_res, id_own, id_vise, role_list_type, form, enabled) VALUES ('$elemName', '$elemId', '$nameOwner', '$nameVizier', '$roleType', '$formSrv', '1')");
                $this->result = $ins ?  $this->rnm_result(true, "Услуга добавлена", "$mysqli->insert_id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $this->result = $this->rnm_result(false, "Услуга с таким именем уже существует");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // add new role
    public function fnc_rol($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = ( substr($elemParentId,0,3) === 'srv' ) ? substr($elemParentId,4) : substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM role WHERE (name='$elemName') AND (FIND_IN_SET('$elemId', id_srv)>0)");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $c = $mysqli->query("SELECT COUNT(*) FROM role WHERE name='$elemName'");
                for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
                if ( $cnt[0]["COUNT(*)"] === "0" ) {
                    $ins = $mysqli->query("INSERT INTO role (name, id_srv) VALUES ('$elemName', '$elemId')");
                    $this->result = $ins ?  $this->rnm_result(true, "Роль добавлена", "$mysqli->insert_id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                } else {
                    $s = $mysqli->query("SELECT id, id_srv FROM role WHERE name='$elemName'");
                    for ( $srv=Array(); $row=$s->fetch_assoc(); $srv[]=$row );
                    $asrv = explode(",", $srv[0]["id_srv"]);
                    $id = $srv[0]["id"];
                    $asrv[] = $elemId;
                    $asrv = array_unique($asrv);
                    sort($asrv, SORT_NUMERIC);
                    $srv = implode(",", $asrv);
                    $upd = $mysqli->query("UPDATE role SET id_srv='$srv' WHERE id='$id'");
                    $this->result = $upd ? $this->rnm_result(true, "Роль добавлена", "$id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                }
            } else {
                $this->result = $this->rnm_result(false, "Роль с таким именем уже существует");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // add new resource
    public function fnc_res($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM resource WHERE name='$elemName'");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $ins = $mysqli->query("INSERT INTO resource (name, visible, form) VALUES ('$elemName', '1', '$formRes')");
                $this->result = $ins ?  $this->rnm_result(true, "Ресурс добавлен", "$mysqli->insert_id") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $this->result = $this->rnm_result(false, "Ресурс с таким именем уже существует");
            }
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
        }
        $mysqli->close();
        return $this->result;
    }

    // add new ouop-cat
    public function fnc_cat($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = ( substr($elemParentId,0,3) === 'rup' ) ? substr($elemParentId,4) : substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM role WHERE id='$elemId' AND FIND_IN_SET((SELECT id FROM ext1 WHERE name='$elemName' AND id_role='$catRW' LIMIT 1), id_ext1)");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $c = $mysqli->query("SELECT COUNT(*) FROM ext1 WHERE name='$elemName' AND id_role='$catRW'");
                for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
                if ( $cnt[0]["COUNT(*)"] === "0" ) {
                    $ins = $mysqli->query("INSERT INTO ext1 (name, id_role) VALUES ('$elemName', '$catRW')");
                    if ( $ins ) {
                        $id = $mysqli->insert_id;
                        $e = $mysqli->query("SELECT (SELECT name FROM role WHERE id='$catRW') AS name, id_ext1 FROM role WHERE id='$elemId'");
                        for ( $ext=Array(); $row=$e->fetch_assoc(); $ext[]=$row );
                        $elemName = $ext[0]["name"];
                        $aext = explode(",", $ext[0]["id_ext1"]);
                        $aext[] = $id;
                        $aext = array_unique($aext);
                        sort($aext, SORT_NUMERIC);
                        if ( ($key = array_search('0',$aext) ) !== false) {
                            unset($aext[$key]);
                        }
                        $ext = implode(",", $aext);
                        $upd = $mysqli->query("UPDATE role SET id_ext1='$ext' WHERE id='$elemId'");
                        $this->result = $upd ?  $this->rnm_result(true, "Каталог добавлен", "$id", "$elemName") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                    } else {
                        $this->result = $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                    }
                } else {
                    $c = $mysqli->query("SELECT e.id, r.name FROM ext1 AS e LEFT JOIN role AS r ON r.id=e.id_role WHERE e.name='$elemName' AND e.id_role='$catRW'");
                    for ( $cat=Array(); $row=$c->fetch_assoc(); $cat[]=$row );
                    $id = $cat[0]["id"];
                    $elemName = $cat[0]["name"];
                    $e = $mysqli->query("SELECT id_ext1 FROM role WHERE id='$elemId'");
                    for ( $ext=Array(); $row=$e->fetch_assoc(); $ext[]=$row );
                    $aext = explode(",", $ext[0]["id_ext1"]);
                    $aext[] = $id;
                    $aext = array_unique($aext);
                    sort($aext, SORT_NUMERIC);
                    if ( ($key = array_search('0',$aext) ) !== false) {
                        unset($aext[$key]);
                    }
                    $ext = implode(",", $aext);
                    $upd = $mysqli->query("UPDATE role SET id_ext1='$ext' WHERE id='$elemId'");
                    $this->result = $upd ?  $this->rnm_result(true, "Каталог добавлен", "$id", "$elemName") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                }
            } else {
                $this->result = $this->rnm_result(false, "Каталог с таким именем и правами доступа уже существует");
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