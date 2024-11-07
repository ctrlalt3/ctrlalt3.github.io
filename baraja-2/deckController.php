<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            justify-content: center;
            align-items: center;
        }
        h1 {
            color: white;
        }
        .player {
            border-bottom: 0.5vh solid white;
            display: flex;
            flex-wrap: wrap;
            row-gap: 1vh;
            text-align: center;
            align-items: center;
            justify-content: space-between;
            padding: 1vh;
            background-color: green;
        }
        .tablero {
            border-bottom: 0.5vh solid white;
            text-align: center;
            align-items: center;
            justify-content: space-between;
            padding: 2vh;
            background-color: navy;
        }
        .card {
            width: 3vh;
            background-color: white;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }
        .card:hover {
            scale: 1.1;
        }
        .hueco {
            text-align: center;
            border: 0.2vh solid white;
            border-radius: 0.5vh;
            padding: 0.6vh;
        }
        .alert {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
            font-size: 16px;
            text-align: center;
        }
        .tablero {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .palo-section {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .cartas {
            display: flex;
            gap: 5px;
        }
        .card {
            width: 50px;
            height: 80px;
        }
    </style>
</head>
<body>
<?php
session_start();

function createDeck() {
    $palos = ['cors', 'diamants', 'piques', 'trebols'];
    $numeros = range(1, 13);
    $baraja = [];
    foreach ($palos as $palo) {
        foreach ($numeros as $numero) {
            $carta = ['palo' => $palo, 'numero' => $numero];
            array_push($baraja, $carta);
        }
    }
    return $baraja;
}

function barajar($baraja) {
    shuffle($baraja);
    return $baraja;  // Devolvemos el array barajado
}

function repartir($baraja, $n_jug) {
    $tapete = [];
    for ($i = 0; $i < $n_jug; $i++) {
        $jugador = [];
        for ($j = 0; $j < 13; $j++) { 
            $carta = array_pop($baraja);
            $_SESSION['n_jug'] = $n_jug;
            array_push($jugador, $carta);
        }
        array_push($tapete, $jugador);
    }
    return $tapete;
}

function printCard($carta, $indice, $jugador) {
    echo '<div class="hueco"><a href="?jugador='.$jugador.'&carta='.$indice.'" name="printedCard"><img class="card" src="naipes2/'.$carta['numero'].'_'.$carta['palo'].'.gif"></a></div>';
}

function noCard() {
    echo '<div class="hueco"><img src="baraja/picas-1.gif"></div>';
}

// Función para validar si la carta seleccionada puede jugarse
function checkValidMove($carta, $tablero) {
    $numero = $carta['numero'];
    $palo = $carta['palo'];
    $fiveInBoard = false;
    if (isset($tablero[$palo])) {
        foreach ($tablero[$palo] as $cartaEnTablero) {
            if ($cartaEnTablero['numero'] == 5) {
                $fiveInBoard = true;
                break;
            }
        }
    }
    if (!$fiveInBoard) {
        return $numero == 5;
    }
    foreach ($tablero[$palo] as $cartaEnTablero) {
        if (abs($cartaEnTablero['numero'] - $numero) == 1) {
            return true;
        }
    }
    return false;
}

// Función para añadir una carta válida al tablero y guardar en sesión
function addCardToBoard($carta) {
    $tablero = $_SESSION['tablero'];
    if (checkValidMove($carta, $tablero)) {
        array_push($tablero[$carta['palo']], $carta);
        $_SESSION['tablero'] = $tablero;
        return true;
    }
    return false;
}

// Función para mostrar la alerta en caso de movimiento inválido
function showAlert($message) {
    echo '<div class="alert">' . $message . '</div>';
}

// Modificar processCardMove para validar y añadir cartas al tablero
function processCardMove($cards, $jugador) {
    $invalidMove = false;
    if (isset($_GET['carta']) && $_GET['jugador'] == $jugador) {
        $key = $_GET['carta'];
        if (isset($cards[$key])) {
            $card = $cards[$key];
            if (addCardToBoard($card)) { 
                unset($cards[$key]);
                $_SESSION['partida'][$jugador] = array_values($cards);
                if (empty($cards)) {
                    showAlert("¡Jugador " . ($jugador + 1) . " ha ganado!");
                    session_destroy();
                    exit;
                }
            } else {
                $invalidMove = true;
            }
        }
    }
    foreach ($cards as $key => $card) {
        printCard($card, $key, $jugador);
    }
    if ($invalidMove) {
        showAlert("Movimiento no válido. Solo puedes jugar un 5 o un número adyacente.");
    }
    return $cards;  // Devolvemos el array de cartas actualizado
}

// Función para mostrar el tablero en el juego
function printBoard() {
    if (isset($_SESSION['tablero'])) {
        $tablero = $_SESSION['tablero'];
        echo '<div class="tablero">';
        foreach ($tablero as $palo => $cartas) {
            echo '<div class="palo-section"><h2>' . $palo . '</h2><div class="cartas">';
            foreach ($cartas as $carta) {
                echo '<div class="hueco"><img class="card" src="naipes2/' . $carta['numero'] . '_' . $carta['palo'] . '.gif"></div>';
            }
            echo '</div></div>';
        }
        echo '</div>';
    } else {
        echo '<div class="tablero">Tablero vacío</div>';
    }
}

function pinta_juego($partida) {
    echo '<div class="mesa" style="flex">';
    foreach ($partida as $jugador => $cartas) {
        echo '<div class="player">';
        // Procesamos el movimiento de carta y actualizamos la sesión
        $cartas = processCardMove($cartas, $jugador);
        $_SESSION['partida'][$jugador] = $cartas; 
        echo '<h1>Jugador ' . ($jugador + 1) . '</h1>'; 
        echo '</div>';         
    }
    echo '</div>';

    printBoard(); // Mostrar el tablero actualizado después de procesar todas las cartas
}
// Inicializar la partida o recuperar del estado de sesión
if (!isset($_SESSION['partida'])) {
    $baraja = createDeck();
    $baraja_barajada = barajar($baraja);
    $partida = repartir($baraja_barajada, 4);
    $_SESSION['partida'] = $partida;
    $_SESSION['tablero'] = ['cors' => [], 'diamants' => [], 'piques' => [], 'trebols' => []];
} else {
    $partida = $_SESSION['partida'];
    $tablero = $_SESSION['tablero'];
}

// Mostrar el tablero y el estado del juego
pinta_juego($partida);
?>


</body>
</html>