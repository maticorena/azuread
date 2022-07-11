<?php
session_start();
namespace Maticorena\AzureAD;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Dotenv;

class AzureAD {

  public function __construct(){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['clientId', 'clientSecret', 'tenantId', 'scope','service','required','lanzador']);
  }

  public function authorize(){
    $url = $_ENV['service'] . $_ENV['tenantId'] . "/oauth2/v2.0/authorize?";
    $url .= "state=" . session_id();
    $url .= "&scope=".$_ENV['scope'];
    $url .= "&response_type=code";
    $url .= "&approval_prompt=auto";
    $url .= "&client_id=" . $_ENV['clientId'];
    $url .= "&redirect_uri=" . $_ENV['redirectUri'];
    header("Location: " . $url);
  }

  public function token($code){
    $guzzle = new \GuzzleHttp\Client();
    $url = $_ENV['service']. $_ENV['tenantId'] . '/oauth2/v2.0/token';
    $token = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $_ENV['clientId'],
            'scope'=> $_ENV['scope'],
            'code'=>$code,
            'grant_type' => 'authorization_code',
            'redirect_uri'=> $_ENV['redirectUri'],
            'client_secret' => $_ENV['clientSecret'] 
        ],
    ])->getBody()->getContents());
    return $token;
  }

  public function refreshToken($refresh_token){
    $guzzle = new \GuzzleHttp\Client();
    $url = $_ENV['service'] . $_ENV['tenantId'] . '/oauth2/v2.0/token';
    $token = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $_ENV['clientId'],
            'scope'=> $_ENV['scope'],
            'refresh_token'=>$refresh_token,
            'grant_type' => 'refresh_token',
            'redirect_uri'=> $_ENV['redirectUri'],
            'client_secret' => $_ENV['clientSecret'] 
        ],
    ])->getBody()->getContents());
    return $token;
  }

  public static function me($token){
    $graph = new Graph();
    $graph->setAccessToken($token);
    $user = $graph->createRequest("GET", "/me")
                ->setReturnType(Model\User::class)
                ->execute();
    return $user;
  }

  public function logout(){
    $url = $_ENV['service']. $_ENV['tenantId'] . "/oauth2/v2.0/logout?";
    $url .= "post_logout_redirect_uri=" .  $_ENV['redirectUri'];
    header("Location: " . $url);
  }

  public function required(){
    $comprobar=explode(' ',$_ENV['required']);
    foreach($comprobar as $indice=>$name){
      if(!isset($_COOKIE[$name]) && !isset($_POST[$name])){
          header('Location: '.$_ENV['lanzador']);exit;
      }
      $value=(isset($_POST[$name]))?$_POST[$name]:$_COOKIE[$name];
      setcookie($name, $value,-1,"/");
    }
  }

  public function saveToken($token){
    $time=time();
    $token->create_in=$time;
    $token->expires_time=$time+$token->expires_in;
    $_SESSION['azuread']=$token;
  }

  public function tokenValid(){
    $time=time();
    if(isset($_SESSION['azuread'])){
      if($time<$_SESSION['azuread']->expires_time)
          return true;
      else
          return false;
    }else{
      return false;
    }
  }

  public function getSavedToken(){
    return (isset($_SESSION['azuread']))?$_SESSION['azuread']:false;
  }

}