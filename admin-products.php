<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){
    User::verifyLogin();

    $products = Product::listAll();

    $page = new PageAdmin();

    $page->setTpl("products", [
		'products' => $products
	]);

});

//tela cria produto
$app->get("/admin/products/create", function(){
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("products-create");

});

//save novo produto
$app->post("/admin/products/create", function(){
    User::verifyLogin();

    $product = new Product();

    $product->setData($_POST);

    $product->save();

    header("Location: /ecommerce/admin/products");
	exit;

});


//tela editar produto
$app->get("/admin/products/:idproduct", function($idproduct){
    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $page = new PageAdmin();

    $page->setTpl("products-update",[
        'product'=>$product->getValues()
    ]);

});

//salvar editar produto
$app->post("/admin/products/:idproduct", function($idproduct){
    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);
    $product->setDate($_POST);
    $product->save();
    //$product->setPhoto($_FILES["file"]);

    if(file_exists($_FILES['file']['tmp_name']) || is_uploaded_file($_FILES['file']['tmp_name'])) 
    {
        $product->setPhoto($_FILES["file"]);
     }

    header("Location: /ecommerce/admin/products");
	exit;

});

//excluir produto
$app->get("/admin/products/:idproduct/delete", function($idproduct){
    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $product->delete();
  
    header("Location: /ecommerce/admin/products");
	exit;
});

?>