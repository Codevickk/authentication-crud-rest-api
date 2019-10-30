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
$name = "";
$email = "";
$phone_number = "";


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
$user_id = "";
$name = isset($data->name) ? $data->name : "";
$email = isset($data->email) ? $data->email : "";
$phone_number = isset($data->phone_number) ? $data->phone_number : "";

$secret_key = $token->secret_key();

if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

        // Access is granted. Add code of the operation here
        $user_id = $decoded->data->user_id;

        // Validate the user's data
        if (empty($name)) {
            $errors['name'][] = "The name field is required";
        }

        if (empty($email)) {
            $errors['email'][] = "The email field is required";
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'][] = "The email entered is invalid";
            }
        }

        if (empty($phone_number)) {
            $errors['phone_number'][] = "The phone number field is required";
        }

        if (count($errors) > 0) {
            // The data entered has errors

            // set response code 422 - Unprocessable entity
            http_response_code(422);

            // Send out the response
            echo(json_encode($errors));
        } else {
            // Process the user's data
            $contact = new Contact($conn, $user_id);
            $contact->name = $name;
            $contact->email = $email;
            $contact->phone_number = $phone_number;

            // Add the database into the contact list
            if ($contact->insert()) {
                // The contact has been successfully added

                // set response code 201 - Created
                http_response_code(201);

                // Prepare the response
                $success['message'] = "Contact was successfully added.";

                // Send out the response
                echo json_encode($success);
            } else {
                // An error occured while adding a new contact

                // set response code 503 - Service Unavailable
                http_response_code(503);

                // Prepare the response
                $errors['error'] = "Service unavailable, try again later";

                // Send out the response
                echo json_encode($errors);
            }
        }
    } catch (Exception $e) {
        // Set reponse code 401 - unauthorised
        http_response_code(401);

        // Prepare the respond and send
        $errors['error'] = $e->getMessage();

        echo(json_encode($errors));
    }
}
