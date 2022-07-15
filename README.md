# azuread
***

* Nombre: AzureAD
* Autor: Gabriel Maticorena
* Creado: 11/07/2022
* Versión: 0.0.1
***

Easy connection on Azure Active Directory
# En el archivo .env ubicado en la raiz de la libreria modificar con los siguientes datos:

**clientId** =id del registro de tu aplicacion (objeto)  
**clientSecret** = Clave secreta que te brinda el azure sino lo tienes deberás crearla  
**tenantId**= aqui puedes poner common o el tennantId de tu objeto el common funciona para todos (sin comillas)  
**scope**=El scope va con comillas como en el ejemplo debes poner los permisos que le daras a tu acceso'user.read offline_access'  
**redirectUri**=aqui va la url de redireccion del aplicativo que tiene que estar registrado en azure  
**service**=este es el servicio de login que vas a usar comunmente usa: https://login.microsoftonline.com/  
**required**=campos requeridos cuando necesitas datos anteriores al login  
**lanzador**=pagina de redireccion cuando no tienes los datos requeridos  
**limitRefresh**=segundos de cuanto tiempo de inactividad puede soportar el refresh para mandarte a login  

## ejemplo de conexion de prueba:

``` 
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
