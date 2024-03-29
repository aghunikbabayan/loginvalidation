<?php
require_once ('functions.php');

//if user tried 10 times already redirect to blocked page
check_if_user_can_login();


    session_start();
    //check if csrf token is in session storage
    $token = isset($_SESSION['csrf']) ? $_SESSION['csrf'] : "";

    if ( ! $token )
    {
        //generate random token for csrf
        $token = md5(uniqid());
        $_SESSION['csrf']= $token;
    }

    session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>

<body>
    <div class="main-content">
        <form action="submit.php" method="post">
            <input type="text" name="username" placeholder="Please enter your username"/>
            <input type="password" name="password" placeholder="Please enter your password"/>
            <input type="hidden" name="token" value="<?php echo $token; ?>" />
            <input type="submit" value="Submit"/>
        </form>
    </div>
</body>
</html>