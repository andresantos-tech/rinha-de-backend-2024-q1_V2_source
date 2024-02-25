<?php

function getDbConnection(): PDO {
    $host = 'rinha_v2_postgres';
    $database = 'postgres';
    $port = '5432';
    $username = 'postgres';
    $password = 'postgres';
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;";

    try {
        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ]
        );
    } catch (\Exception $e) {
        usleep(100000);
        return getDbConnection();
    }
}

function getUser(PDO $db, int $id):? array
{
    $query = $db->prepare('SELECT * FROM accounts WHERE id = ?');
    $query->execute([ $id ]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $query = null;

    return $result ?: null;
}

function createTransaction(PDO $db, int $id, array $values)
{
    $query = $db->prepare("
        SELECT status, limit_amount, current_balance 
        FROM createtransaction(?, ?, ?, ?) 
            as (status INTEGER, limit_amount INTEGER, current_balance INTEGER)
    ");
    $query->execute([
        $id,
        $values['valor'],
        $values['tipo'],
        $values['descricao'],
    ]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $query = null;

    return [
        'status' => $result['status'],
        'limit_amount' => $result['limit_amount'],
        'current_balance' => $result['current_balance'],
    ];
}

function getRecentTransactions(PDO $db, int $id, int $limit = 10): array
{
    $query = $db->prepare('
            SELECT
                amount AS valor,
                transaction_type AS tipo,
                description AS descricao,
                date AS realizada_em
            FROM transactions
            WHERE account_id = ?
            ORDER BY id DESC
            LIMIT ?
        ');

    $query->execute([
        $id,
        $limit
    ]);

    $result = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $result[] = $row;
    }
    $query = null;

    return $result ?: [];
}

function validateInputTransaction(string $payload):? array
{
    $payload = json_decode($payload, true);
    $return = [];

    $return['valor'] = filter_var($payload['valor'] ?? null, FILTER_VALIDATE_INT);
    $return['tipo'] = in_array(($payload['tipo'] ?? null), ['c', 'd']) ? $payload['tipo'] : null;
    $return['descricao'] = strlen($payload['descricao'] ?? '') >= 1
        && strlen($payload['descricao'] ?? '') <= 10 ?
        $payload['descricao'] : null;

    $return = array_filter($return);

    if (count($return) !== 3) {
        return null;
    }

    return $return;
}