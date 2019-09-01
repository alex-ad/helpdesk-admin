<?php
require_once("../modules/data.db.php");

mb_internal_encoding("UTF-8");

$mysqli = new mysqli(db::host, db::user, db::password, db::base);
$mysqli->set_charset("utf8");

$res_ = $mysqli->query("SELECT id, name, form FROM resource WHERE visible='1' ORDER BY name");
for ( $res=Array(); $row=$res_->fetch_assoc(); $res[]=$row );

$srv_ = $mysqli->query("SELECT s.id, s.name, s.id_res, s.role_list_type, s.form, uo.id AS ownname_id, uo.name AS ownname, uv.id AS visename_id, uv.name AS visename FROM service AS s LEFT JOIN user AS uo ON s.id_own=uo.id LEFT JOIN user AS uv ON s.id_vise=uv.id WHERE s.enabled='1' ORDER BY s.name");
for ( $srv=Array(); $row=$srv_->fetch_assoc(); $srv[]=$row );

$rol_ = $mysqli->query("SELECT id, name, id_srv, id_ext1 FROM role WHERE visible='1' ORDER BY name");
for ( $rol=Array(); $row=$rol_->fetch_assoc(); $rol[]=$row );

$tree = Array(
	"text"		=>	".",
	"children"	=>	Array()
);

for ( $i=0; $i<sizeOf($res); $i++ ) {
	$tree["children"][$i]["res_id"] = $res[$i]["id"];
	$tree["children"][$i]["elemId"] = "res_".$res[$i]["id"];
	$tree["children"][$i]["name"] = $res[$i]["name"];
	$tree["children"][$i]["form"] = $res[$i]["form"];
    $tree["children"][$i]["ntype"] = "res";
	$tree["children"][$i]["children"] = null;
    $tree["children"][$i]["loaded"] = true;
}
for ( $i=0; $i<sizeOf($tree["children"]); $i++ ) {
	$c = 0;
	for ( $j=0; $j<sizeOf($srv); $j++ ) {
		if ( $tree["children"][$i]["res_id"] == $srv[$j]["id_res"] ) {
			$tree["children"][$i]["children"][$c]["srv_id"] = $srv[$j]["id"];
			$tree["children"][$i]["children"][$c]["elemId"] = "srv_".$srv[$j]["id"];
			$tree["children"][$i]["children"][$c]["elemParentId"] = "res_".$srv[$j]["id_res"];
			$tree["children"][$i]["children"][$c]["name"] = $srv[$j]["name"];
			$tree["children"][$i]["children"][$c]["form"] = $srv[$j]["form"];
			$tree["children"][$i]["children"][$c]["role_list_type"] = $srv[$j]["role_list_type"];
			$tree["children"][$i]["children"][$c]["ownname"] = $srv[$j]["ownname"];
			$tree["children"][$i]["children"][$c]["ownname_id"] = $srv[$j]["ownname_id"];
			$tree["children"][$i]["children"][$c]["visename"] = $srv[$j]["visename"];
			$tree["children"][$i]["children"][$c]["visename_id"] = $srv[$j]["visename_id"];
			$tree["children"][$i]["children"][$c]["ntype"] = "srv";
			$tree["children"][$i]["children"][$c]["children"] = null;
            $tree["children"][$i]["loaded"] = false;
			$c++;
		}
	}
}

for ( $i=0; $i<sizeOf($tree["children"]); $i++ ) {
	for ( $j=0; $j<sizeOf($tree["children"][$i]["children"]); $j++ ) {
		$c = 0;
		for ( $k=0; $k<sizeOf($rol); $k++ ) {
			$id_srv = explode(",",$rol[$k]["id_srv"]);
			if ( in_array($tree["children"][$i]["children"][$j]["srv_id"], $id_srv) ) {
				$tree["children"][$i]["children"][$j]["children"][$c]["name"] = $rol[$k]["name"];
				$tree["children"][$i]["children"][$j]["children"][$c]["elemId"] = "rol_".$rol[$k]["id"];
				$tree["children"][$i]["children"][$j]["children"][$c]["elemParentId"] = "srv_".$tree["children"][$i]["children"][$j]["srv_id"];
				if ( $rol[$k]["id_ext1"] !== "0" ) {
				    $role_id = $rol[$k]["id"];
                    $ouop = array_map(function($elem) use ($role_id) {
                        $sql = new mysqli(db::host, db::user, db::password, db::base);
                        $sql->set_charset("utf8");
                        $ouop_ = ( strlen($elem)>0 ) ? $sql->query("SELECT e.name, e.id, r.id AS rid, r.name AS rw FROM ext1 AS e LEFT JOIN role AS r ON e.id_role=r.id WHERE e.id='$elem'") : "";
                        $sql->close();
                        for ( $ouop=Array(); $row=$ouop_->fetch_assoc(); $ouop[]=$row );
                        unset($ret);
                        for ( $t=0; $t<sizeOf($ouop); $t++ ) {
                            $ret["name"] = $ouop[$t]["name"];
                            $ret["crw"] = $ouop[$t]["rid"];
                            $ret["elemId"] = "cat_".$ouop[$t]["id"];
                            $ret["elemParentId"] = "rup_".$role_id;
                            $ret["expanded"] = true;
                            $ret["cls"] = "ouop-cat";
                            $ret["ntype"] = "cat";
                            $ret["children"][0]["name"] = $ouop[$t]["rw"];
                            $ret["children"][0]["crw"] = $ouop[$t]["rid"];
                            $ret["children"][0]["elemId"] = "crw_".$ouop[$t]["rid"];
                            $ret["children"][0]["elemParentId"] = "cat_".$ouop[$t]["id"];
                            $ret["children"][0]["leaf"] = true;
                            $ret["children"][0]["cls"] = "ouop-cat-role";
                            $ret["children"][0]["ntype"] = "crw";
                            $ret["children"][0]["loaded"] = true;
                        }
                        return $ret;
                    },explode(",",$rol[$k]["id_ext1"]));
                    $tree["children"][$i]["children"][$j]["children"][$c]["cls"] = "ouop-role";
                    $tree["children"][$i]["children"][$j]["children"][$c]["ntype"] = "rup";
                    $tree["children"][$i]["children"][$j]["children"][$c]["children"] = $ouop;
                    //$tree["children"][$i]["children"][$j]["children"][$c]["loaded"] = true;
                } else {
                    $tree["children"][$i]["children"][$j]["children"][$c]["ntype"] = "rol";
                    $tree["children"][$i]["children"][$j]["children"][$c]["leaf"] = true;
                    $tree["children"][$i]["children"][$j]["children"][$c]["loaded"] = true;
                }
				$c++;
			}
		}
	}
}

$mysqli->close();

echo json_encode($tree);

?>