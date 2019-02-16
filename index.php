<?php

require_once __DIR__.'/vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig   = new Twig_Environment($loader);

$dataToRender = null;

if ($_GET['method'] && $_GET['channel']) {

    $channelName       = htmlspecialchars($_GET['channel']);       //CG5ARMCEA
    $apiMethodCallName = htmlspecialchars($_GET['method']);        //channels.history
    $url               = 'https://slack.com/api/';

    $requestParams = array(
        'token'   => '<token>',
        'channel' => $channelName,
        'pretty'  => 1,
    );

    $queryString = $url.$apiMethodCallName.'?'.urldecode(http_build_query($requestParams));
    $response    = @file_get_contents($queryString);

    if (false == $response) {
        $error = ' Server not available or something incorrect ';

        return new Exception($error);
    }

    $responseData = json_decode($response, true);
    $dataToRender = $responseData['messages'];
    $comments     = array();

    foreach ($dataToRender as $item) {
        if ($item['ts']) {
            $comments[$item['ts']] = $item;
        }
    }
}


try {
    echo $twig->render('index.html.twig', ['response' => $dataToRender, 'comments' => $comments, 'methodName' => "$apiMethodCallName", 'channelName' => $channelName]);
} catch (Exception $exception) {
    echo '<pre>Error with Twig template render '.$exception.'</pre>';
}