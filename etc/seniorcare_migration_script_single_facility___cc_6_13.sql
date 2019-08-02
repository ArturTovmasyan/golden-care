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

### Payment Source
INSERT INTO `db_seniorcare_migration`.`tbl_payment_source`
  (
    `id_space`,
    `id`,
    `title`
  )
SELECT 1,
       `cc_old`.`payment_source`.`id` AS 'id',
       IF(`cc_old`.`payment_source`.`name` != '', TRIM(REGEXP_REPLACE(`cc_old`.`payment_source`.`name`, '\\s+', ' ')), '')                         AS 'title'
FROM `cc_old`.`payment_source`;

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
       (`cc_old`.`citystatezip`.`CSZ_ID` + 10000)                               AS 'id',
       IF(`cc_old`.`citystatezip`.`State_Full`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`State_Full`, '\\s+', ' ')), '')  AS 'state_full',
       IF(`cc_old`.`citystatezip`.`State_2_Ltr`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`State_2_Ltr`, '\\s+', ' ')), '') AS 'state_abbr',
       IF(`cc_old`.`citystatezip`.`ZIP_Main`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`ZIP_Main`, '\\s+', ' ')), '')    AS 'zip_main',
       IF(`cc_old`.`citystatezip`.`ZIP_Sub`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`ZIP_Sub`, '\\s+', ' ')), '')     AS 'zip_sub',
       IF(`cc_old`.`citystatezip`.`City`!='', TRIM(REGEXP_REPLACE(`cc_old`.`citystatezip`.`City`, '\\s+', ' ')), '')        AS 'city'
FROM `cc_old`.`citystatezip`
WHERE `cc_old`.`citystatezip`.`CSZ_ID` >= 2835;
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
FROM `cc_old`.`medications` WHERE `cc_old`.`medications`.`Medication_ID` > 2368;

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
       IF(`cc_old`.`physicians`.`Email`!='', TRIM(REGEXP_REPLACE(`cc_old`.`physicians`.`Email`, '\\s+', ' ')), '')       AS 'email',
       IF(`cc_old`.`physicians`.`Website_URL`!='', TRIM(REGEXP_REPLACE(`cc_old`.`physicians`.`Website_URL`, '\\s+', ' ')), '') AS 'website_url'
FROM `cc_old`.`physicians`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID` WHERE `cc_old`.`physicians`.`Physician_ID` > 1362;

INSERT INTO `db_seniorcare_migration`.`tbl_physician_phone`
  (
    `id_physician`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT `cc_old`.`physicians`.`Physician_ID`                              AS 'id_physician',
       1                                                                 AS 'compatibility',
       4                                                                 AS 'type',
       `cc_old`.`physicians`.`Office_Phone`                              AS 'number',
       0                                                                 AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `cc_old`.`physicians`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`physicians`.`Office_Phone` IS NOT NULL AND  `cc_old`.`physicians`.`Physician_ID` > 1362;

INSERT INTO `db_seniorcare_migration`.`tbl_physician_phone`
  (
    `id_physician`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT `cc_old`.`physicians`.`Physician_ID`                              AS 'id_physician',
       1                                                                 AS 'compatibility',
       5                                                                 AS 'type',
       `cc_old`.`physicians`.`Emergency_Phone`                           AS 'number',
       0                                                                 AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `cc_old`.`physicians`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`physicians`.`Emergency_Phone` IS NOT NULL AND  `cc_old`.`physicians`.`Physician_ID` > 1362;
;

INSERT INTO `db_seniorcare_migration`.`tbl_physician_phone`
  (
    `id_physician`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT `cc_old`.`physicians`.`Physician_ID`                              AS 'id_physician',
       1                                                                 AS 'compatibility',
       6                                                                 AS 'type',
       `cc_old`.`physicians`.`Fax`                                       AS 'number',
       0                                                                 AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `cc_old`.`physicians`
       INNER JOIN `cc_old`.`people` ON `cc_old`.`physicians`.`People_ID` = `cc_old`.`people`.`People_ID`
WHERE `cc_old`.`physicians`.`Fax` IS NOT NULL AND  `cc_old`.`physicians`.`Physician_ID` > 1362;

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
       INNER JOIN `cc_old`.`people` ON `cc_old`.`responsibleperson`.`People_ID` = `cc_old`.`people`.`People_ID` WHERE `cc_old`.`responsibleperson`.`Responsible_Person_ID` > 3760;

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
FROM `cc_old`.`residentresponsiblepersonphone` WHERE `cc_old`.`residentresponsiblepersonphone`.`Responsible_Person_ID` > 3760;

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
       INNER JOIN `cc_old`.`people` ON `cc_old`.`residents`.`People_ID` = `cc_old`.`people`.`People_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);

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
     INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
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
FROM `cc_old`.`resident_phone`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`resident_phone`.`resident_id`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);

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
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`residents`.`Physician_ID` IS NOT NULL;

