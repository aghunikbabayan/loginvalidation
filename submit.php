<?php
session_start();

// validate token
$token = isset($_SESSION['csrf']) ? $_SESSION['csrf'] : "";

if ( $token && $_POST['token'] === $token ) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    require_once ('inc/LoginClass.php');
    $loginclass = new Login();

    $json_response = $loginclass->login_user( $username, $password );

    $response = json_decode($json_response);

    if( $response->success ) {

        echo $response->message;
        header( "refresh:3;url=dashboard.php" );
    } else{

        echo $response->message;
        header( "refresh:3;url=index.php" );
    }

    unset($_SESSION['csrf']);
} else {

    die('Silence golden');
}

session_write_close();