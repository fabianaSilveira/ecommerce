<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


class Product  extends Model{

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products order by desproduct");

    }

    public static function checkList($list)
    {
        foreach ($list as &$row){
            $p = new Product();

            //necessario passar pelo setData de products para setar a imagem que está em arquivo no objeto product
            $p->setData($row);

            $row = $p->getValues();

        }

        return $list;
    }

     public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, 
        :vlheight, :vllength, :vlweight, :desurl)", array(
           ":idproduct"=>$this->getidproduct(),
           ":desproduct"=>$this->getdesproduct(),
           ":vlprice"=>$this->getvlprice(),
           ":vlwidth"=>$this->getvlwidth(),
           ":vlheight"=>$this->getvlheight(),
           ":vllength"=>$this->getvllength(),
           ":vlweight"=>$this->getvlweight(),
           ":desurl"=>$this->getdesurl(),

        ));
        $this->setData($results[0]);
      }


    public function get($idproduct)
    {
        $sql = new Sql();
        $results = $sql->select("select * from tb_products where idproduct = :idproduct", [
            ':idproduct'=>$idproduct
        ]);
        $this->setData($results[0]);
    }
    
    public function delete()
    {
        $sql = new Sql();
        $sql->query("delete from tb_products where idproduct = :idproduct",[
            ':idproduct'=>$this->getidproduct()
        ]);
     }

     public function checkPhoto()  
     {
        //C:\xampp\htdocs\ecommerce\res\site\img\products
         if (file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
            "ecommerce".DIRECTORY_SEPARATOR.
            "res".DIRECTORY_SEPARATOR.
            "site".DIRECTORY_SEPARATOR.
            "img".DIRECTORY_SEPARATOR.
            "products".DIRECTORY_SEPARATOR.
            $this->getidproduct().".jpg"
        )){
            $url = "/ecommerce/res/site/img/products/".$this->getidproduct().".jpg";
         }else{
            $url = "/ecommerce/res/site/img/product.jpg";
         }

         return $this->setdesphoto($url);
     }

     public function getValues()
     {
        $this->checkPhoto();
        $values =  parent::getValues();
     
        return $values;

     }

     public function setPhoto($file)
     {
        //cria a foto com o que foi carregado no temp dp servidor
        $extension = explode('.',$file['name']);
        $extension = end($extension);//ultimo array

       
        switch ($extension){
            case "jpg":
            case "jpeg":
               // var_dump($file["tmp_name"]);
              //  exit;
                $image = imagecreatefromjpeg($file["tmp_name"]);
            
            break;

            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
            break;
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }

        $dist = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        "ecommerce".DIRECTORY_SEPARATOR.
        "res".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg";
       
        imagejpeg($image, $dist);
        imagedestroy($image);

        //seta foto no objeto
        $this->checkPhoto();

     }

     public function getFromURL($desurl)
     {
        $sql = new Sql();
        $results = $sql->select("select * from tb_products where desurl = :desurl limit 1", [
            ':desurl'=>$desurl
        ]);
        $this->setData($results[0]);
     }

     

     public function getCategories()
     {
        $sql = new Sql();
        return $sql->select("select * from tb_categories a
           inner join tb_productscategories b on a.idcategory = b.idcategory 
           where b.idproduct= :idproduct
           ",[
             ':idproduct'=>$this->getidproduct()
           ]);
 
     }

     public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			ORDER BY desproduct
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
			FROM tb_products 
			WHERE desproduct LIKE :search
			ORDER BY desproduct
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