<?php

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Load up the required files
require_once '../../config/Database.php';
require_once '../../config/Token.php';
require_once '../../models/Contact.php';

require_once '../../vendor/autoload.php';


use \Firebase\JWT\JWT;

// Initialise the data to be collected from the client
$jwt = "";
$contact_id = "";

// Initialise the required classes
$database = new Database();
$conn = $database->getConnection();
$token = new Token();

// Initialise the error and response variable
$errors = array();
$success = array();

// Collect the data from the client
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->token) ? $data->token : "";
$contact_id = isset($data->contact_id) ? $data->contact_id : "";
 
$secret_key = $token->secret_key();

if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
       
        // Access is granted. Add code of the operation here
        $user_id = $decoded->data->user_id;

        
        // Process the request
        $contact = new Contact($conn, $user_id);
        $contact->contact_id = $contact_id;
        if ($contact->contact_exists()) {
            // set response code - Okay
            http_response_code(200);

            $stmt = $contact->readOne();
            // $response['jwt'] = $jwt;

        
            // Fetch the contact

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $c = array(
                    "contact_id" => $contact_id,
                    "name" => $name,
                    "email" => $email,
                    "phone_number" => $phone_number
            );
            }

            $success['contact'] = $c;

            // Send out the response
            echo json_encode($success['contact']);
        } else {
            // The contact is not available

            // Set response code 400 - Bad request

            http_response_code(400);
            // Prepare a response

            $errors['error'] = "This contact is not available";

            // Send out the response
            echo(json_encode($errors));
        }
    } catch (Exception $e) {
        http_response_code(401);
        // Prepare the respond and send
        $errors['error'] = $e->getMessage();

        echo(json_encode($errors));
    }
}
