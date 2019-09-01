<?php
$uploaddir = $_SERVER['DOCUMENT_ROOT']."/docs/upload/";
$uploadfile = $uploaddir . basename($_FILES["inputUploadFileX"]["name"]);
if (move_uploaded_file($_FILES["inputUploadFileX"]["tmp_name"], $uploadfile)) {
	echo parseUserData($uploadfile, $_REQUEST["cbUploadCompany"]);
} else {
	echo '{"success": false, "msg": "Ошибка загрузки файла ('. $_FILES["inputUploadFileX"]["error"] .')" }';
}

//------------------------------------------------------------------------------------------------	
function parseUserData($nFile, $company) {
	require_once("functions.php");
	require_once("sql.php");
	require_once("PHPExcel.php");

	mb_internal_encoding("UTF-8");

	$port = "3268";
	$domain = "corp.vgtz.com";
	$basedn = "DC=corp,DC=vgtz,DC=com";
	$group = "VLG_IS_USER";
	$user = "is5493";
	$password = "0,k0vbcm";

	$db_host = "localhost";
	$db_user = "root";
	$db_password = "";
	//$db_password = "dm48hsw2";
	$db_base = "ts";
	
	$oExcel = PHPExcel_IOFactory::load($nFile);
	
	$nRows = $oExcel->getActiveSheet()->getHighestRow();
	$nColumns = PHPExcel_Cell::columnIndexFromString($oExcel->getActiveSheet()->getHighestColumn());

	$iName = -1;
	$iDivision = -1;
	$iFunction = -1;
	//$iCategory = -1;
	$iFuncType = -1;

	for ($j = 0; $j < $nColumns; $j++) {
		$value = trim($oExcel->getActiveSheet()->getCellByColumnAndRow($j, 1)->getValue());
		if ( mb_strtolower($value) == "фио" ) $iName = $j;
		if ( mb_strtolower($value) == "подразделение" ) $iDivision = $j;
		if ( mb_strtolower($value) == "штатная должность" ) $iFunction = $j;
		if ( mb_strtolower($value) == "вид должностного исполнения" ) $iFuncType = $j;
	}

	if ( ($iName < 0) || ($iDivision < 0) || ($iFunction  < 0) ) {
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

	for ( $i=2; $i<=$nRows; $i++ ) {
		$full_name = $oExcel->getActiveSheet()->getCellByColumnAndRow($iName, $i)->getValue();
		$division = $oExcel->getActiveSheet()->getCellByColumnAndRow($iDivision, $i)->getValue();
		$function = $oExcel->getActiveSheet()->getCellByColumnAndRow($iFunction, $i)->getValue();
		
		if ( $iFuncType  > 0 ) {
            $functype = $oExcel->getActiveSheet()->getCellByColumnAndRow($iFuncType, $i)->getValue();

            if ( mb_strpos(mb_strtolower($functype),'основное') === FALSE )
                continue;
        }
		
		$full_name = trim(preg_replace('/\s+/', ' ', $full_name));
		$division = trim(preg_replace('/\s+/', ' ', $division));
		$function = trim(preg_replace('/\s+/', ' ', $function));
		
		$full_name = preg_replace_callback('/([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})/u', "convert", $full_name);
		$division = preg_replace('/([0-9]{10})(\s{0,}:\s{0,})/u', '', $division);
		$division = preg_replace('/([0-9]{3})(\s{0,})/u', '', $division);
		
		$division = preg_replace('/("ВМК КМЗ")/u', '«ВМК КМЗ»', $division);	
		$division = preg_replace('/("КМЗ")/u', '«КМЗ»', $division);	
		$division = preg_replace('/("ВМК "ВгТЗ")/u', '«ВМК «ВгТЗ»', $division);
		
		$function = preg_replace('/("ВМК КМЗ")/u', '«ВМК КМЗ»', $function);
		$function = preg_replace('/("КМЗ")/u', '«КМЗ»', $function);
		$function = preg_replace('/("ВМК "ВгТЗ")/u', '«ВМК «ВгТЗ»', $function);
		
		$c = $mysqli->query("SELECT COUNT(*) FROM user WHERE ((name='$full_name') AND (company='$company') AND (enabled='1'))");
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
			
			$ins = $mysqli->query("INSERT INTO user (name, company, division, function, email, phone, enabled) VALUES ('$full_name', '$company', '$div', '$func', '$email', '$phone', '1')");
			if ( $ins === true ) $new_user++;
		} elseif ( ( $cnt[0]["COUNT(*)"] !== "0" ) && ( mb_strlen($full_name) > 8 ) ) {
            $d_ = $mysqli->query("SELECT id, company FROM division WHERE division='$division'");
            $f_ = $mysqli->query("SELECT id, division FROM function WHERE func='$function'");

            for ( $d=Array(); $row=$d_->fetch_assoc(); $d[]=$row );
            for ( $f=Array(); $row=$f_->fetch_assoc(); $f[]=$row );

            if ( (isset($d[0]["id"])) && (isset($f[0]["id"])) ) {
                $isuser = $mysqli->query("SELECT COUNT(*) FROM user WHERE ((name='$full_name') AND (company='$company') AND (division='".$d[0]["id"]."') AND (function='".$f[0]["id"]."') AND (enabled='1'))");
                for ( $cnt=Array(); $row=$isuser->fetch_assoc(); $cnt[]=$row );
                if ( $cnt[0]["COUNT(*)"] === "0" ){
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

                    $ins = $mysqli->query("INSERT INTO user (name, company, division, function, email, phone, enabled) VALUES ('$full_name', '$company', '".$d[0]["id"]."', '".$f[0]["id"]."', '$email', '$phone', '1')");
                    if ( $ins === true ) $new_user++;
                }
            } else {
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

                $ins = $mysqli->query("INSERT INTO user (name, company, division, function, email, phone, enabled) VALUES ('$full_name', '$company', '$div', '$func', '$email', '$phone', '1')");
                if ( $ins === true ) $new_user++;
            }
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
?>