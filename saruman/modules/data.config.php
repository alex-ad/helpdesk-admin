<?php
	const CONFIG = Array(
		//"HOST"		=>	"http://localhost",
		"HOST"		=>	"http://helpdesk.loc",
		"ORG"		=>	"ДИТ ОСП «ВМК КМЗ» в г. Волгограде",
		"TITLE"		=>	"Единая заявка"
	);
	
	const PATH = Array(
		"PDF"		=>	"c:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe",
		//"PDF"		=>	"wkhtmltopdf",
		"TPL"		=>	"/docs/templates/",
		"TICKET"	=>	"/docs/tickets/",
		"HELP"		=>	"/docs/help/",
		"MOVE"		=>	"/docs/move/"
	);
	
	const SQL = Array(
		"HOST"		=>	"localhost",
		"BASE"		=>	"ts",
		"USER"		=>	"root",
		"PASSWORD"	=>	""
	);
	
	const LDAP = Array(
		"port"		=>	"",
		"domain"	=>	"",
		"basedn"	=>	"",
		"group"		=>	"",
		"password"	=>	"",
		"user"	    =>	""
	);
?>