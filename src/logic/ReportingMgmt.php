<?php

/**
 * ReportingMgmt: Application Logic layer.
 * Feature owner: P6 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class ReportingMgmt
{
    private DataAccess $db;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses DataAccess.
     */
    public function __construct(DataAccess $db)
    {
        $this->db = $db;
    }

    /**
     * SR-7.3: completed orders in the range, counted and totalled by order source; no source = 'Not
     * specified'.
     * @param string $startDate  date as Y-m-d
     * @param string $endDate  date as Y-m-d
     */
    public function generateSalesSummary(string $startDate, string $endDate): Report
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::generateSalesSummary() is not built yet.');
    }

    /**
     * SR-7.4: per delivery worker, the number of Completed and Failed deliveries in the range.
     * @param string $startDate  date as Y-m-d
     * @param string $endDate  date as Y-m-d
     */
    public function generateDeliverySummary(string $startDate, string $endDate): Report
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::generateDeliverySummary() is not built yet.');
    }

    /**
     * SR-7.6: items whose available quantity is at or below getLowStockThreshold().
     */
    public function generateLowStockReport(): Report
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::generateLowStockReport() is not built yet.');
    }

    /**
     * SR-7.5: a whole number of 0 or more, saved in the settings table (setting_key
     * 'low_stock_threshold').
     */
    public function setLowStockThreshold(int $threshold): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::setLowStockThreshold() is not built yet.');
    }

    /**
     * Read 'low_stock_threshold' from the settings table.
     */
    public function getLowStockThreshold(): int
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::getLowStockThreshold() is not built yet.');
    }

    /**
     * SR-7.7: write the report to a PDF file (a PDF library will be added) and return its path.
     * @return string  path of the generated file
     */
    public function exportPdf(Report $report): string
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::exportPdf() is not built yet.');
    }

    /**
     * SR-7.7: write the headings and rows to a CSV file with fputcsv() and return its path.
     * @return string  path of the generated file
     */
    public function exportCsv(Report $report): string
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::exportCsv() is not built yet.');
    }

    /**
     * SR-7.2: both dates present and the end date not before the start date.
     * @param string $startDate  date as Y-m-d
     * @param string $endDate  date as Y-m-d
     */
    private function validateDateRange(string $startDate, string $endDate): bool
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingMgmt::validateDateRange() is not built yet.');
    }
}
