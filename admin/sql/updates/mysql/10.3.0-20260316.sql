-- Add landing_ordering column to teachers for display priority on landing page
ALTER TABLE `#__bsms_teachers`
    ADD COLUMN `landing_ordering` INT(3) NOT NULL DEFAULT 0 AFTER `landing_show`;
