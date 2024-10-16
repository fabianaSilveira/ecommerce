<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User  extends Model{

    const SESSION = "User";

    public static function login($login, $password)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0){
            throw new \Exception("Usuário inxistente ou senha inválida!");
        }

        $data = $results[0];
 

        //verifica a senha, gerado um hash de password e compara com o hash do bd
        if (password_verify($password, $data["despassword"])){
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] =$user->getValues();
            return $user;

        }else{
            throw new \Exception("Usuário inxistente ou senha inválida!");
        }

    }

    public static function verifyLogin($inadmin = true)
    {
        if ( !isset($_SESSION[User::SESSION])
             || !$_SESSION[User::SESSION]
             || !(int)$_SESSION[User::SESSION]["iduser"] > 0
             || (boolean)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
           ){
            header("Location: /ecommerce/admin/login");
            exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }

}

?>