<?php
session_start();
if(isset($_GET['op']) and $_GET['op']=='logout'){ session_destroy();unset($_SESSION);Header('Location:admin.php');}
if(!isset($_SESSION['sondeos_adm'])){
	if(isset($_POST['contrasena']) and $_POST['contrasena']=='hola')
		$_SESSION['sondeos_adm']=1;
	else{
		echo '
			<form method="post" style="
				text-align:center;
				margin:10px;			
			">
				<input name="usuario" autofocus>
				<input name="contrasena" type="password">
				<button>Acceder</button>
			</form>
		';
		exit();
	}
}
include "conn.php";
if(isset($_POST['n_participantes'])){
	$conn->query("
		UPDATE sondeos_ops set valor=".($_POST['n_participantes'])."
		  WHERE opcion='n_participantes';
	");
	$conn->query("TRUNCATE TABLE sondeos;");
}
$n_participantes=$conn->query("
	SELECT valor FROM sondeos_ops
	  WHERE opcion='n_participantes';	
")->fetch_row()[0];
$r=$conn->query("SELECT * FROM sondeos;")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0">
<style>
form{
	text-align:center;
	margin:10px;
}
img{
	vertical-align: middle;
}
</style>
</head>
<body>
<form method="post">
	<input name="n_participantes" autofocus value="<?=$n_participantes?>">
	<button>Modificar</button>
	<a href="admin.php"><img src="figs/refresh.png"></a>
	<a href="?op=logout"><img src="figs/exit.png"></a>
</form>
<pre>
<?php print_r($r);?>
</pre>
</body>
</html>