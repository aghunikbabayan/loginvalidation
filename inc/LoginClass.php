<?php
class Login{

    //set time to allow login after, in seconds
    private static $allowedtime = 30;

    public function login_user( $username, $password )
    {
        $cannotlogin = $this->cannotlogin();
        if( $cannotlogin ) return $cannotlogin;

        if( $username == 'admin' && $this->password_validate( $password ) ) {

            return json_encode([
                'success' => true,
                'message' => 'You are successfully logged in'
            ]);
        }
        else{
            //if login and password wrong, record unsuccessful attempt
            $this->addFailedLoginAttempt();

            return json_encode([
                'success' => false,
                'message' => 'The username or password you specified are not correct !'
            ]);
        }

    }

    private function cannotlogin()
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $json_data = file_get_contents( './data.json', true);
        $data_rows = json_decode( $json_data );

        //check if data.json isnot empty and there is row with user ip
        if( $data_rows && isset($data_rows->$user_ip) )
        {
            $user_row            = $data_rows->$user_ip;
            $user_attempts_count = $user_row->attempt_count;
            $last_attempt        = $user_row->last_attempt;

            return $this->handle_attempts_count($user_attempts_count, $last_attempt);
        }

        return false;
    }

    //check if user reach blocked limit or allowed to login now
    private function handle_attempts_count( $count, $last_attempt ) {

        if( $count < 5 )
        {
          return false;
        }
        elseif( $count > 5 && $count < 10 )
        {
            $to_time = strtotime("now");
            $from_time = strtotime( $last_attempt );
            $diff = $to_time - $from_time;

            if($diff < self::$allowedtime)
            {
                return json_encode([
                    'success' => false,
                    'message' => 'You can try to login after '.self::$allowedtime.' seconds'
                ]);
            }
        } else{

            return json_encode([
                'success' => false,
                'message' => 'You are blocked and can not login anymore'
            ]);
        }
    }

    //get passwords from github list and check with user input
    private function password_validate( $password )
    {
        $curl = curl_init('https://raw.githubusercontent.com/danielmiessler/SecLists/master/Passwords/Common-Credentials/10-million-password-list-top-10000.txt');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $page = curl_exec($curl);

        $passwords_in_array = preg_split('/\r\n|\r|\n/', $page);
        return in_array($password, $passwords_in_array) ? true : false;
    }

    private function addFailedLoginAttempt()
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $json_data = file_get_contents( './data.json', true);

        $data_rows = ($json_data != '') ? json_decode($json_data) : new StdClass;
        $attempts_count = 1;

        if( ! isset( $data_rows->$user_ip ) ) {

            $data_rows->{$user_ip} = [
                'attempt_count' => $attempts_count,
                'last_attempt'  => date('d-m-Y h:i:s')
            ];
        }
        else{
            $attempts_count = $data_rows->{$user_ip}->attempt_count + 1;
            $data_rows->{$user_ip} = [
                'attempt_count' => $attempts_count,
                'last_attempt'  => date('d-m-Y H:i:s')
            ];
        }

        $new_json_data_rows = json_encode($data_rows);
        file_put_contents('./data.json', $new_json_data_rows);

        return $attempts_count;
    }
}