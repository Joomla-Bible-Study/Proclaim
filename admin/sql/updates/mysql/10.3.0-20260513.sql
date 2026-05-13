-- Add transcript field to messages (#__bsms_studies) for issue #1225
-- (epic #1210 step 4): untimed plain-text transcripts get a proper home
-- separate from timed caption files. MEDIUMTEXT (16 MB) chosen over TEXT
-- (64 KB) — long sermons can produce 100k-word transcripts. FULLTEXT key
-- supports the indexed-for-search acceptance criterion.

ALTER TABLE `#__bsms_studies`
    ADD COLUMN `transcript` MEDIUMTEXT DEFAULT NULL AFTER `studytext`;

ALTER TABLE `#__bsms_studies`
    ADD FULLTEXT KEY `idx_transcript` (`transcript`);
