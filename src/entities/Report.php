<?php

/**
 * Report: Entity class (shared by all layers).
 * Feature owner: P6 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class Report
{
    private ReportType $type;
    private ?string $startDate;   // date as Y-m-d
    private ?string $endDate;   // date as Y-m-d
    private string $generatedAt;   // date and time as Y-m-d H:i:s
    private array $headings;
    private array $rows;   // ReportRow[]

    /**
     * @param string $startDate  date as Y-m-d
     * @param string $endDate  date as Y-m-d
     * @param string[] $headings
     */
    public function __construct(ReportType $type, ?string $startDate, ?string $endDate, array $headings)
    {
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->headings = $headings;
        $this->generatedAt = now();
        $this->rows = [];
    }

    /**
     * @param string[] $cells
     */
    public function addRow(array $cells): void
    {
        $this->rows[] = new ReportRow($cells);
    }

    public function getType(): ReportType
    {
        return $this->type;
    }

    /**
     * @return string  date as Y-m-d
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * @return string  date as Y-m-d
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * @return string  date and time as Y-m-d H:i:s
     */
    public function getGeneratedAt(): string
    {
        return $this->generatedAt;
    }

    /**
     * @return string[]
     */
    public function getHeadings(): array
    {
        return $this->headings;
    }

    /**
     * @return ReportRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}
