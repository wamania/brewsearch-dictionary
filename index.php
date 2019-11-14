<?php

require 'vendor/autoload.php';

$dictionary = new \Wamania\BrewSearch\Dictionary\Dictionary(
    dirname(__DIR__.'/data')
);

$dictionary->init();

$nb = 1000;

echo "Récupération des produits\n";
$pdo = new \Pdo('mysql:host=sql2.europeansourcing.com;dbname=europeansourcing', 'es_sourcing', '14EgNxElPOE4MVT8552s');
$pdo->query("SET NAMES 'utf8'");
$s = $pdo->query("
    SELECT pl.name
    FROM product p
    INNER JOIN product_language pl ON (pl.id_product=p.id_product)
"); // LIMIT 100000

$idoc = 0;
$start = microtime(1);

while ($r = $s->fetch(\PDO::FETCH_ASSOC)) {
    $words = explode(' ', $r['name']);

    foreach ($words as $word) {
        if (empty($word)) {
            continue;
        }
        $id = $dictionary->process($word);

        echo $word.': '.$id."\n";
    }
}
