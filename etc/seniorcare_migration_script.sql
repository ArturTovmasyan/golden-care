USE `cc_old`;
DELIMITER $$
DROP PROCEDURE IF EXISTS json_row_data $$
CREATE PROCEDURE json_row_data(IN tbl_tmp CHAR(255),
                               IN tbl_name CHAR(255),
                               IN field_name CHAR(255))
BEGIN
  SET @max_data_length = 0;
  SET @q_max_len =
      CONCAT('SELECT MAX(JSON_LENGTH(`', tbl_name, '`.`', field_name, '`)) INTO @max_data_length FROM `', tbl_name,
             '`');
  SET @q_tmp_drp = CONCAT('DROP TEMPORARY TABLE IF EXISTS `', tbl_tmp, '`');
  SET @q_tmp_crt = CONCAT('CREATE TEMPORARY TABLE `', tbl_tmp, '` (seq BIGINT PRIMARY KEY) ENGINE = MEMORY');
  SET @q_tmp_ins = CONCAT('INSERT INTO `', tbl_tmp, '`(seq) VALUES (@start)');

  PREPARE stmt FROM @q_max_len;
  EXECUTE stmt;

  SET @step = 1;
  SET @start = 1;
  SET @stop = @max_data_length;


  PREPARE stmt FROM @q_tmp_drp;
  EXECUTE stmt;
  PREPARE stmt FROM @q_tmp_crt;
  EXECUTE stmt;

  WHILE @start <= @stop DO
  PREPARE stmt FROM @q_tmp_ins;
  EXECUTE stmt;
  SET @start = @start + @step;
  END WHILE;
END $$
DELIMITER ;

USE `alms`;
DELIMITER $$
DROP PROCEDURE IF EXISTS json_row_data $$
CREATE PROCEDURE json_row_data(IN tbl_tmp CHAR(255),
                               IN tbl_name CHAR(255),
                               IN field_name CHAR(255))
BEGIN
  SET @max_data_length = 0;
  SET @q_max_len =
      CONCAT('SELECT MAX(JSON_LENGTH(`', tbl_name, '`.`', field_name, '`)) INTO @max_data_length FROM `', tbl_name,
             '`');
  SET @q_tmp_drp = CONCAT('DROP TEMPORARY TABLE IF EXISTS `', tbl_tmp, '`');
  SET @q_tmp_crt = CONCAT('CREATE TEMPORARY TABLE `', tbl_tmp, '` (seq BIGINT PRIMARY KEY) ENGINE = MEMORY');
  SET @q_tmp_ins = CONCAT('INSERT INTO `', tbl_tmp, '`(seq) VALUES (@start)');

  PREPARE stmt FROM @q_max_len;
  EXECUTE stmt;

  SET @step = 1;
  SET @start = 1;
  SET @stop = @max_data_length;


  PREPARE stmt FROM @q_tmp_drp;
  EXECUTE stmt;
  PREPARE stmt FROM @q_tmp_crt;
  EXECUTE stmt;

  WHILE @start <= @stop DO
  PREPARE stmt FROM @q_tmp_ins;
  EXECUTE stmt;
  SET @start = @start + @step;
  END WHILE;
END $$
DELIMITER ;
USE `db_seniorcare_migration`;
DELIMITER ;;
DROP PROCEDURE IF EXISTS ValidateByResident;;
CREATE PROCEDURE ValidateByResident(IN _resident_id INT)
BEGIN
  DECLARE row_i INT;
  DECLARE row_n INT;
  DECLARE prev_id INT;
  DECLARE curr_id INT;
  DECLARE prev_date DATETIME DEFAULT NULL;
  DECLARE curr_date DATETIME DEFAULT NULL;

  DROP TABLE IF EXISTS ValidateByResident_temp;
  CREATE TEMPORARY TABLE IF NOT EXISTS ValidateByResident_temp AS (
    SELECT `id`,
           `admission_type`,
           `id_resident`,
           `start`,
           `end`
    FROM `db_seniorcare_migration`.`tbl_resident_admission`
    WHERE `db_seniorcare_migration`.`tbl_resident_admission`.`id_resident` = _resident_id
    ORDER BY `db_seniorcare_migration`.`tbl_resident_admission`.`start`);

  SET @row_i = 0;
  SET @row_n = (SELECT COUNT(1) FROM ValidateByResident_temp);

  SELECT NULL INTO @prev_date FROM ValidateByResident_temp LIMIT 1;
  SELECT NULL INTO @prev_id FROM ValidateByResident_temp LIMIT 1;
  SELECT NULL INTO @curr_date FROM ValidateByResident_temp LIMIT 1;
  SELECT NULL INTO @curr_id FROM ValidateByResident_temp LIMIT 1;

  WHILE @row_n > 0 DO
  IF @row_i = 0 THEN
    SELECT `end` INTO @prev_date FROM ValidateByResident_temp LIMIT 1;
    SELECT `id` INTO @prev_id FROM ValidateByResident_temp LIMIT 1;

    #SELECT @row_n, @row_i, @prev_date, @prev_id, @curr_date, @curr_id;

    UPDATE `db_seniorcare_migration`.`tbl_resident_admission` SET `admission_type` = 1 WHERE `id` = @prev_id;
  ELSE
    SELECT `start` INTO @curr_date FROM ValidateByResident_temp LIMIT 1;
    SELECT `id` INTO @curr_id FROM ValidateByResident_temp LIMIT 1;

    #SELECT @row_n, @row_i, @prev_date, @prev_id, @curr_date, @curr_id;

    IF @prev_date IS NULL THEN
      UPDATE `db_seniorcare_migration`.`tbl_resident_admission` SET `end` = @curr_date WHERE `id` = @prev_id;
    END IF;

    SELECT `end` INTO @prev_date FROM ValidateByResident_temp LIMIT 1;
    SELECT `id` INTO @prev_id FROM ValidateByResident_temp LIMIT 1;
  END IF;

  DELETE FROM ValidateByResident_temp LIMIT 1;
  SET @row_n = (SELECT COUNT(1) FROM ValidateByResident_temp);
  SET @row_i = @row_i + 1;
  END WHILE;

  DROP TABLE IF EXISTS ValidateByResident_temp;
END
;;
DROP PROCEDURE IF EXISTS ValidateAll;;
CREATE PROCEDURE ValidateAll()
BEGIN
  DECLARE curr_id INT;

  DROP TABLE IF EXISTS ValidateAll_temp;
  CREATE TEMPORARY TABLE IF NOT EXISTS ValidateAll_temp AS (
    SELECT DISTINCT(`id_resident`) AS `id` FROM `db_seniorcare_migration`.`tbl_resident_admission`
  );

  WHILE (SELECT COUNT(1) FROM ValidateAll_temp) > 0 DO
  SELECT `id` INTO @curr_id FROM ValidateAll_temp LIMIT 1;
  CALL ValidateByResident(@curr_id);
  DELETE FROM ValidateAll_temp LIMIT 1;
  END WHILE;
END
;;
DELIMITER ;


#-------------------------------------------------------------------------
INSERT INTO `db_seniorcare_migration`.`oauth2_client`
  (
    `id`,
    `random_id`,
    `redirect_uris`,
    `secret`,
    `allowed_grant_types`
  )
VALUES
  (
    1,
    '3hmo4b44gomcss0sk8okk0gc88wo0kwocco0s0w4w0w48ww0c8',
    'a:0:{}',
    '4tonqf6g79yc4ccsscokcogk08wscs40wookco8048wwk0k0kw',
    'a:5:{i:0;s:18:"authorization_code";i:1;s:8:"password";i:2;s:13:"refresh_token";i:3;s:5:"token";i:4;s:18:"client_credentials";}'
  );

INSERT INTO `db_seniorcare_migration`.`tbl_space`
  (
    `id`,
    `name`
  )
VALUES
  (
    1,
    'CiminoCare'
  );

INSERT INTO `tbl_role`
  (
    `id`,
    `name`,
    `is_default`,
    `grants`
  )
VALUES
  (
    1,
    'SC Admin',
    0,
    '{"report-room-payor": {"enabled": true}, "persistence-region": {"level": 5, "enabled": true, "identity": 0}, "persistence-facility": {"level": 5, "enabled": true, "identity": 0}, "report-form-manicure": {"enabled": true}, "persistence-apartment": {"level": 5, "enabled": true, "identity": 0}, "report-physician-full": {"enabled": true}, "report-room-room-list": {"enabled": true}, "report-room-room-rent": {"enabled": true}, "report-form-room-audit": {"enabled": true}, "persistence-common-diet": {"level": 5, "enabled": true, "identity": 0}, "persistence-dining_room": {"level": 5, "enabled": true, "identity": 0}, "report-assessment-blank": {"enabled": true}, "report-physician-simple": {"enabled": true}, "report-resident-profile": {"enabled": true}, "persistence-facility_bed": {"level": 5, "enabled": true, "identity": 0}, "report-assessment-filled": {"enabled": true}, "report-form-meal-monitor": {"enabled": true}, "report-room-room-vacancy": {"enabled": true}, "persistence-apartment_bed": {"level": 5, "enabled": true, "identity": 0}, "persistence-facility_room": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-role": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-user": {"level": 5, "enabled": true, "identity": 0}, "report-form-birthday-list": {"enabled": true}, "persistence-apartment_room": {"level": 5, "enabled": true, "identity": 0}, "persistence-assessment-row": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-space": {"level": 5, "enabled": true, "identity": 0}, "report-form-blood-pressure": {"enabled": true}, "report-form-bowel-movement": {"enabled": true}, "report-form-night-activity": {"enabled": true}, "report-resident-event-list": {"enabled": true}, "report-resident-face-sheet": {"enabled": true}, "report-room-room-occupancy": {"enabled": true}, "persistence-assessment-form": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-allergen": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-client": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-diagnosis": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-physician": {"level": 5, "enabled": true, "identity": 0}, "report-form-changeover-notes": {"enabled": true}, "report-form-medication-chart": {"enabled": true}, "report-form-shower-skin-list": {"enabled": true}, "report-room-room-rent-master": {"enabled": true}, "persistence-common-care_level": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-medication": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-salutation": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-speciality": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-user_log": {"level": 5, "enabled": true, "identity": 0}, "report-resident-simple-roster": {"enabled": true}, "persistence-resident-admission": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-auth_code": {"level": 5, "enabled": true, "identity": 0}, "persistence-assessment-category": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-relationship": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-user_phone": {"level": 5, "enabled": true, "identity": 0}, "report-form-medication-list-all": {"enabled": true}, "report-resident-detailed-roster": {"enabled": true}, "report-room-room-rent-master-new": {"enabled": true}, "persistence-assessment-care_level": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-city_state_zip": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-payment_source": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-access_token": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-user_attempt": {"level": 5, "enabled": true, "identity": 0}, "report-resident-sixty-days-roster": {"enabled": true}, "activity-resident-change-physician": {"enabled": true}, "persistence-resident-resident_diet": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_rent": {"level": 5, "enabled": true, "identity": 0}, "persistence-security-refresh_token": {"level": 5, "enabled": true, "identity": 0}, "report-form-shower-skin-inspection": {"enabled": true}, "persistence-common-event_definition": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_event": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_phone": {"level": 5, "enabled": true, "identity": 0}, "persistence-assessment-form_category": {"level": 5, "enabled": true, "identity": 0}, "report-form-medication-list-resident": {"enabled": true}, "report-resident-dietary-restrictions": {"enabled": true}, "persistence-common-responsible_person": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_allergen": {"level": 5, "enabled": true, "identity": 0}, "persistence-assessment-care_level_group": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_diagnosis": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_physician": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_medication": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-medication_form_factor": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-responsible-person-role": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-assessment-assessment": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-responsible_person_phone": {"level": 5, "enabled": true, "identity": 0}, "persistence-common-medical_history_condition": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-assessment-assessment_row": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_medication_allergy": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_responsible_person": {"level": 5, "enabled": true, "identity": 0}, "persistence-resident-resident_medical_history_condition": {"level": 5, "enabled": true, "identity": 0}}'
  );


INSERT INTO `db_seniorcare_migration`.`tbl_user`
  (
    `id`,
    `id_space`,
    `first_name`,
    `last_name`,
    `username`,
    `password`,
    `email`,
    `enabled`,
    `last_activity_at`,
    `password_requested_at`,
    `password_recovery_hash`,
    `completed`,
    `grants`
  )
VALUES
  (
    1,
    1,
    'user',
    'user',
    'user',
    '$argon2i$v=19$m=1024,t=2,p=2$RmlES0pUNkhWQVZsWDBCVg$j+vH8WyvzWBjR6fieZ+u6dVCcRh24BcM2eWuu7NW0L0',
    'user@user.u',
    1,
    '2018-12-08 15:50:56',
    NULL,
    '',
    1,
    '[]'
  );

