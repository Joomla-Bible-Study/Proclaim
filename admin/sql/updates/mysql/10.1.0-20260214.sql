--
-- Migrate legacy tooltip link types in template params.
-- Link type 4 ("Link to Details with ToolTip") → 1 ("Link to Details")
-- Link type 5 ("Link to Media with ToolTip")   → 2 ("Link to Media")
-- The old hasTip tooltip system has been removed; scripture verse popovers replace it.
--

-- String-quoted values (most common in Joomla Registry JSON)
UPDATE `#__bsms_templates` SET `params` = REPLACE(`params`, 'linktype":"4"', 'linktype":"1"') WHERE `params` LIKE '%linktype":"4"%';
UPDATE `#__bsms_templates` SET `params` = REPLACE(`params`, 'linktype":"5"', 'linktype":"2"') WHERE `params` LIKE '%linktype":"5"%';

-- Integer values followed by comma
UPDATE `#__bsms_templates` SET `params` = REPLACE(`params`, 'linktype":4,', 'linktype":1,') WHERE `params` LIKE '%linktype":4,%';
UPDATE `#__bsms_templates` SET `params` = REPLACE(`params`, 'linktype":5,', 'linktype":2,') WHERE `params` LIKE '%linktype":5,%';

-- Integer values at end of JSON object
UPDATE `#__bsms_templates` SET `params` = REPLACE(`params`, 'linktype":4}', 'linktype":1}') WHERE `params` LIKE '%linktype":4}%';
UPDATE `#__bsms_templates` SET `params` = REPLACE(`params`, 'linktype":5}', 'linktype":2}') WHERE `params` LIKE '%linktype":5}%';
