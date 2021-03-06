<?php
header("Access-Control-Allow-Origin: http://localhost:8080");
header('Access-Control-Max-Age: 86400'); // cache for 1 day
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");         
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");

require __DIR__ . '/vendor/autoload.php';

use App\Controller\AgendaController;
use JPF\App;
use JPF\Router\Router;
use JPF\Router\Request;
use JPF\Router\Response;
use App\Controller\Home;
use App\Controller\AuthController;

function ValidateToken($bearerToken, $response) {
    $auth = (new AuthController())->IsTokenExpired($bearerToken);
    if($auth) {
        $response->status(401)->toJSON([
            "message" => 'Unauthorized'
        ]);
        exit();
    } 

    return true;
}

function ErrorHandler($response, $controller){
    if(isset($controller['code'])) {
        $response->status($controller['code'])->toJSON([
            "message" => $controller['message']
        ]);
    } else {
        $response->toJSON($controller);
    }    
}

/* Simple route for example
Router::get('/api/example/([0-9]*)', function (Request $req, Response $res) {
    $res->toJSON([
        'test' =>  ['id' => $req->params[0]],
        'status' => 'ok'
    ]);
});

Routes that need authentication:
Add 
ValidateToken($req->getBearerToken(), $res);
*/

Router::get('/api', function (Request $req, Response $res) {
    //Just a message to show api is working
    (new Home())->index();
});

//Auth routes
Router::post('/api/auth/login', function (Request $req, Response $res) {
    $login = (new AuthController())->login($req->getJSON());

    ErrorHandler($res, $login);
});

Router::post('/api/auth/register', function (Request $req, Response $res) {
    $register = (new AuthController())->register($req->getJSON());

    ErrorHandler($res, $register);
});

Router::post('/api/auth/checktoken', function (Request $req, Response $res) {
    ValidateToken($req->getBearerToken(), $res);

    $res->toJSON([
        'message' => 'Authorized'
    ]);
});
//End Auth routes

//Agenda Routes
Router::get('/api/agendaPag/(.*[0-9].*)/(.*[0-9].*)', function (Request $req, Response $res) { //agendaPag/page/perpage
    ValidateToken($req->getBearerToken(), $res);

    $agenda = (new AgendaController())->getUserContacts($req->params[0], $req->params[1], $req->getBearerToken());

    ErrorHandler($res, $agenda);
});

Router::get('/api/agenda/(.*[0-9].*)', function (Request $req, Response $res) { //Must contain ID after agenda/ (Regex: .*[0-9].*)
    ValidateToken($req->getBearerToken(), $res);

    $agenda = (new AgendaController())->getUserContactsByID($req->params[0], $req->getBearerToken()); 

    ErrorHandler($res, $agenda);
});

Router::post('/api/agenda', function (Request $req, Response $res) {
    ValidateToken($req->getBearerToken(), $res);

    $agenda = (new AgendaController())->insert($req->getJSON(), $req->getBearerToken()); 

    ErrorHandler($res, $agenda);
});

Router::put('/api/agenda', function (Request $req, Response $res) {
    ValidateToken($req->getBearerToken(), $res);

    $agenda = (new AgendaController())->update($req->getJSON(), $req->getBearerToken()); 

    ErrorHandler($res, $agenda);
});

Router::delete('/api/agenda/(.*[0-9].*)', function (Request $req, Response $res) {
    ValidateToken($req->getBearerToken(), $res);

    $agenda = (new AgendaController())->delete($req->params[0], $req->getBearerToken()); 

    ErrorHandler($res, $agenda);
});
//End Agenda Routes

App::run();