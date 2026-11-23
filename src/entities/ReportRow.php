<?php

/**
 * ReportRow: Entity class (shared by all layers).
 * Feature owner: P6 (see the Track-It demo build plan whiteboard).
 *
 * Generated from the class diagram. Keep class and method names exactly as they are,
 * because the code has to match the SDS design.
 */
class ReportRow
{
    private array $cells;

    /**
     * @param string[] $cells
     */
    public function __construct(array $cells)
    {
        $this->cells = $cells;
    }

    /**
     * @return string[]
     */
    public function getCells(): array
    {
        return $this->cells;
    }
}
