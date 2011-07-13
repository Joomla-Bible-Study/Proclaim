INSERT INTO #__bsms_install VALUES(7.0.1);
ALTER TABLE #__bsms_topics CHANGE `languages` `params`;
ALTER #__bsms_topics ADD `params` varchar(511);
