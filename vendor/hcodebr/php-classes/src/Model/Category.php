<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


class Category  extends Model{

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories order by descategory");

    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
           ":idcategory"=>$this->getidcategory(),
           ":descategory"=>$this->getdescategory()

        ));
        $this->setData($results[0]);
        Category::updateFile();
    }


    public function get($idcategory)
    {
        $sql = new Sql();
        $results = $sql->select("select * from tb_categories where idcategory = :idcategory", [
            ':idcategory'=>$idcategory
        ]);
        $this->setData($results[0]);
    }
    
    public function delete()
    {
        $sql = new Sql();
        $sql->query("delete from tb_categories where idcategory = :idcategory",[
            ':idcategory'=>$this->getidcategory()
        ]);
        Category::updateFile();
    }

    public static function updateFile()
    {
        $categories = Category::listAll();
        $html=[];
        foreach ($categories as $row){
            array_push($html,'<li><a href="/ecommerce/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li><br>');
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."ecommerce".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html",implode("",$html));        

    }

    public function getProducts($related=true)
    {
        $sql = new Sql();
        if ($related === true){
            return $sql->select("
               select * from tb_products where idproduct in (
                select a.idproduct
                from tb_products a
                inner join tb_productscategories b on a.idproduct = b.idproduct
                where b.idcategory = :idcategory);
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
        }else{
            return $sql->select("
               select * from tb_products where idproduct not in (
                select a.idproduct
                from tb_products a
                inner join tb_productscategories b on a.idproduct = b.idproduct
                where b.idcategory = :idcategory);

            ",[
                ':idcategory'=>$this->getidcategory()
            ]);
        }
    }

    public function getProductsPage($page=1,$itemsPerPage=3)
    {
        $start = ($page-1)* $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory
            LIMIT $start, $itemsPerPage;
        ", [
            ':idcategory' => $this->getidcategory()
    ]);

    $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");


        return [
            'data'=>Product::checkList($results),
            'total' => (int)$resultTotal[0]["nrtotal"],
			'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("insert into tb_productscategories (idcategory, idproduct) values (:idcategory, :idproduct )",[
           ':idcategory'=>$this->getidcategory(),
           ':idproduct'=>$product->getidproduct()
        ]);

    }

    public function removeProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("delete from  tb_productscategories where idcategory= :idcategory and idproduct= :idproduct",[
           ':idcategory'=>$this->getidcategory(),
           ':idproduct'=>$product->getidproduct()
        ]);

    }

    public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data' => $results,
			'total' => (int)$resultTotal[0]["nrtotal"],
			'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories 
			WHERE descategory LIKE :search
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		", [
			':search' => '%' . $search . '%'
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data' => $results,
			'total' => (int)$resultTotal[0]["nrtotal"],
			'pages' => ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}
}

?>