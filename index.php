<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Middleware\BodyParsingMiddleware;

use Slim\Factory\AppFactory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require __DIR__ . '/vendor/autoload.php';
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$key = 'ïOÖbÈ3~_Äijb¥d-ýÇ£Hf¿@xyLcP÷@';

$authMiddleware = function($request,$handler)use ($key){
    $authMiddleware = $request->getHeader('Authorization');
    if(!empty($authMiddleware)){
        $token = $authMiddleware[0];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $request = $request->withAttribute('id',$decoded->id);
          return $response = $handler->handle($request);
        }else{
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['erreur' => 'token vide ou invalide']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
};

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});



$app->post('/addUser', function (Request $request, Response $response) {
    $err = array();
    require 'db.php';
    $data = $request->getParsedBody();


    if(empty($data['lastname'])){
    $err['lastname'] = 'nom de famille vide';
    }
    if(empty($data['firstname'])){
        $err['firstname'] = 'prénom vide';
    }
    if(empty($data['email'])){
        $err['email'] = 'adresse email vide';
    }
    if(empty($data['password'])){
        $err['password'] = 'mot de passe vide';
    }

    if(empty($err)){

        $passwordhash = password_hash($data['password'],PASSWORD_DEFAULT);
        $query = 'INSERT INTO `users` (`firstname`,`lastname`,`email`,`password`) VALUES(?,?,?,?)';
        $queryexec = $database->prepare($query);
        $queryexec->bindValue(1, $data['firstname'] ,PDO::PARAM_STR);
        $queryexec->bindValue(2, $data['lastname'] ,PDO::PARAM_STR);
        $queryexec->bindValue(3, $data['email'] ,PDO::PARAM_STR);
        $queryexec->bindValue(4, $passwordhash ,PDO::PARAM_STR);
        $queryexec->execute();

        $response->getBody()->write(json_encode(['valid' => 'ok']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);



    }else{
    $response->getBody()->write(json_encode(['erreur' => $err]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

});


$app->post('/login', function (Request $request, Response $response)use ($key) {
    $data = $request->getParsedBody();
    require 'db.php';
    $query = 'SELECT `id`,`password` FROM `users` WHERE `email` = ?';
    $queryexec = $database->prepare($query);
    $queryexec->bindValue(1, $data['email'] ,PDO::PARAM_STR);
    $queryexec->execute();
    $res = $queryexec->fetchAll();
    if(empty($res)){
        $response->getBody()->write(json_encode(['erreur' => 'utilisateur non trouvé']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    if(password_verify($data['password'],$res[0]['password'])){

        $payload = [
            'iat' => time(),
            'exp' => time() + 1800,
            'id' => $res[0]['id']
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');
        $response->getBody()->write(json_encode(['valid' => 'Vous etes connecté', 'token' => $jwt]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    }else{
        $response->getBody()->write(json_encode(['erreur' => 'mauvais mot de passe ou mauvais mail']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
});

$app->get('/profil', function (Request $request, Response $response) {
    require 'db.php';
    $id = $request->getAttribute('id');
    $query = 'SELECT * FROM `users` WHERE `id` = ?';
    $queryexec = $database->prepare($query);
    $queryexec->bindValue(1, $id ,PDO::PARAM_INT);
    $queryexec->execute();
    $res = $queryexec->fetchAll();
    $response->getBody()->write(json_encode(['profil valid' => 'ok', 'data' => $res]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
})->add($authMiddleware);

$app->put('/updateProfil/{id}', function (Request $request, Response $response, array $args) {
    require 'db.php'; 

    $id = $args['id'];
    $data = $request->getParsedBody();

    $fields = [];
    $values = [];
    
    if (isset($data['email'])) {
        $fields[] = '`email` = ?';
        $values[] = $data['email'];
    }
    if (isset($data['lastname'])) {
        $fields[] = '`lastname` = ?';
        $values[] = $data['lastname'];
    }
    if (isset($data['firstname'])) {
        $fields[] = '`firstname` = ?';
        $values[] = $data['firstname'];
    }
    
    if (!empty($fields)) {
        $query = 'UPDATE `users` SET ' . implode(', ', $fields) . ' WHERE `id` = ?';
        $values[] = $id; 
        $queryexec = $database->prepare($query);
        
        foreach ($values as $index => $value) {
            $queryexec->bindValue($index + 1, $value);
        }
        
        $queryexec->execute();
    
        if ($queryexec->rowCount() > 0) {
            $response->getBody()->write(json_encode(['valid' => 'le profil a été mis à jour']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['message' => 'aucun changement ou echec de la mise à jour']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(304);
        }
    } else {
        // Aucun champ à mettre à jour
        $response->getBody()->write(json_encode(['message' => 'aucune donnée fournie pour la mise à jour']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
})->add($authMiddleware);



$app->post('/addTraining', function (Request $request, Response $response) {
    require 'db.php';
    $data = $request->getParsedBody();
    $sql = "INSERT INTO trainings (date, activity_name, time, comment) VALUES (:date, :activity_name, :time, :comment)";
    $stmt = $database->prepare($sql);
    $stmt->execute([
        'date' => $data['date'],
        'activity_name' => $data['activity_name'],
        'time' => $data['time'],
        'comment' => $data['comment']
    ]);
    $response->getBody()->write(json_encode(['valid' => 'le training a été ajouté avec succès']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
})->add($authMiddleware);

$app->delete('/trainings/{id}', function (Request $request, Response $response, array $args) {
    require 'db.php';
    $sql = "DELETE FROM trainings WHERE id = :id";
    $stmt = $database->prepare($sql);
    $stmt->execute(['id' => $args['id']]);
    $response->getBody()->write(json_encode(['valid' => 'le training a été supprimé avec succès']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
})->add($authMiddleware);

$app->put('/trainings/{id}', function (Request $request, Response $response, array $args) {
    require 'db.php';
    $data = $request->getParsedBody();
    $sql = "UPDATE trainings SET date = :date, activity_name = :activity_name, time = :time, comment = :comment WHERE id = :id";
    $stmt = $database->prepare($sql);

  
    $params = [
        ':id' => $args['id'],
        ':date' => $data['date'] ?? null,
        ':activity_name' => $data['activity_name'] ?? null,
        ':time' => $data['time'] ?? null,
        ':comment' => $data['comment'] ?? null
    ];

    $stmt->execute($params);
    $response->getBody()->write(json_encode(['valid' => 'Le training a été modifié avec succès']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
})->add($authMiddleware);


$app->get('/getTrainings', function (Request $request, Response $response) {
    require 'db.php';
    $sql = "SELECT * FROM trainings";
    $stmt = $database->query($sql);
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);


    if(empty($trainings)){
        $response->getBody()->write(json_encode(['erreur' => 'aucun training trouvé']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    $response->getBody()->write(json_encode(['data' => $trainings]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
})->add($authMiddleware);



$app->run();