### Responsible Person Role
INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person_role`
  (
    `id`,
    `id_space`,
    `title`,
    `icon`,
    `is_financially`,
    `is_emergency`
  )
VALUES
  (
    '1',
    '1',
    'Emergency contact',
    'fas fa-phone',
    '0',
    '1'
  ),
  (
    '2',
    '1',
    'Financial contact',
    'fas fa-usd',
    '1',
    '0'
  ),
  (
    '3',
    '1',
    'Legal contact',
    'fas fa-balance-scale',
    '0',
    '0'
  );


INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person_role`
  (
    `id_space`,
    `id`,
    `title`,
    `icon`
  )
VALUES
  (
    1,
    1,
    'Emergency contact',
    'fas fa-phone'
  ),
  (
    1,
    2,
    'Financial contact',
    'fas fa-usd'
  ),
  (
    1,
    3,
    'Legal contact',
    'fas fa-balance-scale'
  );

### Payment Source
INSERT INTO `db_seniorcare_migration`.`tbl_payment_source`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       `alms`.`common_payment_source`.`id`                                       AS 'id',
       IF(`alms`.`common_payment_source`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`common_payment_source`.`title`, '\\s+', ' ')), '') AS 'title'
FROM `alms`.`common_payment_source`;

### Salutation
INSERT INTO `db_seniorcare_migration`.`tbl_salutation`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       `cc_old`.`salutations`.`Salutation_ID`                                 AS 'id',
       IF(`cc_old`.`salutations`.`Salutation`!='', TRIM(REGEXP_REPLACE(`cc_old`.`salutations`.`Salutation`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`salutations`;

### Relationship
INSERT INTO `db_seniorcare_migration`.`tbl_relationship`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       `cc_old`.`relationshiptypes`.`Relationship_ID`                                 AS 'id',
       IF(`cc_old`.`relationshiptypes`.`Relationship`!='', TRIM(REGEXP_REPLACE(`cc_old`.`relationshiptypes`.`Relationship`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`relationshiptypes`
WHERE `cc_old`.`relationshiptypes`.`Relationship_ID` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_relationship`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       (`cc_old`.`relationshiptypes`.`Relationship_ID` + 100)                         AS 'id',
       IF(`cc_old`.`relationshiptypes`.`Relationship`!='', TRIM(REGEXP_REPLACE(`cc_old`.`relationshiptypes`.`Relationship`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`relationshiptypes`
WHERE `cc_old`.`relationshiptypes`.`Relationship_ID` >= 70
  AND `cc_old`.`relationshiptypes`.`Relationship_ID` <= 73;

INSERT INTO `db_seniorcare_migration`.`tbl_relationship`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       (`alms`.`common_relationship`.`id` + 200)                               AS 'id',
       IF(`alms`.`common_relationship`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`common_relationship`.`title`, '\\s+', ' ')), '') AS 'title'
FROM `alms`.`common_relationship`
WHERE `alms`.`common_relationship`.`id` >= 70
  AND `alms`.`common_relationship`.`id` <= 72;

### Care Level
INSERT INTO `db_seniorcare_migration`.`tbl_care_level`
  (
    `id_space`,
    `id`,
    `title`,
    `description`
  )
SELECT 1,
       `cc_old`.`careleveltypes`.`Care_Level_ID`                                 AS 'id',
       IF(`cc_old`.`careleveltypes`.`Care_Level`!='', TRIM(REGEXP_REPLACE(`cc_old`.`careleveltypes`.`Care_Level`, '\\s+', ' ')), '') AS 'title',
       `cc_old`.`careleveltypes`.`Care_Description`                              AS 'description'
FROM `cc_old`.`careleveltypes`;

### City/State/Zip
INSERT INTO `db_seniorcare_migration`.`tbl_city_state_zip`
  (
    `id_space`,
    `id`,
    `state_full`,
    `state_abbr`,
    `zip_main`,
    `zip_sub`,
    `city`
  )
SELECT 1,
       `cc_old`.`citystatezip`.`CSZ_ID`                                         AS 'id',
       IF(`cc_old`.`citystatezip`.`State_Full`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`State_Full`, '\\s+', ' ')), '')  AS 'state_full',
       IF(`cc_old`.`citystatezip`.`State_2_Ltr`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`State_2_Ltr`, '\\s+', ' ')), '') AS 'state_abbr',
       IF(`cc_old`.`citystatezip`.`ZIP_Main`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`ZIP_Main`, '\\s+', ' ')), '')    AS 'zip_main',
       IF(`cc_old`.`citystatezip`.`ZIP_Sub`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`ZIP_Sub`, '\\s+', ' ')), '')     AS 'zip_sub',
       IF(`cc_old`.`citystatezip`.`City`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`City`, '\\s+', ' ')), '')        AS 'city'
FROM `cc_old`.`citystatezip`
WHERE `cc_old`.`citystatezip`.`CSZ_ID` <= 2816;

INSERT INTO `db_seniorcare_migration`.`tbl_city_state_zip`
  (
    `id_space`,
    `id`,
    `state_full`,
    `state_abbr`,
    `zip_main`,
    `zip_sub`,
    `city`
  )
SELECT 1,
       (`cc_old`.`citystatezip`.`CSZ_ID` + 10000)                               AS 'id',
       IF(`cc_old`.`citystatezip`.`State_Full`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`State_Full`, '\\s+', ' ')), '')  AS 'state_full',
       IF(`cc_old`.`citystatezip`.`State_2_Ltr`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`State_2_Ltr`, '\\s+', ' ')), '') AS 'state_abbr',
       IF(`cc_old`.`citystatezip`.`ZIP_Main`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`ZIP_Main`, '\\s+', ' ')), '')    AS 'zip_main',
       IF(`cc_old`.`citystatezip`.`ZIP_Sub`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`ZIP_Sub`, '\\s+', ' ')), '')     AS 'zip_sub',
       IF(`cc_old`.`citystatezip`.`City`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`City`, '\\s+', ' ')), '')        AS 'city'
FROM `cc_old`.`citystatezip`
WHERE `cc_old`.`citystatezip`.`CSZ_ID` >= 2817;

INSERT INTO `db_seniorcare_migration`.`tbl_city_state_zip`
  (
    `id_space`,
    `id`,
    `state_full`,
    `state_abbr`,
    `zip_main`,
    `zip_sub`,
    `city`
  )
SELECT 1,
       (`alms`.`common_city_state_zip`.`id` + 20000)                                   AS 'id',
       IF(`alms`.`common_city_state_zip`.`state_full`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`state_full`, '\\s+', ' ')), '')  AS 'state_full',
       IF(`alms`.`common_city_state_zip`.`state_2_ltr`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`state_2_ltr`, '\\s+', ' ')), '') AS 'state_abbr',
       IF(`alms`.`common_city_state_zip`.`zip_main`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`zip_main`, '\\s+', ' ')), '')    AS 'zip_main',
       IF(`alms`.`common_city_state_zip`.`zip_sub`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`zip_sub`, '\\s+', ' ')), '')     AS 'zip_sub',
       IF(`alms`.`common_city_state_zip`.`city`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`city`, '\\s+', ' ')), '')        AS 'city'
FROM `alms`.`common_city_state_zip`
WHERE `alms`.`common_city_state_zip`.`id` >= 2817;

### Diet
INSERT INTO `db_seniorcare_migration`.`tbl_diet`
  (
    `id_space`,
    `id`,
    `color`,
    `title`
  )
SELECT 1,
       `cc_old`.`dietcategory`.`Dietcategory_ID`                         AS 'id',
       `cc_old`.`dietcategory`.`Color`                                   AS 'color',
       IF(`cc_old`.`dietcategory`.`Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`dietcategory`.`Name`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`dietcategory`;

### Medical History Condition
INSERT INTO `db_seniorcare_migration`.`tbl_medical_history_condition`
  (
    `id_space`,
    `id`,
    `title`,
    `description`
  )
SELECT 1,
       `cc_old`.`medicalhistoryconditions`.`Medical_History_Condition_ID`                             AS 'id',
       IF(`cc_old`.`medicalhistoryconditions`.`Condition_Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`medicalhistoryconditions`.`Condition_Name`, '\\s+', ' ')), '')        AS 'title',
       IF(`cc_old`.`medicalhistoryconditions`.`Condition_Description`!='', TRIM(REGEXP_REPLACE(`cc_old`.`medicalhistoryconditions`.`Condition_Description`, '\\s+', ' ')), '') AS 'description'
FROM `cc_old`.`medicalhistoryconditions`;

### Allergen
INSERT INTO `db_seniorcare_migration`.`tbl_allergen`
  (
    `id_space`,
    `id`,
    `title`,
    `description`
  )
SELECT 1,
       `cc_old`.`allergens`.`Allergen_ID`                                             AS 'id',
       IF(`cc_old`.`allergens`.`Allergen`!='', TRIM(REGEXP_REPLACE(`cc_old`.`allergens`.`Allergen`, '\\s+', ' ')), '')             AS 'title',
       IF(`cc_old`.`allergens`.`Allergen_Description`!='', TRIM(REGEXP_REPLACE(`cc_old`.`allergens`.`Allergen_Description`, '\\s+', ' ')), '') AS 'description'
FROM `cc_old`.`allergens`;

### Diagnosis
INSERT INTO `db_seniorcare_migration`.`tbl_diagnosis`
  (
    `id_space`,
    `id`,
    `title`,
    `description`,
    `acronym`
  )
SELECT 1,
       IF(`cc_old`.`medicalconditions`.`Medical_Condition_ID` >= 2615,
          (`cc_old`.`medicalconditions`.`Medical_Condition_ID` + 10000),
          `cc_old`.`medicalconditions`.`Medical_Condition_ID`)                                 AS 'id',
       IF(`cc_old`.`medicalconditions`.`Condition_Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`medicalconditions`.`Condition_Name`, '\\s+', ' ')), '')        AS 'title',
       IF(`cc_old`.`medicalconditions`.`Condition_Description`!='', TRIM(REGEXP_REPLACE(`cc_old`.`medicalconditions`.`Condition_Description`, '\\s+', ' ')), '') AS 'description',
       IF(`cc_old`.`medicalconditions`.`Acronym`!='', TRIM(REGEXP_REPLACE(`cc_old`.`medicalconditions`.`Acronym`, '\\s+', ' ')), '')               AS 'acronym'
FROM `cc_old`.`medicalconditions`;

INSERT INTO `db_seniorcare_migration`.`tbl_diagnosis`
  (
    `id_space`,
    `id`,
    `title`,
    `description`,
    `acronym`
  )
SELECT 1,
       (`alms`.`common_diagnosis`.`id` + 20000)                                   AS 'id',
       IF(`alms`.`common_diagnosis`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`common_diagnosis`.`title`, '\\s+', ' ')), '')       AS 'title',
       IF(`alms`.`common_diagnosis`.`description`!='', TRIM(REGEXP_REPLACE(`alms`.`common_diagnosis`.`description`, '\\s+', ' ')), '') AS 'description',
       IF(`alms`.`common_diagnosis`.`acronym`!='', TRIM(REGEXP_REPLACE(`alms`.`common_diagnosis`.`acronym`, '\\s+', ' ')), '')     AS 'acronym'
FROM `alms`.`common_diagnosis`
WHERE `alms`.`common_diagnosis`.`id` >= 2615;

### Medication
INSERT INTO `db_seniorcare_migration`.`tbl_medication`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       IF(`cc_old`.`medications`.`Medication_ID` >= 1035,
          (`cc_old`.`medications`.`Medication_ID` + 10000),
          `cc_old`.`medications`.`Medication_ID`)                           AS 'id',
       IF(`cc_old`.`medications`.`Med_Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`medications`.`Med_Name`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`medications`;

INSERT INTO `db_seniorcare_migration`.`tbl_medication`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       (`alms`.`common_medication`.`id` + 20000)                             AS 'id',
       IF(`alms`.`common_medication`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`common_medication`.`title`, '\\s+', ' ')), '') AS 'title'
FROM `alms`.`common_medication`
WHERE `alms`.`common_medication`.`id` >= 1035;

### Physician
INSERT INTO `db_seniorcare_migration`.`tbl_physician`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `office_phone`,
    `fax`,
    `emergency_phone`,
    `email`,
    `website_url`
  )
SELECT 1,
       `cc_old`.`physicians`.`Physician_ID`                                   AS 'id',
       IF(`cc_old`.`physicians`.`CSZ_ID` >= 2817,
          (`cc_old`.`physicians`.`CSZ_ID` + 10000),
          `cc_old`.`physicians`.`CSZ_ID`)                                     AS 'id_csz',
       `cc_old`.`people`.`Salutation_ID`                                      AS 'id_salutation',
       IF(`cc_old`.`people`.`First_Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`First_Name`, '\\s+', ' ')), '')      AS 'first_name',
       IF(`cc_old`.`people`.`Last_name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`Last_name`, '\\s+', ' ')), '')       AS 'last_name',
       IF(`cc_old`.`people`.`Middle_Initial`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`Middle_Initial`, '\\s+', ' ')), '')  AS 'middle_name',
       IF(`cc_old`.`physicians`.`Address_1`!='', TRIM(REGEXP_REPLACE(`cc_old`.`physicians`.`Address_1`, '\\s+', ' ')), '')   AS 'address_1',
       IF(`cc_old`.`physicians`.`Address_2`!='', TRIM(REGEXP_REPLACE(`cc_old`.`physicians`.`Address_2`, '\\s+', ' ')), '')   AS 'address_2',
       `cc_old`.`physicians`.`Office_Phone`                                   AS 'office_phone',
       `cc_old`.`physicians`.`Fax`                                            AS 'fax',
       `cc_old`.`physicians`.`Emergency_Phone`                                AS 'emergency_phone',
       IF(`cc_old`.`physicians`.`Email`!='', TRIM(REGEXP_REPLACE(`cc_old`.`physicians`.`Email`, '\\s+', ' ')), '')       AS 'email',
       IF(`cc_old`.`physicians`.`Website_URL`!='', TRIM(REGEXP_REPLACE(`cc_old`.`physicians`.`Website_URL`, '\\s+', ' ')), '') AS 'website_url'
FROM `cc_old`.`physicians`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID`;

INSERT INTO `db_seniorcare_migration`.`tbl_physician`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `office_phone`,
    `fax`,
    `emergency_phone`,
    `email`,
    `website_url`
  )
