<?php
session_start();
if (!isset($_SESSION['user_login'])) {
	if ( isset($_SERVER["REQUEST_URI"]) )
		$_SESSION['uri'] = $_SERVER["REQUEST_URI"];
    header("Location: auth.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Единая заявка | Администрирование</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<link rel="stylesheet" href="dist/css/AdminLTE.min.css">
	<link rel="stylesheet" href="dist/css/skins/skin-blue.min.css">
	<link href="extjs/build/classic/theme-classic/resources/theme-classic-all.css" rel="stylesheet" />
	<link href="extjs/build/packages/font-pictos/resources/font-pictos-all.css" rel="stylesheet" />
	<link href="style/style-b.css" rel="stylesheet">
	<script type="text/javascript" src="extjs/build/ext-all.js"></script>
	<script type="text/javascript" src="extjs/build/classic/locale/locale-ru.js"></script>
	<script type="text/javascript" src="js/locale-ru.custom.js"></script>
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
	<div class="wrapper">

		<!-- Main Header -->
		<header class="main-header">
			<!-- Logo -->
			<a href="/saruman/" class="logo">
				<span class="logo-mini fa fa-gear"></span>
				<span class="logo-lg"><b>HelpDesk</b></span>
			</a>

			<!-- Header Navbar -->
			<nav class="navbar navbar-static-top" role="navigation">
				<!-- Sidebar toggle button-->
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>
				<!-- Navbar Right Menu -->
				<div class="navbar-custom-menu">
					<ul class="nav navbar-nav">
						<li>
							<a href="" data-toggle="control-sidebar">ДИТ ОСП «ВМК КМЗ» в г. Волгограде</a>
						</li>
					</ul>
				</div>
			</nav>
		</header>
     
		<!-- Left side column. contains the logo and sidebar -->
		<aside class="main-sidebar">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<!-- Sidebar Menu -->
				<ul class="sidebar-menu">
					<li class="header">База данных</li>
					<li id="db-user-item" class="active"><a href="pages/user.php" class="db-item" id="db-user"><i class="fa fa-user"></i> <span>Пользователи</span></a></li>
					<li id="db-ticket-item" class=""><a href="pages/ticket.php" class="db-item" id="db-ticket"><i class="fa fa-ticket"></i> <span>Заявки</span></a></li>
					<li id="db-staff-item" class=""><a href="pages/stafftree.php" class="db-item" id="db-staff"><i class="fa fa-suitcase"></i> <span>Штатная структура</span></a></li>
					<li id="db-tree-item" class=""><a href="pages/restree.php" class="db-item" id="db-tree"><i class="fa fa-chain"></i> <span>ИТ-ресурсы</span></a></li>
                    <li id="db-admin-item" class=""><a href="pages/admin.php" class="db-item" id="db-tree"><i class="fa fa-unlock"></i> <span>Администраторы</span></a></li>
				</ul>
			</section>
		</aside>

		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<!-- Content Header (Page header) -->
			<section class="content-header">
				<h1>Пользователи<small>Список пользователей: просмотр, изменение, добавление, удаление</small></h1>
			</section>

			<!-- Main content -->
			<section class="content">
				<div id="grid"></div>
				<div id="logs"></div>
			</section>
		</div>

		<!-- Main Footer -->
		<footer class="main-footer">
			<div class="pull-right hidden-xs">
				/ Вся информация защищена страшными бумажками /
			</div>
			<strong>&copy; 2017 <a href="/">ДИТ ОСП «ВМК КМЗ» в г. Волгограде</a> ::: </strong> Только для внутреннего пользования.
		</footer>
	</div>
    
	<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="dist/js/app.min.js"></script>
	<script type="text/javascript" src="js/ExcelExportGrid.js"></script>
	<script src="js/getuser.js?ver=1"></script>
</body>
</html>
