<?php
require_once("data.config.php");
class db {
	const	host = SQL["HOST"];
	const	base = SQL["BASE"];
	const	user = SQL["USER"];
	const	password = SQL["PASSWORD"];
	
	/*---*/
	public static function getUserNameList($data, $v1) {
		return "SELECT name FROM user ORDER BY name";
	}
	const	errgetUserNameList	=	"ERROR: getUserNameList";

    /*---*/
    public static function getUserListAsWP($data, $v1) {
        return "SELECT u.id, u.name, c.company, f.func, d.division FROM user AS u LEFT JOIN company AS c ON c.id=u.company LEFT JOIN function AS f ON f.id=u.function LEFT JOIN division AS d ON d.id=u.division ORDER BY name";
    }
    const	errgetUserListAsWP	=	"ERROR: getUserListAsWP";
	
	/*---*/
	public static function getUserList($data, $v1) {
		return "SELECT u.id, u.name, c.company, d.id AS did, d.division, f.func, u.phone, u.email FROM user AS u LEFT JOIN company AS c ON c.id=u.company LEFT JOIN division AS d ON d.id=u.division LEFT JOIN function AS f ON f.id=u.function LEFT JOIN division ON u.division=division.id ORDER BY u.name";
	}
	const	errgetUserList	=	"ERROR: getUserList";
	
	/*---*/
	public static function getSuperUserList($data, $v1) {
		return "SELECT login, acr FROM su";
	}
	const	errgetSuperUserList	=	"ERROR: getSuperUserList";
	
	/*---*/
	public static function fnUser($data, $v1) {
		$upd = json_decode($data, true, 3);
		if ( sizeOf($upd) > 1 ) {
			$params = Array();
			if ( $upd["id"] === 0 ) {
				$params[] = ( isset($upd["name"]) ) ? "'".$upd["name"]."'" : "'name'";
				$params[] = ( isset($upd["login"]) ) ? "'".$upd["login"]."'" : "'name'";
				$params[] = ( isset($upd["phone"]) ) ? "'".$upd["phone"]."'" : "''";
				$params[] = ( isset($upd["email"]) ) ? "'".$upd["email"]."'" : "''";
				$params[] = ( isset($upd["company"]) ) ? "'".$upd["company"]."'" : "'0'";
				$params[] = ( isset($upd["division"]) ) ? "'".$upd["division"]."'" : "'0'";
				$params[] = ( isset($upd["func"]) ) ? "'".$upd["func"]."'" : "'0'";
				$params[] = ( isset($upd["location"]) ) ? "'".$upd["location"]."'" : "'0'";
				$params[] = ( isset($upd["tnumber"]) ) ? "'".$upd["tnumber"]."'" : "'0'";
				$params[] = ( isset($upd["enabled"]) ) ? "'".$upd["enabled"]."'" : "'0'";
				$params = implode(",", $params);
				return "INSERT INTO user (name, phone, email, company, division, function, enabled) VALUES ($params)";
			} else {
				if ( isset($upd["name"]) ) $params[] = "name='".$upd["name"]."'";
				if ( isset($upd["login"]) ) $params[] = "login='".$upd["login"]."'";
				if ( isset($upd["phone"]) ) $params[] = "phone='".$upd["phone"]."'";
				if ( isset($upd["email"]) ) $params[] = "email='".$upd["email"]."'";
				if ( isset($upd["company"]) ) $params[] = "company='".$upd["company"]."'";
				if ( isset($upd["division"]) ) $params[] = "division='".$upd["division"]."'";
				if ( isset($upd["func"]) ) $params[] = "function='".$upd["func"]."'";
				if ( isset($upd["location"]) ) $params[] = "location='".$upd["location"]."'";
				if ( isset($upd["tnumber"]) ) $params[] = "tnumber='".$upd["tnumber"]."'";
				if ( isset($upd["enabled"]) ) $params[] = "enabled='".(integer) $upd["enabled"]."'";
				$params = implode(",", $params);
				return "UPDATE user SET $params WHERE id='".$upd["id"]."'";
			}
		} else {
			return "SELECT u.id, u.name, u.login, c.company, d.division, f.func, u.email, u.phone, u.location, u.tnumber, u.enabled FROM user AS u LEFT JOIN company AS c ON c.id=u.company LEFT JOIN division AS d ON d.id=u.division LEFT JOIN function AS f ON f.id=u.function ORDER BY u.name";
		}
	}
	const errfnUser	=	"ERROR: fnUser";
		
	/*---*/
	public static function getCompanyList($data, $v1) {
		return "SELECT id, company FROM company ORDER BY company";
	}
	const	errgetCompanyList	=	"ERROR: getCompanyList";

	/*---*/
	public static function getCompanyListAsName($data, $v1) {
		return "SELECT company AS name FROM company ORDER BY company";
	}
	const	errgetCompanyListAsName	=	"ERROR: getCompanyListAsName";
	
