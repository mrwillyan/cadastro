<?php

error_reporting(0);

require_once("../includes/conexao.php");
require_once("../includes/manage.php");
$chave = md5(uniqid());

$usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
$senha = mysqli_real_escape_string($conexao, $_POST['senha']);
$valor = mysqli_real_escape_string($conexao, $_POST['valor']);

if(empty($usuario) or empty($senha) or empty($valor)){
$json = ["success" => false, "message" => "Preencha todos os campos"];
die(json_encode($json));
}

$data = array (
"secret" => "6Lc1ptkZAAAAACD8Mqo-t5EOljeCMoNuqahf2P0c",
"response" => $_POST["g-recaptcha-response"],
"remoteip" => $_SERVER["REMOTE_ADDR"]
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$api = json_decode(curl_exec($curl), true);

if($api["success"] == false){
$json = ["success" => false, "message" => "Recaptcha Incorreto"];
echo json_encode($json);
exit();
}

$sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
$result = mysqli_query($conexao, $sql);

if(mysqli_num_rows($result) >= 1) {
$json = array("success" => false, "message" => "Usuario Ja Cadastrado!");
echo json_encode($json);
mysqli_close($conexao);
exit();
}

if($valor < 10 || !ctype_digit($valor)){
$json = ["success" => false, "message" => "Valor Invalido"];
echo json_encode($json);
exit();
}

$ch = curl_init();
curl_setopt_array($ch, array(
CURLOPT_URL => "https://api.mercadopago.com/checkout/preferences?access_token=$access_token",
CURLOPT_RETURNTRANSFER => 1,
CURLOPT_HTTPHEADER => array(
'content-type:application/json'),
CURLOPT_POSTFIELDS => '{"items":[{"title":"ACESSO SECCENTER","currency_id":"BRL","quantity":1,"unit_price":'.$valor.'}],"back_urls":{"success":"'.$host.'/pagamentos/","failure":"'.$host.'/pagamentos/","pending":"'.$host.'/pagamentos/"},"auto_return":"approved","payment_methods":{"excluded_payment_types":[{"id":"credit_card"}],"installments":1},"notification_url":"'.$host.'/pagamentos/notificacao.php","external_reference":"'.$chave.'"}'));
$request = curl_exec($ch);
    
$dados = json_decode($request, true);

if(isset($dados["init_point"])){

$cad = mysqli_query($conexao, "INSERT INTO usuarios (usuario, senha, saldo, chave, nivel)
VALUES ('$usuario', md5('$senha'), '0', '$chave', '0');");

if(mysqli_affected_rows($conexao) > 0) {
$json = array("success" => true, "message" => "Usuario cadastrado!", "init_point" => $dados["init_point"]);
echo json_encode($json);
mysqli_close($conexao);
exit();
}

}else{
$json = ["success" => false, "message" => "Falha Interna"];
echo json_encode($json);
exit();
}

?>