<?php
namespace Maticorena\AzureAD;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AzureAD {

  public function __construct(){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['clientId', 'clientSecret', 'tenantId', 'scope']);
  }

  public function authorize(){
    $url = "https://login.microsoftonline.com/" . $_ENV['tenantId'] . "/oauth2/v2.0/authorize?";
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
    $url = 'https://login.microsoftonline.com/' . $_ENV['tenantId'] . '/oauth2/v2.0/token';
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
    $url = 'https://login.microsoftonline.com/' . $_ENV['tenantId'] . '/oauth2/v2.0/token';
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
    $url = "https://login.microsoftonline.com/" . $_ENV['tenantId'] . "/oauth2/v2.0/logout?";
    $url .= "post_logout_redirect_uri=" .  $_ENV['redirectUri'];
    header("Location: " . $url);
  }

}