SELECT 1,
       (`alms`.`common_physician`.`id` + 10000)                                      AS `id`,
       IF(`alms`.`common_physician`.`id_common_csz` >= 2817,
          (`alms`.`common_physician`.`id_common_csz` + 20000),
          `alms`.`common_physician`.`id_common_csz`)                                 AS `id_csz`,
       `alms`.`common_physician`.`id_common_salutation`                              AS `id_salutation`,
       IF(`alms`.`common_physician`.`first_name`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`first_name`, '\\s+', ' ')), '')     AS `first_name`,
       IF(`alms`.`common_physician`.`last_name`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`last_name`, '\\s+', ' ')), '')      AS `last_name`,
       IF(`alms`.`common_physician`.`middle_initial`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`middle_initial`, '\\s+', ' ')), '') AS `middle_name`,
       IF(`alms`.`common_physician`.`address_1`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`address_1`, '\\s+', ' ')), '')      AS `address_1`,
       IF(`alms`.`common_physician`.`address_2`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`address_2`, '\\s+', ' ')), '')      AS `address_2`,
       `alms`.`common_physician`.`office_phone`                                      AS `office_phone`,
       `alms`.`common_physician`.`fax`                                               AS `fax`,
       `alms`.`common_physician`.`emergency_phone`                                   AS `emergency_phone`,
       IF(`alms`.`common_physician`.`email`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`email`, '\\s+', ' ')), '')          AS `email`,
       IF(`alms`.`common_physician`.`website_url`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`website_url`, '\\s+', ' ')), '')    AS `website_url`
FROM `alms`.`common_physician`;

### Responsible Person
INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `email`
  )
SELECT 1,
       `cc_old`.`responsibleperson`.`Responsible_Person_ID`                        AS 'id',
       IF(`cc_old`.`responsibleperson`.`CSZ_ID` >= 2817,
          (`cc_old`.`responsibleperson`.`CSZ_ID` + 10000),
          `cc_old`.`responsibleperson`.`CSZ_ID`)                                   AS 'id_csz',
       `cc_old`.`people`.`Salutation_ID`                                           AS 'id_salutation',
       IF(`cc_old`.`people`.`First_Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`First_Name`, '\\s+', ' ')), '')           AS 'first_name',
       IF(`cc_old`.`people`.`Last_name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`Last_name`, '\\s+', ' ')), '')            AS 'last_name',
       IF(`cc_old`.`people`.`Middle_Initial`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`Middle_Initial`, '\\s+', ' ')), '')       AS 'middle_name',
       IF(`cc_old`.`responsibleperson`.`Address_1`!='', TRIM(REGEXP_REPLACE(`cc_old`.`responsibleperson`.`Address_1`, '\\s+', ' ')), '') AS 'address_1',
       IF(`cc_old`.`responsibleperson`.`Address_2`!='', TRIM(REGEXP_REPLACE(`cc_old`.`responsibleperson`.`Address_2`, '\\s+', ' ')), '') AS 'address_2',
       REPLACE(`cc_old`.`responsibleperson`.`Email`, 'aaa@aaa.aa', NULL)           AS 'email'
FROM `cc_old`.`responsibleperson`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`responsibleperson`.`People_ID` = `cc_old`.`people`.`People_ID`;

INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `address_1`,
    `address_2`,
    `email`
  )
SELECT 1,
       (`alms`.`common_responsible_person`.`id` + 10000)                                      AS 'id',
       IF(`alms`.`common_responsible_person`.`id_csz` >= 2817,
          (`alms`.`common_responsible_person`.`id_csz` + 20000),
          `alms`.`common_responsible_person`.`id_csz`)                                        AS 'id_csz',
       `alms`.`common_responsible_person`.`id_common_salutation`                              AS 'id_salutation',
       IF(`alms`.`common_responsible_person`.`first_name`!='', TRIM(REGEXP_REPLACE(`alms`.`common_responsible_person`.`first_name`, '\\s+', ' ')), '')     AS 'first_name',
       IF(`alms`.`common_responsible_person`.`last_name`!='', TRIM(REGEXP_REPLACE(`alms`.`common_responsible_person`.`last_name`, '\\s+', ' ')), '')      AS 'last_name',
       IF(`alms`.`common_responsible_person`.`middle_initial`!='', TRIM(REGEXP_REPLACE(`alms`.`common_responsible_person`.`middle_initial`, '\\s+', ' ')), '') AS 'middle_name',
       IF(`alms`.`common_responsible_person`.`address_1`!='', TRIM(REGEXP_REPLACE(`alms`.`common_responsible_person`.`address_1`, '\\s+', ' ')), '')      AS 'address_1',
       IF(`alms`.`common_responsible_person`.`address_2`!='', TRIM(REGEXP_REPLACE(`alms`.`common_responsible_person`.`address_2`, '\\s+', ' ')), '')      AS 'address_2',
       IF(`alms`.`common_responsible_person`.`email`!='', TRIM(REGEXP_REPLACE(`alms`.`common_responsible_person`.`email`, '\\s+', ' ')), '')          AS 'email'
FROM `alms`.`common_responsible_person`;

### Responsible Peron Phone
INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person_phone`
  (
    `id_responsible_person`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT `cc_old`.`residentresponsiblepersonphone`.`Responsible_Person_ID` AS 'id_responsible_person',
       1                                                                 AS 'compatibility',
       `cc_old`.`residentresponsiblepersonphone`.`number_type`           AS 'type',
       `cc_old`.`residentresponsiblepersonphone`.`number`                AS 'number',
       `cc_old`.`residentresponsiblepersonphone`.`is_primary`            AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `cc_old`.`residentresponsiblepersonphone`;

# CALL `alms`.`json_row_data`('tmp_responsible_phone_data', 'common_responsible_person', 'phones');

INSERT INTO `db_seniorcare_migration`.`tbl_responsible_person_phone`
  (
    `id_responsible_person`,
    `compatibility`,
    `type`,
    `number`,
    `extension`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT (`alms`.`common_responsible_person`.`id` + 10000)    AS 'id_responsible_person',
       IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].c'))) = '"US"', 1,
          2)                                                AS 'compatibility',
       JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`,
                                 CONCAT('$[', idx, '].t'))) AS 'type',
       JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`,
                                 CONCAT('$[', idx, '].n'))) AS 'number',
       NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].e'))),
              '')                                           AS 'extension',
       IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].p'))) = 'true', 1,
          0)                                                AS 'is_primary',
       IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, '].s'))) = 'true', 1,
          0)                                                AS 'is_sms_enabled'
FROM `alms`.`common_responsible_person`
       -- Inline table of sequential values to index into JSON array
       JOIN (SELECT `alms`.`tmp_responsible_phone_data`.`seq` AS idx
             FROM `alms`.`tmp_responsible_phone_data`) AS INDEXES
WHERE `alms`.`common_responsible_person`.`phones` IS NOT NULL
  AND `alms`.`common_responsible_person`.`phones` != '[]'
  AND JSON_EXTRACT(`alms`.`common_responsible_person`.`phones`, CONCAT('$[', idx, ']')) IS NOT NULL;


######################################################
### Resident
INSERT INTO `db_seniorcare_migration`.`tbl_resident`
  (
    `id_space`,
    `id`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `birthday`,
    `gender`
  )
SELECT 1,
       `cc_old`.`residents`.`Resident_ID`                                    AS 'id',
       `cc_old`.`people`.`Salutation_ID`                                     AS 'id_salutation',
       IF(`cc_old`.`people`.`First_Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`First_Name`, '\\s+', ' ')), '')     AS 'first_name',
       IF(`cc_old`.`people`.`Last_name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`Last_name`, '\\s+', ' ')), '')      AS 'last_name',
       IF(`cc_old`.`people`.`Middle_Initial`!='', TRIM(REGEXP_REPLACE(`cc_old`.`people`.`Middle_Initial`, '\\s+', ' ')), '') AS 'middle_name',
       `cc_old`.`residents`.`DOB`                                            AS 'birthday',
       `cc_old`.`residents`.`Sex`                                            AS 'gender'
FROM `cc_old`.`residents`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`residents`.`People_ID` = `cc_old`.`people`.`People_ID`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident`
  (
    `id_space`,
    `id`,
    `id_salutation`,
    `first_name`,
    `last_name`,
    `middle_name`,
    `birthday`,
    `gender`
  )
SELECT 1,
       (`alms`.`base_resident`.`id` + 10000)                                      AS 'id',
       `alms`.`base_resident`.`id_common_salutation`                              AS 'id_salutation',
       IF(`alms`.`base_resident`.`first_name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident`.`first_name`, '\\s+', ' ')), '')     AS 'first_name',
       IF(`alms`.`base_resident`.`last_name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident`.`last_name`, '\\s+', ' ')), '')      AS 'last_name',
       IF(`alms`.`base_resident`.`middle_initial`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident`.`middle_initial`, '\\s+', ' ')), '') AS 'middle_name',
       `alms`.`base_resident`.`dob`                                               AS 'birthday',
       `alms`.`base_resident`.`sex`                                               AS 'gender'
FROM `alms`.`base_resident`;

### Resident Phone
INSERT INTO `db_seniorcare_migration`.`tbl_resident_phone`
  (
    `id_resident`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT `cc_old`.`residents`.`Resident_ID` AS 'id_resident',
       1                                  AS 'compatibility',
       1                                  AS 'type',
       `cc_old`.`residents`.`Phone`       AS 'number',
       1                                  AS 'is_primary',
       0                                  AS 'is_sms_enabled'
FROM `cc_old`.`residents`
WHERE `cc_old`.`residents`.`Phone` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_phone`
  (
    `id_resident`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT `cc_old`.`resident_phone`.`resident_id` AS 'id_resident',
       1                                       AS 'compatibility',
       `cc_old`.`resident_phone`.`type`        AS 'type',
       `cc_old`.`resident_phone`.`number`      AS 'number',
       `cc_old`.`resident_phone`.`is_primary`  AS 'is_primary',
       0                                       AS 'is_sms_enabled'
FROM `cc_old`.`resident_phone`;

# CALL `alms`.`json_row_data`('tmp_resident_phone_data', 'base_resident', 'phones');
INSERT INTO `db_seniorcare_migration`.`tbl_resident_phone`
  (
    `id_resident`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT (`alms`.`base_resident`.`id` + 10000)                AS 'id_resident',
       IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].c'))) = '"US"', 1,
          2)                                                AS 'compatibility',
       JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`,
                                 CONCAT('$[', idx, '].t'))) AS 'type',
       JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`,
                                 CONCAT('$[', idx, '].n'))) AS 'number',
       IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].p'))) = 'true', 1,
          0)                                                AS 'is_primary',
       IF(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, '].s'))) = 'true', 1,
          0)                                                AS 'is_sms_enabled'
FROM `alms`.`base_resident`
       -- Inline table of sequential values to index into JSON array
       JOIN (SELECT `alms`.`tmp_resident_phone_data`.`seq` AS idx FROM `alms`.`tmp_resident_phone_data`) AS INDEXES
