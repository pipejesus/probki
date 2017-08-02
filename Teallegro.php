<?php

// Próbka 1: kawałek mini-aplikacji opartej o Slim PHP Framework

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Teallegro {
    
    public $settings;
    public $slimApp;
    public $slimContainer;
    
    public function __construct() {
        
        $this->settings = array(
            'displayErrorDetails' => true,
            'addContentLengthHeader' => false,
            'db' => array(
                'host' 		=> DB_HOST,
                'port' 		=> DB_PORT,
                'name' 		=> DB_NAME,
                'user' 		=> DB_USER,
                'pass' 		=> DB_PASS,
                'charset'	=> DB_CHARSET,
            ),
            'allegro' => array(
                'webapi_key'		=> ALLEGRO_WEBAPI_KEY,
                'webapi_user'		=> ALLEGRO_WEBAPI_USER,
                'webapi_password'	=> ALLEGRO_WEBAPI_PASSWORD
            ),
        );
        
        $this->slimApp = new Slim\App(array('settings' => $this->settings));
        $this->slimContainer = $this->slimApp->getContainer();
    }
    
    public function run() {
		
	$this->registerLogger();
	$this->registerDB();
	$this->registerAllegro();
	$this->registerDataAccessModel();
	$this->registerControllers();
	$this->registerMiddlewares();
	$this->registerRoutes();        
	$this->slimApp->run();
		
    }
    
    private function registerDB() {
		
        $this->slimContainer['db'] = function($c) {
			
		$database = new Medoo\Medoo( array(
			'database_type' => 'mysql',
			'database_name' => $c['settings']['db']['name'],
			'server' 		=> $c['settings']['db']['host'],
			'username' 		=> $c['settings']['db']['user'],
			'password' 		=> $c['settings']['db']['pass'],
			'charset' 		=> $c['settings']['db']['charset'],
			'port' 			=> $c['settings']['db']['port'],
		));			
		$database->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);            
		return $database;
		
        };
		
    }
    
    private function registerAllegro() {
		
	$this->slimContainer['allegro'] = function($c) {            
		$allegro = new Awrapper($this->slimContainer, $c['settings']['allegro']['webapi_key']);
            	return $allegro;
        };
		
    }
    
    private function registerLogger() {        
		
	$this->slimContainer['logger'] = function($c) {
		$logger = new \Monolog\Logger('Teallegro');
		$file_handler = new \Monolog\Handler\StreamHandler( TEAFRAMEWORK . "logs/app.log");
		$logger->pushHandler($file_handler);
		return $logger;
        };
		
    }
	
	private function registerDataAccessModel() {

		$this->slimContainer['dam'] = function($c) {
			$dataAccessModel = new DataAccessModel($this->slimContainer);
			return $dataAccessModel;
		};

	}
    
	private function registerControllers() {		

		$this->registerApiController();

	}
    
	private function registerApiController() {

		$this->slimContainer['ApiController'] = function($c) {            
			return new ApiController($this->slimContainer);
		};

	}
    
	private function registerRoutes() {        

		$this->slimApp->get( '/api/categories[/{parent_id:[0-9]+}]', \ApiController::class . ':getCategories' );
		$this->slimApp->get( '/api/items/scrape', \ApiController::class . ':scrapeItems' );
		$this->slimApp->get( '/api/items', \ApiController::class . ':getItems' );
		$this->slimApp->get( '/api/lastlogin', \ApiController::class . ':getLastLogin' );

	}
	
	private function registerMiddlewares() {
		
		$this->registerUnslashMiddleware();
		
	}
	
	private function registerUnslashMiddleware() {
		
		$this->slimApp->add(function (Request $request, Response $response, callable $next) {
			
			$uri = $request->getUri();
			$path = $uri->getPath();
			
			if ($path != '/' && substr($path, -1) == '/') {
				$uri = $uri->withPath(substr($path, 0, -1));
				
				if($request->getMethod() == 'GET') {
					return $response->withRedirect((string)$uri, 301);
				}
				else {
					return $next($request->withUri($uri), $response);
				}
			}
		
			return $next($request, $response);
		
		});
		
	}
		
}
