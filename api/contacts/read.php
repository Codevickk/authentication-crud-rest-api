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

// Initialise the required classes
$database = new Database();
$conn = $database->getConnection();
$token = new Token();

// Initialise the error and response variable
$errors = array();
$success = array();

// Collect the data from the client
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->token) ? $data->token : " ";

$secret_key = $token->secret_key();

if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
       
        // Access is granted. Add code of the operation here
        $user_id = $decoded->data->user_id;
 
        // set response code
        http_response_code(200);
        
        // Process the request
        $contact = new Contact($conn, $user_id);
        $stmt = $contact->read();
        $count = $stmt->rowCount();

        // Regenerate JWT
        $issuer_claim = $token->issuer_claim();
        $audience_claim = $token->audience_claim();
        $issuedat_claim = $token->issuedat_claim();
        $notbefore_claim = $token->notbefore_claim();
        $expire_claim = $token->expire_claim();

        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
            "id" => $user_id,
            )
        );

        $jwt = JWT::encode($token, $secret_key);

        $success['count'] = $count;
        $success['contacts'] = array();
        
        // Fetch the contacts available
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $c = array(
                    "contact_id" => $contact_id,
                    "name" => $name,
                    "email" => $email,
                    "phone_number" => $phone_number
            );

            array_push($success['contacts'], $c);
        }

        // Send out the response
        echo json_encode($success);
    } catch (Exception $e) {
        http_response_code(401);
        // Prepare the respond and send
        $errors['error'] = $e->getMessage();

        echo(json_encode($errors));
    }
}
