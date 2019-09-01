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
		//"PASSWORD"	=>	"dm48hsw2"
	);
	
	const LDAP = Array(
		"port"		=>	"3268",
		"domain"	=>	"corp.vgtz.com",
		"basedn"	=>	"DC=corp,DC=vgtz,DC=com",
		"group"		=>	"VLG_IS_USER",
		"password"	=>	"is5493",
		"user"	=>	"0,k0vbcm"
	);
?>