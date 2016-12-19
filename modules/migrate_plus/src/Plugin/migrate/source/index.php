<?php

$MSSQL_HOST = '192.168.1.107';
$MSSQL_DATABASE = 'iepr_db';
$MSSQL_USERNAME = 'sa';
$MSSQL_USERNAME = 'v13ws0n1c!';
ini_set('mssql.charset', 'UTF-8');
try {
    $pdo = new \PDO(
        sprintf(
            "dblib:host=%s;dbname=%s",
            '192.168.1.107',
            'iepr_db'
        ),
        'sa',
        'v13ws0n1c!'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "There was a problem connecting. " . $e->getMessage();
}

$query = "SELECT * FROM dbo.Documents";

$statement = $pdo->prepare($query);
$statement->execute();

$results = $statement->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results[0]);

//echo '<html><head></head><body> Hello Worlds </body></html>';


