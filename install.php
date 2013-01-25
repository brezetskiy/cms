<!DOCTYPE XHTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Настройка сайта</title>
<meta http-equiv="Content-Type" content="text/html" charset="windows-1251" />

	<style type='text/css' media='screen,projection'>

	    body { margin:20px auto;width:600px;padding:20px;border:1px solid #ccc; background:#fff;font-family:georgia,times,serif; font-size: 14px; min-height: 500px;}
	    
	    input,textarea { width:250px;font:12px/12px 'courier new',courier,monospace;color:#333;padding:3px; border:1px solid #ccc; margin-left: 10px;}
	    input.submit { padding:7px; margin-top:20px; width:100px; color: #000; background-color: #a5d9ed;} 

	    td {margin-left: 10px; color:#4a423c;}
	    th {background: #ccc; padding:5px; color: #000; text-shadow:0px 1px 1px #fff;}
	    td, th {font-size: 14px;}

	    span {font-size:0.9em; color: #c5c5c5; margin-left: 10px; }
	    h2 {color: #a5d9ed; text-shadow: 0px 1px 0px grey;  text-align: center;}

	    h3 {color: red; text-shadow: 0px 1px 0px grey;}
	    div {text-align: left; color:#4a423c; font-size: 14px;}
	    div.describe { border: 1px solid #a5d9ed; text-align: left; font-size:11px; margin:10px;}
	    div.warning { margin:10px; padding: 10px; background: #e9f876; border:1px solid #ccc; font-size:12px;}
	    a {color: #4a423c; font-weight: bold;}
	    a:hover{color: #a5d9ed;}
	
	</style>
	<script language="JavaScript">
	    function checkForm(){
			for (i=0; i<document.forms.length; i++) {
				if ( document.forms[0].elements[i].value == '')
				{
				document.getElementById("error").innerHTML="Все поля обязательны для заполнения!";
				return false;
				}
			}
			reg=/^[a-z0-9\-\_\.\+]+@[a-z0-9\-\_\.]+\.[a-z]{2,4}$/;
			str = document.getElementById("email").value;
			if ( !reg.test(str) )
			{ 
				document.getElementById("error").innerHTML="Логин не соответствует требованиям";
				return false;
			}
	    }
	</script>
</head>
<body>
<?php 

		$file_writeble = array('config'=>'system/config.inc.php', 'dbtable'=>'database.sql', 'dbtable_procedure'=>'database_procedure.sql');
		
		$post['password']   = (isset($_POST['password'])) ? $_POST['password'] : '';
		$post['email']  	= (isset($_POST['email'])) ? $_POST['email'] : '';
		
		$post['dbhost'] = (isset($_POST['dbhost'])) ? $_POST['dbhost'] : '';
		$post['dbname'] = (isset($_POST['dbname'])) ? $_POST['dbname'] : '';
		$post['dbpass'] = (isset($_POST['dbpass'])) ? $_POST['dbpass'] : '';
		$post['dbuser'] = (isset($_POST['dbuser'])) ? $_POST['dbuser'] : '';
		
		$flag_ok = false;
	if (isset($_POST['install_submit'])) { 
	
		foreach($post as $value){
			if(empty($value) || $value==''){
				$error[] = "Все поля обязательны для заполнения!";
			}
		}
		$preg_email = '/^[a-z0-9\-\_\.\+]+@[a-z0-9\-\_\.]+\.[a-z]{2,4}$/is';
		if(!preg_match($preg_email, $post['email'])){
			$error[] = "Логин не соответствует требованиям!";
		}
		
		foreach($file_writeble as $value){
			if (!is_writable($value)) {
				$error[] = "Файл $value не доступен на запись";
			}
		}
		
	
		if (count($error) == 0 ) 
		{
	     
		  $copy_from = array('{dbhost}', '{dbname}', '{dbuser}', '{dbpass}', '{user}');
	      $copy_to = array($post['dbhost'], $post['dbname'], $post['dbuser'], $post['dbpass'], $post['email']);

		  // запись данных в system/config.inc.php
	      if(!write($copy_from, $copy_to, $file_writeble['config'])){
				$error[] = 'Не удалось записать данные в файл '.$file_writeble['config'];
		  }
		 
		  // запись данных в дамп базы
		  $copy_from = array('{dbname}', '{user}', '{pass}');
		  $copy_to = array($post['dbname'], $post['email'], md5($post['password']));
		  if(!write($copy_from, $copy_to, $file_writeble['dbtable'])){
				$error[] = 'Не удалось записать данные в файл '.$file_writeble['dbtable'];
		  }			  

		  if(!write($copy_from, $copy_to, $file_writeble['dbtable_procedure'])){
				$error[] = 'Не удалось записать данные в файл '.$file_writeble['dbtable_procedure'];
		  }
		  
		  if(count($error) == 0){
				define('CMS_INTERFACE', 'SITE');
				
				require_once($file_writeble['config']);
				
				/**
				 * Session start
				 */
				if (!isset($_SESSION)) {
					session_start();
				}
				/**
				 * Connect to database
				*/
				$DB = DB::factory('default');

				mysql_import_file($file_writeble['dbtable'], $error);
				mysql_import_file_procedure($file_writeble['dbtable_procedure'], $error);
				 
				Header('Location: /');
				//echo '<div><h2>Поздравляем!! </h2>Сайт удачно установлен.</div>';
				//$flag_ok = true;
		  }
		 
      }
	
 } 
 
 if(!$flag_ok){
 ?>
 <h2>Настройка сайта</h2>
	<div class="describe">
		Предварительные действия: 
		<ol>
		    <li>Создайте пустую базу данных </li>		    
		</ol>
	</div><span style="color:red" id="error">
		<?php 
			if(count($error) > 0){
				echo '<ol>';
				foreach ($error as $val) {
					echo '<li>'.$val.'</li>';
				}
				echo '</ol>';
			}
		?>
	</span>
	<table width="100%">
	    
	    <form action="install.php" method="post" onsubmit="return checkForm();">
		<tr><th colspan="2" align="left">
		      База данных
		</th></tr>
		<tr>
		      <td colspan="2"></td>
		</tr>

		<tr>
		      <td align="right">Хост БД</td><td><input type="text" name="dbhost" value="<?php echo $post['dbhost']; ?>"/></td>
		</tr>
		<tr>
		      <td align="right">Имя БД </td><td><input type="text" name="dbname" value="<?php echo $post['dbname']; ?>"/><br /><span>(БД уже должна существовать)<span></td>
		</tr>
		<tr>
		      <td align="right">Логин к БД</td><td><input type="text" name="dbuser" value="<?php echo $post['dbuser']; ?>"/></td>

		</tr>
		<tr>
		    <td align="right">Пароль к БД</td><td><input type="password" name="dbpass" value="<?php echo $post['dbpass']; ?>"/></td>
		</tr>
				<tr><th colspan="2" align="left">
		      Админ
		</th></tr>
		<tr>
		      <td align="right">Логин (почтовый ящик)</td><td><input id="email" type="text" name="email" value="<?php echo $post['email']; ?>"/></td>
		</tr>
		<tr>
		      <td align="right">Пароль</td><td><input type="password" name="password" value="<?php echo $post['password']; ?>"/></td>
		</tr>		
		<tr>
		      <td colspan="2" align="center"><input type="submit" name="install_submit" class="submit" value="Сохранить" /></td>
		</tr>

	    </form>
	</table>
	<?php


}?>	
		
</body>
</html>

<?php
function write($from, $to, $source) {
	$config_data = fread(fopen($source, 'r'), filesize($source));
	$config_data = str_replace($from, $to, $config_data);
	$fp = fopen($source,"w");
	if(fwrite($fp,$config_data) === FALSE){
		fclose($fp);
		return false;		
	}
	fclose($fp);
	return true;
}


function mysql_import_file($filename, &$error) 
{ 
	global $DB;
   /* Read the file */ 
   $lines = file($filename); 

   if(!$lines) 
   { 
      $error[] = "cannot open file $filename"; 
      return false; 
   } 

   $scriptfile = false; 

   /* Get rid of the comments and form one jumbo line */ 
   foreach($lines as $line) 
   { 
      $line = trim($line); 

      if(!ereg('^--', $line)) 
      { 
         $scriptfile.=" ".$line; 
      } 
   } 

   if(!$scriptfile) 
   { 
      $error[] = "no text found in $filename"; 
      return false; 
   } 

   /* Split the jumbo line into smaller lines */ 

   $queries = explode('_;_', $scriptfile); 

   /* Run each line as a query */ 

   $part_query = '';
   
   $trigger = false;
   foreach($queries as $query) 
   { 
      $query = trim($query); 
	  if($query == "") { continue; }

	  
	  if (!preg_match("/DELIMITER/", $query) && !$trigger) 
	  {		
			$DB->query($query);
			
	  }
	  else if(preg_match("/DELIMITER/", $query)) {
			
			$part = explode('/', $query);			  
			if(count($part) == 1)
				continue;
			  
			if($trigger){
				$part_query .= ' '.array_shift($part);
				//echo '<pre>'; print_r($part_query); echo '</pre><br/><br/>';
				$DB->query($part_query);
				$part_query = '';
				$trigger = false;
			}
			else {				
				$part_query = array_pop($part).'; ';
				$trigger = true;
			}
			
	  } else {
	
			$part_query .= $query.'; ';
	  }

       
      //$DB->query($query);
   } 

   /* All is well */ 
   return true; 
} 

function mysql_import_file_procedure($filename, &$error) 
{ 
	global $DB;
   /* Read the file */ 
   $lines = file($filename); 

   if(!$lines) 
   { 
      $error[] = "cannot open file $filename"; 
      return false; 
   } 

   $scriptfile = false; 

   /* Get rid of the comments and form one jumbo line */ 
   foreach($lines as $line) 
   { 
      $line = trim($line); 

      if(!ereg('^--', $line)) 
      { 
         $scriptfile.=" ".$line; 
      } 
   } 

   if(!$scriptfile) 
   { 
      $error[] = "no text found in $filename"; 
      return false; 
   } 

      /* Split the jumbo line into smaller lines */ 

   $queries = explode('$$', $scriptfile); 

   /* Run each line as a query */ 

   $part_query = '';
   
   foreach($queries as $query) 
   { 
		$query = trim($query); 
		if(preg_match("/DELIMITER/", $query) || $query == '') { continue; }
		//echo '<pre>'; print_r($query); echo '</pre><br/><br/>';
		
       
		$DB->query($query);
   }
   /* All is well */ 
   return true; 
} 


?>