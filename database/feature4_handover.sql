-- Feature 4 (Confirm Delivery): adds the handover record to an EXISTING track_it database.
-- Only needed if you imported track_it_backup.sql BEFORE this change.
-- phpMyAdmin -> select track_it -> Import -> choose this file -> Import.
USE track_it;

ALTER TABLE deliveries
    ADD COLUMN handed_over_at DATETIME NULL AFTER outcome_recorded_by,
    ADD COLUMN handed_over_by INT NULL AFTER handed_over_at,
    ADD CONSTRAINT deliveries_ibfk_4 FOREIGN KEY (handed_over_by) REFERENCES users (user_id);
