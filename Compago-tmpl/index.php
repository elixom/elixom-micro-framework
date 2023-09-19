<?php
/**
 * @version 20131028.11
 */


if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'localhost')){
    //on LOCAL ONLY
    error_log(str_repeat(' ', 255));
    error_log('===========');
    error_log('===========');
}
    
    
chdir(__DIR__);
if (file_exists('./config.php')){
    require_once realpath("config.php");
}
if (!isset($CFG)){
    die('Unable to locate configuration. Please verify the server setup.');
}
try{
    foreach (array('dbhost','dbname','dbuser','dbpass') as $key){
        if (empty($CFG->$key))
            die("The \$CFG->{$key} variable must be set!");
    }

    define('INCLUDE_DIR', __DIR__ . DIRECTORY_SEPARATOR .'.includes');
    define('CORE_DIR', __DIR__ . DIRECTORY_SEPARATOR .'.core');
    define('APP_DIR', __DIR__ . DIRECTORY_SEPARATOR .'.app');
    
    if (!is_dir(INCLUDE_DIR)){
        die('Unable to locate system libraries.');
    }
    if (!is_dir(APP_DIR)){
        die('Unable to locate app files.');
    }
    (include_once(INCLUDE_DIR . DIRECTORY_SEPARATOR .'Compago'  . DIRECTORY_SEPARATOR  . 'autoload.php')) || die('Unable to include Compago.');
    (include_once(INCLUDE_DIR . DIRECTORY_SEPARATOR . 'SAP/SAP.php')) || die('Unable to include SAP.');
    
} catch (Exception $e){
    echo $e->getMessage();
    die('Fatal error during bootstrap phase.');
}
try{
    (include_once(APP_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php')) || die('Unable to include bootstrap.');
    
    SAP::setLogLevel('DEVELOPER');
    
    $app = app();
    $app::startSession();
    $app->run();
    
} catch (Exception $e){
    echo $e->getMessage();
}
