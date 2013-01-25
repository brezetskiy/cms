<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
<head>
<title>Инсталяционный скрипт</title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
	<style>
body, td {
 font-family: Tahoma, Verdana;
 font-size:12px;
}
	</style>
</head>
<body>

<?php if(!empty($this->vars['error_message'])): ?>
	<div style="color:red;font-weight:bold;"><?php echo $this->vars['error_message']; ?></div>
<?php endif; ?>

<?php if(!empty($this->vars['ok_message'])): ?>
	<div style="color:green;font-weight:bold;"><?php echo $this->vars['ok_message']; ?></div>
<?php endif; ?>

<form method="POST" action="/install/index.php">
<table border="0" cellspacing="5" width="100%">
<tr>
	<td></td>
	<td><h2>Настройки</h2></td>
</tr>
<tr>
	<td width="10%" style="text-align:right; padding-right:10px;">Хост БД:</td>
	<td><input type="text" name="db_host" value="<?php echo $this->vars['db_host']; ?>" style="width:300px;"></td>
</tr>
<tr>
	<td width="10%" style="text-align:right; padding-right:10px;">Имя БД:</td>
	<td><input type="text" name="db_name" value="<?php echo $this->vars['db_name']; ?>" style="width:300px;"></td>
</tr>
<tr>
	<td valign="top" width="10%" style="text-align:right; padding-right:10px;">Логин к БД:</td>
	<td>
		<input type="text" name="db_login" value="<?php echo $this->vars['db_login']; ?>" style="width:300px;"><br>
		<span style="color:gray;font-size:11px;">Если логин совпадает с именем БД, то поле "Логин к БД" можно оставить пустым.</span>
	</td>
</tr>
<tr>
	<td width="10%" style="text-align:right; padding-right:10px;">Пароль к БД:</td>
	<td><input type="password" name="db_password" value="<?php echo $this->vars['db_password']; ?>" style="width:300px;"></td>
</tr>
<tr>
	<td width="10%"></td>
	<td><input type="submit" value="Сохранить"></td>
</tr>
</table>


</form>
</body>
</html>