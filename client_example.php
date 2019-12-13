<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

require_once 'client_sdk.php';



//////////////////////////////////////////////
//                                          //
// Example of client-side exceptions/errors //
//                                          //
//////////////////////////////////////////////



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
        test('gera Error');
    }catch(TypeError $e){
        handleWithWhoops($e);
    }
  }
}

main();
