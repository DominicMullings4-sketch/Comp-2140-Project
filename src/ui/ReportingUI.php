<?php

/**
 * ReportingUI: User Interface layer.
 * Feature owner: P6 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class ReportingUI
{
    private UserPermission $permission;
    private ReportingMgmt $reports;

    /**
     * Each parameter is one dependency arrow on the class diagram:
     * this class uses UserPermission, ReportingMgmt.
     */
    public function __construct(UserPermission $permission, ReportingMgmt $reports)
    {
        $this->permission = $permission;
        $this->reports = $reports;
    }

    /**
     * SR-7.1: owner only; choose the report type and date range, and set the low-stock threshold.
     */
    public function showReportOptions(): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingUI::showReportOptions() is not built yet.');
    }

    /**
     * SR-7.2 to SR-7.6: generate the chosen report, keep it in $_SESSION for downloading, and show it as a
     * table.
     * @param string $startDate  date as Y-m-d
     * @param string $endDate  date as Y-m-d
     */
    public function submitReportRequest(ReportType $type, string $startDate, string $endDate): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingUI::submitReportRequest() is not built yet.');
    }

    /**
     * SR-7.5: ReportingMgmt::setLowStockThreshold(). A value of -1 means the form didn't hold a whole
     * number.
     */
    public function submitLowStockThreshold(int $threshold): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingUI::submitLowStockThreshold() is not built yet.');
    }

    /**
     * SR-7.7: exportPdf() on the report kept in $_SESSION, then send the file.
     */
    public function downloadPdf(): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingUI::downloadPdf() is not built yet.');
    }

    /**
     * SR-7.7: exportCsv() on the report kept in $_SESSION, then send the file.
     */
    public function downloadCsv(): void
    {
        // TODO (P6): build this method. The note above says what it must do.
        throw new Exception('ReportingUI::downloadCsv() is not built yet.');
    }
}