WHERE `alms`.`base_resident`.`phones` IS NOT NULL
  AND `alms`.`base_resident`.`phones` != '[]'
  AND JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, ']')) IS NOT NULL;


# Resident Physician
INSERT INTO `db_seniorcare_migration`.`tbl_resident_physician`
  (
    `id_resident`,
    `id_physician`,
    `is_primary`
  )
SELECT `cc_old`.`residents`.`Resident_ID`  AS 'id_resident',
       `cc_old`.`residents`.`Physician_ID` AS 'id_physician',
       1                                   AS 'is_primary'
FROM `cc_old`.`residents`
WHERE `cc_old`.`residents`.`Physician_ID` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_physician`
  (
    `id_resident`,
    `id_physician`,
    `is_primary`
  )
SELECT (`alms`.`base_resident`.`id` + 10000)           AS 'id',
       (`alms`.`base_resident`.`id_physician` + 10000) AS 'id_physician',
       1                                               AS 'is_primary'
FROM `alms`.`base_resident`
WHERE `alms`.`base_resident`.`id_physician` IS NOT NULL;

# Resident Responsible Person
INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`
  )
SELECT `cc_old`.`residentresponsibleperson`.`Res_RP_ID`             AS 'id',
       `cc_old`.`residentresponsibleperson`.`Resident_ID`           AS 'id_resident',
       `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID` AS 'id_responsible_person',
       `cc_old`.`residentresponsibleperson`.`Relationship_ID`       AS 'id_relationship'
FROM `cc_old`.`residentresponsibleperson`
WHERE `cc_old`.`residentresponsibleperson`.`Relationship_ID` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`
  )
SELECT `cc_old`.`residentresponsibleperson`.`Res_RP_ID`               AS 'id',
       `cc_old`.`residentresponsibleperson`.`Resident_ID`             AS 'id_resident',
       `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID`   AS 'id_responsible_person',
       (`cc_old`.`residentresponsibleperson`.`Relationship_ID` + 100) AS 'id_relationship'
FROM `cc_old`.`residentresponsibleperson`
WHERE `cc_old`.`residentresponsibleperson`.`Relationship_ID` >= 70
  AND `cc_old`.`residentresponsibleperson`.`Relationship_ID` <= 73;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person_roles`
  (
    `id_resident_responsible_person`,
    `id_responsible_person_role`
  )
SELECT `cc_old`.`residentresponsibleperson`.`Res_RP_ID` AS 'id_resident_responsible_person',
       1                                                AS 'id_responsible_person_role'
FROM `cc_old`.`residentresponsibleperson`
       INNER JOIN `cc_old`.`responsibleperson`
                  ON `cc_old`.`responsibleperson`.`Responsible_Person_ID` =
                     `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID`
WHERE `cc_old`.`responsibleperson`.`Emergency` = 1;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person_roles`
  (
    `id_resident_responsible_person`,
    `id_responsible_person_role`
  )
SELECT `cc_old`.`residentresponsibleperson`.`Res_RP_ID` AS 'id_resident_responsible_person',
       2                                                AS 'id_responsible_person_role'
FROM `cc_old`.`residentresponsibleperson`
       INNER JOIN `cc_old`.`responsibleperson`
                  ON `cc_old`.`responsibleperson`.`Responsible_Person_ID` =
                     `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID`
WHERE `cc_old`.`responsibleperson`.`Financially` = 1;


INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`
  )
SELECT (`alms`.`base_resident_responsible_person`.`id` + 10000)                    AS 'id',
       (`alms`.`base_resident_responsible_person`.`id_resident` + 10000)           AS 'id_resident',
       (`alms`.`base_resident_responsible_person`.`id_responsible_person` + 10000) AS 'id_physician',
       `alms`.`base_resident_responsible_person`.`id_relationship`                 AS 'id_relationship'
FROM `alms`.`base_resident_responsible_person`
WHERE `alms`.`base_resident_responsible_person`.`id_relationship` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`
  )
SELECT (`alms`.`base_resident_responsible_person`.`id` + 10000)                    AS 'id',
       (`alms`.`base_resident_responsible_person`.`id_resident` + 10000)           AS 'id_resident',
       (`alms`.`base_resident_responsible_person`.`id_responsible_person` + 10000) AS 'id_physician',
       (`alms`.`base_resident_responsible_person`.`id_relationship` + 200)         AS 'id_relationship'
FROM `alms`.`base_resident_responsible_person`
WHERE `alms`.`base_resident_responsible_person`.`id_relationship` >= 70
  AND `alms`.`base_resident_responsible_person`.`id_relationship` <= 72;


INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person_roles`
  (
    `id_resident_responsible_person`,
    `id_responsible_person_role`
  )
SELECT (`alms`.`base_resident_responsible_person`.`id` + 10000) AS 'id_resident_responsible_person',
       1                                                        AS 'id_responsible_person_role'
FROM `alms`.`base_resident_responsible_person`
       INNER JOIN `alms`.`common_responsible_person`
                  ON `alms`.`common_responsible_person`.`id` =
                     `alms`.`base_resident_responsible_person`.`id_responsible_person`
WHERE `alms`.`common_responsible_person`.`is_emergency` = 1;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person_roles`
  (
    `id_resident_responsible_person`,
    `id_responsible_person_role`
  )
SELECT (`alms`.`base_resident_responsible_person`.`id` + 10000) AS 'id_resident_responsible_person',
       2                                                        AS 'id_responsible_person_role'
FROM `alms`.`base_resident_responsible_person`
       INNER JOIN `alms`.`common_responsible_person`
                  ON `alms`.`common_responsible_person`.`id` =
                     `alms`.`base_resident_responsible_person`.`id_responsible_person`
WHERE `alms`.`common_responsible_person`.`is_financially` = 1;

#--- Add to end of tbl_resident_responsible_person

UPDATE `db_seniorcare_migration`.`tbl_resident_responsible_person`
SET `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 172
WHERE `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 270;

UPDATE `db_seniorcare_migration`.`tbl_resident_responsible_person`
SET `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 170
WHERE `db_seniorcare_migration`.`tbl_resident_responsible_person`.`id_relationship` = 272;

DELETE
FROM `db_seniorcare_migration`.`tbl_relationship`
WHERE `db_seniorcare_migration`.`tbl_relationship`.`id` = 270;
DELETE
FROM `db_seniorcare_migration`.`tbl_relationship`
WHERE `db_seniorcare_migration`.`tbl_relationship`.`id` = 272;

########################################################
# Facility
INSERT INTO `db_seniorcare_migration`.`tbl_facility`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
  )
SELECT 1,
       `cc_old`.`facilities`.`Facility_ID`                                       AS 'id',
       IF(`cc_old`.`facilities`.`CSZ_ID` >= 2817,
          (`cc_old`.`facilities`.`CSZ_ID` + 10000),
          `cc_old`.`facilities`.`CSZ_ID`)                                        AS 'id_csz',
       IF(`cc_old`.`facilities`.`Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`facilities`.`Name`, '\\s+', ' ')), '')           AS 'name',
       NULL                                                                      AS 'description',
       IF(`cc_old`.`facilities`.`Shorthand`!='', TRIM(REGEXP_REPLACE(`cc_old`.`facilities`.`Shorthand`, '\\s+', ' ')), '')      AS 'shorthand',
       `cc_old`.`facilities`.`Phone`                                             AS 'phone',
       `cc_old`.`facilities`.`Fax`                                               AS 'fax',
       IF(`cc_old`.`facilities`.`Street_Address`!='', TRIM(REGEXP_REPLACE(`cc_old`.`facilities`.`Street_Address`, '\\s+', ' ')), '') AS 'address',
       IF(`cc_old`.`facilities`.`License`!='', TRIM(REGEXP_REPLACE(`cc_old`.`facilities`.`License`, '\\s+', ' ')), '')        AS 'license',
       `cc_old`.`facilities`.`MaxBedsNumber`                                     AS 'license_capacity',
       `cc_old`.`facilities`.`MaxBedsNumber`                                     AS 'capacity'
FROM `cc_old`.`facilities`;

INSERT INTO `db_seniorcare_migration`.`tbl_facility`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
  )
SELECT 1,
       (`alms`.`base_group`.`id` + 10000)                                     AS 'id',
       IF(`alms`.`base_group`.`id_csz` >= 2817,
          (`alms`.`base_group`.`id_csz` + 20000),
          `alms`.`base_group`.`id_csz`)                                       AS 'id_csz',
       IF(`alms`.`base_group`.`name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`name`, '\\s+', ' ')), '')          AS 'name',
       TRIM(
           REGEXP_REPLACE(`alms`.`base_group`.`description`, '\\s+', ' '))    AS 'description',
       IF(`alms`.`base_group`.`shorthand`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`shorthand`, '\\s+', ' ')), '')     AS 'shorthand',
       `alms`.`base_group`.`phone`                                            AS 'phone',
       `alms`.`base_group`.`fax`                                              AS 'fax',
       TRIM(
           REGEXP_REPLACE(`alms`.`base_group`.`street_address`, '\\s+', ' ')) AS 'address',
       IF(`alms`.`base_group`.`license`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`license`, '\\s+', ' ')), '')       AS 'license',
       `alms`.`base_group`.`max_beds_number`                                  AS 'license_capacity',
       `alms`.`base_group`.`max_beds_number`                                  AS 'capacity'
FROM `alms`.`base_group`
WHERE `alms`.`base_group`.`discriminator` = 'FFC';

# Facility Dining Room
INSERT INTO `db_seniorcare_migration`.`tbl_dining_room`
  (
    `id`,
    `id_facility`,
    `title`
  )
