<?php
session_start();
require_once("modules/functions.php");
require_once("modules/auth_users.php");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Единая заявка | Администрирование: Авторизация</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="style/bootstrap.min.css">
	<link href="style/style-f.css" rel="stylesheet">
	<script src="js/jquery-1.12.4.min.js"></script>
</head>
<body>
	<div id="page-auth">
		<div id="wrapper"><?php
			$user_current = "";
			$user_login = "";
			$user_name = "";
			if (isset($_POST["user"]) && isset($_POST["password"])) {
				$user = $_POST["user"];
				$password = $_POST["password"];
				$port = "3268";
				$domain = "corp.vgtz.com";
				$basedn = "DC=corp,DC=vgtz,DC=com";
				$group = "VLG_IS_USER";
				if (($ad = ldap_connect($domain, $port)) !== false ) {
					ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);
					if (ldap_bind($ad, "{$user}@{$domain}", $password)) {
						$userdn = getDN($ad, $user, $basedn);
						if (checkGroup($ad, $userdn, getDN($ad, $group, $basedn))) {
							$_SESSION["user_login"] = $user;
							if ( isset($_COOKIE["acr"]) )
							    $_SESSION["acr"] = $_COOKIE["acr"];
							setcookie("user_login", $_POST["user"], time()+3600*24*30);
							if ( isset($_SESSION['uri']) ) {
								if ( strlen($_SESSION['uri']) > 4 ) {
                                    header("Location: ".$_SESSION['uri']);
                                    exit;
                                }
								else {
                                    header("Location: index.php");
                                    exit;
                                }
							} else {
                                header("Location: index.php");
                                exit;
                            }
						} else { ?>
							<div class="alert alert-warning center" role="alert" style="margin:15px 15px 15px 15px;"><h4>К сожалению, Вам доступ закрыт</h4><a href="/saruman/auth.php" class="alert-link">Вернуться назад</a></div>
						<?php }
						ldap_unbind($ad);
					} else {?>
						<div class="alert alert-danger center" role="alert" style="margin:15px 15px 15px 15px;"><h4>Пара "имя-пароль" не распознана</h4><a href="/saruman/auth.php" class="alert-link">Вернуться назад</a></div>
					<?php }
				} else { ?>
					<div class="alert alert-warning center" role="alert" style="margin:15px 15px 15px 15px;"><h4>Ошибка подключения к AD</h4><a href="/saruman/auth.php" class="alert-link">Вернуться назад</a></div>
				<?php }
			} else {
				if ( isset($_COOKIE['user_login']) ) {
					$user_login = $_COOKIE['user_login'];
				}?>
				<div class="panel panel-primary center" style="width: 500px; margin-top: 100px;">
					<div class="panel-heading bold">Авторизация пользователя</div>
					<div class="panel-body"><div class="panel-auth">
						<form action="" method="post" id="form-auth" name="form-auth">
							<div class="form-item">
								<div class="label" style="width:60px">Имя: </div>
								<select class="user_auth" style="width:407px; height:22px" size="1" name="user" onchange="changeUser()">
									<option value="0" disabled>Выберите свое имя из списка</option><?php
                                        $authUsers = new authUsers();
                                        $u = $authUsers->getAdmin();
                                        if ( $u )  {
                                            $u = json_decode($u, true, 4);
                                            for ( $i=0; $i<sizeOf($u); $i++ ) {
                                                echo '<option value="' . $u[$i]['login'] . '" class="'.$u[$i]['acr'].'">' . $u[$i]['name'] . '</option>';
                                                if ( strToLower($u[$i]['login']) === strToLower($user_login) )
                                                    $user_current = $u[$i]['login'];
                                            }
                                            if ( strlen($user_current) > 1 ) { ?>
                                                <script type="text/javascript">
                                                    $("select").val("<?php echo $user_current; ?>");
                                                    document.cookie = 'acr='+($('.user_auth option:selected').attr('class'));
                                                </script>
                                            <?php }
                                        }
									?>
                                    <script type="text/javascript">
                                        function changeUser() {
                                            document.cookie = 'acr='+($('.user_auth option:selected').attr('class'));
                                            console.log($('.user_auth option:selected').attr('class'));
                                        }
                                    </script>
								</select>
								<div class="hidden" id="auth_ac"/></div>
							</div>
							<div class="clr"></div>
							<div class="form-item"><div class="label" style="width:60px">Пароль: </div><input style="width:407px; height:21px;" type="password" name="password" required autofocus/></div><br />
							<button type="submit" class="btn btn-primary">Войти</button>
						</form>
					</div></div>
					<div class="alert alert-info" role="alert" style="margin:0 15px 15px 15px;">Здесь нужно вводить пароль Вашей учетной записи,<br />т.е. тот пароль, который вводится при включении компьютера.</div>
				</div>
			<?php }?>
		</div>
	</div>
</body>
</html>