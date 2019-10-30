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
require_once '../../models/User.php';

// Initialise the data to be collected from the client
$username = "";
$password = "";

// Initialise the required classes
$database = new Database();
$conn = $database->getConnection();
$user = new User($conn);

// Initialise the error and response variable
$errors = array();
$success = array();

// Collect the data from the client
$data = json_decode(file_get_contents("php://input"));
$username = isset($data->username) ? $data->username : "";
$password = isset($data->password) ? $data->password : "";

// Verify the data that is been entered
if (empty($username)) {
    $errors['username'][] = "The username field is required";
} else {
    if (strlen($username) < 6) {
        $errors['username'][] = "The username must be at least 6 characters";
    }
}

if ($user->username_exists($username)) {
    $errors['username'][] = "The username has already been taken";
}

if (empty($password)) {
    $errors['password'][] = "The password field is required";
} else {
    if (strlen($password) < 8) {
        $errors['password'][] = "The password must be at least 8 characters";
    }
}

if (count($errors) > 0) {
    // There are errors in the data supplied

    // set response code 422 - Unprocessable entity
    http_response_code(422);

    // Send out the response
    echo json_encode($errors);
} else {
    // The data supplied are okay, Process the data

    $user->username = $username;
    $user->password = $password;

    if ($user->register()) {
        // The user has been successfully registered

        // set response code 201 - Resource is created successfully
        http_response_code(201);

        // Prepare the response
        $success['message'] = "User was successfully registered.";

        // Send out the response
        echo json_encode($success);
    } else {
        // An error occurred while registering the user

        // set response code 503 - Service Unavailable
        http_response_code(503);

        // Prepare the response
        $errors['error'] = "Service unavailable, try again later";

        // Send out the response
        echo json_encode($errors);
    }
}
