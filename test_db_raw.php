<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

$results = "";
$results .= "Active Prefix: " . DB::getTablePrefix() . "\n\n";

function test($input, &$results) {
    $res = db_raw($input)->getValue(DB::connection()->getQueryGrammar());
    $results .= "IN:  $input\n";
    $results .= "OUT: $res\n\n";
}

test('products.name', $results);
test('YEAR(created_at)', $results);
test('SUM(order_items.quantity) as total', $results);
test("name = 'products.name'", $results);
test('`order_items`.quantity', $results);
test('`order_items` . `quantity`', $results);

file_put_contents('test_results.txt', $results);
echo "Results written to test_results.txt\n";
