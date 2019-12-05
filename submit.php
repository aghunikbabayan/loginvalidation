<?php
require_once ('functions.php');

//if user tried 10 times already redirect to blocked page
check_if_user_can_login();

session_start();

// validate token
$token = isset($_SESSION['csrf']) ? $_SESSION['csrf'] : "";

//check if request comes from our index page, otherwise blocks bots
if ( $token && $_POST['token'] === $token ) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    require_once ('inc/LoginClass.php');
    $loginclass = new Login();

    $json_response = $loginclass->login_user( $username, $password );

    $response = json_decode($json_response);

    //if user data is correct, function returns true
    if( $response->success ) {

        echo $response->message;
        header( "refresh:3;url=dashboard.php" );
    } else{

        echo $response->message;
        header( "refresh:3;url=index.php" );
    }

    //delete csrf token
    unset($_SESSION['csrf']);
} else {

    die('Silence golden');
}

session_write_close();