<?php

namespace Cart\Controller\Mail;

use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\Exception;

class MailController
{
    public function sendCheckout($data = array(), $products = array(), $id) {
        
        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'electronicforum2@gmail.com';           // SMTP username
            $mail->Password   = 'depanatorultv';                        // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 25;                                     // TCP port to connect to
        
            //Recipients
            $mail->setFrom('shoppiesetv@gmail.com', 'Shop Piese TV');
            $mail->addAddress('shoppiesetv@gmail.com');
            #$mail->addAddress('ionuzcostin@gmail.com');
            $mail->addReplyTo('shoppiesetv@gmail.com', 'Information');
        
            // Attachments
            #$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            #$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        
            $content = "<br /><br />Produse Comandate: <br />--------------------------------------------------------<br />";
            foreach($products as $item) {
                $content .= "{$item['title']} - x {$item['quantity']} bucati (Total: ". ($item['quantity'] * $item['price']) ." Lei)<br />";
            }

            $plata = "";
            if(isset($data['paymentMethod2'])) {
                $plata = "Posta Romana (Ramburs)";
            }

            if(isset($data['paymentMethod1'])) {
                $plata = "Fan Curier (Ramburs)";
            }

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = "Comanda noua plasata de {$data['firstName']} {$data['lastName']}";
            $mail->Body    = "Comanda noua plasata de {$data['firstName']} {$data['lastName']}<br />
            ID Tranzactie: {$id}<br />".
            $content . "<br /><br />Adresa de facturare:<br />
            --------------------------------------------------------<br />
            Adresa: {$data['state']}, {$data['address']}, {$data['zip']}<br />
            Telefon: {$data['telefon']}<br />
            Metoda de plata: {$plata}";
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function sendMailNews($mailUser, $products = array())
    {
        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'electronicforum2@gmail.com';           // SMTP username
            $mail->Password   = 'depanatorultv';                        // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 25;                                     // TCP port to connect to
        
            //Recipients
            $mail->setFrom('shoppiesetv@gmail.com', 'Newsletters :: Shop PieseTV');
            $mail->addAddress($mailUser);
            $mail->addReplyTo('info@example.com', 'Information');

            $body = "";
            foreach ($products as $item) {
                $body .= "<div style='width: 15%;float: left; background: #607d8b78;padding: 6px;text-align: center; clear: right;display: block;color: white;border-radius: 4px; margin: 10px;'>".
                    "<img src='{$item->image}' style='width: 90px;' /><br /><a href='{$item->slug}'>{$item->title}</a> <br />Pret: {$item->price} Lei<br /><br />".
                '</div>';
            }

            $mail->isHTML(true);
            $mail->Subject = "Produse noi adaugate";
            $mail->Body    = $body;

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function sendMailUser($mailUser, $data = array(), $products = array(), $id) {
        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'electronicforum2@gmail.com';           // SMTP username
            $mail->Password   = 'depanatorultv';                        // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 25;                                     // TCP port to connect to
        
            //Recipients
            $mail->setFrom('shoppiesetv@gmail.com', 'Shop Piese TV');
            $mail->addAddress($mailUser);
            $mail->addReplyTo('info@example.com', 'Information');
        
            // Attachments
            #$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            #$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        
            $produse = "Produse Comandate: <br />--------------------------------------------------------<br />";
            $suma = 0;
            foreach($products as $item) {
                $suma = $suma + $item->price * $item->quantity;
                $produse .= "{$item['title']} - x {$item['quantity']} bucati (Total: ". ($item['quantity'] * $item['price']) ." Lei)<br />";
            }

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = "Comanda noua plasata";
            $mail->Body    = "Buna ziua, {$data['firstName']} {$data['lastName']},<br /><br />
            Comanda dvs a fost plasata, va rugam sa asteptati un raspuns de la unul dintre operatori nostri, veti primi un email cu detalii.
            <br /><br />
            {$produse}
            <br />
            Suma de plata: {$suma} Lei<br />
            <br />
            <br />
            Pentru a vedea statutul comenzii acesati Contul Meu > Comenzile Mele > Tranzactie {$id}<br />
            O zi buna.
            <br /><br />Cu respect,<br />Echipa Shop Piese TV.";
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function newUser($mailUser, $data = array())
    {

    }

    public function contact($data = array())
    {
        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'electronicforum2@gmail.com';           // SMTP username
            $mail->Password   = 'depanatorultv';                        // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 25;                                     // TCP port to connect to
        
            //Recipients
            $mail->setFrom($data['mail'], 'Contact :: Shop Piese TV');
            $mail->addAddress('shoppiesetv@gmail.com');
            $mail->addReplyTo('shoppiesetv@gmail.com', 'Information');

            $mail->isHTML(true);
            $mail->Subject = "Contact";
            $mail->Body    = htmlspecialchars($data['message']);
            $mail->AltBody = htmlspecialchars($data['message']);
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}