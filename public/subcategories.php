<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$parent = isset($_GET['parent']) ? (int)$_GET['parent'] : 0;
if($parent <= 0){ echo json_encode([]); exit; }
$subs = get_subcategories($parent);
echo json_encode(array_map(function($c){
    return [
        'category_id' => (int)$c['category_id'],
        'name' => $c['name']
    ];
}, $subs));
