<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html>
<head>
<title>�������������� ������</title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
	<style type='text/css' media='screen,projection'>

	    body { margin:120px auto;width:600px;padding:20px;border:1px solid #ccc; background:#fff;font-family:georgia,times,serif; font-size: 14px; min-height: 500px;}
	    span {font-size:1.2em; color: red; }
	    h1 {color: #a5d9ed; text-shadow: 0px 1px 0px grey;  text-align: center;font-size:20px;}
	    a {color: #4a423c; font-weight: bold;}
	    a:hover{color: #a5d9ed;}
	
	</style>
	

</head>
<body>
<h1>����������� ������� ������� �����������</h1>
	<ul>
	<li>���� ������ - <span><?php echo $this->vars['db_name']; ?></span></li>
	<li>���������� ������ - <?php echo $this->vars['tb_count']; ?></li>
	<li>���������� �������� - <?php echo $this->vars['pd_count']; ?></li>
	</ul>
	
	<div>���������� ������� ������� <span>installation</span>  ��������, ����� ��� ���� �� ����������</div><br/>
	<div>��� ��������� �������� ��������� �� <a href="<?php echo $this->vars['url']; ?>">������</a></div>

</body>
</html>