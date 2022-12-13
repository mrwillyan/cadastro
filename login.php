<?php

error_reporting(0);
require_once("../includes/conexao.php");
header("content-type: application/json");

if(empty($_POST['usuario']) or empty($_POST['senha'])) {
$json = ["success" => false, "message" => "Preencha Todos os Campos"];
die(json_encode($json));
}




$usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
$senha = mysqli_real_escape_string($conexao, $_POST['senha']);

$query = "SELECT * FROM usuarios WHERE usuario = '$usuario' AND senha = md5('$senha')";

$result = mysqli_query($conexao, $query);

if(mysqli_num_rows($result) > 0) {

$auth_token = md5(uniqid());

$array = mysqli_fetch_assoc($result);

$chave = $array["chave"];

mysqli_query($conexao, "UPDATE usuarios SET auth_token = '$auth_token' WHERE chave = '$chave'");

session_start();
$_SESSION["logado"] = true;
$_SESSION["auth_token"] = $auth_token;
$_SESSION["saldo"] = $array["saldo"];
$_SESSION["lives"] = $array["lives"];
$_SESSION["nivel"] = $array["nivel"];
$_SESSION["chave"] = $array["chave"];
$_SESSION["usuario"] = $array["usuario"];

mysqli_close($conexao);

$json = ["success" => true, "message" => "Usuario Logado"];
die(json_encode($json));

}else{

$json = ["success" => false, "message" => "Usuario ou Senha Invalidos"];
die(json_encode($json));

}


?>