<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

require_once 'config.php';

// Init error configurantion
error_reporting(E_ALL);
ini_set('display_errors', '1');


function getToken():string {
    $loginData = array(
        'email' => MAIL,
        'password' => PW,    
    );
    
    // Create the context for the request
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($loginData)
        )
    ));

    // Send the request
    $response = file_get_contents('https://frozen-fortress-37712.herokuapp.com/api/login', FALSE, $context);

    // Check for errors
    if($response === FALSE){
        die('Error');
    }

    // Decode the response
    $responseData = json_decode($response, TRUE);
    
    return $responseData['token'];
}

function sendData($data) {

    $authToken = getToken();


    // Create the context for the request
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($data)
        )
    ));

    // Send the request
    $response = file_get_contents('https://frozen-fortress-37712.herokuapp.com/api/logs?token=' . $authToken, FALSE, $context);

    // Check for errors
    if($response === FALSE){
        die('Error');
    }else {
        $json_string = json_encode($data, JSON_PRETTY_PRINT);
        print_r($json_string . " foi enviado via HTTP POST para https://frozen-fortress-37712.herokuapp.com/api/logs \r\n");
        
    }

}

function discoverEventType ($event):String {
    if (is_a($event,'Exception')) {
        return 'warning';
    }
    elseif (is_a($event,'TypeError')) {
        return 'error';
    }
    else {
        return 'debug';
    }
}

function handleWithWhoops($event)
{

    $whoops = new \Whoops\Run;
    $whoops->allowQuit(false);
    $whoops->writeToOutput(false);
    $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
   
    $obj = $whoops->handleException($event); 
    
    $data = array();
    $data['ambiente'] = ENV;
    $data['detalhe'] = $event->getTraceAsString();
    $data['level'] = discoverEventType($event);
    $decode_data = json_decode($obj);

    foreach($decode_data as $key=>$value){
            
        $data['descricao'] = $value->message . ' in line ' . $value->line . ' on file ' . $value->file;
        $data['origem'] = $value->file;
        $data['titulo'] = $value->type . ' on ' . $value->file;
    }

    sendData($data);
}
