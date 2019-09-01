<?php
require_once("data.db.php");
require_once("functions.php");

$data = json_decode(file_get_contents("php://input"), true, 4);
$fncSU = new fncSU();
$fncSU->adminRequest($data);

class fncSU {

    // db connecting
    private function db_connect() {
        $mysqli_ = new mysqli(db::host, db::user, db::password, db::base);

        if ( $mysqli_->connect_error ) {
            return false;
        }
        $mysqli_->set_charset("utf8");
        return $mysqli_;
    }

    // get admins
    private function getAdmin() {
        if ( $mysqli = $this->db_connect() ) {
            $a = $mysqli->query("SELECT login, acr FROM su");
            for ( $su=Array(); $row=$a->fetch_assoc(); $su[]=$row );
            $port = "3268";
            $domain = "corp.vgtz.com";
            $basedn = "DC=corp,DC=vgtz,DC=com";
            $group = "VLG_IS_USER";
            $user = "is5493";
            $password = "0,k0vbcm";
            $admin = array();
            if (($ad = ldap_connect("ldap://".$domain, $port)) !== false ) {
                ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
                if (@ldap_bind($ad, "{$user}@{$domain}", $password)) {
                    $userdn = getDN($ad, $user, $basedn);
                    if (checkGroup($ad, $userdn, getDN($ad, $group, $basedn))) {
                        //$search_filter = "(&(l=Волгоград)(memberOf=CN=VLG_IS_USER,OU=group tech,OU=Volgograd,DC=corp,DC=vgtz,DC=com))";
                        $search_filter = "(&(l=Волгоград)(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
                        $attributes = Array();
                        $attributes[] = "cn";
                        $attributes[] = "dn";
                        $attributes[] = "samaccountname";
                        $attributes[] = "displayname";
                        $result = ldap_search($ad, $basedn, $search_filter, $attributes);
                        $entries = ldap_get_entries($ad, $result);
                        for ( $x=0; $x<$entries["count"]; $x++ ) {
                            $obj = $entries[$x]["displayname"][0];
                            $nm = $this->correctName($obj);
                            if (!preg_match('/([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})/u', $nm)) continue;
                            if ( strtolower(substr($entries[$x]["samaccountname"][0], 0, 2)) !== "is" ) continue;
                            for ( $i=0; $i<sizeOf($su); $i++ ) {
                                $acr = "0";
                                if ( strtolower($su[$i]["login"]) === strtolower($entries[$x]["samaccountname"][0]) ) {
                                    $acr = $su[$i]["acr"];
                                    break;
                                }
                            }
                            $admin[] = array(
                                "id" => strtolower($entries[$x]["samaccountname"][0]),
                                "name" => $nm,
                                "login" => strtolower($entries[$x]["samaccountname"][0]),
                                "acr" => $acr
                            );
                        }
                    }
                    ldap_unbind($ad);
                }
            }
            sort($admin);
            $mysqli->close();
            return $admin;
        } else {
            return json_encode($mysqli->connect_error);
        }
    }

    private function correctName($nm) {
        $full_name = trim(preg_replace('/\s+/', ' ', $nm));
        $full_name = preg_replace_callback('/([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})+\s+([А-Яа-яёЁ]){1}+([А-Яа-яёЁ]{2,})/u', "convert1", $full_name);
        $full_name = str_replace("ё", "е", $full_name);
        $full_name = str_replace("Ё", "Е", $full_name);
        return $full_name;
    }

    // set admins
    private function setAdmin($data) {
        if ( $mysqli = $this->db_connect() ) {
            $acr = $data["write"]["acr"];
            $sam = strtolower($data["write"]["id"]);
            $c = $mysqli->query("SELECT COUNT(*) FROM su WHERE login='$sam'");
            for ($cnt = Array(); $row = $c->fetch_assoc(); $cnt[] = $row);
            if ( $cnt[0]["COUNT(*)"] === "0" ) {
                $ins = $mysqli->query("INSERT INTO su (login, acr) VALUES ('$sam', '$acr')");
                if (!$ins) echo json_encode($mysqli->error);
            } else {
                $upd = $mysqli->query("UPDATE su SET acr='$acr' WHERE login='$sam'");
                if (!$upd) echo json_encode($mysqli->error);
            }
        } else {
            echo json_encode($mysqli->error);
        }
    }

    // ajax request
    public function adminRequest($data=array()) {
        if ( (sizeOf($data) > 0) and (isset($data["write"])) ) $this->setAdmin($data);
        else echo json_encode($this->getAdmin());
    }
}

function convert1($p) {
    return mb_strtoupper($p[1]).mb_strtolower($p[2])." ".mb_strtoupper($p[3]).mb_strtolower($p[4])." ".mb_strtoupper($p[5]).mb_strtolower($p[6]);
}
?>