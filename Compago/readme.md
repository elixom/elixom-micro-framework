COMPAGO framework

==SETUP==
- Start a new project in a folder
- Add a index.php file
- Add a .htaccess file
- Add a .app folder, all app specific files go here
- Add a .includes folder, the COmpago libary and all vendor/third party libraries go here
- Add a app/bootstrap.php file

==INDEX==
- in the .htaccess file redirect all requests to the index file
`
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_URI} !index.php?$
RewriteRule ^(.+)$ index.php [NC]

`

-Index index.php file
  - add these lines 
    define('APP_DIR', __DIR__ . DIRECTORY_SEPARATOR .'.app');
    define('INCLUDE_DIR', __DIR__ . DIRECTORY_SEPARATOR .'.includes');
  - then load any extenal config files
  - add this line
    (include_once(INCLUDE_DIR . DIRECTORY_SEPARATOR .'Compago'  . DIRECTORY_SEPARATOR  . 'autoload.php')) || die('Unable to include Compago.');
  - include any other necessary autoloaders
  - add this line
    (include_once(CORE_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php')) || die('Unable to include bootstrap.');
  - if routes are defined in a separate file this is a google place to include them
  - add the following
  `
  $app = app();
    $app::startSession();
    $app->run();
  `



==BOOTSRAP.PHP==
- in the bootrap file efine a app() function which, creates, set up and  return a new  (static) instance of a Compago\App
- several other commmon utility functions can be defined in this file if needed
  - db()
  - asset()
  - env()
  - session()


==LOCAL COMPAGO==
use the following from the project route to create a symlink to COmpago
mklink /D ".includes\Compago" "Z:\elixom-micro-framework\Compago"
