<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

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


$app->get('/admin/categories/:idcategory/products', function($idcategory){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);

	$page =new PageAdmin();
	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
});

$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /ecommerce/admin/categories/".$idcategory ."/products");
	exit;


});


$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct){
	User::verifyLogin();
    
	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /ecommerce/admin/categories/".$idcategory ."/products");
	exit;


});

?>