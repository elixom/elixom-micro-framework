STRUCTURE OF AN APP
root\
    index.php
    .htaccess
    \assets
    \.includes
        \Compago
    \.core
        bootstrap.php
        routes.php
        \Controllers
        \Models
        \Views
        \templates
        \fragments



FILES

-index.php
    includes bootstrap
    runs app  ( app()->run();)
    

-bootstrap.php
    autoloads Compago
    autoloads any other libraries used by app
    loads configs
    create helper functions either by defining own 
     or by pulling Campago's function into the global space
     with php's "use function"; 
        app()
        router()
        session();
        abort()
        asset()
        url()
    

-routes.php
    included in the bootstrap file
    it adds all the routes to app