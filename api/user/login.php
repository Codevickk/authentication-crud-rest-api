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
require_once '../../models/User.php';
require_once '../../vendor/autoload.php';

use \Firebase\JWT\JWT;

// Initialise the data to be collected from the client
$username = "";
$password = "";

// Initialise the required classes
$database = new Database();
$conn = $database->getConnection();
$user = new User($conn);
$token = new Token();

// Initialise the error and response variable
$errors = array();
$success = array();

// Collect the data from the client
$data = json_decode(file_get_contents("php://input"));
$username = isset($data->username) ? $data->username : "";
$password = isset($data->password) ? $data->password : "";

if (empty($username)) {
    $errors['username'][] = "The username field is required";
}

if (empty($password)) {
    $errors['password'][] = "The password field is required";
}

if (count($errors) > 0) {
    // the data entered contains errors

    // set response code 422 - Unprocessable entity
    http_response_code(422);

    // Send out the response
    echo json_encode($errors);
} else {
    // the data entered are correct, process the data
    $user->username = $username;
    $user->password = $password;

    if ($user->checkLogin()) {
        // The login details are correct
        $user_id = $user->user_id;

        $issuer_claim = $token->issuer_claim();
        $audience_claim = $token->audience_claim();
        $issuedat_claim = $token->issuedat_claim();
        $notbefore_claim = $token->notbefore_claim();
        $expire_claim = $token->expire_claim();
        $secret_key = $token->secret_key();
        
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "user_id" => $user_id,
        ));

        // Set response code 200 - Okay
        http_response_code(200);

        // Generate the web token
        $jwt = JWT::encode($token, $secret_key);

        // Prepare the response
        $success['token'] = $jwt;
        echo json_encode($success);
    } else {
        // The login details are incorrect

        // set response code 401 - Unauthorised
        http_response_code(401);
        
        // Prepare the response
        $errors['error'] = "Incorrect login credentials.";

        // Send out the response
        echo json_encode($errors);
    }
}
