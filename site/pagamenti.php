<?php

namespace classi\payments;

require_once '../classi/users/Cicerone.php';
require_once '../classi/utilities/Database.php';
require_once '../classi/utilities/Functions.php';
require_once '../classi/payments/CardPayment.php';
require_once '../classi/payments/PaypalPayment.php';

define('MAX_TENTATIVI', 3);


use classi\utilities\Database;
use classi\utilities\Functions;

session_start();

$utente = $_SESSION['utente'];
$costo_premium = $_SESSION['costo_premium'];

// connessione database
$database = new Database();
$link = $database->getConnection();
$functions = new Functions();

?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>
<script src="js/bootstrap.min.js"></script>

<?php
if (isset($_POST["pagamento_carta"])) {

    $pagamento = new CardPayment();
    $tentativo = $_SESSION['tentativo'];
    $tentativo++;
    $codice_ok = false;
    

    if ($tentativo <= MAX_TENTATIVI && !$codice_ok) {

        if (($functions->code_control($_POST["num_carta"], $pagamento::CODE_SIZE)) && ($functions->code_control($_POST["cvv_code"], $pagamento::CVV_SIZE))
        ) {

            
            $pagamento->setCode($_POST["num_carta"]);
            $pagamento->setCvv($_POST["cvv_code"]);
            $codice_ok = true;

            //cambio data premium
            $utente->setPremiumDate(date("Y-m-d"));

            $query = "UPDATE ciceroni SET data_premium='{$utente->getPremiumDate()}' where id_cicerone = '{$utente->getId()}'";
            $result = mysqli_query($link, $query) or die("Errore nella modifica data premium!");
        } else {
           
            if ($tentativo < MAX_TENTATIVI) {
               
                $_SESSION['tentativo'] = $tentativo;

                echo "<div class='alert alert-danger' role='alert'>
                    <a href='javascript:history.back(1);' class='alert-link'>Codici carta non validi!<br>
                    Ti restano " . (MAX_TENTATIVI - $tentativo) . " tentativi, altrimenti sarà effettuato il logout. Click per riprovare</a>
                    </div>";
                    
            } else {
                header("location:logout.php");
            }
        }
    }

    if ($codice_ok) {
        $pagamento->sendPayment($costo_premium);
    }
} else {  //pagamento paypal
    $pagamento = new PaypalPayment($utente->getContact()->getMail());
    $pagamento->sendPayment($costo_premium);

    //cambio data premium
    $utente->setPremiumDate(date("Y-m-d"));

    $query = "UPDATE ciceroni SET data_premium='{$utente->getPremiumDate()}' where id_cicerone = '{$utente->getId()}'";
    $result = mysqli_query($link, $query) or die("Errore nella modifica data premium!");
}
mysqli_close($link);
?>