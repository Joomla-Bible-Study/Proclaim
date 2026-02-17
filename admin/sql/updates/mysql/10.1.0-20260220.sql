--
-- Teacher social links: add social_links column for unlimited link storage (JSON subform).
-- Legacy columns (facebooklink, twitterlink, bloglink, link1-3, linklabel1-3, website)
-- are preserved for frontend backward compatibility.
--

ALTER TABLE `#__bsms_teachers` ADD COLUMN `social_links` TEXT DEFAULT NULL AFTER `address`;
