<?php
$uploaddir = $_SERVER['DOCUMENT_ROOT']."/docs/upload/";
$uploadfile = $uploaddir . basename($_FILES["inputUploadFile"]["name"]);
if (move_uploaded_file($_FILES["inputUploadFile"]["tmp_name"], $uploadfile)) {
	echo parseUserData($uploadfile, $_REQUEST["cbUploadCompany"]);
} else {
	echo '{"success": false, "msg": "Ошибка загрузки файла ('. $_FILES["inputUploadFile"]["error"] .')" }';
}

//------------------------------------------------------------------------------------------------	
function parseUserData($nFile, $company) {
	require_once("functions.php");
	require_once("sql.php");

	mb_internal_encoding("UTF-8");

	$port = "3268";
	$domain = "corp.vgtz.com";
	$basedn = "DC=corp,DC=vgtz,DC=com";
	$group = "VLG_IS_USER";
	$user = "is5493";
	$password = "),k0vbcm";

	$db_host = "localhost";
	$db_user = "root";
	$db_password = "";
	//$db_password = "dm48hsw2";
	$db_base = "ts";
		
	$hFile = fopen($nFile, "r");
	for ( $i=0; $data[]=fgetcsv($hFile, filesize($nFile), ";"); $i++ );
	fclose($hFile);

	/*$name_id = array_search("фио", strtolower($data[0]));
	$div_id = array_search("подразделение", strtolower($data[0]));
	$func_id = array_search("штатная должность", strtolower($data[0]));*/

	/*if ( @!mb_detect_encoding($data[0][0], "UTF-8", true) )
		for ( $i=0; $i<sizeOf($data); $i++ )
			for ( $j=0; $j<sizeOf($data[$i]); $j++ )
				$data[$i][$j] = utf8($data[$i][$j]);*/
	
	$name_id = array_search("ФИО", $data[0]);
	$div_id = array_search("Подразделение", $data[0]);
	$func_id = array_search("Штатная должность", $data[0]);
	
	if ( ($name_id === false) || ($div_id === false) || ($func_id === false) ) {
		return '{"success": false, "msg": "Неверный формат файла, отсутствуют необходимые поля" }';
	}
	
	$new_func = 0;
	$new_div = 0;
	$new_user = 0;

	if (($ad = ldap_connect($domain,$port)) !== false ) {
		ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
		if (@ldap_bind($ad, "{$user}@{$domain}", $password)) {
			$userdn = getDN($ad, $user, $basedn);
			if (checkGroup($ad, $userdn, getDN($ad, $group, $basedn))) {
				$adusr = getDataFromAD($ad, $basedn);
			} else {
				return '{"success": false, "msg": "К сожалению, у Вас нет доступа к AD" }';
			}
			ldap_unbind($ad);
		} else {
			return '{"success": false, "msg": "Пара имя-пароль AD не распознана" }';
		}
	} else {
		return '{"success": false, "msg": "Ошибка подключения к AD" }';
	}
	
	$mysqli = new mysqli($db_host, $db_user, $db_password, $db_base);
	$mysqli->set_charset("utf8");

	for ( $i=1; $i<sizeOf($data); $i++ ) {
		$full_name = $data[$i][$name_id];
		$division = $data[$i][$div_id];
		$function = $data[$i][$func_id];
		
		$full_name = trim(preg_replace('/\s+/', ' ', $full_name));
		$division = trim(preg_replace('/\s+/', ' ', $division));
		$function = trim(preg_replace('/\s+/', ' ', $function));
		
		$full_name = preg_replace_callback('/([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})/u', "convert", $full_name);
		$division = preg_replace('/([0-9]{10})(\s{0,}:\s{0,})/u', '', $division);
		
		$division = preg_replace('/("ВМК КМЗ")/u', '«ВМК КМЗ»', $division);	
		$division = preg_replace('/("КМЗ")/u', '«КМЗ»', $division);	
		$division = preg_replace('/("ВМК "ВгТЗ")/u', '«ВМК «ВгТЗ»', $division);
		
		$function = preg_replace('/("ВМК КМЗ")/u', '«ВМК КМЗ»', $function);
		$function = preg_replace('/("КМЗ")/u', '«КМЗ»', $function);
		$function = preg_replace('/("ВМК "ВгТЗ")/u', '«ВМК «ВгТЗ»', $function);
		
		$c = $mysqli->query("SELECT COUNT(*) FROM user WHERE (name='$full_name')");
		for ( $cnt=Array(); $row=$c->fetch_assoc(); $cnt[]=$row );
		if ( ( $cnt[0]["COUNT(*)"] === "0" ) && ( mb_strlen($full_name) > 8 ) ) {
			$d_ = $mysqli->query("SELECT id, company FROM division WHERE division='$division'");
			$f_ = $mysqli->query("SELECT id, division FROM function WHERE func='$function'");
			
			for ( $d=Array(); $row=$d_->fetch_assoc(); $d[]=$row );
			for ( $f=Array(); $row=$f_->fetch_assoc(); $f[]=$row );
			
			if ( !isset($d[0]["id"]) ) {
				$ins = $mysqli->query("INSERT INTO division (division, company, chief, director) VALUES ('$division', '$company', '0', '0')");
				if ( $ins === true ) {
					$new_div++;
					$div = $mysqli->insert_id;
				} else $div = 0;
			} else {
				$div = $d[0]["id"];
			}
			
			if ( !isset($f[0]["id"]) ) {
				$ins = $mysqli->query("INSERT INTO function (func, division) VALUES ('$function', '$div')");
				if ( $ins === true ) {
					$new_func++;
					$func = $mysqli->insert_id;
				} else $func = 0;
			} else {
				$func = $f[0]["id"];
				$ad = explode(",", $f[0]["division"]);
				$t = array_search($div, $ad);
				if ( $t === false ) {
					$ad[] = $div;
					asort($ad);
					$sd = implode(",", $ad);
					$upd = $mysqli->query("UPDATE function SET division='$sd' WHERE id='$func'");
				}
			}
			
			for ( $j=0; $j<$adusr["count"]; $j++ ) {
				if ( $adusr[$j]["cn"] == $full_name ) {
					$email = isset($adusr[$j]["mail"][0]) ? $adusr[$j]["mail"][0] : "";
					$phone = isset($adusr[$j]["telephoneNumber"][0]) ? $adusr[$j]["telephoneNumber"][0] : "";
					break;
				} else {
					$email = "";
					$phone = "";
				}
			}
			
			$ins = $mysqli->query("INSERT INTO user (name, company, division, function, email, phone, enabled) VALUES ('$full_name', '$company', '$div', '$func', '$email', '$phone', '0')");
			if ( $ins === true ) $new_user++;
		}
	}

	$mysqli->close();
	if ( error_get_last() ) {
		return '{"success": false, "msg": "' . error_get_last()["message"] . ' (' . error_get_last()["file"] . ' :' . error_get_last()["line"] . ')" }';
	} else {
		return '{"success": true, "msg": "пользователей '. $new_user .', подразделений '.$new_div.', должностей '.$new_func.'" }';
	}
}

