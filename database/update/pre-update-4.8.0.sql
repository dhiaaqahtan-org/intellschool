--
-- Database: `InstiKit`
--

-- --------------------------------------------------------

--
-- InstiKit 4.7.0 post update queries
--

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `custom_fields` CHANGE `type` `type` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
