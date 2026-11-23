# COMP2140-TrackIt
Repository to facilitate the development of TrackIt, a software solution for Variety Corner JA. Software will aide with inventory, delivery and client management.

---

## 1. Set up your laptop (about 15 minutes)

1. **Install XAMPP** (PHP 8.1 or newer) from apachefriends.org. Open the XAMPP Control Panel and **Start Apache and MySQL**.
2. **Clone this repo into `C:\xampp\htdocs`**, so you get `C:\xampp\htdocs\trackit`. With GitHub Desktop: *File → Clone repository*, local path `C:\xampp\htdocs`.
3. **Create the database:** open http://localhost/phpmyadmin → **Import** tab → choose `database/schema.sql` → **Import**. Then do the same with `database/seed.sql` (test data).
4. **Create your settings file:** copy `config/config.example.php` to `config/config.php`. The values already match XAMPP. `config.php` is ignored by Git, so your own settings never get pushed.
5. **Open http://localhost/trackit/inventory.php**. You should see the inventory list with five test items. That page is the worked example (section 4).

Until the login page is built, `DEV_AUTO_LOGIN` in `config.php` signs you in automatically. Change it to `'owner'`, `'staff'`, `'driver1'` or `'driver2'` to test each role. **Set it to `''` before the demo.**

### Test accounts (from `database/seed.sql`)

| Username | Role | Password |
|---|---|---|
| owner | BusinessOwner | `TrackIt-Dev-2026!` |
| staff | OfficeStaff | `TrackIt-Dev-2026!` |
| driver1, driver2 | DeliveryWorker | `TrackIt-Dev-2026!` |

These are development-only test accounts. Never load `seed.sql` onto a database with real customer data.

## 2. Where things are

The folders follow the four layers in the SDS. **Each class has its own file, named exactly as on the class diagram.**

| Folder | Layer | Classes |
|---|---|---|
| `src/ui/` | User Interface | LoginUI, UserUI, InventoryUI, CustomerUI, OrderUI, DeliveryUI, ReportingUI |
| `src/auth/` | Authentication & Authorization | LoginValidation, TwoFactorAuth, UserPermission |
| `src/logic/` | Application Logic | InventoryMgmt, CustomerMgmt, OrderMgmt, DeliveryMgmt, ReportingMgmt, UserMgmt |
| `src/data/` | Data Access | DataAccess |
| `src/entities/` | Entity classes and enums | Customer, Item, Order, Delivery, ... |
| `database/` | Table script and test data | `schema.sql`, `seed.sql` |
| `*.php` in the root | One entry page per feature | `inventory.php`, `orders.php`, ... |
| `templates/`, `assets/` | Shared page top/bottom and styles | |

`src/bootstrap.php` runs first on every page. It loads the settings, `require`s every class file, starts the session, and creates one object of each class (`$inventoryUI`, `$orderMgmt`, ...). Each `new` passes in the classes that class uses, matching the dependency arrows on the diagrams.

## 3. Who builds what

Every method you need to write already exists, with a note saying which requirement (SR / SC) it covers. Until it's built, it stops the page with *"... is not built yet"*. Search your files for `TODO (P` to find your work.

| Person | Feature | Files |
|---|---|---|
| P1 | Login, 2FA, page protection | LoginUI, LoginValidation, TwoFactorAuth, UserMgmt, User |
| P2 | Record stock (F1) | InventoryUI, InventoryMgmt, Item, Supplier, StockReceipt, StockReceiptLine |
| P3 | Customers (F2) | CustomerUI, CustomerMgmt, Customer |
| P4 | Orders and invoices (F3, F4) | OrderUI, OrderMgmt, Order, OrderLine, Invoice |
| P5 | Deliveries (F5, F6) | DeliveryUI, DeliveryMgmt, Delivery |
| P6 | Reports and users (F7) | ReportingUI, ReportingMgmt, Report, ReportRow, UserUI |

Already built (the foundation): DataAccess, UserPermission, the entity constructors and getters, `fromRow()`, the page layout, and the inventory list example.

## 4. How the layers work together (copy this pattern)

The inventory list goes through every layer. Read these three methods in order:

1. `InventoryUI::showItems()` checks the role, asks the logic layer for the items, and prints the HTML table (the same `<?php foreach ... ?>` table style as INFO2180 Lab 5).
2. `InventoryMgmt::getItems()` runs the SQL through DataAccess and turns each row into an `Item` object.
3. `Item::fromRow()` rebuilds an `Item` from a database row.

Use `new Item(...)` for a **new** record and `Item::fromRow($row)` for one **already saved**.

### Running SQL

DataAccess uses the same PDO steps as INFO2180 Lab 5 (prepare → execute → fetchAll). From a Mgmt class:

```php
// Reading
$rows = $this->db->select('SELECT * FROM items WHERE item_id = :id', ['id' => $itemId]);

// Saving (INSERT or UPDATE)
$this->db->execute('UPDATE items SET stock_quantity = :stock WHERE item_id = :id',
    ['stock' => $item->getStockQuantity(), 'id' => $item->getItemId()]);
```

## 5. Rules for everyone

- **Don't rename classes or methods.** The lecturer checks the code against the class diagram. If something needs to change, tell Tevin, so the diagram and the SDS are updated too.
- **Use `:name` placeholders in SQL** and pass the values in the array, as above. Never paste form input into the SQL text (SQL injection).
- **Print user-typed values with `e()`**: `<?= e($customer->getName()) ?>`.
- **Saves that touch more than one table go in one transaction** (SF-2): `beginTransaction()`, then `commit()`, and `rollback()` if something fails.
- **Never DELETE records** (SF-3). Cancel, void or anonymize instead.
- **Start every page method with `$this->permission->requireRole([...])`**, using the roles in the SRS access table (SC-1).
- **Adding a new class file?** Add a `require` line for it in `src/bootstrap.php` too.
- **Git:** work on a branch named after your feature (for example `p4-orders`). Pull `main` often, commit small pieces, and open a pull request to merge. Never commit `config/config.php`.

## 6. Still to add

- A TOTP library for two-factor codes (P1) and a PDF library for invoices and reports (P4, P6).
- Connecting to the Azure database for the demo: change the `DB_` settings in `config.php` to the Azure values, and set `DB_SSL_CA` to the Azure certificate file.
