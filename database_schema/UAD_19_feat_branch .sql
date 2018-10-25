ALTER TABLE `campaigns` ADD COLUMN `auto_dial` ENUM('0','1') DEFAULT '0'  NOT NULL AFTER `eg_end_date`;

ALTER TABLE `campaigns`   
  ADD COLUMN `auto_abandoned_rate` FLOAT NULL AFTER `auto_dial`,
  ADD COLUMN `auto_time_threshold_one` INT(2) NULL AFTER `auto_abandoned_rate`,
  ADD COLUMN `auto_recorded_msg_one` VARCHAR(255) NULL AFTER `auto_time_threshold_one`,
  ADD COLUMN `auto_time_threshold_two` INT(2) NULL AFTER `auto_recorded_msg_one`,
  ADD COLUMN `auto_recorded_msg_two` VARCHAR(255) NULL AFTER `auto_time_threshold_two`,
  ADD COLUMN `auto_hopper_level` INT(5) NULL AFTER `auto_recorded_msg_two`,
  ADD COLUMN `auto_hopper_multiplier` FLOAT NULL AFTER `auto_hopper_level`,
  ADD COLUMN `auto_dial_timeout` INT(2) NULL AFTER `auto_hopper_multiplier`,
  ADD COLUMN `auto_dial_level` INT(3) NULL AFTER `auto_dial_timeout`;
  
  
CREATE TABLE `auto_live_agents` 
  ( 
     `id`               INT(10) NOT NULL auto_increment, 
     `agent_id`         INT(10) NOT NULL, 
     `campaign_id`      INT(10) NULL, 
     `active_status`    ENUM('login', 'ready', 'queue', 'incall', 'done') NULL 
     DEFAULT 
     'login', 
     `agent_session`    ENUM('0', '1') NULL DEFAULT '0', 
     `created_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
     `updated_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
     PRIMARY KEY (`id`) 
  ) 
engine = innodb; 


CREATE TABLE `auto_live_agents_logs` 
  ( 
     `id`                INT(10) NOT NULL auto_increment, 
     `agent_id`          INT(5) NOT NULL, 
     `campaign_id`       INT(5) NOT NULL, 
     `session_status`    ENUM('start', 'stop') NOT NULL, 
     `status_changed_by` ENUM('system', 'agent') NOT NULL, 
     `created_datetime`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
     PRIMARY KEY (`id`), 
     INDEX (`agent_id`), 
     INDEX (`campaign_id`) 
  ) 
engine=innodb; 