<?php
if(isset($_GET['api_anonima'])){
	include "conn.php";
	echo json_encode($conn->query("
		SELECT IFNULL(ROUND((SUM(voto)/COUNT(voto)-1)/3*100),'-'),
		  ROUND(COUNT(voto)/(
			SELECT valor FROM sondeos_ops WHERE opcion='n_participantes'
		  )*100)
		  FROM sondeos;
	")->fetch_row());
	exit();
}
session_start();
if(isset($_GET['op']) and $_GET['op']=='refresh'){
	session_destroy();
	unset($_SESSION);
	Header('Location:.');
	exit();
}
include "conn.php";
if(!isset($_SESSION['id_sondeo'])){
	$_SESSION['instante']=date('Y-m-d H:i:s');
	$conn->query("INSERT INTO sondeos (instante) VALUES ('".$_SESSION['instante']."');");
	$_SESSION['id_sondeo']=$conn->insert_id;
}
else{
	if(!$conn->query("
		SELECT COUNT(*) FROM sondeos
		  WHERE id_sondeo=".($_SESSION['id_sondeo']*1)."
		  AND instante='".$_SESSION['instante']."';
	")->fetch_row()[0])
		Header('Location:?op=refresh');
}
if(isset($_GET['api'])){
	$r=$conn->query("
		UPDATE sondeos set voto=".($_GET['voto']*1)."
		  WHERE id_sondeo=".$_SESSION['id_sondeo'].";
	");
	echo json_encode([1]);
	exit();
}
else{
	$conn->query("
		UPDATE sondeos set voto=NULL
		  WHERE id_sondeo=".$_SESSION['id_sondeo'].";
	");
}
?>
<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0">
<script>
window.onload=inicio;
function inicio(){
	for(i=0;i<4;i++){
		let v = document.getElementById('v'+i);
		v.addEventListener('click', function(){
			var valor=v.getAttribute('valor');
			v.src='figs/ok.jpg';
			colorea(valor);
		});
	}
	var timer = setInterval(refreshing, 2e3);
}
async function refreshing(){
	let url = '?api_anonima=1';	
	let r = await (await fetch(url,{mode:"no-cors"})).json();
	let resultado=document.getElementById('resultado');
	let participacion=document.getElementById('participacion');
	resultado.innerHTML=r[0];
	participacion.innerHTML=r[1];
}
async function colorea(valor){
	for(i=0;i<valor;i++){
		let v = document.getElementById('v'+i);
		v.src='figs/ok.jpg';
	}
	for(i=valor;i<4;i++){
		let v = document.getElementById('v'+i);
		v.src='figs/ko.jpg';
	}
	let url = '?api=1&voto='+valor;	
	let ps = await (await fetch(url,{mode:"no-cors"})).json();
}
</script>
<style>
div{
	text-align:center;
}
img{
	width:70px;
}
#dislike{
	transform: rotate(180deg);
}
#resultado{
	font-size:3em;
}
.titulo{
	font-family:Tahoma;
	font-size:2em;
}
</style>
</head>
<body>
<div class="titulo"><span id="resultado">-</span>%</div>
<div>
<img src="figs/ko.jpg" id="v0" valor="1">
<img src="figs/ko.jpg" id="v1" valor="2">
<img src="figs/ko.jpg" id="v2" valor="3">
<img src="figs/ko.jpg" id="v3" valor="4">
</div>
<div><span id="participacion">-</span>% de participación</div>
<div>
<!--<?=$_SESSION['id_sondeo']?>-->
</div>
</body>
</html>