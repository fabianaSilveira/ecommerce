<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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
/* $app->post('/admin/users/create', function(){
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
}); */

$app->post("/admin/users/create", function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$_POST['despassword'] = User::getPasswordHash($_POST['despassword']);

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
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

?>