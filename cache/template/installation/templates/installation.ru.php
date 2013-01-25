<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
<head>
<title>�������������� ������</title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
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
			
			for (i=0; i<document.forms[0].length; i++) {
				if ( document.forms[0].elements[i].value == '')
				{
				document.getElementById("error").innerHTML="��� ���� ����������� ��� ����������!";
				return false;
				}
			}
			reg=/^[a-z0-9\-\_\.\+]+@[a-z0-9\-\_\.]+\.[a-z]{2,4}$/;
			str = document.getElementById("email").value;
			if ( !reg.test(str) )
			{ 
				document.getElementById("error").innerHTML="����� �� ������������� �����������";
				return false;
			}
	    }
	</script>
	
</head>
<body>
<h2><?php echo $this->vars['title']; ?></h2>

	<?php if($this->vars['ok_message']): ?>
	<div class="ok_message">����������, ��������� ������� ������� installation, ����� ��� ���� �� ����������</div>
	<?php else: ?>
	<div class="describe">
		��������������� ��������: 
		<ol>
		    <li>�������� ������ ���� ������ </li>		    
		</ol>
	</div><span style="color:red" id="error">
		
			<ol>
		<?php
			reset($this->vars['/error/'][$__key]);
			while(list($_error_key,) = each($this->vars['/error/'][$__key])):
			?>
			<li><?php echo $this->vars['/error/'][$__key][$_error_key]['message']; ?></li>
		<?php 
			endwhile;
			?>
			</ol>
		
	</span>
	   <form action="/installation/index.php" method="post" onsubmit="return checkForm();">
	<table width="100%">	    	 
		<tr><th colspan="2" align="left">
		      ���� ������
		</th></tr>
		<tr>
		      <td colspan="2"></td>
		</tr>

		<tr>
		      <td align="right">���� ��</td><td><input type="text" name="dbhost" value="<?php echo $this->vars['dbhost']; ?>"/></td>
		</tr>
		<tr>
		      <td align="right">��� �� </td><td><input type="text" name="dbname" value="<?php echo $this->vars['dbname']; ?>"/><br /><span>(�� ��� ������ ������������)<span></td>
		</tr>
		<tr>
		      <td align="right">����� � ��</td><td><input type="text" name="dbuser" value="<?php echo $this->vars['dbuser']; ?>"/></td>

		</tr>
		<tr>
		    <td align="right">������ � ��</td><td><input type="password" name="dbpass" value="<?php echo $this->vars['dbpass']; ?>"/></td>
		</tr>
				<tr><th colspan="2" align="left">
		      �����
		</th></tr>
		<tr>
		      <td align="right">����� (�������� ����)</td><td><input id="email" type="text" name="email" value="<?php echo $this->vars['email']; ?>"/></td>
		</tr>
		<tr>
		      <td align="right">������</td><td><input type="password" name="password" value="<?php echo $this->vars['password']; ?>"/></td>
		</tr>		
		<tr>
		      <td colspan="2" align="center"><input type="submit" name="install_submit" class="submit" value="���������" /></td>
		</tr>

	    </form>
	</table>		
	<?php endif; ?>
</body>
</html>