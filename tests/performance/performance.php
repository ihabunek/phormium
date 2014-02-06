<?php

/**
 * A simple performance test suite for Phormium.
 *
 * Ideally run on a local instance of PgSQL.
 */

require "../../vendor/autoload.php";
require "model.php";
require "functions.php";

Phormium\DB::configure([
    'databases' => [
        'test' => [
            'dsn' => 'pgsql:host=localhost;dbname=phtest',
            'username' => 'postgres',
            'password' => ''
        ]
    ]
]);

echo "Phormium performance test suite\n";
echo "===============================\n";

echo "Reseting database.\n";
`psql --quiet --username postgres --dbname=phtest < world.sql`;

echo "-------------------------------\n";

// ----------------------------------------------

start("Select all");
repeat(20, function() {
    City::all();
});
finish();

// ----------------------------------------------

$cities = City::all();

start("Select each by ID");
foreach($cities as $city) {
    City::get($city->id);
}
finish();

// ----------------------------------------------

start("Select each by name");
foreach($cities as $city) {
    City::get($city->id);
}
finish();

// ----------------------------------------------z

start("Update all");
repeat(20, function() {
    City::objects()->update([
        'population' => 1
    ]);
});
finish();

// ----------------------------------------------z

$cities = City::all();

start("Update each (update)");
foreach($cities as $city) {
    $city->population += 1;
    $city->update();
}
finish();

// ----------------------------------------------z

$cities = City::all();

start("Update each (save)");
foreach($cities as $city) {
    $city->population += 1;
    $city->save();
}
finish();

// ----------------------------------------------

save();


