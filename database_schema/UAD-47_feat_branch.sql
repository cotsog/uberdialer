ALTER TABLE `campaigns`
	CHANGE COLUMN `auto_hopper_level` `auto_hopper_level` INT(5) NULL DEFAULT '1' AFTER `auto_recorded_msg_two`; 