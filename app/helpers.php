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

function getUser(PDO $db, int $id):? array {
    $query = $db->prepare('SELECT * FROM accounts WHERE id = ?');
    $query->execute([ $id ]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $query = null;

    return $result ?: null;
}

function canCreateTransaction(PDO $db, array $account, array $values): bool
{
    $invalid = $values['tipo'] === 'd' &&
        $account['limit_amount'] + $account['current_balance'] < $values['valor'];

    return !$invalid;
}

function createTransaction(PDO $db, int $id, array $values)
{
    $db->beginTransaction();

    try {
        $query = $db->prepare("
        INSERT INTO 
            transactions (account_id, amount, transaction_type, description) 
            VALUES (?, ?, ?, ?)
        ");
        $query->execute([
            $id,
            $values['valor'],
            $values['tipo'],
            $values['descricao']
        ]);

        $query = $db->prepare("
            UPDATE accounts 
                SET current_balance = current_balance + ? 
            WHERE 
                ID = ? 
              AND (? OR limit_amount + current_balance >= ?) 
            RETURNING limit_amount, current_balance");

        $query->execute([
            $values['tipo'] === 'd' ? -$values['valor'] : $values['valor'],
            $id,
            ($values['tipo'] === 'c') ? 'true' : 'false',
            $values['valor']
        ]);
        $account = $query->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            throw new \Exception();
        }
    } catch (\Throwable $e) {
        $db->rollBack();
        return [
            'success' => false,
            'code' => 422
        ];
    }

    $db->commit();

    return [
        'success' => true,
        'limit_amount' => $account['limit_amount'],
        'current_balance' => $account['current_balance'],
    ];
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

    $result = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $result[] = $row;
    }
    $query = null;

    return $result ?: [];
}

function validateInputTransaction(string $payload):? array {
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

function warmUp(): void {
    for ($i = 1; $i <= 1000; $i++) {
        $id = rand(1, 6);
        $curl = curl_init("http://localhost:3000/clientes/$id/extrato");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        curl_close($curl);
    }

    for ($i = 1; $i <= 1000; $i++) {
        $id = rand(1, 6);
        $curl = curl_init("http://localhost:3000/clientes/$id/transacoes");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            "valor" => "10",
            "tipo" => "d",
            "descricao" => "descricao"
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        curl_close($curl);
    }

    $db = getDbConnection();

    $query = $db->query("UPDATE accounts SET current_balance = 0");
    $query->execute();

    $query = $db->query("TRUNCATE TABLE transactions");
    $query->execute();
}