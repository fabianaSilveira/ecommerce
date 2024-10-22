<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page =new Page();
	$page->setTpl("index");

});

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

//listar usuarios
$app->get('/admin/users', function(){
	User::verifyLogin();
    
	$users = User::listAll();
	$page =new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
	));
});

//tela create
$app->get('/admin/users/create', function(){
	User::verifyLogin();
    
	$page =new PageAdmin();
	$page->setTpl("users-create");
});

//excluir usuario
$app->get('/admin/users/:iduser/delete', function($iduser){
	User::verifyLogin();
    
	$user = new User();
	$user->get((int)$iduser);
	$user->delete();

	header("Location: /ecommerce/admin/users");
	exit;
});

//tela editar
$app->get('/admin/users/:iduser', function($iduser){
	User::verifyLogin();
    
	$user = new User();
	$user->get((int)$iduser);
	$page =new PageAdmin();
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});

//salvar novo usuario
$app->post('/admin/users/create', function(){
	User::verifyLogin();
	//var_dump($_POST);
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])?1:0);

	$user->setData($_POST);
	$user->save();

	header("Location: /ecommerce/admin/users");
	exit;
});

//atualizar usuario
$app->post('/admin/users/:iduser', function($iduser){
	User::verifyLogin();
    
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])?1:0);

	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();

	header("Location: /ecommerce/admin/users");
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
 
	$User = User::getFotgot($_POST["email"]);

	header("Location: /ecommerce/forgot/sent");
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
	$user = User::validarForgotDecrypt($_GET["code"]);

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
	$forgot = User::validarForgotDecrypt($_POST["code"]);

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

$app->run();

 ?>