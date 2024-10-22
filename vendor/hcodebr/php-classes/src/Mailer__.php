<?php
namespace Hcode;

use Rain\Tpl;

class Mailer {
    const USERNAME = "fabiana.silveira74@outlook.com";
    CONST PASSWORD = "Cris@2907";
    CONST NAME_FROM = "Fabiana Silveira";

    private $mail;

    public function __construc($toAddress, $toName, $subject, $tplName, $data = array()){
        $config = array(
		    "base_url"      => null,
		    "tpl_dir"       => "views/email",
		    "cache_dir"     => "views-cache/",
		    "debug"         => true
		);

		Tpl::configure( $config );

		$tpl = new Tpl();
        
        foreach ($data as $key => $value){
            $tpl->assng($key, $value);
        }
       
        $html = $tpl->draw($tplName, true);
       
        $this->mail = new \PHPMailer(true);

        try {
        
            $this->mail->isSMTP();

            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );


            $this->mail->Host       = 'smtp.gmail.com';//'smtp.office365.com';
            //$this->mail->Host       = 'smtp-mail.outlook.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->SMTPSecure = 'tls';//PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port       = 587;
        
        
            $this->mail->Username   = Mailer::USERNAME; 
            $this->mail->Password   = Mailer::PASSWORD;
        
            $this->mail->setFrom( Mailer::USERNAME, Mailer::NAME_FROM);
            $this->mail->addAddress($toAddress, $toName);
        
          //  $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->msgHTML($html);
          //  $this->mail->Body    = 'Corpo da mensagem em <b>HTML</b>';
        
            
        
        } catch (Exception $e) {
        
            echo "A mensagem não pôde ser enviada. Erro do PHPMailer: {$this->mail->ErrorInfo}";
        
        }

    }

    
    public function send()
    {
        if (!$this->mail->send()) {
            echo 'Mailer Error: ' . $this->mail->ErrorInfo;
        } else {
            echo 'Message sent!';
            //Section 2: IMAP
            //Uncomment these to save your message in the 'Sent Mail' folder.
            #if (save_mail($this->mail)) {
            #    echo "Message saved!";
            #}
        }
    }
}

?>