	/*---*/
	public static function getCompanyListComplex($data, $v1) {
		return "SELECT id AS idComp, company FROM company ORDER BY company";
	}
	const	errgetCompanyListComplex	=	"ERROR: getCompanyListComplex";
	
	/*---*/
	public static function fnCompany($data, $v1) {
		$upd = json_decode($data, true, 2);
		if ( sizeOf($upd) > 1 ) {
			return "UPDATE company SET company='".$upd["company"]."' WHERE id='".$upd["id"]."'";
		} else {
			return "SELECT id, company FROM company ORDER BY company";
		}
	}
	const	errfnCompany	=	"ERROR: fnCompany";
		
	/*---*/
	public static function getDivisionList($data, $v1) {
		return "SELECT id, division FROM division WHERE company='$data' ORDER BY division";
	}
	const	errgetDivisionList	=	"ERROR: getDivisionList";

	/*---*/
	public static function getDivisionListAsName($data, $v1) {
		//return "SELECT division AS name FROM division WHERE company='$data' ORDER BY division";
		return "SELECT division AS name FROM division ORDER BY division";
	}
	const	errgetDivisionListAsName	=	"ERROR: getDivisionListAsName";
	
	/*---*/
	public static function getDivisionListAll($data, $v1) {
		return "SELECT id, division FROM division ORDER BY division";
	}
	const	errgetDivisionListAll	=	"ERROR: getDivisionListAll";
	
	/*---*/
	public static function getDivisionListAllComplex($data, $v1) {
        $param = ( $data !== "" ) ? "WHERE company='$data'" : "";
		return "SELECT id AS idDiv, division FROM division $param ORDER BY division";
	}
	const	errgetDivisionListAllComplex	=	"ERROR: getDivisionListAllComplex";
	
	/*---*/
	public static function getFunctionList($data, $v1) {
		return "SELECT id, func FROM function WHERE ( FIND_IN_SET('$data', division)>0 ) ORDER BY func";
	}
	const	errgetFunctionList	=	"ERROR: getFunctionList";

	/*---*/
	public static function getFunctionListAsName($data, $v1) {
		return "SELECT func AS name FROM function ORDER BY func";
	}
	const	errgetFunctionListAsName	=	"ERROR: getFunctionListAsName";
	
	/*---*/
	public static function getFunctionListAll($data, $v1) {
		return "SELECT id, func FROM function ORDER BY func";
	}
	const	errgetFunctionListAll	=	"ERROR: getFunctionListAll";
	
	/*---*/
	public static function getFunctionListAllComplex($data, $v1) {
	    $param = ( $data !== "" ) ? "WHERE FIND_IN_SET('$data', division)" : "";
		return "SELECT id AS idFunc, func FROM function $param ORDER BY func";
	}
	const	errgetFunctionListAllComplex	=	"ERROR: getFunctionListAllComplex";
			
	/*---*/
	public static function getOwnerNameById($data, $v1) {
		return "SELECT u.name, f.func FROM user AS u LEFT JOIN function AS f ON f.id=u.function WHERE (u.id='$data')";
	}
	const errgetOwnerNameById	=	"ERROR: getOwnerNameById";
	
	/*---*/
	public static function getChiefByDivisionId($data, $v1) {
		return "SELECT d.division, u.name, f.func FROM division AS d LEFT JOIN user AS u ON u.id=d.chief LEFT JOIN function AS f ON u.function=f.id WHERE d.id='$data'";
	}
	const errgetChiefByDivisionId	=	"ERROR: getChiefByDivisionId";

	/*---*/
	public static function getExt1ById($data, $v1) {
		return "SELECT e.name, r.name AS rw FROM ext1 AS e LEFT JOIN role AS r ON e.id_role=r.id WHERE e.id='$data'";
	}
	const errgetExt1ById	=	"ERROR: getExt1ById";
	
	/*---*/
	public static function getRoleById($data, $v1) {
		if ( strpos($data,"a") !== false ) {
			$id = substr($data,1);
			return "SELECT func AS name FROM function WHERE id='$id'";
		} else if ( strpos($data,"b") !== false ) {
			$id = substr($data,1);
			return "SELECT division AS name FROM division_osp WHERE id='$id'";
		} else {
			return "SELECT name, id_ext1 FROM role WHERE id='$data'";
		}
	}
	const errgetRoleById	=	"ERROR: getRoleById";
	
	/*---*/
	public static function getUserName($data, $v1) {
		return "SELECT u.id, u.name, c.company, f.func, d.division FROM user AS u LEFT JOIN company AS c ON c.id=u.company LEFT JOIN function AS f ON f.id=u.function LEFT JOIN division AS d ON d.id=u.division WHERE ((u.name LIKE '$data%') AND (u.enabled='1')) ORDER BY name";
	}
	const errgetUserName	=	"ERROR: getUserName";
	
