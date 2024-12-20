<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\User;

class Cart  extends Model{
        const SESSION = "Cart";
        const SESSION_ERROR = "CartError";
    
        //veirifcar se a já tem carrinho na sessão
        public static function getFromSession()
        {
    
            $cart = new Cart();
    
            //se  carrinho já foi inserido no bd e esta na sessão
            if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
               
                //carrega o carrinho do bd a apartit do idcart da sessão
                $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
            
            //se carrinho não existe na sessão
            } else {
    
                $cart->getFromSessionID();
    
                if (!(int)$cart->getidcart() > 0) {
    
                    $data = [
                        'dessessionid' => session_id()
                    ];
    
                     if (User::checkLogin(false)) {
    
                        $user = User::getFromSession();
    
                        $data['iduser'] = $user->getiduser();
                    } 
    
                    $cart->setData($data);
    
                    $cart->save();//grava no bd
    
                    $cart->setToSession();//coloca o cart na sessão
                }
            }
    
            return $cart;
        }
    
        public function setToSession()
        {
    
            $_SESSION[Cart::SESSION] = $this->getValues();
        }
    
        public function getFromSessionID()
        {
    
            $sql = new Sql();
    
            $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
                ':dessessionid' => session_id()
            ]);
    
            if (count($results) > 0) {
    
                $this->setData($results[0]);
            }
        }
    
        public function get(int $idcart)
        {
    
            $sql = new Sql();
    
            $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
                ':idcart' => $idcart
            ]);
    
            if (count($results) > 0) {
    
                $this->setData($results[0]);
            }
        }
    
        public function save()
        {
    
            $sql = new Sql();
    
            $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
                ':idcart' => $this->getidcart(),
                ':dessessionid' => $this->getdessessionid(),
                ':iduser' => $this->getiduser(),
                ':deszipcode' => $this->getdeszipcode(),
                ':vlfreight' => $this->getvlfreight(),
                ':nrdays' => $this->getnrdays()
            ]);
    
            $this->setData($results[0]);
        }
    
        public function addProduct(Product $product)
        {
    
            $sql = new Sql();
    
            $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
                ':idcart' => $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ]);
    
            $this->getCalculateTotal();
        }
    
        public function removeProduct(Product $product, $all = false)
        {
    
            $sql = new Sql();
    
            if ($all) {
    
                $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
                    ':idcart' => $this->getidcart(),
                    ':idproduct' => $product->getidproduct()
                ]);
            } else {
    
                $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
                    ':idcart' => $this->getidcart(),
                    ':idproduct' => $product->getidproduct()
                ]);
            }
    
            $this->getCalculateTotal();
        }
    
        //soma dos atributos que estão no carrinho
        public function getProducts()
        {
    
            $sql = new Sql();
    
            $rows = $sql->select("
                SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
                FROM tb_cartsproducts a 
                INNER JOIN tb_products b ON a.idproduct = b.idproduct 
                WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
                GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
                ORDER BY b.desproduct
            ", [
                ':idcart' => $this->getidcart()
            ]);
    
            return Product::checkList($rows);
        }
    
        public function getProductsTotals()
        {
    
            $sql = new Sql();
    
            $results = $sql->select("
                SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
                FROM tb_products a
                INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
                WHERE b.idcart = :idcart AND dtremoved IS NULL;
            ", [
                ':idcart' => $this->getidcart()
            ]);
    
            if (count($results) > 0) {
                return $results[0];
            } else {
                return [];
            }
        }
    
        public function setFreight($nrzipcode)
        {
    
           

            $nrzipcode = str_replace('-', '', $nrzipcode);
    
            $totals = $this->getProductsTotals();

           // var_dump($totals);
           // exit;
    
            if ($totals['nrqtd'] > 0) {
    
                if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
                if ($totals['vllength'] < 16) $totals['vllength'] = 16;
                if ($totals['vlwidth'] < 11) $totals['vlwidth'] = 11;
    
                // array com as variaveis que serão enviados para o service dos correios
                $qs = http_build_query([
                    'nCdEmpresa' => '',
                    'sDsSenha' => '',
                    'nCdServico' => '40010',
                    'sCepOrigem' => '09853120',
                    'sCepDestino' => $nrzipcode,
                    'nVlPeso' => $totals['vlweight'],
                    'nCdFormato' => '1',
                    'nVlComprimento' => $totals['vllength'],
                    'nVlAltura' => $totals['vlheight'],
                    'nVlLargura' => $totals['vlwidth'],
                    'nVlDiametro' => '0',
                    'sCdMaoPropria' => 'S',
                    'nVlValorDeclarado' => $totals['vlprice'],
                    'sCdAvisoRecebimento' => 'S'
                ]);
    
                /*
                SERVIÇO DOS CORREIOS NÃO ESTÁ MAIS DISPONIVEL
                */
                //caminho do webservice dos correios
                // $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs);
                    // $result = $xml->Servicos->cServico;
    
                // if ($result->MsgErro != '') {
    
                //     Cart::setMsgError($result->MsgErro);
                // } else {
    
                //     Cart::clearMsgError();
                // }
    
                // $this->setnrdays($result->PrazoEntrega);
                // $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
                $this->setnrdays($totals['nrqtd']*3);
                $this->setvlfreight($totals['nrqtd']*35);
                $this->setdeszipcode($nrzipcode);
    
                $this->save();
    
                
                $result = [];
                return $result;
            } else {
            }
        }
    
        public static function formatValueToDecimal($value): float
        {
    
            $value = str_replace('.', '', $value);
            return str_replace(',', '.', $value);
        }
    
        //seta mensagem de erro na sessao
        public static function setMsgError($msg)
        {
    
            $_SESSION[Cart::SESSION_ERROR] = $msg;
        }
    
        //get mensagem da sessão
        public static function getMsgError()
        {
    
            $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
    
            Cart::clearMsgError();
    
            return $msg;
        }
    
        //limpa sessão
        public static function clearMsgError()
        {
    
            $_SESSION[Cart::SESSION_ERROR] = NULL;
        }
    
        public function updateFreight()
        {
    
            if ($this->getdeszipcode() != '') {
    
                $this->setFreight($this->getdeszipcode());
            }
        }
    
        public function getValues()
        {
    
            $this->getCalculateTotal();
    
            return parent::getValues();
        }
    
        public function getCalculateTotal()
        {
    
            $this->updateFreight();
    
            $totals = $this->getProductsTotals();
    
            $this->setvlsubtotal($totals['vlprice']);
            $this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight());
        }

}

?>