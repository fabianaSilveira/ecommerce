<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;


$app->get('/admin/', function() {
	User::verifyLogin();
    
	$page =new PageAdmin();
	$page->setTpl("index");

});

$app->get('/admin/login/', function() {

	//echo "get login";
    
	$page =new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");

});

//valifdar o login
$app->post('/admin/login', function() {
   
	//echo "post login";

	User::login($_POST["login"], $_POST["password"]);

    header("Location: /ecommerce/admin/");
	exit;
});

$app->get('/admin/logout', function(){
	User::logout();
	header("Location: /ecommerce/admin/login");
	exit;
});


//esquecei a senha
$app->get('/admin/forgot', function() {
 
	$page =new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");
});

$app->post('/admin/forgot', function() {
 
	$User = User::getForgot($_POST["email"]);

	header("Location: /ecommerce/admin/forgot/sent");
	exit;
});


$app->get('/admin/forgot/sent', function() {
 
	$page =new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");

});

$app->get('/admin/forgot/reset', function() {
	$user = User::validForgotDecrypt($_GET["code"]);

	$page =new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post('/admin/forgot/reset', function() {
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);
	$user->setPassword($password);

	$page =new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");

});
?>