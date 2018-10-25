CREATE TABLE `auto_hopper`(  
  `hopper_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `campaign_contact_id` BIGINT(20),
  `phone_number` BIGINT(20),
  `campaign_id` BIGINT(20),
  `list_id` INT(11),
  `created_datetime` DATETIME,
  `updated_datetime` DATETIME,
  PRIMARY KEY (`hopper_id`)
) ENGINE=INNODB;

ALTER TABLE `campaign_contacts`   
  ADD COLUMN `auto_added_as_hopper` ENUM('0','1') NULL   COMMENT '0 indicate contact is not added in hopper table' AFTER `reference_link`,
  ADD COLUMN `auto_last_call_datetime` DATETIME NULL AFTER `auto_added_as_hopper`;
  
ALTER TABLE `auto_hopper`  
  ADD INDEX (`campaign_contact_id`),
  ADD FOREIGN KEY (`campaign_contact_id`) REFERENCES `uberdialer_stage`.`campaign_contacts`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
 
ALTER TABLE `auto_hopper`   
  ADD INDEX (`list_id`),
  ADD FOREIGN KEY (`list_id`) REFERENCES `uberdialer_stage`.`campaign_lists`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;  
  
ALTER TABLE `auto_hopper`   
  ADD COLUMN `ext_phone_number` VARCHAR(25) NULL AFTER `phone_number`;
  
ALTER TABLE `auto_hopper`   
  ADD COLUMN `contact_id` BIGINT(20) NULL AFTER `ext_phone_number`;
  
ALTER TABLE `auto_hopper`   
  ADD INDEX (`contact_id`),
  ADD FOREIGN KEY (`contact_id`) REFERENCES `uberdialer_stage`.`contacts`(`id`) ON UPDATE CASCADE ON DELETE CASCADE;
  
  ALTER TABLE `campaign_contacts`   
  CHANGE `auto_added_as_hopper` `auto_added_as_hopper` ENUM('0','1') CHARSET latin1 COLLATE latin1_swedish_ci DEFAULT '0'  NULL   COMMENT '0 indicate contact is not added in hopper table';