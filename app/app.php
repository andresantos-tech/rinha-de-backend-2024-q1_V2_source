<?php

require __DIR__ . '/vendor/autoload.php';

use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\UnprocessableEntityException;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;

$worker = Worker::create();
$factory = new Psr17Factory();
$psr7 = new PSR7Worker($worker, $factory,$factory, $factory);

$db = getDbConnection();

// https://github.com/thephpleague/route
// https://route.thephpleague.com/5.x/strategies/
    // https://github.com/thephpleague/route/issues/164
$router = (new Router())->setStrategy(new JsonStrategy($factory));

$router->get(
    '/clientes/{id:\d}/extrato',
    static function(ServerRequestInterface $request, array $args) use(&$db): array {
        $id = (int) $args['id'];
        $account = getUser($db, $id);

        if (!$account) {
            throw new NotFoundException();
        }

        return [
            'saldo' => [
                'total' => $account['current_balance'],
                'data_extrato' => date('c'),
                'limite' => $account['limit_amount'],
            ],
            'ultimas_transacoes' => getRecentTransactions($db, $id),
        ];
    }
);

$router->post(
    '/clientes/{id:\d}/transacoes',
    static function(ServerRequestInterface $request, array $args) use(&$db): array {
        $id = (int) $args['id'];
        $account = getUser($db, $id);

        if (!$account) {
            throw new NotFoundException();
        }


    }
);

while (true) {
    try {
        $request = $psr7->waitRequest();
        if ($request === null) {
            break;
        }
    } catch (\Throwable $e) {
        $psr7->respond(new Response(400));
        continue;
    }

    try {
        $psr7->respond($router->dispatch($request));
    } catch (NotFoundException $e) {
        $psr7->respond(new Response(404));
    } catch (UnprocessableEntityException $e) {
        $psr7->respond(new Response(422));
    } catch (\Throwable $e) {
        $psr7->respond(new Response(500, [], 'Something Went Wrong!'));
        $psr7->getWorker()->error((string) $e);
    }
}