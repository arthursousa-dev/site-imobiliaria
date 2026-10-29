<?php
// Os testes de banco apagam e criam registros: só rodam contra um banco terminado em _test.
//
//   DB_NAME=horizonte_test vendor/bin/phpunit

if (!str_ends_with(getenv('DB_NAME') ?: '', '_test')) {
    fwrite(STDERR, "\nDB_NAME precisa apontar para um banco de testes (ex.: horizonte_test).\n\n");
    exit(1);
}

require_once __DIR__ . '/../app/helpers.php';
