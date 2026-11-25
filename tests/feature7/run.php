<?php
// Run from CLI on the disposable database after importing demo.sql.
// Reads the same config as the application. Only DEMO7 records are changed.
if (PHP_SAPI !== 'cli') { exit; }
require __DIR__ . '/../../src/bootstrap.php';

function verifyCase(string $id, bool $passed, array $observed): void
{
    echo $id, $passed ? ' PASS ' : ' FAIL ', json_encode($observed), PHP_EOL;
    if (!$passed) { exit(1); }
}

$day = '2026-05-12';
$orderA = 'DEMO7-A';
$orderB = 'DEMO7-B';
$orderC = 'DEMO7-C';
$rows = $dataAccess->select("SELECT order_id, dispatch_date FROM orders
    WHERE order_id IN ('DEMO7-A','DEMO7-B','DEMO7-C') ORDER BY order_id", []);
$dates = array_column($rows, 'dispatch_date', 'order_id');
verifyCase('TC-7.01', ($dates[$orderA] ?? '') === $day && ($dates[$orderB] ?? '') === $day, $dates);
verifyCase('TC-7.02', ($dates[$orderC] ?? '') === '2026-05-13', $dates);

$list = $deliveryMgmt->dispatchList($day);
$a = $list[$orderA] ?? [];
$b = $list[$orderB] ?? [];
verifyCase('TC-7.03', array_keys($list) === [$orderA,$orderB]
    && $a['name'] === 'Alicia Reid' && $a['phone'] === '8765550104'
    && $a['address'] === '8 Hope Road, Kingston'
    && $a['lines'] === ['2 × Wireless Earbuds','1 × Phone Case'] && $a['total'] == 206.25
    && $b['name'] === 'Kemar Brown' && $b['phone'] === '8765550105'
    && $b['address'] === '14 Main Street, Ocho Rios'
    && $b['lines'] === ['3 × Phone Case'] && $b['total'] == 15.75, $list);

$_SESSION['user'] = User::fromRow($dataAccess->select("SELECT * FROM users WHERE user_id='U-OWNER'", [])[0]);
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['date'] = $day;
$dataAccess->beginTransaction();
try {
    $dataAccess->execute("UPDATE customers SET phone='' WHERE customer_id='DEMO7-C1'", []);
    $dataAccess->execute("UPDATE customers SET address='' WHERE customer_id='DEMO7-C2'", []);
    ob_start();
    $deliveryUI->showAwaitingAssignment();
    $html = ob_get_clean();
} finally {
    $dataAccess->rollback();
}
preg_match_all('~<section>(.*?)</section>~s', $html, $sections);
$flagged = [];
foreach ($sections[1] as $section) {
    if (str_contains($section, 'Incomplete delivery information')) {
        foreach ([$orderA,$orderB] as $id) {
            if (str_contains($section, $id)) { $flagged[] = $id; }
        }
    }
}
verifyCase('TC-7.04', $flagged === [$orderA,$orderB], $flagged);

$worker = 'U-DRIVER1';
$deliveryMgmt->assignBearer($orderA, $worker, $day);
$assigned = $deliveryMgmt->dispatchList($day)[$orderA];
$before = $deliveryMgmt->handovers($day);
$dataAccess->execute("SET time_zone = '-05:00'", []);
$dataAccess->execute("SET timestamp = UNIX_TIMESTAMP('2026-05-12 09:45:00')", []);
$deliveryMgmt->recordHandover($orderA, $day, $worker);
$dataAccess->execute('SET timestamp = DEFAULT', []);
$saved = $dataAccess->select("SELECT o.order_status,o.bearer_id,o.handed_over_at,d.worker_id,d.status
    FROM orders o JOIN deliveries d ON o.order_id=d.order_id WHERE o.order_id=:id", ['id'=>$orderA]);
verifyCase('TC-7.05', $assigned['bearer_id'] === $worker && !$before && count($saved) === 1
    && $saved[0] === ['order_status'=>'OutForDelivery','bearer_id'=>$worker,
        'handed_over_at'=>'2026-05-12 09:45:00','worker_id'=>$worker,'status'=>'Pending'], $saved);

$rejected = false;
try {
    $deliveryMgmt->recordHandover($orderB, $day, $worker);
} catch (DomainException $error) {
    $rejected = true;
}
$row = $dataAccess->select('SELECT order_status, bearer_id, handed_over_at FROM orders WHERE order_id=:id',
    ['id'=>$orderB])[0];
$count = count($dataAccess->select('SELECT delivery_id FROM deliveries WHERE order_id=:id', ['id'=>$orderB]));
verifyCase('TC-7.06', $rejected && $row['order_status'] === 'OrderConfirmed' && $row['bearer_id'] === null
    && $row['handed_over_at'] === null && $count === 0, [$row,'delivery_rows'=>$count]);
