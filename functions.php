<?php
function check_if_user_can_login()
{
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $json_data = file_get_contents( 'data.json', true);
    $data_rows = json_decode( $json_data );

    //check if data.json isnot empty and there is row with user ip
    if( $data_rows && isset($data_rows->$user_ip) )
    {
        $user_row = $data_rows->$user_ip;
        $user_attempts_count = $user_row->attempt_count;

        if($user_attempts_count >= 10){

            header('Location: blocked.php');
        }
    }
}