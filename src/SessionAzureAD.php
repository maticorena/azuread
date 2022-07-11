<?php
namespace Maticorena\AzureAD;

use Dotenv;

class AzureAD {

  public function __construct(){
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $dotenv->required(['required','lanzador']);
  }

  public function required(){
    $comprobar=$_ENV['required'];
    foreach($comprobar as $indice=>$name){
      if(!isset($_COOKIE[$name]) && !isset($_POST[$name])){
          header('Location: '.$_ENV['lanzador']);exit;
      }
      $value=(isset($_POST[$name]))?$_POST[$name]:$_COOKIE[$name];
      setcookie($name, $value,-1,"/");
    }
  }

}