<?php
$jData = $_REQUEST["data"];
$data = json_decode($jData, true, 2);

require_once("../modules/data.db.php");

$data["elemType"] = strlen($data["elemType"])>0 ? $data["elemType"] : "root";
$fnc = "fnc_".$data["elemType"];

$add = new fncStaffMove();
echo json_encode($add->$fnc($jData));

class fncStaffMove {
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

    // move division
    public function fnc_division($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $nextParentId = substr($nextParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $upd = $mysqli->query("UPDATE division SET company='$nextParentId' WHERE id='$elemId'");
            $this->result = $upd ?  $this->rnm_result(true, "Подразделение перемещено в другую организацию") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }

    // move function
    public function fnc_function($jData) {
        $data = json_decode($jData, true, 2);
        foreach($data as $k=>$v){
            $$k = is_null($v) ? 0 : $v;
        }
        $elemId = substr($elemId,4);
        $nextParentId = substr($nextParentId, 4);
        $prevParentId = substr($prevParentId, 4);
        if ( $mysqli = $this->db_connect() ) {
            $prevDivArr_ = $mysqli->query("SELECT division FROM function WHERE id='$elemId'");
            for ( $prevDivArr=Array(); $row=$prevDivArr_->fetch_assoc(); $prevDivArr[]=$row );
            $prevDivArr = explode(",", $prevDivArr[0]["division"]);
            $nextDiv = Array();
            for ( $i=0; $i<sizeOf($prevDivArr); $i++ ) {
                if ( $prevDivArr[$i] !== $prevParentId ) $nextDiv[] = $prevDivArr[$i];
            }
            $nextDiv[] = $nextParentId;
            ksort($nextDiv);
            $nextDiv = array_unique($nextDiv);
            $nextDiv = implode(",", $nextDiv);
            $upd = $mysqli->query("UPDATE function SET division='$nextDiv' WHERE id='$elemId'");
            $this->result = $upd ?  $this->rnm_result(true, "Должность перемещена в другое подразделение") : $this->rnm_result(false, "SQL-ERROR: $mysqli->error");
            $mysqli->close();
            return $this->result;
        } else {
            $this->result = $this->rnm_result(false,"MySQL Connecting Error: $mysqli->error");
            $mysqli->close();
            return $this->result;
        }
    }
}
/*if ( $data["elemType"] === "division" ) {
    $upd = $mysqli->query("UPDATE division SET company='".$data["nextParentId"]."' WHERE id='".$data["elemId"]."'");
    if ( $upd === true ) {
        echo '{"success": true, "msg": "Подразделение перемещено в другую организацию" }';
    } else {
        echo '{"success": false, "msg": "'.$mysqli->error.'" }';
    }
} else if ( $data["elemType"] === "function" ) {
	$prevDivArr_ = $mysqli->query("SELECT division FROM function WHERE id='".$data["elemId"]."'");
	for ( $prevDivArr=Array(); $row=$prevDivArr_->fetch_assoc(); $prevDivArr[]=$row );
	$prevDivArr = explode(",", $prevDivArr[0]["division"]);
	$nextDiv = Array();
	for ( $i=0; $i<sizeOf($prevDivArr); $i++ ) {
		if ( $prevDivArr[$i] !== $data["prevParentId"] ) $nextDiv[] = $prevDivArr[$i];
	}
	$nextDiv[] = $data["nextParentId"];
	ksort($nextDiv);
	$nextDiv = array_unique($nextDiv);
	$nextDiv = implode(",", $nextDiv);
	$upd = $mysqli->query("UPDATE function SET division='$nextDiv' WHERE id='".$data["elemId"]."'");
	if ( $upd === true ) {
		echo '{"success": true, "msg": "Должность перемещена в другое подразделение" }';
	} else {
		echo '{"success": false, "msg": "'.$mysqli->error.'" }';
	}
}

$mysqli->close();*/
?>