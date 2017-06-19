<?php
if(!defined('OKNO') || OKNO != true) { exit; }

/**
 * OKNO-notify
 * v.1.0
 * @author Kamil Żywolewski <k.zywolewski@dhcorp.eu>
 * @link https://github.com/kamzyw/OKNO-notify
 */


$config = array(
    'hash' => array(
	'enabled' => true,
	'value' => 'okno'
    ),
    'email' => array(
        'to' => 'email@example.com',
        'from' => 'email@example.com'
    ),

    'okno' => array(
        'login' => 'login',
        'password' => 'hasło',
        'history_file' => 'dane.json'
    )
);