SELECT `cc_old`.`dinningroom`.`Dinningroom_ID`                          AS 'id',
       `cc_old`.`dinningroom`.`Facility_ID`                             AS 'id_facility',
       IF(`cc_old`.`dinningroom`.`Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`dinningroom`.`Name`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`dinningroom`;

INSERT INTO `db_seniorcare_migration`.`tbl_dining_room`
  (
    `id`,
    `id_facility`,
    `title`
  )
SELECT (`alms`.`residents_ffc_dining_room`.`id` + 10000)                             AS 'id',
       (`alms`.`residents_ffc_dining_room`.`id_facility` + 10000)                    AS 'id_facility',
       IF(`alms`.`residents_ffc_dining_room`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`residents_ffc_dining_room`.`title`, '\\s+', ' ')), '') AS 'title'
FROM `alms`.`residents_ffc_dining_room`;


# Facility Room
SELECT CONCAT(
           'INSERT INTO `db_seniorcare_migration`.`tbl_facility_room` (`db_seniorcare_migration`.`tbl_facility_room`.`id_facility`, `db_seniorcare_migration`.`tbl_facility_room`.`number`, `db_seniorcare_migration`.`tbl_facility_room`.`floor`, `db_seniorcare_migration`.`tbl_facility_room`.`notes`) SELECT ',
           `cc_old`.`facilityrooms`.`Facility_ID`,
           ', \'',
           REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
           '\', \'',
           `cc_old`.`facilityrooms`.`Floor`,
           '\', "',
           IFNULL(TRIM(`cc_old`.`facilityrooms`.`Notes`), ''),
           '" WHERE NOT EXISTS ( SELECT `id` FROM `db_seniorcare_migration`.`tbl_facility_room` WHERE `db_seniorcare_migration`.`tbl_facility_room`.`id_facility` = ',
           `cc_old`.`facilityrooms`.`Facility_ID`,
           ' AND `db_seniorcare_migration`.`tbl_facility_room`.`number` = \'',
           REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
           '\');'
         ) AS 'statement'
FROM `cc_old`.`facilityrooms`
WHERE `cc_old`.`facilityrooms`.`Facility_ID` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_facility_bed`
  (
    `id`,
    `id_facility_room`,
    `number`
  )
SELECT `cc_old`.`facilityRooms`.`Room_ID` AS 'id',
       (SELECT `id`
        FROM `db_seniorcare_migration`.`tbl_facility_room`
        WHERE `db_seniorcare_migration`.`tbl_facility_room`.`id_facility` = `cc_old`.`facilityrooms`.`Facility_ID`
          AND `db_seniorcare_migration`.`tbl_facility_room`.`number` =
              REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'))
                                          AS 'id_facility_room',
       IF(TRIM(REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$2')) = '', 'A',
          TRIM(REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$2')))
                                          AS 'number'
FROM `cc_old`.`facilityRooms`
WHERE `cc_old`.`facilityrooms`.`Facility_ID` IS NOT NULL;

#--- Review room bed shared/private issues

#------
INSERT INTO `db_seniorcare_migration`.`tbl_facility_room`
  (
    `id`,
    `id_facility`,
    `number`,
    `floor`,
    `notes`
  )
SELECT (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id',
       (`alms`.`base_sub_group`.`id_group` + 10000)                                    AS 'id_facility',
       REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1') AS 'number',
       `alms`.`base_sub_group`.`floor`                                                 AS 'floor',
       `alms`.`base_sub_group`.`notes`                                                 AS 'notes'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'FFC'
  AND `alms`.`base_sub_group`.`parent_room_id` IS NULL
  /***/
  AND `alms`.`base_sub_group`.`id` NOT IN
      (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

INSERT INTO `db_seniorcare_migration`.`tbl_facility_bed`
  (
    `id`,
    `id_facility_room`,
    `number`
  )
SELECT (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id',
       (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id_facility_room',
       REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2') AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'FFC'
  AND `alms`.`base_sub_group`.`parent_room_id` IS NULL
  AND `alms`.`base_sub_group`.`is_shared` = 0
  /***/
  AND `alms`.`base_sub_group`.`id` NOT IN
      (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

INSERT INTO `db_seniorcare_migration`.`tbl_facility_bed`
  (
    `id`,
    `id_facility_room`,
    `number`
  )
SELECT (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id',
       (`alms`.`base_sub_group`.`parent_room_id` + 10000)                              AS 'id_facility_room',
       REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2') AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'FFC'
  AND `alms`.`base_sub_group`.`parent_room_id` IS NOT NULL
  AND `alms`.`base_sub_group`.`is_shared` = 0
  /***/
  AND `alms`.`base_sub_group`.`id` NOT IN
      (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

# Apartment
INSERT INTO `db_seniorcare_migration`.`tbl_apartment`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
  )
SELECT 1,
       `cc_old`.`il_apartment`.`id`                                                AS 'id',
       IF(`cc_old`.`il_apartment`.`CSZ_ID` >= 2817,
          (`cc_old`.`il_apartment`.`CSZ_ID` + 10000),
          `cc_old`.`il_apartment`.`CSZ_ID`)                                        AS 'id_csz',
       IF(`cc_old`.`il_apartment`.`name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`il_apartment`.`name`, '\\s+', ' ')), '')           AS 'name',
       NULL                                                                        AS 'description',
       IF(`cc_old`.`il_apartment`.`Shorthand`!='', TRIM(REGEXP_REPLACE(`cc_old`.`il_apartment`.`Shorthand`, '\\s+', ' ')), '')      AS 'shorthand',
       `cc_old`.`il_apartment`.`Phone`                                             AS 'phone',
       `cc_old`.`il_apartment`.`Fax`                                               AS 'fax',
       IF(`cc_old`.`il_apartment`.`Street_Address`!='', TRIM(REGEXP_REPLACE(`cc_old`.`il_apartment`.`Street_Address`, '\\s+', ' ')), '') AS 'address',
       IF(`cc_old`.`il_apartment`.`License`!='', TRIM(REGEXP_REPLACE(`cc_old`.`il_apartment`.`License`, '\\s+', ' ')), '')        AS 'license',
       0                                                                           AS 'license_capacity',
       `cc_old`.`il_apartment`.`MaxBedsNumber`                                     AS 'license_capacity'
FROM `cc_old`.`il_apartment`;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment`
  (
    `id_space`,
    `id`,
    `id_csz`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`,
    `address`,
    `license`,
    `license_capacity`,
    `capacity`
  )
SELECT 1,
       (`alms`.`base_group`.`id` + 10000)                                      AS 'id',
       IF(`alms`.`base_group`.`id_csz` >= 2817,
          (`alms`.`base_group`.`id_csz` + 20000),
          `alms`.`base_group`.`id_csz`)                                        AS 'id_csz',
       IF(`alms`.`base_group`.`name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`name`, '\\s+', ' ')), '')           AS 'name',
       IF(`alms`.`base_group`.`description`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`description`, '\\s+', ' ')), '')    AS 'description',
       IF(`alms`.`base_group`.`shorthand`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`shorthand`, '\\s+', ' ')), '')      AS 'shorthand',
       `alms`.`base_group`.`phone`                                             AS 'phone',
       `alms`.`base_group`.`fax`                                               AS 'fax',
       IF(`alms`.`base_group`.`street_address`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`street_address`, '\\s+', ' ')), '') AS 'address',
       IF(`alms`.`base_group`.`license`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`license`, '\\s+', ' ')), '')        AS 'license',
       0                                                                       AS 'license_capacity',
       `alms`.`base_group`.`max_beds_number`                                   AS 'capacity'
FROM `alms`.`base_group`
WHERE `alms`.`base_group`.`discriminator` = 'IL';

# Apartment Room
SELECT CONCAT(
           'INSERT INTO `db_seniorcare_migration`.`tbl_apartment_room` (`db_seniorcare_migration`.`tbl_apartment_room`.`id_apartment`, `db_seniorcare_migration`.`tbl_apartment_room`.`number`, `db_seniorcare_migration`.`tbl_apartment_room`.`floor`, `db_seniorcare_migration`.`tbl_apartment_room`.`notes`) SELECT ',
           `cc_old`.`il_room`.`apartment_id`,
           ', \'',
           REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
           '\', \'',
           `cc_old`.`il_room`.`Floor`,
           '\', "',
           IFNULL(TRIM(`cc_old`.`il_room`.`Notes`), ''),
           '" WHERE NOT EXISTS ( SELECT `id` FROM `db_seniorcare_migration`.`tbl_apartment_room` WHERE `db_seniorcare_migration`.`tbl_apartment_room`.`id_apartment` = ',
           `cc_old`.`il_room`.`apartment_id`,
           ' AND `db_seniorcare_migration`.`tbl_apartment_room`.`number` = \'',
           REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
           '\');'
         ) AS 'statement'
FROM `cc_old`.`il_room`
WHERE `cc_old`.`il_room`.`apartment_id` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment_bed`
  (
    `id`,
    `id_apartment_room`,
    `number`
  )
SELECT `cc_old`.`il_room`.`id` AS 'id',
       (SELECT `id`
        FROM `db_seniorcare_migration`.`tbl_apartment_room`
        WHERE `db_seniorcare_migration`.`tbl_apartment_room`.`id_apartment` = `cc_old`.`il_room`.`apartment_id`
          AND `db_seniorcare_migration`.`tbl_apartment_room`.`number` =
              REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1'))
                               AS 'id_apartment_room',
       IF(TRIM(REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')) = '', 'A',
          TRIM(REGEXP_REPLACE(`cc_old`.`il_room`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2')))
                               AS 'number'
FROM `cc_old`.`il_room`
WHERE `cc_old`.`il_room`.`apartment_id` IS NOT NULL;

#--- Review room bed shared/private issues
INSERT INTO `db_seniorcare_migration`.`tbl_apartment_room`
  (
    `id`,
    `id_apartment`,
    `number`,
    `floor`,
    `notes`
  )
SELECT (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id',
       (`alms`.`base_sub_group`.`id_group` + 10000)                                    AS 'id_apartment',
       REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$1') AS 'number',
       `alms`.`base_sub_group`.`floor`                                                 AS 'floor',
       `alms`.`base_sub_group`.`notes`                                                 AS 'notes'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'IL'
  AND `alms`.`base_sub_group`.`parent_room_id` IS NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment_bed`
  (
    `id`,
    `id_apartment_room`,
    `number`
  )
SELECT (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id',
       (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id_apartment_room',
       REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2') AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'IL'
  AND `alms`.`base_sub_group`.`parent_room_id` IS NULL
  AND `alms`.`base_sub_group`.`is_shared` = 0;

INSERT INTO `db_seniorcare_migration`.`tbl_apartment_bed`
  (
    `id`,
    `id_apartment_room`,
    `number`
  )
SELECT (`alms`.`base_sub_group`.`id` + 10000)                                          AS 'id',
       (`alms`.`base_sub_group`.`parent_room_id` + 10000)                              AS 'id_apartment_room',
       REGEXP_REPLACE(`alms`.`base_sub_group`.`number`, '\s?([0-9]+)\s?(.*)\s?', '$2') AS 'number'
FROM `alms`.`base_sub_group`
WHERE `alms`.`base_sub_group`.`discriminator` = 'IL'
  AND `alms`.`base_sub_group`.`parent_room_id` IS NOT NULL
  AND `alms`.`base_sub_group`.`is_shared` = 0;

# Region
INSERT INTO `db_seniorcare_migration`.`tbl_region`
  (
    `id_space`,
    `id`,
    `name`,
    `description`,
    `shorthand`
  )
SELECT 1,
       `cc_old`.`region`.`id`                                             AS 'id',
       IF(`cc_old`.`region`.`name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`region`.`name`, '\\s+', ' ')), '')        AS 'name',
       IF(`cc_old`.`region`.`description`!='', TRIM(REGEXP_REPLACE(`cc_old`.`region`.`description`, '\\s+', ' ')), '') AS 'description',
       ''                                                                 AS 'shorthand'
FROM `cc_old`.`region`;


INSERT INTO `db_seniorcare_migration`.`tbl_region`
  (
    `id_space`,
    `id`,
    `name`,
    `description`,
    `shorthand`,
    `phone`,
    `fax`
  )
SELECT 1,
       (`alms`.`base_group`.`id` + 10000)                                   AS 'id',
       IF(`alms`.`base_group`.`name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`name`, '\\s+', ' ')), '')        AS 'name',
       IF(`alms`.`base_group`.`description`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`description`, '\\s+', ' ')), '') AS 'description',
       IF(`alms`.`base_group`.`shorthand`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`shorthand`, '\\s+', ' ')), '')   AS 'shorthand',
       `alms`.`base_group`.`phone`                                          AS 'phone',
       `alms`.`base_group`.`fax`                                            AS 'fax'
FROM `alms`.`base_group`
WHERE `alms`.`base_group`.`discriminator` = 'IHC';


# Resident Diagnosis
INSERT INTO `db_seniorcare_migration`.`tbl_resident_diagnosis`
  (
    `id_resident`,
    `id_diagnosis`,
    `type`,
    `notes`
  )
SELECT `cc_old`.`Residentmedcondition`.`Resident_ID`                              AS 'id_resident',
       IF(`cc_old`.`Residentmedcondition`.`Medical_Condition_ID` >= 2615,
          (`cc_old`.`Residentmedcondition`.`Medical_Condition_ID` + 10000),
          `cc_old`.`Residentmedcondition`.`Medical_Condition_ID`)                 AS 'id_diagnosis',
       (`cc_old`.`Residentmedcondition`.`Condition_Type` + 1)                     AS 'type',
       IF(`cc_old`.`Residentmedcondition`.`Notes`!='', TRIM(REGEXP_REPLACE(`cc_old`.`Residentmedcondition`.`Notes`, '\\s+', ' ')), '') AS 'notes'
FROM `cc_old`.`Residentmedcondition`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_diagnosis`
  (
    `id_resident`,
    `id_diagnosis`,
    `type`,
    `notes`
  )
SELECT (`alms`.`base_resident_diagnosis`.`id_resident` + 10000)                    AS 'id_resident',
       IF(`alms`.`base_resident_diagnosis`.`id_diagnosis` >= 2615,
          (`alms`.`base_resident_diagnosis`.`id_diagnosis` + 20000),
          `alms`.`base_resident_diagnosis`.`id_diagnosis`)                         AS 'id_diagnosis',
       `alms`.`base_resident_diagnosis`.`id_diagnosis_type`                        AS 'type',
       IF(`alms`.`base_resident_diagnosis`.`notes`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_diagnosis`.`notes`, '\\s+', ' ')), '') AS 'notes'
FROM `alms`.`base_resident_diagnosis`;


# Resident Medication Allergy
INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication_allergy`
  (
    `id_resident`,
    `id_medication`,
    `notes`
  )
SELECT `cc_old`.`drugallergy`.`Resident_ID`       AS 'id_resident',
       IF(`cc_old`.`drugallergy`.`Medication_ID` >= 1035, (`cc_old`.`drugallergy`.`Medication_ID` + 10000),
          `cc_old`.`drugallergy`.`Medication_ID`) AS 'id_medication',
       NULL                                       AS 'notes'
FROM `cc_old`.`drugallergy`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication_allergy`
  (
    `id_resident`,
    `id_medication`,
    `notes`
  )
