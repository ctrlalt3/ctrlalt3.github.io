
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php


session_start();
$baraja = createDeck();
        $c = barajar($baraja);
        $_SESSION['tapete'] = $tapete = repartir($c,4,5);
        pinta_juego($tapete);


    ?>
</body>
</html>