	/*---*/
	public static function getResourceById($data, $v1) {
		return "SELECT name FROM resource WHERE id='$data'";
	}
	const errgetResourceById	=	"ERROR: getResourceById";
	
	/*---*/
	public static function getServiceById($data, $v1) {
		return "SELECT name FROM service WHERE id='$data'";
	}
	const errgetServiceById	=	"ERROR: getServiceById";

    /*---*/
    public static function getServiceList($data, $v1) {
        return "SELECT name, role_list_type, form FROM service WHERE enabled='1'";
    }
    const errgetServiceList	=	"ERROR: getServiceList";
	
	/*---*/
	public static function getServiceFromResource($data, $v1) {
		$v = json_decode($v1, true, 3);
		$v1 = $v["v1"];
		$v2 = $v["v2"];
		if ( $v2 === "-1" ) {
			$w = "";
		} else {
			$w = "AND (form='$v2')";
		}
		return "SELECT id, name, role_type, role_list_type FROM service WHERE ( (id_res='$data') AND (enabled='1') $w ) ORDER BY name";
	}
	const errgetServiceFromResource	=	"ERROR: getServiceFromResource";
	
	/*---*/
	public static function getRoleFromService($data, $v1) {
		if ( $data ==="a" ) {
			return "SELECT id, func AS name FROM function WHERE (visible='1') ORDER BY func";
		} else if ( $data === "b" ) {
			return "SELECT id, division AS name FROM division_osp WHERE (visible='1') ORDER BY division";
		} else
			return "SELECT id, name FROM role WHERE ((FIND_IN_SET('$data',id_srv)>0) AND (visible='1')) ORDER BY name";
	}
	const errgetRoleFromService	=	"ERROR: getRoleFromService";

    /*---*/
    public static function getRoleList($data, $v1) {
        return "SELECT name, id_ext1 FROM role WHERE visible='1'";
    }
    const errgetRoleList	=	"ERROR: getRoleList";
	
	/*---*/
	public static function getDivisionChiefName($data, $v1) {
		return "SELECT name AS chief FROM user WHERE (id='$data')";
	}
	const errgetDivisionChiefName	=	"ERROR: getDivisionChiefName";
	
	/*---*/
	public static function getHelpDocs($data, $v1) {
		return "SELECT * FROM helpdocs ORDER BY title";
	}
	const errgetHelpDocs	=	"ERROR: getHelpDocs";

	/*---*/
	public static function getMoveDocs($data, $v1) {
		return "SELECT * FROM movedocs ORDER BY title";
	}
	const errgetMoveDocs	=	"ERROR: getMoveDocs";
	
	/*---*/
	public static function getServiceOwner($data, $v1) {
		return "SELECT u.id, u.name, s.form AS form_org FROM user AS u, service AS s WHERE (u.id=(SELECT id_own FROM service WHERE id='$data') AND (s.id='$data'))";
	}
	const errgetServiceOwner	=	"ERROR: getServiceOwner";
	
	/*---*/
	public static function getServiceVizier($data, $v1) {
		return "SELECT u.id, u.name FROM user AS u, service AS s WHERE (u.id=(SELECT id_vise FROM service WHERE id='$data') AND (s.id='$data'))";
	}
	const errgetServiceVizier	=	"ERROR: getServiceVizier";
	
	/*---*/
	public static function getUserInfo($data, $v1) {
		return "SELECT name, company, division, function AS func, email, phone, tnumber, location FROM user WHERE id='$data' ORDER BY name";
	}
	const errgetUserInfo	=	"ERROR: getUserInfo";
	
	/*---*/
	public static function getUserNamedInfo($data, $v1) {
		return "SELECT u.name, c.company, d.division, f.func, u.email, u.phone FROM user AS u LEFT JOIN company AS c ON c.id=u.company LEFT JOIN division AS d ON d.id=u.division LEFT JOIN function AS f ON f.id=u.function WHERE u.id='$data'";
	}
	const errgetUserNamedInfo	=	"ERROR: getUserNamedInfo";
	