SELECT (`alms`.`base_resident_medication_allergy`.`id_resident` + 10000)                    AS 'id_resident',
       IF(`alms`.`base_resident_medication_allergy`.`id_medication` >= 1035,
          (`alms`.`base_resident_medication_allergy`.`id_medication` + 20000),
          `alms`.`base_resident_medication_allergy`.`id_medication`)                        AS 'id_medication',
       IF(`alms`.`base_resident_medication_allergy`.`notes`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_medication_allergy`.`notes`, '\\s+', ' ')), '') AS 'notes'
FROM `alms`.`base_resident_medication_allergy`;


# Resident Medicatal History Condition
INSERT INTO `db_seniorcare_migration`.`tbl_resident_medical_history_condition`
  (
    `id_resident`,
    `id_medical_history_condition`,
    `date`,
    `notes`
  )
SELECT `cc_old`.`residentmedhistory`.`Resident_ID`                              AS 'id_resident',
       `cc_old`.`residentmedhistory`.`Medical_History_Condition_ID`             AS 'id_medical_history_condition',
       CONCAT(`cc_old`.`residentmedhistory`.`Med_Date`, ' 00:00:00')            AS 'date',
       IF(`cc_old`.`residentmedhistory`.`Notes`!='', TRIM(REGEXP_REPLACE(`cc_old`.`residentmedhistory`.`Notes`, '\\s+', ' ')), '') AS 'notes'
FROM `cc_old`.`residentmedhistory`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medical_history_condition`
  (
    `id_resident`,
    `id_medical_history_condition`,
    `date`,
    `notes`
  )
SELECT (`alms`.`base_resident_medical_history_condition`.`id_resident` + 10000)                    AS 'id_resident',
       `alms`.`base_resident_medical_history_condition`.`id_medical_history_condition`             AS 'id_medical_history_condition',
       `alms`.`base_resident_medical_history_condition`.`date`                                     AS 'date',
       IF(`alms`.`base_resident_medical_history_condition`.`notes`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_medical_history_condition`.`notes`, '\\s+', ' ')), '') AS 'notes'
FROM `alms`.`base_resident_medical_history_condition`;

# Resident Allergen
INSERT INTO `db_seniorcare_migration`.`tbl_resident_allergen`
  (
    `id_resident`,
    `id_allergen`,
    `notes`
  )
SELECT `cc_old`.`residentallergies`.`Resident_ID`                              AS 'id_resident',
       `cc_old`.`residentallergies`.`Allergen_ID`                              AS 'id_allergen',
       IF(`cc_old`.`residentallergies`.`notes`!='', TRIM(REGEXP_REPLACE(`cc_old`.`residentallergies`.`notes`, '\\s+', ' ')), '') AS 'notes'
FROM `cc_old`.`residentallergies`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_allergen`
  (
    `id_resident`,
    `id_allergen`,
    `notes`
  )
SELECT (`alms`.`base_resident_allergen`.`id_resident` + 10000)                    AS 'id_resident',
       `alms`.`base_resident_allergen`.`id_allergen`                              AS 'id_allergen',
       IF(`alms`.`base_resident_allergen`.`notes`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_allergen`.`notes`, '\\s+', ' ')), '') AS 'notes'
FROM `alms`.`base_resident_allergen`;

# Resident Diet
INSERT INTO `db_seniorcare_migration`.`tbl_resident_diet`
  (
    `id_resident`,
    `id_diet`,
    `description`
  )
SELECT `cc_old`.`dietresidentrestriction`.`Resident_ID`                             AS 'id_resident',
       `cc_old`.`dietresidentrestriction`.`Dietcategory_ID`                         AS 'id_diet',
       IF(`cc_old`.`dietresidentrestriction`.`Name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`dietresidentrestriction`.`Name`, '\\s+', ' ')), '') AS 'description'
FROM `cc_old`.`dietresidentrestriction`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_diet`
  (
    `id_resident`,
    `id_diet`,
    `description`
  )
SELECT (`alms`.`base_resident_diet`.`id_resident` + 10000)                          AS 'id_resident',
       `alms`.`base_resident_diet`.`id_diet`                                        AS 'id_diet',
       IF(`alms`.`base_resident_diet`.`description`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_diet`.`description`, '\\s+', ' ')), '') AS 'description'
FROM `alms`.`base_resident_diet`;

# Resident Medication
INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication`
  (
    `id_resident`,
    `id_physician`,
    `id_medication`,
    `dosage`,
    `dosage_unit`,
    `prescription_number`,
    `notes`,
    `medication_am`,
    `medication_nn`,
    `medication_pm`,
    `medication_hs`,
    `medication_prn`,
    `medication_discontinued`,
    `medication_treatment`
  )
SELECT `cc_old`.`residentmeds`.`Resident_ID`                                            AS 'id_resident',
       `cc_old`.`residentmeds`.`Physician_ID`                                           AS 'id_physician',
       IF(`cc_old`.`residentmeds`.`Medication_ID` >= 1035,
          (`cc_old`.`residentmeds`.`Medication_ID` + 10000),
          `cc_old`.`residentmeds`.`Medication_ID`)                                      AS 'id_medication',
       IFNULL(`cc_old`.`residentmeds`.`Dosage`, 0)                                      AS 'dosage',
       IF(`cc_old`.`residentmeds`.`Dosage_Units`!='', TRIM(REGEXP_REPLACE(`cc_old`.`residentmeds`.`Dosage_Units`, '\\s+', ' ')), '')        AS 'dosage_unit',
       IF(`cc_old`.`residentmeds`.`Prescription_Number`!='', TRIM(REGEXP_REPLACE(`cc_old`.`residentmeds`.`Prescription_Number`, '\\s+', ' ')), '') AS 'prescription_number',
       IF(`cc_old`.`residentmeds`.`Medication_Notes`!='', TRIM(REGEXP_REPLACE(`cc_old`.`residentmeds`.`Medication_Notes`, '\\s+', ' ')), '')    AS 'notes',
       `cc_old`.`residentmeds`.`AM`                                                     AS 'medication_am',
       `cc_old`.`residentmeds`.`NN`                                                     AS 'medication_nn',
       `cc_old`.`residentmeds`.`PM`                                                     AS 'medication_pm',
       `cc_old`.`residentmeds`.`HS`                                                     AS 'medication_hs',
       `cc_old`.`residentmeds`.`PRN`                                                    AS 'medication_prn',
       `cc_old`.`residentmeds`.`DISC`                                                   AS 'medication_discontinued',
       0                                                                                AS 'medication_treatment'
FROM `cc_old`.`residentmeds`;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_medication`
  (
    `id_resident`,
    `id_physician`,
    `id_medication`,
    `dosage`,
    `dosage_unit`,
    `prescription_number`,
    `notes`,
    `medication_am`,
    `medication_nn`,
    `medication_pm`,
    `medication_hs`,
    `medication_prn`,
    `medication_discontinued`,
    `medication_treatment`
  )
SELECT (`alms`.`base_resident_medication`.`id_resident` + 10000)                          AS 'id_resident',
       (`alms`.`base_resident_medication`.`id_physician` + 10000)                         AS 'id_physician',
       IF(`alms`.`base_resident_medication`.`id_medication` >= 1035,
          (`alms`.`base_resident_medication`.`id_medication` + 20000),
          `alms`.`base_resident_medication`.`id_medication`)                              AS 'id_medication',
       `alms`.`base_resident_medication`.`dosage`                                         AS 'dosage',
       IF(`alms`.`base_resident_medication`.`dosage_unit`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_medication`.`dosage_unit`, '\\s+', ' ')), '') AS 'dosage_unit',
       TRIM(REGEXP_REPLACE(`alms`.`base_resident_medication`.`prescription_number`, '\\s+',
                           ' '))                                                          AS 'prescription_number',
       IF(`alms`.`base_resident_medication`.`notes`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_medication`.`notes`, '\\s+', ' ')), '')       AS 'notes',
       `alms`.`base_resident_medication`.`medication_am`                                  AS 'medication_am',
       `alms`.`base_resident_medication`.`medication_nn`                                  AS 'medication_nn',
       `alms`.`base_resident_medication`.`medication_pm`                                  AS 'medication_pm',
       `alms`.`base_resident_medication`.`medication_hs`                                  AS 'medication_hs',
       `alms`.`base_resident_medication`.`medication_prn`                                 AS 'medication_prn',
       `alms`.`base_resident_medication`.`medication_disc`                                AS 'medication_discontinued',
       `alms`.`base_resident_medication`.`medication_treatment`                           AS 'medication_treatment'
FROM `alms`.`base_resident_medication`;


### Assessments
INSERT INTO `db_seniorcare_migration`.`tbl_assessment_care_level_group`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       `cc_old`.`assessment_care_level_group`.`id`                                            AS 'id',
       IF(`cc_old`.`assessment_care_level_group`.`level_name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment_care_level_group`.`level_name`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`assessment_care_level_group`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_care_level`
  (
    `id`,
    `id_care_level_group`,
    `title`,
    `level_low`,
    `level_high`
  )
SELECT `cc_old`.`assessment_care_level`.`id`                                            AS 'id',
       `cc_old`.`assessment_care_level`.`id_group`                                      AS 'id_care_level_group',
       IF(`cc_old`.`assessment_care_level`.`level_name`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment_care_level`.`level_name`, '\\s+', ' ')), '') AS 'title',
       `cc_old`.`assessment_care_level`.`level_low`                                     AS 'level_low',
       `cc_old`.`assessment_care_level`.`level_high`                                    AS 'level_high'
FROM `cc_old`.`assessment_care_level`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_form`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       `cc_old`.`assessment_form`.`id`                                       AS 'id',
       IF(`cc_old`.`assessment_form`.`title`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment_form`.`title`, '\\s+', ' ')), '') AS 'title'
FROM `cc_old`.`assessment_form`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_category`
  (
    `id_space`,
    `id`,
    `title`,
    `multi_item`
  )
SELECT 1,
       `cc_old`.`assessment_category`.`id`                                       AS 'id',
       IF(`cc_old`.`assessment_category`.`title`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment_category`.`title`, '\\s+', ' ')), '') AS 'title',
       `cc_old`.`assessment_category`.`multi_item`                               AS 'multi_item'
FROM `cc_old`.`assessment_category`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_form_category`
  (
    `id_category`,
    `id_form`,
    `order_number`
  )
SELECT `cc_old`.`assessment_form_category`.`id_category` AS 'id_category',
       `cc_old`.`assessment_form_category`.`id_form`     AS 'id_form',
       0                                                 AS 'order_number'
FROM `cc_old`.`assessment_form_category`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_form_care_level_group`
  (
    `id_care_level_group`,
    `id_form`
  )
SELECT `cc_old`.`assessment_care_level_groups`.`assessmentcarelevelgroup_id` AS 'id_care_level_group',
       `cc_old`.`assessment_care_level_groups`.`assessmentform_id`           AS 'id_form'
FROM `cc_old`.`assessment_care_level_groups`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment_row`
  (
    `id`,
    `id_category`,
    `title`,
    `score`,
    `order_number`
  )
SELECT `cc_old`.`assessment_row`.`id`                                       AS 'id',
       `cc_old`.`assessment_row`.`id_category`                              AS 'id_category',
       IF(`cc_old`.`assessment_row`.`title`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment_row`.`title`, '\\s+', ' ')), '') AS 'title',
       `cc_old`.`assessment_row`.`value`                                    AS 'score',
       `cc_old`.`assessment_row`.`order_no`                                 AS 'order_number'
FROM `cc_old`.`assessment_row`;

INSERT INTO `db_seniorcare_migration`.`tbl_assessment`
  (
    `id`,
    `id_form`,
    `id_resident`,
    `date`,
    `performed_by`,
    `notes`,
    `score`
  )
