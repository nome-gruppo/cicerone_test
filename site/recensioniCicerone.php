<?php
namespace classi\users;

require_once '..\classi\users\Cicerone.php';
require_once '..\classi\users\Turista.php';
require_once '..\classi\activities\Review.php';
require_once '..\classi\utilities\Database.php';
require_once '..\classi\utilities\Functions.php';
use classi\utilities\Database;
use classi\utilities\Functions;
use classi\activities\Review;


session_start();

$cicerone = $_SESSION['utente'];
$functions = new Functions();
$database = new Database();
$link = $database->getConnection();

//query ricerca recensioni per cicerone
$query_search = "SELECT * from recensioni where id_cicerone ='{$cicerone->getId()}'";
$result_search = mysqli_query($link, $query_search) or die("Errore query ricerca!");

$array_recensioni = array();

//carico le attività del cicerone su un array
while ($row = mysqli_fetch_array($result_search)) {

    $recensione = new Review($row['id_cicerone'], $row['id_turista'], $row['titolo'], $row['valutazione'], $row['testo']);
    $array_recensioni[] = $recensione;
}


$array_valutazioni = array(0.0, 0.0, 0.0, 0.0, 0.0);      //array contenente il numero delle valutazioni con 1,2,3,4,5 stelle
//se presente almeno una recensione
if (count($array_recensioni) > 0) {
    
    foreach ($array_recensioni as $item) {
        if (is_object($item) && $item instanceof Review) {
            
            if ($item->getValutation() == 1) {
                $array_valutazioni[0]++;
            } elseif ($item->getValutation() == 2) {
                $array_valutazioni[1]++;
            } elseif ($item->getValutation() == 3) {
                $array_valutazioni[2]++;
            } elseif ($item->getValutation() == 4) {
                $array_valutazioni[3]++;
            } elseif ($item->getValutation() == 5) {
                $array_valutazioni[4]++;
            }
        }
    }
}

//query per nome turisti che hanno scritto una recensione
$query_turista = "SELECT id_turista,nome from turista where id_turista in (SELECT id_turista from recensioni) ";
$result_turista = mysqli_query($link, $query_turista) or die("Errore query turista!");

$array_turisti = array();

//carico i turisti su un array
while ($row = mysqli_fetch_array($result_turista)) {
    $turista = new Turista();
    $turista->setName($row['nome']);
    $turista->setId($row['id_turista']);
    $array_turisti[] = $turista;
}

function getPercentage($num) {
    global $array_recensioni;
    global $array_valutazioni;
    $num_rec = count($array_recensioni); 
    
    if ($num_rec > 0) {
        return $array_valutazioni[$num] * 100/ $num_rec ;
    } else {
        return 0;
    }
}
$perc5 = getPercentage(4);
$perc4 = getPercentage(3);
$perc3 = getPercentage(2);
$perc2 = getPercentage(1);
$perc1 = getPercentage(0);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Recensioni</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap.css" rel="stylesheet">

    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!-- Include the above in your HEAD tag -->

</head>

