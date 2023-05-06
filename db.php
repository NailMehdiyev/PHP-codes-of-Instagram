<?php
define('AUTH_USER','nail');

define('AUTH_PW','19921995');

$host = "localhost";

$user ="root"; 

$pass =""; 

$db = "instagram_clone";

try { 
   $db = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
   $db->query("SET CHARACTER SET utf8");
   
} catch (PDOException $e) { 
   die($e->getMessage());
} 

date_default_timezone_set('asia/baku');
?>


