<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$data["elemType"] = strlen($data["elemType"])>0 ? $data["elemType"] : "root";
$fnc = "fnc_".$data["elemType"];

$rnm = new fncResRename();
echo json_encode($rnm->$fnc($jData));


class fncResRename {

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

    // change resource
    public function fnc_res($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM resource WHERE name='$elemName'");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $upd = $mysqli->query("UPDATE resource SET name='$elemName', form='$formRes' WHERE id='$elemId'");
                $this->result = $upd ? $this->rnm_result(true, "ИТ-ресурс изменен") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $this->result = $this->rnm_result(false, "Аналогичный ИТ-ресурс уже существует");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // change service
    public function fnc_srv($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $elemParentId = substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM service WHERE (name='$elemName') AND (id_res='$elemParentId')");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $upd = $mysqli->query("UPDATE service SET name='$elemName', id_own='$nameOwner', id_vise='$nameVizier', form='$formSrv', role_list_type='$roleType' WHERE id='$elemId'");
                $this->result = $upd ? $this->rnm_result(true, "ИТ-услуга переименована") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $this->result = $this->rnm_result(false, "Аналогичная ИТ-услуга уже существует");
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // change role
    public function fnc_rol($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $roleIdOld = substr($elemId,4);
        $srvId = substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM role WHERE name='$elemName'");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $r = $mysqli->query("UPDATE role SET name='$elemName' WHERE id='$roleIdOld'");
                $this->result = $r ? $this->rnm_result(true, "ИТ-роль переименована") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $t_ = $mysqli->query("SELECT id_srv FROM role WHERE name='$elemName'");
                for ( $t=Array(); $row=$t_->fetch_assoc(); $t[]=$row );
                $t = explode(',',$t[0]["id_srv"]);
                if ( in_array($srvId, $t) ) {
                    $this->result = $this->rnm_result(false, "Аналогичная ИТ-роль уже существует");
                } else {
                    $c = $mysqli->query("SELECT id FROM role WHERE name='$elemName'");
                    for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
                    $roleIdNew = $cnt[0]["id"];
                    $prevRolArr_ = $mysqli->query("SELECT id_srv FROM role WHERE id='$roleIdOld'");
                    for ( $prevRolArr=Array(); $row=$prevRolArr_->fetch_assoc(); $prevRolArr[]=$row );
                    $prevRolArr = explode(",", $prevRolArr[0]["id_srv"]);
                    $n = Array();
                    for ( $i=0; $i<sizeOf($prevRolArr); $i++ ) {
                        if ( $prevRolArr[$i] !== $srvId ) $n[] = $prevRolArr[$i];
                    }
                    sort($n);
                    $n = array_unique($n);
                    $n = implode(",", $n);
                    $upd = $mysqli->query("UPDATE role SET id_srv='$n' WHERE id='$roleIdOld'");
                    if ( $upd ) {
                        $NextRolArr_ = $mysqli->query("SELECT id_srv FROM role WHERE id='$roleIdNew'");
                        for ( $nextRolArr=Array(); $row=$NextRolArr_->fetch_assoc(); $nextRolArr[]=$row );
                        $nextRolArr = explode(",", $nextRolArr[0]["id_srv"]);
                        $nextRolArr[] = $srvId;
                        sort($nextRolArr);
                        $nextRolArr = array_unique($nextRolArr);
                        $nextRolArr = implode(",", $nextRolArr);
                        $upd = $mysqli->query("UPDATE role SET id_srv='$nextRolArr' WHERE id='$roleIdNew'");
                        $this->result = $upd ? $this->rnm_result(true, "ИТ-роль изменена") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                    } else {
                        $this->result = $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                    }
                }
            }
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // change ouop-role
    public function fnc_rup($jData) {
        return $this->fnc_rol($jData);
    }

    // change ouop-cat
    public function fnc_cat($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $catIdOld = substr($elemId,4);
        $rupId = substr($elemParentId,4);
        if ( $mysqli = $this->db_connect() ) {
            $elemName = $mysqli->real_escape_string($elemName);
            $c = $mysqli->query("SELECT COUNT(*) FROM ext1 WHERE (name='$elemName') AND (id_role='$catRW')");
            for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $r = $mysqli->query("UPDATE ext1 SET name='$elemName', id_role='$catRW' WHERE id='$catIdOld'");
                $this->result = $r ? $this->rnm_result(true, "ОУОП-каталог изменен") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            } else {
                $t_ = $mysqli->query("SELECT id FROM ext1 WHERE (name='$elemName') AND (id_role='$catRW')");
                for ( $t=Array(); $row=$t_->fetch_assoc(); $t[]=$row );
                $idCat = $t[0]["id"];
                $t_ = $mysqli->query("SELECT id_ext1 FROM role WHERE id='$rupId'");
                for ( $t=Array(); $row=$t_->fetch_assoc(); $t[]=$row );
                $t = explode(',',$t[0]["id_ext1"]);
                if ( in_array($idCat, $t) ) {
                    $this->result = $this->rnm_result(false, "Аналогичный ОУОП-каталог уже существует");
                } else {
                    $c = $mysqli->query("SELECT id FROM ext1 WHERE (name='$elemName') AND (id_role='$catRW')");
                    for ($cnt = Array(); $row = $c->fetch_assoc(); $cnt[] = $row) ;
                    $catIdNew = $cnt[0]["id"];
                    $prevCatArr_ = $mysqli->query("SELECT id_ext1 FROM role WHERE id='$rupId'");
                    for ($prevCatArr = Array(); $row = $prevCatArr_->fetch_assoc(); $prevCatArr[] = $row) ;
                    $prevCatArr = explode(",", $prevCatArr[0]["id_ext1"]);
                    $n = Array();
                    for ($i = 0; $i < sizeOf($prevCatArr); $i++) {
                        if ($prevCatArr[$i] !== $catIdOld) $n[] = $prevCatArr[$i];
                    }
                    $n[] = $rupId;
                    sort($n);
                    $n = array_unique($n);
                    $n = implode(",", $n);
                    $upd = $mysqli->query("UPDATE role SET id_ext1='$n' WHERE id='$rupId'");
                    if ($upd) {
                        $NextCatArr_ = $mysqli->query("SELECT id_ext1 FROM role WHERE id='$rupId'");
                        for ($nextCatArr = Array(); $row = $NextCatArr_->fetch_assoc(); $nextCatArr[] = $row) ;
                        $nextCatArr = explode(",", $nextCatArr[0]["id_ext1"]);
                        $nextCatArr[] = $catIdNew;
                        sort($nextCatArr);
                        $nextCatArr = array_unique($nextCatArr);
                        $nextCatArr = implode(",", $nextCatArr);
                        $upd = $mysqli->query("UPDATE role SET id_ext1='$nextCatArr' WHERE id='$rupId'");
                        $this->result = $upd ? $this->rnm_result(true, "ОУОП-каталог изменен") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
                    } else {
                        $this->result = $this->rnm_result(false, "SQL-ERROR: $mysqli->error");

                    }
                }
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