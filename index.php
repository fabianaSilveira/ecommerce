<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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

	$user = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

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

//lista de categorias
$app->get('/admin/categories', function() {
	User::verifyLogin();

	$categories = Category::listAll();

	$page =new PageAdmin();
	$page->setTpl("categories", [
		'categories' => $categories
	]);

});

//pagina create categoria
$app->get('/admin/categories/create', function() {

	User::verifyLogin();
	$page =new PageAdmin();
	$page->setTpl("categories-create");

});


//salvar categoria
$app->post('/admin/categories/create', function() {
	User::verifyLogin();
	$category = new Category();
	$category->setData($_POST);
	$category->save();
	header("Location: /ecommerce/admin/categories");
	exit;

});

//excluir usuario
$app->get('/admin/categories/:idcategory/delete', function($idcategory){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();

	header("Location: /ecommerce/admin/categories");
	exit;
});

//tela editar categoria
$app->get('/admin/categories/:idcategory', function($idcategory){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);

	$page =new PageAdmin();
	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);

});

//atuailza categoria
$app->post('/admin/categories/:idcategory', function($idcategory){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);
	$category->setData($_POST);
	$category->save();

	header("Location: /ecommerce/admin/categories");
	exit;

});

$app->get('/categories/:idcategory', function($idcategory){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);

	$page =new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});

$app->run();

 ?>