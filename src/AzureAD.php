<?php

namespace Maticorena\AzureAD;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Dotenv;

class AzureAD {

/**
 * It loads the .env file and checks that the required variables are set
 */
  public function __construct(){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['clientId', 'clientSecret', 'tenantId', 'scope','service','required','lanzador']);
  }

/**
 * It builds a URL to the Microsoft Graph API's authorization endpoint, and then redirects the user to
 * that URL
 */
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

/**
 * It takes the authorization code from the previous step and uses it to request an access token from
 * the Microsoft Graph API
 * 
 * @param code The authorization code returned from the initial request to Azure.
 * 
 * @return The token is being returned.
 */
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

/**
 * It takes a refresh token and returns a new access token
 * 
 * @param refresh_token The refresh token that was returned from the initial authorization request.
 * 
 * @return The token is being returned.
 */
  public function refreshToken($refresh_token){
    $this->limitRefresh();
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

  private function limitRefresh(){
    $time=time();
    $token=$this->getSavedToken();
    if($token!=false){
       if($time > $token->create_in+(int)$_ENV['limitRefresh']){
           $this->logout();exit;
       }
    }
  }

/**
 * It takes a token, creates a new Graph object, sets the token, creates a new request, sets the return
 * type, and executes the request
 * 
 * @param token The access token you got from the login process
 * 
 * @return An object of type User. and his properties
 */
  public static function me($token){
    $graph = new Graph();
    $graph->setAccessToken($token);
    $user = $graph->createRequest("GET", "/me")
                ->setReturnType(Model\User::class)
                ->execute();
    return $user;
  }

/**
 * It takes the user to the Azure AD logout page, and then redirects them back to the application
 */
  public function logout(){
    if(isset($_SESSION['azuread']))unset($_SESSION['azuread']);
    $url = $_ENV['service']. $_ENV['tenantId'] . "/oauth2/v2.0/logout?";
    $url .= "post_logout_redirect_uri=" .  $_ENV['redirectUri'];
    header("Location: " . $url);
  }

/**
 * If the user has not sent the required data, redirect to the launcher
 */
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

/**
 * It saves the token in the session
 * 
 * @param token The token object returned from the Azure AD server.
 */
  public function saveToken($token){
    $time=time();
    $token->create_in=$time;
    $token->expires_time=$time+$token->expires_in;
    $_SESSION['azuread']=$token;
  }

/**
 * If the session variable is set and the current time is less than the expiration time, return true.
 * Otherwise, return false
 * 
 * @return A boolean value.
 */
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

/**
 * It checks if the session variable 'azuread' is set. If it is, it returns the value of the session
 * variable. If it is not set, it returns false.
 * 
 * @return The token is being returned.
 */
  public function getSavedToken(){
    return (isset($_SESSION['azuread']))?$_SESSION['azuread']:false;
  }

}