<body>
    <?php
    $functions->stampaNavbarCicerone($cicerone->getName());
    ?>
    <br /><br /><br />
    <div class="container">

        <div class="row">
            <div class="col-sm-3">
                <!--Stampa media valutazioni-->
                <div class="rating-block">
                    <h4>Media valutazione utenti</h4>
                    <br />
                    <h2 class="bold padding-bottom-7">&nbsp&nbsp
                        <?php echo number_format($cicerone->getValutazione(), 1);?> <small>/ 5</small></h2>
                </div>
                <!--Stampa numero recensioni-->
                <div>
                &nbsp&nbsp&nbsp<span class="glyphicon glyphicon-user"></span>
                    <?php echo count($array_recensioni); ?> recensioni
                </div>
            </div>

            <!--Barre valutazioni-->
            <div class="col-sm-3 ">
                <h4>Valutazioni</h4>
                <!--5 stelle-->
                <div class="pull-left">
                    <div class="pull-left" style="width:35px; line-height:1;">
                        <div style="height:9px; margin:5px 0;">5 <span class="glyphicon glyphicon-star" style="color:#FFCA28"></span></div>
                    </div>
                    <div class="pull-left" style="width:180px;">
                        <div class="progress" style="height:9px; margin:8px 0;">
                            <div class="progress-bar progress-bar-success" id="progress5" role="progressbar" aria_valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:50%">
                            </div>
                        </div>
                    </div>
                    <div class="pull-right" style="margin-left:10px;"><?php echo $array_valutazioni[4]; ?></div>
                </div>
                <!--4 stelle-->
                <div class="pull-left">
                    <div class="pull-left" style="width:35px; line-height:1;">
                        <div style="height:9px; margin:5px 0;">4 <span class="glyphicon glyphicon-star" style="color:#FFCA28"></span></div>
                    </div>
                    <div class="pull-left" style="width:180px;">
                        <div class="progress" style="height:9px; margin:8px 0;">
                            <div class="progress-bar progress-bar-primary" id="progress4" role="progressbar" aria_valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:50%">
                            </div>
                        </div>
                    </div>
                    <div class="pull-right" style="margin-left:10px;"><?php echo $array_valutazioni[3]; ?></div>
                </div>
                <!--3 stelle-->
                <div class="pull-left">
                    <div class="pull-left" style="width:35px; line-height:1;">
                        <div style="height:9px; margin:5px 0;">3 <span class="glyphicon glyphicon-star" style="color:#FFCA28"></span></div>
                    </div>
                    <div class="pull-left" style="width:180px;">
                        <div class="progress" style="height:9px; margin:8px 0;">
                            <div class="progress-bar progress-bar-info" id="progress3" role="progressbar" aria_valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:50%">                              
                            </div>
                        </div>
                    </div>
                    <div class="pull-right" style="margin-left:10px;"><?php echo $array_valutazioni[2]; ?></div>
                </div>
                <!--2 stelle-->
                <div class="pull-left">
                    <div class="pull-left" style="width:35px; line-height:1;">
                        <div style="height:9px; margin:5px 0;">2 <span class="glyphicon glyphicon-star" style="color:#FFCA28"></span></div>
                    </div>
                    <div class="pull-left" style="width:180px;">
                        <div class="progress" style="height:9px; margin:8px 0;">
                            <div class="progress-bar progress-bar-warning" id="progress2" role="progressbar" aria_valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:50%">
                            </div>
                        </div>
                    </div>
                    <div class="pull-right" style="margin-left:10px;"><?php echo $array_valutazioni[1]; ?></div>
                </div>
                <!--1 stella-->
                <div class="pull-left">
                    <div class="pull-left" style="width:35px; line-height:1;">
                        <div style="height:9px; margin:5px 0;">1 <span class="glyphicon glyphicon-star" style="color:#FFCA28"></span></div>
                    </div>
                    <div class="pull-left" style="width:180px;">
                        <div class="progress" style="height:9px; margin:8px 0;">
                            <div class="progress-bar progress-bar-danger" id="progress1" role="progressbar" aria_valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:50%">
                            </div>
                        </div>
                    </div>
                    <div class="pull-right" style="margin-left:10px;"><?php echo $array_valutazioni[0]; ?></div>
                </div>
            </div>
        </div>

        <!--Recensioni-->
        <div class="row">
            <div class="col-sm-12">
                <hr />
                <hr />
                <div class="review-block">

                    <?php

                    foreach ($array_recensioni as $item_rec) {

                        if (is_object($item_rec) && $item_rec instanceof Review) {
                            echo '<div class="row">
                        <div class="col-sm-3">
                            <img src="images\userIcon.png">
                            <div class="review-block-name"><a href="#">&nbsp';
                            //ricerca nome turista che ha fatto la recensione
                            $trovato = false;
                            for ($i = 0; !$trovato && $i < count($array_turisti); $i++) {
                                if ((is_object($array_turisti[$i]) && $array_turisti[$i] instanceof Turista) && ($item_rec->getId_turista() == $array_turisti[$i]->getId())) {
                                   
                                        echo $array_turisti[$i]->getName();
                                  
                                }
                            }
                            echo '</a></div></div>
                            
                        <div class="col-sm-9">
                            <div class="review-block-rate">';

                            //stampa valutazione in stelle
                            //stampa stelle gialle
                            for ($i = 1; $i <= $item_rec->getValutation(); $i++) {

                                 echo '<span class="glyphicon glyphicon-star" aria-hidden="true" style="color:#FFCA28"></span>';
                                        
                            }
                            //stampa stelle grigie
                            for ($i = $item_rec->getValutation(); $i < 5; $i++) {
                                echo '<span class="glyphicon glyphicon-star" aria-hidden="true" style="color:#808080"></span>';
                            }
                            echo '</div>
                            <div class="review-block-title"><h4>' . $item_rec->getTitle() . '</h4></div>
                            <div class="review-block-description">' . $item_rec->getText() . '</div>
                        </div>
                    </div>
                    <hr />';
                        } //end if
                    } //end foreach
                    ?>

                </div>
            </div>
        </div>

    </div> <!-- /container -->


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>
        window.jQuery || document.write('<script src="assets/js/vendor/jquery.min.js"><\/script>')
    </script>

    <script src="js/bootstrap.min.js"></script>
    
  
    <script>
        var percentage5 = parseInt(<?php echo $perc5?>);
        var percentage4 = parseInt(<?php echo $perc4?>);
        var percentage3 = parseInt(<?php echo $perc3?>);
        var percentage2 = parseInt(<?php echo $perc2?>);
        var percentage1 = parseInt(<?php echo $perc1?>);

        $('#progress5').attr('aria-valuenow', percentage5 ).css('width', percentage5  + '%');
        $('#progress4').attr('aria-valuenow', percentage4 ).css('width', percentage4  + '%');
        $('#progress3').attr('aria-valuenow', percentage3 ).css('width', percentage3  + '%');
        $('#progress2').attr('aria-valuenow', percentage2 ).css('width', percentage2  + '%');
        $('#progress1').attr('aria-valuenow', percentage1 ).css('width', percentage1  + '%');
    </script>

</body>

</html>
