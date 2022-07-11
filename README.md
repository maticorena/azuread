# azuread
Easy connection on Azure Active Directory
#en el Archivo .env ubicado en la raiz de la libreria modificar con los siguientes datos
clientId =id del registro de tu aplicacion (objeto)
clientSecret = Clave secreta que te brinda el azure sino lo tienes deberás crearla
tenantId= aqui puedes poner common o el tennantId de tu objeto el common funciona para todos (sin comillas)
scope=El scope va con comillas como en el ejemplo debes poner los permisos que le daras a tu acceso'user.read offline_access' 
redirectUri=aqui va la url de redireccion del aplicativo que tiene que estar registrado en azure 
service=este es el servicio de login que vas a usar comunmente usa: https://login.microsoftonline.com/

ejemplo de conexion de prueba:

<?php
require_once realpath(__DIR__ . '/vendor/autoload.php');
use Maticorena\AzureAD\AzureAD;

if(isset($_GET["code"])){
    $ad=new AzureAD();
    $token=$ad->token($_GET["code"]);
    $ad->me($token->access_token);
    print_r($token);
    $token2=$ad->refreshToken($token->refresh_token);
    print_r($token2);
    $ad->me($token2->access_token);
    //sleep(10);
    //$ad->logout();
}else{
    $ad=new AzureAD();
    $ad->authorize();
}