<?php
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

//rotas mais especificas primeiro


//Em lista de pedidos - entrar pelo leink http://localhost/ecommerce/admin/ e
//                      logar com perfil admin
//botão status de um pedido listado, abre a tela order-status.html
$app->get("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

    echo "idorder".$idorder;
	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
	]);

});

//na tela de status -> order-status.html
//botão salvar, apos salvar retora para tela ordes.html
$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
		Order::setError("Informe o status atual.");
		header("Location: /ecommerce/admin/orders/".$idorder."/status");
		exit;
	}

	$order = new Order();

	$order->get((int)$idorder);

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado.");

	header("Location: /ecommerce/admin/orders/".$idorder."/status");
	exit;

});


//Em lista de pedidos - entrar pelo leink http://localhost/ecommerce/admin/ e
//                      logar com perfil admin
// Ação do botão "excluir" retorna para tela ordes.html
$app->get("/admin/orders/:idorder/delete", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /ecommerce/admin/orders");
	exit;

});

//Em lista de pedidos - entrar pelo link http://localhost/ecommerce/admin/ e
//                      logar com perfil admin
// clicar na aba lateral "pedidos" orders.html
//botão detalhe  de um dos pedidos listados-> order.html
$app->get("/admin/orders/:idorder", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);

});

//lista de pedidos - entrar pelo link http://localhost/ecommerce/admin/ e
// logar com perfil admin e 
// clicar na aba lateral pedidos orders.html
$app->get("/admin/orders", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = Order::getPageSearch($search, $page);

	} else {

		$pagination = Order::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("orders", [
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);

});
?>