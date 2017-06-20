<?php

/**
 * OKNO-notify
 * v.1.0
 * @author Kamil Żywolewski <k.zywolewski@dhcorp.eu>
 * @link http://okno.zywy.me/
 */

define('OKNO', true);

require_once 'config.php';

if($config['hash']['enabled'] == true && (empty($_GET['hash']) || $_GET['hash'] !== $config['hash']['value'])){
	exit;
}

class OknoBrowser
{
    const SERVER = 'https://red.okno.pw.edu.pl/witryna/';
    private $login;
    private $password;
    private $historyFile;

    public function __construct($config)
    {
        $this->login = $config['login'];
        $this->password = $config['password'];
        $this->historyFile = $config['history_file'];
    }

    public function clean()
    {
        unlink('cookies.txt');
    }

    public function doLogin()
    {
        $post = array(
            'okno_login' => $this->login,
            'okno_passwd' => $this->password
        );

        $this->post('check_login.php', $post);
    }

    public function getGrades()
    {
        $data = $this->get('oceny_zestawienie.php?ord=wystawil_data&desc');

        if (empty($data)) return false;

        preg_match_all("#<tbody id=\"grdef_table\">(.*?)</tbody>#si", $data, $grade_table);
        preg_match_all("#<tr>(.*?)</tr>#si", $grade_table[1][1], $subjects);

        $grades = array();
	
        foreach ($subjects[1] as $subject) {
            preg_match_all("#<td>(.*?)</td>#si", $subject, $subject_data);
		
            if (!empty($subject_data[1][4])) {
                $key = null;

                //$name = $subject_data[1][0] . $subject_data[1][1] . $subject_data[1][2];
                $nameHash = md5($subject_data[1][0] . $subject_data[1][1] . $subject_data[1][2]);

                $grades[$nameHash] = array(
                    'przedmiot' => $subject_data[1][0],
                    'edycja' => $subject_data[1][1],
                    'ocena' => $subject_data[1][4],
                    'termin' => $subject_data[1][2],
                    'data' => $subject_data[1][6],
                    'wystawil' => $subject_data[1][5],
                    'ects' => $subject_data[1][3]
                );
                
            }
        }
	
        return $grades;
    }

    public function loadHistory()
    {
        $data = @file_get_contents($this->historyFile);
        if ($data === false) return array();

        return json_decode($data, true);
    }

    public function saveHistory($history)
    {
        $data = json_encode($history);

        return file_put_contents($this->historyFile, $data) !== false;
    }

    public function compareGrades($grades, $history)
    {
        foreach ($grades as $hash => &$grade) {
            if (!empty($history[$hash])) { // grade already exists in history
                $subject = $history[$hash];

                if ($subject['ocena'] != $grade['ocena']) {
                    $grade['changed'] = true;
                    $grade['poprzednia_ocena'] = $subject['ocena'];
                }
            } else {
                $grade['changed'] = true;
                $grade['poprzednia_ocena'] = null;
            }
        }

        return $grades;
    }

    private function post($url, $data)
    {
        $post_string = http_build_query($data);

        $curl_connection = curl_init(self::SERVER . $url);
        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_connection, CURLOPT_COOKIEJAR, 'cookies.txt');
        curl_setopt($curl_connection, CURLOPT_COOKIEFILE, 'cookies.txt');
        curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

        $result = curl_exec($curl_connection);

        if (DEBUG) {
            print_r(curl_getinfo($curl_connection));
            echo curl_errno($curl_connection) . '-' . curl_error($curl_connection);
        }

        curl_close($curl_connection);

        return $result;
    }

    private function get($url)
    {
        $curl_connection = curl_init(self::SERVER . $url);
        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_connection, CURLOPT_COOKIEJAR, 'cookies.txt');
        curl_setopt($curl_connection, CURLOPT_COOKIEFILE, 'cookies.txt');

        $result = curl_exec($curl_connection);

        if (DEBUG) {
            print_r(curl_getinfo($curl_connection));
            echo curl_errno($curl_connection) . '-' . curl_error($curl_connection);
        }

        curl_close($curl_connection);

        return $result;
    }
}

$okno = new OknoBrowser($config['okno']);
$okno->doLogin();

$grades = $okno->getGrades();
$history = $okno->loadHistory();
$changes = $okno->compareGrades($grades, $history);

$okno->saveHistory($grades);

$message = '';
$count_changes = 0;

foreach ($changes as $subject) {

    if ($subject['changed'] === true) {

        $message .= 'Przedmiot: <b>' . $subject['przedmiot'] . '</b> (Edycja: <i>' . $subject['edycja'] . '</i>)<br />
        Ocena: <b>' . $subject['ocena'] . '</b> - ' . $subject['termin'] . ' (' . $subject['ects'] . ' ECTS)<br />
        Wystawione: <b>' . $subject['data'] . '</b> przez <i>' . $subject['wystawil'] . '</i><br />';

        if (!empty($subject['poprzednia_ocena']))
            $message .= '[ZMIANA] Poprzednia ocena: ' . $subject['poprzednia_ocena'];

        $message .= '<br /><hr />';
        
        $count_changes++;
    }

}

$headers = 'MIME-Version: 1.0' . "\r\n" .
    'Content-type: text/html; charset=utf-8' . "\r\n" .
    'From: ' . $config['email']['from'] . "\r\n" .
    'Reply-To: ' . $config['email']['from'] . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$subject = 'Wykryto nowe oceny [' . $count_changes . ']! - Platforma Administracyjna OKNO';
if ($count_changes > 0) {
	$message .= '<i>Wysłano ' . date('d-m-Y'). ' o godz. ' . date('H:i:s') . ' przez <a href="https://github.com/kamzyw/OKNO-notify">OKNO-notify</a></i>';
    echo $subject . '<br />' . $message . '<br /><br />';  
    mail($config['email']['to'], $subject, $message, $headers);
	if (DEBUG) {
		echo 'wyslano';
	}
}

$okno->clean();