SELECT `cc_old`.`assessment`.`id`                                              AS 'id',
       `cc_old`.`assessment`.`id_form`                                         AS 'id_form',
       `cc_old`.`assessment`.`id_resident`                                     AS 'id_resident',
       `cc_old`.`assessment`.`date`                                            AS 'date',
       IF(`cc_old`.`assessment`.`performed_by`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment`.`performed_by`, '\\s+', ' ')), '') AS 'performed_by',
       IF(`cc_old`.`assessment`.`notes`!='', TRIM(REGEXP_REPLACE(`cc_old`.`assessment`.`notes`, '\\s+', ' ')), '')        AS 'notes',
       `cc_old`.`assessment`.`score`                                           AS 'score'
FROM `cc_old`.`assessment`
WHERE `cc_old`.`assessment`.`discriminator` = 'r';

# CALL `cc_old`.`json_row_data`('tmp_assessment_data', 'assessment', 'data');
INSERT INTO `db_seniorcare_migration`.`tbl_assessment_assessment_row`
  (
    `id_assessment`,
    `id_row`,
    `score`
  )
SELECT `cc_old`.`assessment`.`id`                                                               AS 'id_assessment',
       JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')))         AS 'id_row',
       (SELECT `cc_old`.`assessment_row`.`value`
        FROM `cc_old`.`assessment_row`
        WHERE `cc_old`.`assessment_row`.`id` =
              JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')))) AS 'score'
FROM `cc_old`.`assessment`
       JOIN (SELECT `cc_old`.`tmp_assessment_data`.`seq` AS idx FROM `cc_old`.`tmp_assessment_data`) AS INDEXES
WHERE `cc_old`.`assessment`.`discriminator` = 'r'
  AND `cc_old`.`assessment`.`data` IS NOT NULL
  AND `cc_old`.`assessment`.`data` != '[]'
  AND JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')) IS NOT NULL;


INSERT INTO `db_seniorcare_migration`.`tbl_assessment`
  (
    `id`,
    `id_form`,
    `id_resident`,
    `date`,
    `performed_by`,
    `notes`,
    `score`
  )
SELECT (`alms`.`common_assessment_assessment`.`id` + 10000)   AS 'id',
       `alms`.`common_assessment_assessment`.`id_form`        AS 'id_form',
       `alms`.`common_assessment_assessment`.`id_assesstable` AS 'id_resident',
       `alms`.`common_assessment_assessment`.`date`           AS 'date',
       `alms`.`common_assessment_assessment`.`performed_by`   AS 'performed_by',
       `alms`.`common_assessment_assessment`.`notes`          AS 'notes',
       `alms`.`common_assessment_assessment`.`score`          AS 'score'
FROM `alms`.`common_assessment_assessment`
WHERE `alms`.`common_assessment_assessment`.`discriminator` = 'residents';

#CALL `alms`.`json_row_data`('tmp_assessment_data', 'common_assessment_assessment', 'data');
INSERT INTO `db_seniorcare_migration`.`tbl_assessment_assessment_row`
  (
    `id_assessment`,
    `id_row`,
    `score`
  )
SELECT (`alms`.`common_assessment_assessment`.`id` + 10000) AS 'id_assessment',
       JSON_UNQUOTE(JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`,
                                 CONCAT('$[', idx, ']')))   AS 'id_row',
       (SELECT `alms`.`common_assessment_row`.`value`
        FROM `alms`.`common_assessment_row`
        WHERE `alms`.`common_assessment_row`.`id` = JSON_UNQUOTE(
            JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`,
                         CONCAT('$[', idx, ']'))))          AS 'score'
FROM `alms`.`common_assessment_assessment`
       JOIN (SELECT `alms`.`tmp_assessment_data`.`seq` AS idx FROM `alms`.`tmp_assessment_data`) AS INDEXES
WHERE `alms`.`common_assessment_assessment`.`discriminator` = 'residents'
  AND `alms`.`common_assessment_assessment`.`data` IS NOT NULL
  AND `alms`.`common_assessment_assessment`.`data` != '[]'
  AND JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`, CONCAT('$[', idx, ']')) IS NOT NULL;


### Event Definitions 
INSERT INTO `db_seniorcare_migration`.`tbl_event_definition`
  (
    `id_space`,
    `id`,
    `title`,
    `show_resident_ffc`,
    `show_resident_ihc`,
    `show_resident_il`,
    `show_physician`,
    `show_responsible_person`,
    `show_additional_date`
  )
SELECT 1,
       `cc_old`.`eventdefinition`.`Event_Definition_ID`                                         AS 'id',
       `cc_old`.`eventdefinition`.`Event_Name`                                                  AS 'title',
       `cc_old`.`eventdefinition`.`facility_show`                                               AS 'show_resident_ffc',
       `cc_old`.`eventdefinition`.`in_home_show`                                                AS 'show_resident_ihc',
       `cc_old`.`eventdefinition`.`il_show`                                                     AS 'show_resident_il',
       IF(`cc_old`.`eventdefinition`.`Event_Field_Options` LIKE '%"name"%:%"phycisian"%', 1, 0) AS 'show_physician',
       IF(`cc_old`.`eventdefinition`.`Event_Field_Options` LIKE '%"name"%:%"rpPerson"%', 1,
          0)                                                                                    AS 'show_responsible_person',
       IF(`cc_old`.`eventdefinition`.`Event_Field_Options` LIKE '%"name"%:%"dischargeDate"%', 1,
          0)                                                                                    AS 'show_additional_date'
FROM `cc_old`.`eventdefinition`
WHERE `cc_old`.`eventdefinition`.`Event_Code` LIKE 'res_%'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE 'res_old_'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_assignment'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_transfer'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_admit'
  AND `cc_old`.`eventdefinition`.`Event_Code` NOT LIKE '%_checkout';

### Events
#--- fix Sheila record 850 - P - 621
SET @@SESSION.sql_mode = 'ALLOW_INVALID_DATES';
INSERT INTO `db_seniorcare_migration`.`tbl_resident_event`
  (
    `id`,
    `id_resident`,
    `id_definition`,
    `notes`,
    `date`,
    `additional_date`,
    `id_physician`
  )
SELECT `cc_old`.`events`.`Event_ID`            AS 'id',
       `cc_old`.`events`.`Resident_ID`         AS 'id_resident',
       `cc_old`.`events`.`Event_Definition_ID` AS 'id_definition',
       IF(`cc_old`.`events`.`Event_Notes` != '', TRIM(REGEXP_REPLACE(`cc_old`.`events`.`Event_Notes`, '\\s+', ' ')),
          '')                                  AS 'notes',
       `cc_old`.`events`.`Event_Date`          AS 'date',
       IF(JSON_VALID(`cc_old`.`events`.`Event_Data`),
          STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.dischargeDate')), '%m/%d/%Y'),
          NULL)                                AS 'additional_date',
       IF(JSON_VALID(`cc_old`.`events`.`Event_Data`),
          CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.phycisian.id')), SIGNED INTEGER),
          NULL)                                AS 'id_physician'
FROM `cc_old`.`events`
WHERE `cc_old`.`events`.`Resident_ID` IS NOT NULL
  AND `cc_old`.`events`.`Event_Definition_ID` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_event`
  (
    `id`,
    `id_resident`,
    `id_definition`,
    `notes`,
    `date`,
    `additional_date`,
    `id_physician`
  )
SELECT (`alms`.`base_event`.`id` + 10000)                                                                        AS 'id',
       (`alms`.`base_event`.`id_resident` + 10000)                                                               AS 'id_resident',
       `alms`.`base_event`.`id_definition`                                                                       AS 'id_definition',
       IF(`alms`.`base_event`.`notes` != '', TRIM(REGEXP_REPLACE(`alms`.`base_event`.`notes`, '\\s+', ' ')),
          '')                                                                                                    AS 'notes',
       `alms`.`base_event`.`date`                                                                                AS 'date',
       IF(JSON_VALID(`alms`.`base_event`.`data`),
          STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.dischargeDate')), '%m/%d/%Y'),
          NULL)                                                                                                  AS 'additional_date',
       IF(JSON_VALID(`alms`.`base_event`.`data`),
          CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.phycisian.id')), SIGNED INTEGER),
          NULL)                                                                                                  AS 'id_physician'
FROM `alms`.`base_event`
WHERE `alms`.`base_event`.`id_resident` IS NOT NULL
  AND `alms`.`base_event`.`id_definition` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
;



INSERT INTO `db_seniorcare_migration`.`tbl_resident_event_responsible_persons`
  (
    `id_resident_event`,
    `id_responsible_person`
  )
SELECT `cc_old`.`events`.`Event_ID` AS 'id',
       IF(JSON_VALID(`cc_old`.`events`.`Event_Data`),
          CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.rpPerson.id')), SIGNED INTEGER),
          NULL)                     AS 'id_responsible_person'
FROM `cc_old`.`events`
WHERE `cc_old`.`events`.`Resident_ID` IS NOT NULL
  AND `cc_old`.`events`.`Event_Definition_ID` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
  AND IF(JSON_VALID(`cc_old`.`events`.`Event_Data`),
         CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.rpPerson.id')), SIGNED INTEGER),
         NULL) IS NOT NULL
;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_event_responsible_persons`
  (
    `id_resident_event`,
    `id_responsible_person`
  )
SELECT (`alms`.`base_event`.`id` + 10000) AS 'id',
       IF(JSON_VALID(`alms`.`base_event`.`data`),
          CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.rpPerson.id')), SIGNED INTEGER),
          NULL)                           AS 'id_responsible_person'
FROM `alms`.`base_event`
WHERE `alms`.`base_event`.`id_resident` IS NOT NULL
  AND `alms`.`base_event`.`id_definition` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
  AND IF(JSON_VALID(`alms`.`base_event`.`data`),
         CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`alms`.`base_event`.`data`, '$.fields.rpPerson.id')), SIGNED INTEGER),
         NULL) IS NOT NULL
;


### Rent
INSERT INTO `db_seniorcare_migration`.`tbl_resident_rent`
  (
    `id`,
    `id_resident`,
    `rent_period`,
    `start`,
    `end`,
    `amount`,
    `notes`,
    `source`
  )
SELECT `cc_old`.`contract`.`id`                                      AS 'id',
       `cc_old`.`contract`.`owner`                                   AS 'id_resident',
       `cc_old`.`contract`.`type`                                    AS 'rent_period',
       `cc_old`.`contract`.`start`                                   AS 'start',
       `cc_old`.`contract`.`end`                                     AS 'end',
       `cc_old`.`contract`.`amount`                                  AS 'amount',
       IF(`cc_old`.`contract`.`note`!='', TRIM(REGEXP_REPLACE(`cc_old`.`contract`.`note`, '\\s+', ' ')), '') AS 'notes',
       `cc_old`.`contract`.`source`                                  AS 'source'
