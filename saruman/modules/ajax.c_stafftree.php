<?php
require_once("../modules/data.db.php");
mb_internal_encoding("UTF-8");

$mysqli = new mysqli(db::host, db::user, db::password, db::base);
$mysqli->set_charset("utf8");

$comp_ = $mysqli->query("SELECT id, company FROM company ORDER BY company");
for ( $comp=Array(); $row=$comp_->fetch_assoc(); $comp[]=$row );

$div_ = $mysqli->query("SELECT d.id, d.division, d.company AS id_comp FROM division AS d LEFT JOIN company AS c ON c.id=d.company ORDER BY d.division");
for ( $div=Array(); $row=$div_->fetch_assoc(); $div[]=$row );

$func_ = $mysqli->query("SELECT id, func, division AS id_div FROM function ORDER BY func");
for ( $func=Array(); $row=$func_->fetch_assoc(); $func[]=$row );

$mysqli->close();

$tree = Array(
	"text"		=>	".",
	"children"	=>	Array()
);

for ( $i=0; $i<sizeOf($comp); $i++ ) {
	$tree["children"][$i]["comp_id"] = $comp[$i]["id"];
	$tree["children"][$i]["elemId"] = "org_".$comp[$i]["id"];
	$tree["children"][$i]["name"] = $comp[$i]["company"];
	$tree["children"][$i]["ntype"] = "company";
	$tree["children"][$i]["loaded"] = true;
	$tree["children"][$i]["children"] = null;
}

for ( $i=0; $i<sizeOf($tree["children"]); $i++ ) {
	$c = 0;
	for ( $j=0; $j<sizeOf($div); $j++ ) {
		if ( $tree["children"][$i]["comp_id"] === $div[$j]["id_comp"] ) {
            //$tree["children"][$i]["leaf"] = false;
			$tree["children"][$i]["children"][$c]["div_id"] = $div[$j]["id"];
			$tree["children"][$i]["children"][$c]["elemId"] = "div_".$div[$j]["id"];
			$tree["children"][$i]["children"][$c]["elemParentId"] = "org_".$div[$j]["id_comp"];
			$tree["children"][$i]["children"][$c]["name"] = $div[$j]["division"];
			$tree["children"][$i]["children"][$c]["ntype"] = "division";
			$tree["children"][$i]["children"][$c]["children"] = null;
            $tree["children"][$i]["loaded"] = false;
			$c++;
		}
	}
}

for ( $i=0; $i<sizeOf($tree["children"]); $i++ ) {
	for ( $j=0; $j<sizeOf($tree["children"][$i]["children"]); $j++ ) {
		$c = 0;
		for ( $k=0; $k<sizeOf($func); $k++ ) {
			$id_div = explode(",",$func[$k]["id_div"]);
			if ( in_array($tree["children"][$i]["children"][$j]["div_id"], $id_div) ) {
                //$tree["children"][$i]["children"][$j]["leaf"] = false;
				$tree["children"][$i]["children"][$j]["children"][$c]["name"] = $func[$k]["func"];
				$tree["children"][$i]["children"][$j]["children"][$c]["elemId"] = "fnc_".$func[$k]["id"];
				$tree["children"][$i]["children"][$j]["children"][$c]["elemParentId"] = "div_".$tree["children"][$i]["children"][$j]["div_id"];
				$tree["children"][$i]["children"][$j]["children"][$c]["ntype"] = "function";
				$tree["children"][$i]["children"][$j]["children"][$c]["loaded"] = true;
				$tree["children"][$i]["children"][$j]["children"][$c]["leaf"] = true;
				$c++;
			}
		}
	}
}

echo json_encode($tree);
?>