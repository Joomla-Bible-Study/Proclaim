INSERT INTO #__bsms_update VALUES(2,7.0.1);
ALTER TABLE #__bsms_topics CHANGE `languages` `params` varchar(511) null';