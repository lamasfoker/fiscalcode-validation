<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use App\Handler;

$handler = new Handler();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request = &$_POST;
} else {
    $request = &$_GET;
}
$handler->handle(json_encode($request));
