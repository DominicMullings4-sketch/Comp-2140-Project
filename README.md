# Track-It

Track-It

Setup: Clone this repo into C:\xampp\htdocs, and start Apache and MySQL in the XAMPP Control Panel. Then set up the database in phpMyAdmin (http://localhost/phpmyadmin): if a track_it database already exists, drop it first (select it, then Operations → Drop the database), because importing over old tables fails with "table already exists" errors. 

Next, click New, create an empty database named track_it (collation utf8mb4_unicode_ci), select it, open the Import tab and import database/track_it_backup.sql. That file creates all 8 tables and loads the team's demo data, but it does not create the database itself, which is why you make the empty one first. Finally, copy config/config.example.php to config/config.php (the defaults already match XAMPP) and open http://localhost/Comp-2140-Project/.

On macOS with XAMPP installed in `/Applications/XAMPP`, keep MySQL running and use VS Code's **Terminal → Run Task → Run Track-It**. Open http://127.0.0.1:8080/. This serves the current project folder, so it does not need to be copied into XAMPP's web folder. Stop the task's terminal to stop the site.

Test accounts (password for all: TrackIt-Demo-2026!): staff1 (Office Staff), bearer1 and bearer2 (Delivery Bearers).
