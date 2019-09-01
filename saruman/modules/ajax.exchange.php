<?php
	$func = ( isset($_REQUEST["func"]) ) ? $_REQUEST["func"] : "";
	$data = ( isset($_REQUEST["data"]) ) ? $_REQUEST["data"] : "";
	$v1 = ( isset($_REQUEST["v1"]) ) ? $_REQUEST["v1"] : "";
	if (isset($_REQUEST["term"])) $data = $_REQUEST["term"];
	
	if ( $func !== "" )  dataExchange($func, $data, $v1);
	
	function dataExchange($func, $data="", $v1="") {
		$upd = json_decode(file_get_contents("php://input"), true, 3);
		require_once("sql.php");
		$sql = new sql;
		if ( (sizeOf($upd) > 0) and (isset($upd["write"])) ) {
			$upd = json_encode($upd["write"]);
			$sql->dataSQL($func, $upd, $v1);
		} else {
			$ret = $sql->dataSQL($func, $data, $v1);
			switch ($func) {
				case "getTicketList":
					$ret = formTicketList($ret);
					break;
			}
			echo json_encode($ret);
		}
		
	}
	
	function formTicketList($t) {
		require_once("sql.php");
		require_once("data.config.php");
		require_once("functions.php");
		for ( $i=0; $i<sizeOf($t); $i++ ) {
			$t[$i]["resource"] = implode("; ", array_map(function($elem){
				$sql = new sql;
				return ( strlen($elem)>0 ) ? $sql->dataSQL("getResourceById", $elem)[0]["name"] : "";
			},array_unique(explode("|",$t[$i]["resource"]))));
		
			$t[$i]["service"] = implode("; ", array_map(function($elem){
				$sql = new sql;
				return ( strlen($elem)>0 ) ? $sql->dataSQL("getServiceById", $elem)[0]["name"] : "";
			},array_unique(explode("|",$t[$i]["service"]))));
			
			$hostroot = $_SERVER['DOCUMENT_ROOT'];
			$file = $hostroot . PATH["TICKET"] . substr($t[$i]["start"], 0, 7) . "/ticket_" . $t[$i]["id"] . ".pdf";
			$flink = CONFIG["HOST"] . PATH["TICKET"] . substr($t[$i]["start"], 0, 7) . "/ticket_" . $t[$i]["id"] . ".pdf";
			if ( file_exists($file) ) {
				$t[$i]["file"] = $file;
			} else {
				$raw = Array();
				$raw = $t[$i];
				$raw["u_name"] = $t[$i]["name"];
				$raw["u_company"] = $t[$i]["company"];
				$raw["u_division"] = $t[$i]["division"];
				$raw["u_func"] = $t[$i]["func"];
				$raw["u_phone"] = $t[$i]["phone"];
				$raw = json_encode($raw);
				/*$ticket = createPDF($raw, $t[$i]["id"]);
				$t[$i]["file"] = $flink;*/
			}
			$t[$i]["file"] = ( file_exists($file) ) ? $flink : "";
		}
		return $t;
	}
?>