# Resident Responsible Person
INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`,
    `sort_order`
  )
SELECT `cc_old`.`residentresponsibleperson`.`Res_RP_ID`             AS 'id',
       `cc_old`.`residentresponsibleperson`.`Resident_ID`           AS 'id_resident',
       `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID` AS 'id_responsible_person',
       `cc_old`.`residentresponsibleperson`.`Relationship_ID`       AS 'id_relationship',
       0                                                            AS 'sort_order'
FROM `cc_old`.`residentresponsibleperson`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentresponsibleperson`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`residentresponsibleperson`.`Relationship_ID` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`,
    `sort_order`
  )
SELECT `cc_old`.`residentresponsibleperson`.`Res_RP_ID`               AS 'id',
       `cc_old`.`residentresponsibleperson`.`Resident_ID`             AS 'id_resident',
       `cc_old`.`residentresponsibleperson`.`Responsible_Person_ID`   AS 'id_responsible_person',
       (`cc_old`.`residentresponsibleperson`.`Relationship_ID` + 100) AS 'id_relationship',
       0                                                              AS 'sort_order'
FROM `cc_old`.`residentresponsibleperson`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentresponsibleperson`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
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
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentresponsibleperson`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
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
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentresponsibleperson`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`responsibleperson`.`Financially` = 1;

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
FROM `cc_old`.`Residentmedcondition`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`Residentmedcondition`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);

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
FROM `cc_old`.`drugallergy`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`drugallergy`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);


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
FROM `cc_old`.`residentmedhistory`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentmedhistory`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);


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
FROM `cc_old`.`residentallergies`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentallergies`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);

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
FROM `cc_old`.`dietresidentrestriction`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`dietresidentrestriction`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);


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
FROM `cc_old`.`residentmeds`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`residentmeds`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);


### Assessments
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
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`assessment`.`id_resident`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`assessment`.`discriminator` = 'r' AND `cc_old`.`assessment`.`id_form` = 20;

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
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`assessment`.`id_resident`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
       JOIN (SELECT `cc_old`.`tmp_assessment_data`.`seq` AS idx FROM `cc_old`.`tmp_assessment_data`) AS INDEXES
WHERE `cc_old`.`assessment`.`discriminator` = 'r' AND  `cc_old`.`assessment`.`id_form` = 20
  AND `cc_old`.`assessment`.`data` IS NOT NULL
  AND `cc_old`.`assessment`.`data` != '[]'
  AND JSON_EXTRACT(`cc_old`.`assessment`.`data`, CONCAT('$[', idx, ']')) IS NOT NULL;


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
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`events`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`events`.`Resident_ID` IS NOT NULL
  AND `cc_old`.`events`.`Event_Definition_ID` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
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
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`events`.`Resident_ID`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`events`.`Resident_ID` IS NOT NULL
  AND `cc_old`.`events`.`Event_Definition_ID` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
  AND IF(JSON_VALID(`cc_old`.`events`.`Event_Data`),
         CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`cc_old`.`events`.`Event_Data`, '$.fields.rpPerson.id')), SIGNED INTEGER),
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
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`contract`.`owner_type` = 1;

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
FROM `cc_old`.`facilities`
WHERE `cc_old`.`facilities`.`Facility_ID` IN (6, 13);

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
FROM `cc_old`.`dinningroom`
WHERE `cc_old`.`dinningroom`.`Facility_ID` IN (6, 13);


