-- Primary key enforcement moved to proclaim.script.php postflight()
-- because ALTER TABLE … ADD PRIMARY KEY is not idempotent in MySQL
-- and breaks fresh installs where install.sql already created the PKs.

-- Drop legacy storage table (no code references it; only exists on upgraded sites)
DROP TABLE IF EXISTS `#__bsms_storage`;
