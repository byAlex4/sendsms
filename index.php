<?php
require 'vendor/autoload.php';

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clientes_num";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$clientes = [];
$sql = "SELECT * FROM clientes";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}

// Credenciales de Vonage
$basic = new \Vonage\Client\Credentials\Basic("005742cd", "VT6lHOLqXgNQj3Jg");
$client = new \Vonage\Client($basic);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['clientes']) && is_array($_POST['clientes'])) {
        $selected_ids = $_POST['clientes'];
        $message_body = $_POST['message'];

        foreach ($selected_ids as $id) {
            foreach ($clientes as $cliente) {
                if ($cliente['id'] == $id) {
                    $number = $cliente['numero'];
                    $response = $client->sms()->send(
                        new \Vonage\SMS\Message\SMS($number, 'RedDoctors', $message_body)
                    );

                    $message = $response->current();

                    if ($message->getStatus() == 0) {
                        echo "The message was sent successfully to $number<br>";
                    } else {
                        echo "The message to $number failed with status: " . $message->getStatus() . "<br>";
                    }
                }
            }
        }
    } else {
        echo "Error: No se ha seleccionado ningún cliente.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Enviar Mensajes a Clientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333333;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }

        textarea {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            background-color: #f9f9f9;
            color: #333333;
            resize: none;
        }

        textarea:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Enviar Mensajes a Clientes</h1>
        <form method="post" action="">
            <label for="clientes">Selecciona los clientes:</label><br>
            <?php foreach ($clientes as $cliente): ?>
                <input type="checkbox" name="clientes[]"
                    value="<?php echo $cliente['id']; ?>"><?php echo $cliente['nombre']; ?><br>
            <?php endforeach; ?><br>

            <label for="message">Mensaje:</label>
            <textarea name="message" id="message" rows="5" cols="40" placeholder="Escribe tu mensaje aquí..."
                required></textarea><br><br>

            <input type="submit" value="Enviar">
        </form>
    </div>
</body>

</html>