# Facility Room
SELECT CONCAT(
           'INSERT INTO `db_seniorcare_migration`.`tbl_facility_room` (`db_seniorcare_migration`.`tbl_facility_room`.`id_facility`, `db_seniorcare_migration`.`tbl_facility_room`.`number`, `db_seniorcare_migration`.`tbl_facility_room`.`floor`, `db_seniorcare_migration`.`tbl_facility_room`.`notes`) SELECT ',
           `cc_old`.`facilityrooms`.`Facility_ID`,
           ', \'',
           REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
           '\', \'',
           `cc_old`.`facilityrooms`.`Floor`,
           '\', "',
           IFNULL(TRIM(CAST(`cc_old`.`facilityrooms`.`Notes` AS CHAR CHARACTER SET utf8)), ''),
           '" WHERE NOT EXISTS ( SELECT `id` FROM `db_seniorcare_migration`.`tbl_facility_room` WHERE `db_seniorcare_migration`.`tbl_facility_room`.`id_facility` = ',
           `cc_old`.`facilityrooms`.`Facility_ID`,
           ' AND `db_seniorcare_migration`.`tbl_facility_room`.`number` = \'',
           REGEXP_REPLACE(`cc_old`.`facilityrooms`.`Room_Number`, '\s?([0-9]+)\s?(.*)\s?', '$1'),
           '\');'
         ) AS 'statement'
FROM `cc_old`.`facilityrooms`
WHERE `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);

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
WHERE `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13);

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
       IF(`cc_old`.`hosting`.`owner_type` != 3, IF(`cc_old`.`residents`.`Ambulatory`=1, 1, 0), NULL)    AS 'ambulatory',
       IFNULL(`cc_old`.`events`.`Event_Notes`, '')                                          AS 'notes'
FROM `cc_old`.`hosting`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13) AND (`cc_old`.`hosting`.`object` NOT IN (7, 401, 543, 647))
       INNER JOIN `cc_old`.`events` ON `cc_old`.`events`.`Event_ID` = `cc_old`.`hosting`.`admitted_id`
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
       IFNULL(`cc_old`.`events`.`Event_Notes`, '')                                          AS 'notes'
FROM `cc_old`.`hosting`
       INNER JOIN `cc_old`.`residents` ON `cc_old`.`residents`.`Resident_ID` = `cc_old`.`hosting`.`owner`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
       INNER JOIN `cc_old`.`events` ON `cc_old`.`events`.`Event_ID` = `cc_old`.`hosting`.`discharged_id`
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

CALL `db_seniorcare_migration`.ValidateAll();

#------------------------------------------------------------------------------------------------------------------------

### Resident Photo
SELECT JSON_OBJECT(
           'id', `cc_old`.`residents`.`Resident_ID`,
           'photo', CONCAT('https://ccdb.ciminocare.com/uploads/documents/', CAST(`cc_old`.`residents`.`Photo` AS CHAR CHARACTER SET utf8))
         ) AS 'item'
FROM `cc_old`.`residents`
       INNER JOIN `cc_old`.`facilityrooms` ON `cc_old`.`facilityrooms`.`Room_ID` = `cc_old`.`residents`.`Room_ID` AND `cc_old`.`facilityrooms`.`Facility_ID` IN (6, 13)
WHERE `cc_old`.`residents`.`Photo` != '';

# Use app:migrate:photos command to import these photos to SeniorCare.


