<?php
// Feature 7: Reporting (owner only). Builder: P6.
// This page only decides WHICH ReportingUI method to call. ReportingUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'options';

if ($action == 'run') {
    $reportingUI->submitReportRequest(ReportType::from($_POST['type']), $_POST['start'], $_POST['end']);
} elseif ($action == 'threshold') {
    // SR-7.5: filter_var gives false when the box doesn't hold a whole number; we send -1 so it's rejected.
    $threshold = filter_var($_POST['threshold'], FILTER_VALIDATE_INT);
    if ($threshold === false) {
        $threshold = -1;
    }
    $reportingUI->submitLowStockThreshold($threshold);
} elseif ($action == 'pdf') {
    $reportingUI->downloadPdf();
} elseif ($action == 'csv') {
    $reportingUI->downloadCsv();
} else {
    $reportingUI->showReportOptions();
}
