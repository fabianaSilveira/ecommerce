<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//lista de usuários- entrar pelo link http://localhost/ecommerce/admin/ e
// logar com perfil admin e 
// clicar na aba lateral usuários users.html
//clicar bt alterar senha de um usuario
$app->get("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", [
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	]);

});

//Salva alteração de senha do usuário
// bt alterar senha de um usuario
$app->post("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	if (!isset($_POST['despassword']) || $_POST['despassword']==='') {

		User::setError("Preencha a nova senha.");
		header("Location: /ecommerce/admin/users/$iduser/password");
		exit;

	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm']==='') {

		User::setError("Preencha a confirmação da nova senha.");
		header("Location: /ecommerce/admin/users/$iduser/password");
		exit;

	}

	if ($_POST['despassword'] === $_POST['despassword-confirm']) {

		User::setError("A sua nova senha deve ser diferente da atual.");
		header("Location: /ecommerce/admin/users/$iduser/password");
		exit;
	}

	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword(User::getPasswordHash($_POST['despassword']));

	User::setSuccess("Senha alterada com sucesso.");

	header("Location: /ecommerce/admin/users/$iduser/password");
	exit;

});



//listar usuarios
$app->get('/admin/users', function(){
	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = User::getPageSearch($search, $page);

	} else {

		$pagination = User::getPage($page, 3);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/ecommerce/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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