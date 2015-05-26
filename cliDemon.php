<?php
/*
	Copyright © Oleg Bogdanov
	E-mail: mail@olegbogdanov.ru
	Developing: Oleg Bogdanov
*/

$mc = explode('|', file_get_contents(__DIR__.'/cli.lock'));

// для предотвращения досрочного завершения скрипта, запускаем цикл
for($i=0;;$i++){
    sleep(1);

    Ping($mc[0].'://'.$mc[1].'/cron.php'); // пинг CRON сервиса


    // обновляем метку
    $c = [ $mc[0], $mc[1],time()];
    file_put_contents(__DIR__.'/cli.lock', implode('|', $c), LOCK_EX);
}


function Ping($url){
    $ch = curl_init();
    // GET запрос указывается в строке URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Cli Bot');
    $data = curl_exec($ch);
    curl_close($ch);
}
