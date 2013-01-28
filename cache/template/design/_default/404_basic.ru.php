<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
<html>
<head>
<title>Запрошенная страница не найдена</title>
<style type="text/css">
html,body {
	height: 100%;
}
body {
	margin:0;
	padding:0;
	font-size: 16px;
	font-family: arial, tahoma, verdana;
}
div.container {
	width: 700px;
	margin: 200px auto 0 auto;
	color:#6f6f6f;
}
div.block {
	background-color:#489ad2;
	color:#fff;
	padding:10px 35px;
	font-size:36px;
	font-weight:bold;
	
}
h1 {
	margin:0px;
	color:#000;
}
h3 {
	margin-top:0px;
}
</style>
</head>
<body>

<script>
Cd = document;
Cd.write("<img src='/tools/stat/error.php?code=404&r="+escape(Cd.referrer)+"&u="+escape(window.location.href)+"' border='0' wi"+"dth='1' he"+"ight='1'>");
</script>
<noscript>
<img width="1" height="1" src="/tools/stat/error.php?code=404">
</noscript>

<div class="container">

<table cellspacing="10">
	<tr>
		<td valign="top">
			<div class="block">
				404
			</div>
		</td>
		<td valign="top">
			<h1>Упс! Нет такой страницы</h1>
			Запрошенная страница не существует. Мы уже знаем об этом
			и работаем над устранением проблемы.<br><br>
		</td>
	</tr>
</table>



<!--<script type="text/javascript">
	var GOOG_FIXURL_LANG = 'ru';
	var GOOG_FIXURL_SITE = 'http://<?php echo HTTP_HOST; ?>/';
</script>
<script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>

Можете продолжить поиск с <a href="/">главной страницы</a>

--></div>

</body>
</html>