/*function utf8($str) {
	return iconv("windows-1251", "UTF-8//IGNORE", $str);
}*/

function convert($p) {
	return mb_strtoupper($p[1]).mb_strtolower($p[2])." ".mb_strtoupper($p[3]).mb_strtolower($p[4])." ".mb_strtoupper($p[5]).mb_strtolower($p[6]);
}

function getDataFromAD($ad, $basedn) {
	$search_filter = "(&(l=Волгоград)(memberOf=CN=VLG_ALL_USER,OU=group tech,OU=Volgograd,DC=corp,DC=vgtz,DC=com))";
	$attributes = Array();
	$attributes[] = "cn";
	$attributes[] = "mail";
	$attributes[] = "telephoneNumber";
	$result = ldap_search($ad, $basedn, $search_filter, $attributes);
	$entries = ldap_get_entries($ad, $result);
	
	for ( $x=0; $x<$entries["count"]; $x++ ) {
		$nm = trim($entries[$x]["cn"][0]);
		$nm = trim(preg_replace('/\s+/', ' ', $nm));
		if ( preg_match('/([а-яА-ЯёЁ]{2,})+\s+([а-яА-ЯёЁ]{2,})+\s+([а-яА-ЯёЁ]{2,})/', $nm) == 1 ) {
			$nm = preg_replace_callback('/([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})/u', "convert", $nm);
		}
		$entries[$x]["cn"] = $nm;
	}
	//var_dump($entries);
	return $entries;
}

function getDN($ad, $samaccountname, $basedn) {
	$attributes = array('dn');
	$result = ldap_search($ad, $basedn,"(sAMAccountName={$samaccountname})");
	if ($result === FALSE) { return ''; }
	$entries = ldap_get_entries($ad, $result);
	if ($entries['count']>0) { return $entries[0]['dn']; }
	else { return ''; };
}

function getCN($dn) {
	preg_match('/[^,]*/', $dn, $matchs, PREG_OFFSET_CAPTURE, 3);
	return $matchs[0][0];
}


function checkGroup($ad, $userdn, $groupdn) {
	$attributes = array('members');
	$result = ldap_read($ad, $userdn, "(memberof={$groupdn})", $attributes);
	if ($result === FALSE) { return FALSE; };
	$entries = ldap_get_entries($ad, $result);
	return ($entries['count'] > 0);
}

function checkGroupEx($ad, $userdn, $groupdn) {
	$attributes = array('memberof');
	$result = ldap_read($ad, $userdn, '(objectclass=*)', $attributes);
	if ($result === FALSE) { return FALSE; };
	$entries = ldap_get_entries($ad, $result);
	if ($entries['count'] <= 0) { return FALSE; };
	if (empty($entries[0]['memberof'])) { return FALSE; } else {
		for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
			if ($entries[0]['memberof'][$i] == $groupdn) { return TRUE; }
			elseif (checkGroupEx($ad, $entries[0]['memberof'][$i], $groupdn)) { return TRUE; };
		};
	};
	return FALSE;
}
?>