# Validate Phone numbers
UPDATE `db_seniorcare_migration`.`tbl_resident_phone`           SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2)  $4-$6');
UPDATE `db_seniorcare_migration`.`tbl_responsible_person_phone` SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2)  $4-$6');
UPDATE `db_seniorcare_migration`.`tbl_physician_phone`          SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2)  $4-$6');
UPDATE `db_seniorcare_migration`.`tbl_user_phone`               SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2)  $4-$6');

UPDATE `db_seniorcare_migration`.`tbl_facility`                 SET `phone`           = REGEXP_REPLACE(`phone`,           '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2)  $4-$6');
UPDATE `db_seniorcare_migration`.`tbl_facility`                 SET `fax`             = REGEXP_REPLACE(`fax`,             '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2)  $4-$6');



DELETE FROM `sc_0015_db`.`tbl_allergen`;
DELETE FROM `sc_0015_db`.`tbl_assessment_care_level`;
DELETE FROM `sc_0015_db`.`tbl_assessment_care_level_group`;
DELETE FROM `sc_0015_db`.`tbl_assessment_category`;
DELETE FROM `sc_0015_db`.`tbl_assessment_form`;
DELETE FROM `sc_0015_db`.`tbl_assessment_form_care_level_group`;
DELETE FROM `sc_0015_db`.`tbl_assessment_form_category`;
DELETE FROM `sc_0015_db`.`tbl_assessment_row`;
DELETE FROM `sc_0015_db`.`tbl_care_level`;
DELETE FROM `sc_0015_db`.`tbl_city_state_zip`;
DELETE FROM `sc_0015_db`.`tbl_diagnosis`;
DELETE FROM `sc_0015_db`.`tbl_diet`;
DELETE FROM `sc_0015_db`.`tbl_dining_room`;
DELETE FROM `sc_0015_db`.`tbl_event_definition`;
DELETE FROM `sc_0015_db`.`tbl_facility`;
DELETE FROM `sc_0015_db`.`tbl_facility_bed`;
DELETE FROM `sc_0015_db`.`tbl_facility_room`;
DELETE FROM `sc_0015_db`.`tbl_medical_history_condition`;
DELETE FROM `sc_0015_db`.`tbl_medication`;
DELETE FROM `sc_0015_db`.`tbl_payment_source`;
DELETE FROM `sc_0015_db`.`tbl_physician`;
DELETE FROM `sc_0015_db`.`tbl_physician_phone`;
DELETE FROM `sc_0015_db`.`tbl_relationship`;
DELETE FROM `sc_0015_db`.`tbl_resident`;
DELETE FROM `sc_0015_db`.`tbl_resident_admission`;
DELETE FROM `sc_0015_db`.`tbl_resident_allergen`;
DELETE FROM `sc_0015_db`.`tbl_resident_diagnosis`;
DELETE FROM `sc_0015_db`.`tbl_resident_diet`;
DELETE FROM `sc_0015_db`.`tbl_resident_event`;
DELETE FROM `sc_0015_db`.`tbl_resident_event_responsible_persons`;
DELETE FROM `sc_0015_db`.`tbl_resident_medical_history_condition`;
DELETE FROM `sc_0015_db`.`tbl_resident_medication`;
DELETE FROM `sc_0015_db`.`tbl_resident_medication_allergy`;
DELETE FROM `sc_0015_db`.`tbl_resident_phone`;
DELETE FROM `sc_0015_db`.`tbl_resident_physician`;
DELETE FROM `sc_0015_db`.`tbl_resident_rent`;
DELETE FROM `sc_0015_db`.`tbl_resident_responsible_person`;
DELETE FROM `sc_0015_db`.`tbl_resident_responsible_person_roles`;
DELETE FROM `sc_0015_db`.`tbl_responsible_person`;
DELETE FROM `sc_0015_db`.`tbl_responsible_person_phone`;
DELETE FROM `sc_0015_db`.`tbl_responsible_person_role`;
DELETE FROM `sc_0015_db`.`tbl_salutation`;