	/*---*/
	public static function newTicket($data, $v1) {
		$tickets = json_decode($data, true, 2, JSON_BIGINT_AS_STRING);
		$req = "INSERT INTO ticket (u_name, u_company, u_division, u_func, u_phone, u_tnumber, u_location, resource, service, role, switch0, start, vise_chief, vise_ess, vise_own, vise_vise, form_org, comment) VALUES ";
		$t = "('".self::mysql_safe($tickets['name'])."', '".self::mysql_safe($tickets['company'])."', '".self::mysql_safe($tickets['division'])."', '".self::mysql_safe($tickets['func'])."', '".self::mysql_safe($tickets['phone'])."', '".self::mysql_safe($tickets['tnumber'])."', '".self::mysql_safe($tickets['location'])."', '".self::mysql_safe($tickets['resource'])."', '".self::mysql_safe($tickets['service'])."', '".self::mysql_safe($tickets['role'])."', '".self::mysql_safe($tickets['switch0'])."', '".date('Y-m-d')."', '".self::mysql_safe($tickets['chief_name'])."', '".self::mysql_safe($tickets['ess_name'])."', '".self::mysql_safe($tickets['own_name'])."', '".self::mysql_safe($tickets['vise_name'])."', '".self::mysql_safe($tickets['form_org'])."', '".self::mysql_safe(htmlspecialchars($tickets['comment']))."')";
		$req = $req . $t;
		return $req;
	}
	const errnewTicket	=	"ERROR: errNewTicket";

    /*---*/
    public static function addUser($data, $v1) {
        $new = json_decode($data, true, 2, JSON_BIGINT_AS_STRING);
        return "INSERT INTO user (name, company, division, function, email, phone, location, tnumber) VALUES ('".self::mysql_safe($new['name'])."', '".$new['company']."', '".$new['division']."', '".$new['func']."', '".self::mysql_safe($new['email'])."', '".self::mysql_safe($new['phone'])."', '".self::mysql_safe($new['location'])."', '".self::mysql_safe($new['tnumber'])."')";
    }
    const erraddUser	=	"ERROR: addUser";
	
	/*---*/
	public static function getTicket($data, $v1) {
		return "SELECT t.id, t.u_name, c.company AS u_company, d.division AS u_division, f.func AS u_func, t.u_phone, t.resource, t.service, t.role, t.switch0, t.start, t.vise_ess, t.vise_own, t.vise_vise, t.form_org, t.comment FROM ticket AS t LEFT JOIN company AS c ON c.id=t.u_company LEFT JOIN division AS d ON d.id=t.u_division LEFT JOIN function AS f ON f.id=t.u_func LEFT JOIN division AS dd ON dd.id=t.vise_chief WHERE t.id='$data'";
	}
	const errgetTicket	=	"ERROR: getTicket";
	
	/*---*/
	public static function getTicketList($data, $v1) {
		return "SELECT t.id, t.u_name AS name, t.u_phone AS phone, c.company AS company, d.division, f.func, t.resource, t.service, t.start FROM ticket AS t LEFT JOIN company AS c ON c.id=t.u_company LEFT JOIN division AS d ON d.id=t.u_division LEFT JOIN function AS f ON f.id=t.u_func ORDER BY t.start DESC";
	}
	const errgetTicketList	=	"ERROR: getTicketList";
	
	/*---*/
	public static function getTicketRaw($data, $v1) {
		return "SELECT u_name AS name, u_phone AS phone FROM ticket WHERE id='$data'";
	}
	const errgetTicketRaw	=	"ERROR: getTicketRaw";
	
	/*---*/
	public static function isTicket($data, $v1) {
		return "SELECT COUNT(*) FROM ticket WHERE id='$data'";
	}
	const errisTicket	=	"ERROR: isTicket";

	/*---*/
	public static function getCatRWRoleList($data, $v1) {
		return "SELECT DISTINCT e.id_role AS id, r.name FROM ext1 AS e LEFT JOIN role AS r ON r.id=e.id_role";
	}
	const errgetCatRWRoleList	=	"ERROR: getCatRWRoleList";

	/*---*/
	public static function getCatRoleList($data, $v1) {
		return "SELECT DISTINCT id, name FROM ext1 ORDER BY name";
        //SELECT DISTINCT e.name, r.name AS role FROM ext1 AS e LEFT JOIN role AS r ON r.id=e.id_role ORDER BY e.name
	}
	const errgetCatRoleList	=	"ERROR: getCatRoleList";
	
	/*---*/
	public static function getResourceList($data, $v1) {
		if ( $data === "-1" ) {
			$w = "";
		} else {
			$w = "WHERE ( FIND_IN_SET('$data',form)>0 )";
		}
		return "SELECT * FROM resource $w ORDER BY name";
	}
	const	errgetResourceList = "ERROR: getResourceList";

    /*---*/
    public static function getResourceListAsName($data, $v1) {
        return "SELECT name, form FROM resource WHERE visible='1' ORDER BY name";
    }
    const	errgetResourceListAsName = "ERROR: getResourceListAsName";

    /*---*/
    /*public static function getCatOUOPListAsName($data, $v1) {
        return "SELECT name FROM ext1 ORDER BY name";
    }
    const	errgetCatOUOPListAsName = "ERROR: getCatOUOPListAsName";*/
	
	/*---*/
	private static function mysql_safe($data) {
		return mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]', '\\\0', $data );
	}
}
?>