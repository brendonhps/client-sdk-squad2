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
    $response = file_get_contents('http://127.0.0.1:8000/api/login', FALSE, $context);

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
    $response = file_get_contents('http://127.0.0.1:8000/api/logs?token=' . $authToken, FALSE, $context);

    // Check for errors
    if($response === FALSE){
        die('Error');
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


 // Auxiliar Function
function inverse($x) {
    if (!$x) {
        throw new Exception('Division by zero.');
    }
    return 1/$x;
}

function test($x):int {
    return $x;
}

//////////////////////////////////////////////
//                                          //
// Example of client-side exceptions/errors //
//                                          //
//////////////////////////////////////////////
function main() {



 for($i = 0; $i < 5 ; ++$i) {
    try {
        inverse(0) . "\n";
    
    } catch (Exception $e) {
        gettype($e);
        handleWithWhoops($e);
    }

    try {
        throw new UnderflowException('Underflow exception in ahritmetic operation');
    }catch (UnderflowException $e) {
       
        handleWithWhoops($e);
    }

    try {
        throw new InvalidArgumentException('Invalid Arguments on the function');
    }catch (InvalidArgumentException $e) {
       
        handleWithWhoops($e);
    }

    try {
        throw new UnexpectedValueException('Unexpected Value');
    }catch (UnexpectedValueException $e) {
        
        handleWithWhoops($e);
    }

    try {
        test('ss');
    }catch(TypeError $e){
        handleWithWhoops($e);
    }
    }
}

main();
