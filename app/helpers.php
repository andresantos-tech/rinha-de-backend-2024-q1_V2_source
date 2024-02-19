<?php

function getDbConnection(): PDO {
    $host = 'rinha_v2_postgres';
    $database = 'postgres';
    $port = '5432';
    $username = 'postgres';
    $password = 'postgres';
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;";

    return new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,
        ]
    );
}

function getUser(PDO $db, int $id):? array {
    $query = $db->prepare('SELECT * FROM accounts WHERE id = ?');
    $query->execute([ $id ]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $query = null;

    return $result ?: null;
}

function createTransaction(int $id, array $values) {

}

function getRecentTransactions(PDO $db, int $id, int $limit = 10): array {
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

    $result = $query->fetch(PDO::FETCH_ASSOC);
    $query = null;

    return $result ?: [];
}

function warmUp() {
    // TODO
}