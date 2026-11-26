<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StoreProductsTest extends TestCase
{
    private DataAccess $db;
    private InventoryMgmt $inv;
    private string $staffId = 'U-STAFF';   // from database/seed.sql
    private string $ownerId = 'U-OWNER';

    protected function setUp(): void
    {
        $this->db = new DataAccess();
        $this->db->execute('TRUNCATE TABLE stock_items', []);   // test database only
        $this->inv = new InventoryMgmt($this->db);
    }

    private function countRows(): int
    {
        return (int) $this->db->select('SELECT COUNT(*) AS c FROM stock_items', [])[0]['c'];
    }

    private function insertRow(string $name, string $loggedAt, int $delivered = 0, int $flagged = 0): void
    {
        $this->db->execute(
            'INSERT INTO stock_items (stock_item_id, item_name, quantity, rate, taken_for_delivery, logged_at, logged_by, is_flagged)
             VALUES (:id, :name, 1, 100, :delivered, :at, :by, :flagged)',
            ['id' => new_id('S'), 'name' => $name, 'delivered' => $delivered, 'at' => $loggedAt, 'by' => $this->staffId, 'flagged' => $flagged]
        );
    }

    #[Test]
    public function tc5_01_deliveryCheckboxDefaultsToUnchecked(): void
    {
        $item = $this->inv->logStockItem('Phone Case', null, 10, 2500, $this->staffId);
        $listed = $this->inv->getStockItems(UserRole::OfficeStaff)[0];
        $this->assertFalse($listed->isTakenForDelivery());
        $this->assertEquals(25000.0, $item->getTotalValue());
    }

    #[Test]
    public function tc5_02_invalidAttributesAreRejectedAndNothingIsStored(): void
    {
        foreach ([['Phone Case', -1, 2500], ['Phone Case', 10, 'abc'], ['', 10, 2500]] as [$name, $qty, $rate]) {
            try {
                $this->inv->logStockItem($name, null, $qty, $rate, $this->staffId);
                $this->fail('Invalid input was accepted.');
            } catch (InvalidArgumentException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
        $this->assertSame(0, $this->countRows());
    }

    #[Test]
    public function tc5_03_newItemAppearsAfterRefresh(): void
    {
        $this->inv->logStockItem('iPhone 0', null, 7, 25000, $this->staffId);
        $items = $this->inv->getStockItems(UserRole::OfficeStaff);   // a fresh query is the refresh
        $this->assertCount(1, $items);
        $this->assertSame('iPhone 0', $items[0]->getName());
        $this->assertSame(7, $items[0]->getQuantity());
        $this->assertEquals(175000.0, $items[0]->getTotalValue());
    }

    #[Test]
    public function tc5_04_editIsSavedAndTotalRecalculated(): void
    {
        $item = $this->inv->logStockItem('iPhone 0', null, 7, 25000, $this->staffId);
        $this->inv->updateStockItem($item->getStockItemId(), 'iPhone 0', null, 12, 25000);
        $listed = $this->inv->getStockItems(UserRole::OfficeStaff)[0];
        $this->assertSame(12, $listed->getQuantity());
        $this->assertEquals(300000.0, $listed->getTotalValue());
    }

    #[Test]
    public function tc5_05_timestampIsSetWithinFiveSecondsOfSubmission(): void
    {
        $before = time();
        $item = $this->inv->logStockItem('Table', null, 1, 500, $this->staffId);
        $this->assertLessThanOrEqual(5, abs(strtotime($item->getLoggedAt()) - $before));
    }

    #[Test]
    public function tc5_06_noNullTimestampsAndTimestampCannotBeEdited(): void
    {
        $table = $this->inv->logStockItem('Table', null, 1, 500, $this->staffId);
        $this->inv->logStockItem('Phone Case', null, 10, 2500, $this->staffId);
        $this->inv->logStockItem('iPhone 0', null, 7, 25000, $this->staffId);

        $nulls = (int) $this->db->select('SELECT COUNT(*) AS c FROM stock_items WHERE logged_at IS NULL', [])[0]['c'];
        $this->assertSame(0, $nulls);

        // Set a known old timestamp, run an edit, and check it did not change.
        $this->db->execute('UPDATE stock_items SET logged_at = :at WHERE stock_item_id = :id',
            ['at' => '2026-01-01 00:00:00', 'id' => $table->getStockItemId()]);
        $this->inv->updateStockItem($table->getStockItemId(), 'Table', null, 2, 500);
        $this->assertSame('2026-01-01 00:00:00', $this->inv->getStockItemById($table->getStockItemId())->getLoggedAt());
    }

    #[Test]
    public function tc5_07_flaggedItemIsHiddenFromEmployeeButStaysInDatabase(): void
    {
        $item = $this->inv->logStockItem('ITEMTOREMOVE', null, 1, 1, $this->staffId);
        $this->inv->flagStockItem($item->getStockItemId(), $this->staffId);

        $this->assertCount(0, $this->inv->getStockItems(UserRole::OfficeStaff));
        $this->assertSame(1, $this->countRows());
    }

    #[Test]
    public function tc5_08_flaggedItemIsStillVisibleToOwner(): void
    {
        $item = $this->inv->logStockItem('ITEMTOREMOVE', null, 1, 1, $this->staffId);
        $this->inv->flagStockItem($item->getStockItemId(), $this->staffId);

        $this->assertCount(0, $this->inv->getStockItems(UserRole::OfficeStaff));
        $ownerView = $this->inv->getStockItems(UserRole::BusinessOwner);
        $this->assertCount(1, $ownerView);
        $this->assertTrue($ownerView[0]->isFlagged());
        // No supervisor role exists in UserRole. Add a case here if the team adds one.
    }

    #[Test]
    public function tc5_09_summaryMatchesWhatEmployeesSee(): void
    {
        $this->inv->logStockItem('Table', null, 1, 500, $this->staffId);
        $this->inv->logStockItem('iPhone 0', null, 7, 25000, $this->staffId);
        $this->inv->logStockItem('Phone Case', null, 10, 2500, $this->staffId);
        $bad = $this->inv->logStockItem('ITEMTOREMOVE', null, 1, 1, $this->staffId);
        $this->inv->flagStockItem($bad->getStockItemId(), $this->staffId);

        $this->assertSame(3, $this->inv->getStockSummary()['total']);
        $this->assertCount(3, $this->inv->getStockItems(UserRole::OfficeStaff));
    }

    #[Test]
    public function tc5_10_summaryCountsOnlyTheRequestedPeriod(): void
    {
        // Assumed reading of the test data: see the note about TC-5-10.
        $this->insertRow('A normal',    '2026-11-03 10:00:00');
        $this->insertRow('B delivered', '2026-11-08 10:00:00', 1);
        $this->insertRow('C flagged',   '2026-11-10 10:00:00', 0, 1);
        $this->insertRow('D after',     '2026-11-16 10:00:00');

        $summary = $this->inv->getStockSummary('2026-11-01', '2026-11-15');
        $this->assertSame(2, $summary['total']);
        $this->assertSame(1, $summary['incoming']);
    }
}