FROM `cc_old`.`contract`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`contract`.`owner`
WHERE `cc_old`.`contract`.`owner_type` = 1;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_rent`
  (
    `id`,
    `id_resident`,
    `rent_period`,
    `start`,
    `end`,
    `amount`,
    `notes`,
    `source`
  )
SELECT (`alms`.`contract`.`id` + 10000)                            AS 'id',
       (`alms`.`contract`.`id_resident` + 10000)                   AS 'id_resident',
       `alms`.`contract`.`type`                                    AS 'rent_period',
       `alms`.`contract`.`start`                                   AS 'start',
       `alms`.`contract`.`end`                                     AS 'end',
       `alms`.`contract`.`amount`                                  AS 'amount',
       IF(`alms`.`contract`.`note`!='', TRIM(REGEXP_REPLACE(`alms`.`contract`.`note`, '\\s+', ' ')), '') AS 'notes',
       `alms`.`contract`.`source`                                  AS 'source'
FROM `alms`.`contract`;

### Resident Admission
INSERT INTO `db_seniorcare_migration`.`tbl_resident_admission`
  (
    `id_resident`,
    `group_type`,
    `admission_type`,
    `date`,
    `start`,
    `end`,
    `id_facility_bed`,
    `id_dining_room`,
    `id_apartment_bed`,
    `id_region`,
    `id_csz`,
    `address`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `notes`
  )
SELECT `cc_old`.`hosting`.`owner`                                                           AS 'id_resident',

       CASE
         WHEN `cc_old`.`hosting`.`owner_type` = 1 THEN 1 # Facility
         WHEN `cc_old`.`hosting`.`owner_type` = 3 THEN 2 # Apartment
         WHEN `cc_old`.`hosting`.`owner_type` = 2 THEN 3 # Region
         END                                                                                AS 'group_type',
       2                                                                                    AS 'admission_type',

       `cc_old`.`hosting`.`start`                                                           AS 'date',
       `cc_old`.`hosting`.`start`                                                           AS 'start',
       `cc_old`.`hosting`.`end`                                                             AS 'end',

       IF(`cc_old`.`hosting`.`owner_type` = 1, `cc_old`.`hosting`.`object`, NULL)           AS 'id_facility_bed',
       IF(`cc_old`.`hosting`.`owner_type` = 1, IF(`cc_old`.`residents`.`Dinningroom_ID` != 0,
                                                  `cc_old`.`residents`.`Dinningroom_ID`,
                                                  NULL), NULL)                              AS 'id_dining_room',

       IF(`cc_old`.`hosting`.`owner_type` = 3, `cc_old`.`hosting`.`object`, NULL)           AS 'id_apartment_bed',

       IF(`cc_old`.`hosting`.`owner_type` = 2, `cc_old`.`hosting`.`object_group`, NULL)     AS 'id_region',

       IF(`cc_old`.`hosting`.`owner_type` = 2, IF(`cc_old`.`residents`.`csz_id` >= 2817,
                                                  (`cc_old`.`residents`.`csz_id` + 10000),
                                                  `cc_old`.`residents`.`csz_id`), NULL)     AS 'id_csz',
       IF(`cc_old`.`hosting`.`owner_type` = 2, `cc_old`.`residents`.`street_address`, NULL) AS 'address',

       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`Care_Level_ID`, NULL) AS 'id_care_level',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`Care_Group`, NULL)    AS 'care_group',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`DNR`, NULL)           AS 'dnr',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`POLST`, NULL)         AS 'polst',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`Ambulatory`, NULL)    AS 'ambulatory',
       ''                                                                                   AS 'notes'
FROM `cc_old`.`hosting`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
WHERE (`cc_old`.`hosting`.`owner_type` = 1 AND `cc_old`.`hosting`.`object` IS NOT NULL AND
       `cc_old`.`hosting`.`object` IS NOT NULL AND
       `cc_old`.`hosting`.`object` IN (SELECT `cc_old`.`facilityrooms`.`Room_ID`
                                       FROM `cc_old`.`facilityrooms`
                                       WHERE `cc_old`.`facilityrooms`.`Facility_ID` IS NOT NULL))
   OR (`cc_old`.`hosting`.`owner_type` = 3 AND `cc_old`.`hosting`.`object` IS NOT NULL AND
       `cc_old`.`hosting`.`object` IS NOT NULL AND `cc_old`.`hosting`.`object` IN (SELECT `cc_old`.`il_room`.`id`
                                                                                   FROM `cc_old`.`il_room`
                                                                                   WHERE `cc_old`.`il_room`.`apartment_id` IS NOT NULL))
   OR (`cc_old`.`hosting`.`owner_type` = 2 AND `cc_old`.`hosting`.`object_group` IS NOT NULL);

INSERT INTO `db_seniorcare_migration`.`tbl_resident_admission`
  (
    `id_resident`,
    `group_type`,
    `admission_type`,
    `date`,
    `start`,
    `end`,
    `id_facility_bed`,
    `id_dining_room`,
    `id_apartment_bed`,
    `id_region`,
    `id_csz`,
    `address`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `notes`
  )
SELECT `cc_old`.`hosting`.`owner`                                                           AS 'id_resident',

       CASE
         WHEN `cc_old`.`hosting`.`owner_type` = 1 THEN 1
         WHEN `cc_old`.`hosting`.`owner_type` = 3 THEN 2
         WHEN `cc_old`.`hosting`.`owner_type` = 2 THEN 3
         END                                                                                AS 'group_type',
       4                                                                                    AS 'admission_type',

       `cc_old`.`hosting`.`end`                                                             AS 'date',
       `cc_old`.`hosting`.`end`                                                             AS 'start',
       NULL                                                                                 AS 'end',

       IF(`cc_old`.`hosting`.`owner_type` = 1, `cc_old`.`hosting`.`object`, NULL)           AS 'id_facility_bed',
       IF(`cc_old`.`hosting`.`owner_type` = 1, IF(`cc_old`.`residents`.`Dinningroom_ID` != 0,
                                                  `cc_old`.`residents`.`Dinningroom_ID`,
                                                  NULL), NULL)                              AS 'id_dining_room',

       IF(`cc_old`.`hosting`.`owner_type` = 3, `cc_old`.`hosting`.`object`, NULL)           AS 'id_apartment_bed',

       IF(`cc_old`.`hosting`.`owner_type` = 2, `cc_old`.`hosting`.`object_group`, NULL)     AS 'id_region',

       IF(`cc_old`.`hosting`.`owner_type` = 2, IF(`cc_old`.`residents`.`csz_id` >= 2817,
                                                  (`cc_old`.`residents`.`csz_id` + 10000),
                                                  `cc_old`.`residents`.`csz_id`), NULL)     AS 'id_csz',
       IF(`cc_old`.`hosting`.`owner_type` = 2, `cc_old`.`residents`.`street_address`, NULL) AS 'address',

       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`Care_Level_ID`, NULL) AS 'id_care_level',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`Care_Group`, NULL)    AS 'care_group',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`DNR`, NULL)           AS 'dnr',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`POLST`, NULL)         AS 'polst',
       IF(`cc_old`.`hosting`.`owner_type` != 3, `cc_old`.`residents`.`Ambulatory`, NULL)    AS 'ambulatory',
       ''                                                                                   AS 'notes'
FROM `cc_old`.`hosting`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
WHERE `cc_old`.`hosting`.`discharged_id` IS NOT NULL AND
  ((`cc_old`.`hosting`.`owner_type` = 1 AND `cc_old`.`hosting`.`object` IS NOT NULL AND
       `cc_old`.`hosting`.`object` IS NOT NULL AND
       `cc_old`.`hosting`.`object` IN (SELECT `cc_old`.`facilityrooms`.`Room_ID`
                                       FROM `cc_old`.`facilityrooms`
                                       WHERE `cc_old`.`facilityrooms`.`Facility_ID` IS NOT NULL))
   OR (`cc_old`.`hosting`.`owner_type` = 3 AND `cc_old`.`hosting`.`object` IS NOT NULL AND
       `cc_old`.`hosting`.`object` IS NOT NULL AND `cc_old`.`hosting`.`object` IN (SELECT `cc_old`.`il_room`.`id`
                                                                                   FROM `cc_old`.`il_room`
                                                                                   WHERE `cc_old`.`il_room`.`apartment_id` IS NOT NULL))
   OR (`cc_old`.`hosting`.`owner_type` = 2 AND `cc_old`.`hosting`.`object_group` IS NOT NULL));


INSERT INTO `db_seniorcare_migration`.`tbl_resident_admission`
  (
    `id_resident`,
    `group_type`,
    `admission_type`,
    `date`,
    `start`,
    `end`,
    `id_facility_bed`,
    `id_dining_room`,
    `id_apartment_bed`,
    `id_region`,
    `id_csz`,
    `address`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `notes`
  )
SELECT (`alms`.`hosting`.`id_resident` + 10000)                                                        AS 'id_resident',

       CASE
         WHEN `alms`.`base_resident`.`discriminator` = 'FFC' THEN 1
         WHEN `alms`.`base_resident`.`discriminator` = 'IL' THEN 2
         WHEN `alms`.`base_resident`.`discriminator` = 'IHC' THEN 3
         END                                                                                           AS 'group_type',
       2                                                                                               AS 'admission_type',

       `alms`.`hosting`.`start`                                                                        AS 'date',
       `alms`.`hosting`.`start`                                                                        AS 'start',
       `alms`.`hosting`.`end`                                                                          AS 'end',

       IF(`alms`.`base_resident`.`discriminator` = 'FFC', (`alms`.`hosting`.`id_sub_group` + 10000),
          NULL)                                                                                        AS 'id_facility_bed',
       IF(`alms`.`base_resident`.`discriminator` = 'FFC', (`alms`.`base_resident`.`id_dining_room` + 10000),
          NULL)                                                                                        AS 'id_dining_room',

       IF(`alms`.`base_resident`.`discriminator` = 'IL', (`alms`.`hosting`.`id_sub_group` + 10000),
          NULL)                                                                                        AS 'id_apartment_bed',

       IF(`alms`.`base_resident`.`discriminator` = 'IHC', (`alms`.`hosting`.`id_group` + 10000), NULL) AS 'id_region',

       IF(`alms`.`base_resident`.`discriminator` = 'IHC', IF(`alms`.`base_resident`.`id_csz` >= 2817,
                                                             (`alms`.`base_resident`.`id_csz` + 20000),
                                                             `alms`.`base_resident`.`id_csz`), NULL)   AS 'id_csz',
       IF(`alms`.`base_resident`.`discriminator` = 'IHC', `alms`.`base_resident`.`address`, NULL)      AS 'address',

       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`id_care_level`,
          NULL)                                                                                        AS 'id_care_level',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`care_group`, NULL)   AS 'care_group',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`dnr`, NULL)          AS 'dnr',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`polst`, NULL)        AS 'polst',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`ambulatory`, NULL)   AS 'ambulatory',
       ''                                                                                              AS 'notes'
FROM `alms`.`hosting`
       INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident`
WHERE `alms`.`hosting`.`id_sub_group` NOT IN
      (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

INSERT INTO `db_seniorcare_migration`.`tbl_resident_admission`
  (
    `id_resident`,
    `group_type`,
    `admission_type`,
    `date`,
    `start`,
    `end`,
    `id_facility_bed`,
    `id_dining_room`,
    `id_apartment_bed`,
    `id_region`,
    `id_csz`,
    `address`,
    `id_care_level`,
    `care_group`,
    `dnr`,
    `polst`,
    `ambulatory`,
    `notes`
  )
SELECT (`alms`.`hosting`.`id_resident` + 10000)                                                        AS 'id_resident',

       CASE
         WHEN `alms`.`base_resident`.`discriminator` = 'FFC' THEN 1
         WHEN `alms`.`base_resident`.`discriminator` = 'IL' THEN 2
         WHEN `alms`.`base_resident`.`discriminator` = 'IHC' THEN 3
         END                                                                                           AS 'group_type',
       4                                                                                               AS 'admission_type',

       `alms`.`hosting`.`end`                                                                          AS 'date',
       `alms`.`hosting`.`end`                                                                          AS 'start',
       NULL                                                                                            AS 'end',

       IF(`alms`.`base_resident`.`discriminator` = 'FFC', (`alms`.`hosting`.`id_sub_group` + 10000),
          NULL)                                                                                        AS 'id_facility_bed',
       IF(`alms`.`base_resident`.`discriminator` = 'FFC', (`alms`.`base_resident`.`id_dining_room` + 10000),
          NULL)                                                                                        AS 'id_dining_room',

       IF(`alms`.`base_resident`.`discriminator` = 'IL', (`alms`.`hosting`.`id_sub_group` + 10000),
          NULL)                                                                                        AS 'id_apartment_bed',

       IF(`alms`.`base_resident`.`discriminator` = 'IHC', (`alms`.`hosting`.`id_group` + 10000), NULL) AS 'id_region',

       IF(`alms`.`base_resident`.`discriminator` = 'IHC', IF(`alms`.`base_resident`.`id_csz` >= 2817,
                                                             (`alms`.`base_resident`.`id_csz` + 20000),
                                                             `alms`.`base_resident`.`id_csz`), NULL)   AS 'id_csz',
       IF(`alms`.`base_resident`.`discriminator` = 'IHC', `alms`.`base_resident`.`address`, NULL)      AS 'address',

       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`id_care_level`,
          NULL)                                                                                        AS 'id_care_level',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`care_group`, NULL)   AS 'care_group',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`dnr`, NULL)          AS 'dnr',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`polst`, NULL)        AS 'polst',
       IF(`alms`.`base_resident`.`discriminator` != 'IL', `alms`.`base_resident`.`ambulatory`, NULL)   AS 'ambulatory',
       ''                                                                                              AS 'notes'
FROM `alms`.`hosting`
       INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident`
WHERE `alms`.`hosting`.`discharged_id` IS NOT NULL
  AND `alms`.`hosting`.`id_sub_group` NOT IN
      (4, 7, 64, 85, 130, 94, 100, 79, /**p**/133, 135, 8, 9, 65, 66, 80, 81, 86, 87, 95, 96, 101, 102, 131, 132);

CALL `db_seniorcare_migration`.ValidateAll();

#------------------------------------------------------------------------------------------------------------------------

# Validate Phone numbers
UPDATE `db_seniorcare_migration`.`tbl_resident_phone`           SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_responsible_person_phone` SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_user_phone`               SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');

UPDATE `db_seniorcare_migration`.`tbl_apartment`                SET `phone`           = REGEXP_REPLACE(`phone`,           '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_apartment`                SET `fax`             = REGEXP_REPLACE(`fax`,             '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');

UPDATE `db_seniorcare_migration`.`tbl_facility`                 SET `phone`           = REGEXP_REPLACE(`phone`,           '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_facility`                 SET `fax`             = REGEXP_REPLACE(`fax`,             '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');

UPDATE `db_seniorcare_migration`.`tbl_region`                   SET `phone`           = REGEXP_REPLACE(`phone`,           '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_region`                   SET `fax`             = REGEXP_REPLACE(`fax`,             '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');

UPDATE `db_seniorcare_migration`.`tbl_physician`                SET `office_phone`    = REGEXP_REPLACE(`office_phone`,    '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_physician`                SET `emergency_phone` = REGEXP_REPLACE(`emergency_phone`, '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_physician`                SET `fax`             = REGEXP_REPLACE(`fax`,             '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})', '($2) ($4)-($6)');


### Resident Photo
SELECT `cc_old`.`residents`.`Resident_ID` COLLATE utf8_general_ci AS 'id',
       CONCAT('https://ccdb.ciminocare.com/uploads/documents/', `cc_old`.`residents`.`Photo`) COLLATE
       utf8_general_ci                                            AS 'photo'
FROM `cc_old`.`residents`
WHERE `cc_old`.`residents`.`Photo` != ''
UNION
SELECT (`alms`.`base_resident`.`id` + 10000) COLLATE utf8_general_ci AS 'id',
       CONCAT('https://alms.ciminocare.com/uploads/documents/', `alms`.`base_resident`.`photo`) COLLATE
       utf8_general_ci                                               AS 'photo'
FROM `alms`.`base_resident`
WHERE `alms`.`base_resident`.`photo` != '';

# Use app:migrate:photos command to import these photos to SeniorCare.
