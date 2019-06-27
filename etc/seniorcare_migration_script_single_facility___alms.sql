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
#### Payment Source
#INSERT INTO `db_seniorcare_migration`.`tbl_payment_source`
#  (
#    `id_space`,
#    `id`,
#    `title`
#  )
#SELECT 1,
#       `alms`.`common_payment_source`.`id`                                       AS 'id',
#       IF(`alms`.`common_payment_source`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`common_payment_source`.`title`, '\\s+', ' ')), '') AS 'title'
#FROM `alms`.`common_payment_source`;

### Relationship
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
       (`alms`.`common_city_state_zip`.`id` + 20000)                                   AS 'id',
       IF(`alms`.`common_city_state_zip`.`state_full`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`state_full`, '\\s+', ' ')), '')  AS 'state_full',
       IF(`alms`.`common_city_state_zip`.`state_2_ltr`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`state_2_ltr`, '\\s+', ' ')), '') AS 'state_abbr',
       IF(`alms`.`common_city_state_zip`.`zip_main`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`zip_main`, '\\s+', ' ')), '')    AS 'zip_main',
       IF(`alms`.`common_city_state_zip`.`zip_sub`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`zip_sub`, '\\s+', ' ')), '')     AS 'zip_sub',
       IF(`alms`.`common_city_state_zip`.`city`!='', TRIM(REGEXP_REPLACE(`alms`.`common_city_state_zip`.`city`, '\\s+', ' ')), '')        AS 'city'
FROM `alms`.`common_city_state_zip`
WHERE `alms`.`common_city_state_zip`.`id` >= 2817;

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
       IF(`alms`.`common_physician`.`email`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`email`, '\\s+', ' ')), '')          AS `email`,
       IF(`alms`.`common_physician`.`website_url`!='', TRIM(REGEXP_REPLACE(`alms`.`common_physician`.`website_url`, '\\s+', ' ')), '')    AS `website_url`
FROM `alms`.`common_physician`;


INSERT INTO `db_seniorcare_migration`.`tbl_physician_phone`
  (
    `id_physician`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT (`alms`.`common_physician`.`id` + 10000)                          AS 'id_physician',
       1                                                                 AS 'compatibility',
       4                                                                 AS 'type',
       `alms`.`common_physician`.`office_phone`                          AS 'number',
       0                                                                 AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `alms`.`common_physician` WHERE `alms`.`common_physician`.`office_phone` IS NOT NULL;

INSERT INTO `db_seniorcare_migration`.`tbl_physician_phone`
  (
    `id_physician`,
    `compatibility`,
    `type`,
    `number`,
    `is_primary`,
    `is_sms_enabled`
  )
SELECT (`alms`.`common_physician`.`id` + 10000)                          AS 'id_physician',
       1                                                                 AS 'compatibility',
       5                                                                 AS 'type',
       `alms`.`common_physician`.`emergency_phone`                       AS 'number',
       0                                                                 AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `alms`.`common_physician` WHERE `alms`.`common_physician`.`emergency_phone` IS NOT NULL;
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
SELECT (`alms`.`common_physician`.`id` + 10000)                          AS 'id_physician',
       1                                                                 AS 'compatibility',
       6                                                                 AS 'type',
       `alms`.`common_physician`.`fax`                                   AS 'number',
       0                                                                 AS 'is_primary',
       0                                                                 AS 'is_sms_enabled'
FROM `alms`.`common_physician` WHERE `alms`.`common_physician`.`fax` IS NOT NULL;

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
       (`alms`.`base_resident`.`id` + 10000)                                      AS 'id',
       `alms`.`base_resident`.`id_common_salutation`                              AS 'id_salutation',
       IF(`alms`.`base_resident`.`first_name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident`.`first_name`, '\\s+', ' ')), '')     AS 'first_name',
       IF(`alms`.`base_resident`.`last_name`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident`.`last_name`, '\\s+', ' ')), '')      AS 'last_name',
       IF(`alms`.`base_resident`.`middle_initial`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident`.`middle_initial`, '\\s+', ' ')), '') AS 'middle_name',
       `alms`.`base_resident`.`dob`                                               AS 'birthday',
       `alms`.`base_resident`.`sex`                                               AS 'gender'
FROM `alms`.`base_resident`
WHERE `alms`.`base_resident`.`id_group` IN (1);

### Resident Phone
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
  AND JSON_EXTRACT(`alms`.`base_resident`.`phones`, CONCAT('$[', idx, ']')) IS NOT NULL
  AND `alms`.`base_resident`.`id_group` IN (1);


# Resident Physician
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
WHERE `alms`.`base_resident`.`id_physician` IS NOT NULL
  AND `alms`.`base_resident`.`id_group` IN (1);

# Resident Responsible Person
INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`,
    `sort_order`
  )
SELECT (`alms`.`base_resident_responsible_person`.`id` + 10000)                    AS 'id',
       (`alms`.`base_resident_responsible_person`.`id_resident` + 10000)           AS 'id_resident',
       (`alms`.`base_resident_responsible_person`.`id_responsible_person` + 10000) AS 'id_physician',
       `alms`.`base_resident_responsible_person`.`id_relationship`                 AS 'id_relationship',
       `alms`.`base_resident_responsible_person`.`order_no`                        AS 'sort_order'
FROM `alms`.`base_resident_responsible_person`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_responsible_person`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
WHERE `alms`.`base_resident_responsible_person`.`id_relationship` <= 69;

INSERT INTO `db_seniorcare_migration`.`tbl_resident_responsible_person`
  (
    `id`,
    `id_resident`,
    `id_responsible_person`,
    `id_relationship`,
    `sort_order`
  )
SELECT (`alms`.`base_resident_responsible_person`.`id` + 10000)                    AS 'id',
       (`alms`.`base_resident_responsible_person`.`id_resident` + 10000)           AS 'id_resident',
       (`alms`.`base_resident_responsible_person`.`id_responsible_person` + 10000) AS 'id_physician',
       (`alms`.`base_resident_responsible_person`.`id_relationship` + 200)         AS 'id_relationship',
       `alms`.`base_resident_responsible_person`.`order_no`                        AS 'sort_order'
FROM `alms`.`base_resident_responsible_person`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_responsible_person`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
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
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_responsible_person`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
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
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_responsible_person`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
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

# Resident Diagnosis
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
FROM `alms`.`base_resident_diagnosis`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_diagnosis`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;


# Resident Medication Allergy
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
FROM `alms`.`base_resident_medication_allergy`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_medication_allergy`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;


# Resident Medicatal History Condition
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
FROM `alms`.`base_resident_medical_history_condition`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_medical_history_condition`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;

# Resident Allergen
INSERT INTO `db_seniorcare_migration`.`tbl_resident_allergen`
  (
    `id_resident`,
    `id_allergen`,
    `notes`
  )
SELECT (`alms`.`base_resident_allergen`.`id_resident` + 10000)                    AS 'id_resident',
       `alms`.`base_resident_allergen`.`id_allergen`                              AS 'id_allergen',
       IF(`alms`.`base_resident_allergen`.`notes`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_allergen`.`notes`, '\\s+', ' ')), '') AS 'notes'
FROM `alms`.`base_resident_allergen`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_allergen`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;

# Resident Diet
INSERT INTO `db_seniorcare_migration`.`tbl_resident_diet`
  (
    `id_resident`,
    `id_diet`,
    `description`
  )
SELECT (`alms`.`base_resident_diet`.`id_resident` + 10000)                          AS 'id_resident',
       `alms`.`base_resident_diet`.`id_diet`                                        AS 'id_diet',
       IF(`alms`.`base_resident_diet`.`description`!='', TRIM(REGEXP_REPLACE(`alms`.`base_resident_diet`.`description`, '\\s+', ' ')), '') AS 'description'
FROM `alms`.`base_resident_diet`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_diet`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;

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
FROM `alms`.`base_resident_medication`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_resident_medication`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;


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
SELECT (`alms`.`common_assessment_assessment`.`id` + 10000)   AS 'id',
       `alms`.`common_assessment_assessment`.`id_form`        AS 'id_form',
       `alms`.`common_assessment_assessment`.`id_assesstable` AS 'id_resident',
       `alms`.`common_assessment_assessment`.`date`           AS 'date',
       `alms`.`common_assessment_assessment`.`performed_by`   AS 'performed_by',
       `alms`.`common_assessment_assessment`.`notes`          AS 'notes',
       `alms`.`common_assessment_assessment`.`score`          AS 'score'
FROM `alms`.`common_assessment_assessment`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`common_assessment_assessment`.`id_assesstable` AND `alms`.`base_resident`.`id_group` IN (1)
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
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`common_assessment_assessment`.`id_assesstable` AND `alms`.`base_resident`.`id_group` IN (1)
       JOIN (SELECT `alms`.`tmp_assessment_data`.`seq` AS idx FROM `alms`.`tmp_assessment_data`) AS INDEXES
WHERE `alms`.`common_assessment_assessment`.`discriminator` = 'residents'
  AND `alms`.`common_assessment_assessment`.`data` IS NOT NULL
  AND `alms`.`common_assessment_assessment`.`data` != '[]'
  AND JSON_EXTRACT(`alms`.`common_assessment_assessment`.`data`, CONCAT('$[', idx, ']')) IS NOT NULL;


### Event Definitions 
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
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_event`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
WHERE `alms`.`base_event`.`id_resident` IS NOT NULL
  AND `alms`.`base_event`.`id_definition` IN (SELECT `id` FROM `db_seniorcare_migration`.`tbl_event_definition`)
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
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`base_event`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
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
SELECT (`alms`.`contract`.`id` + 10000)                            AS 'id',
       (`alms`.`contract`.`id_resident` + 10000)                   AS 'id_resident',
       `alms`.`contract`.`type`                                    AS 'rent_period',
       `alms`.`contract`.`start`                                   AS 'start',
       `alms`.`contract`.`end`                                     AS 'end',
       `alms`.`contract`.`amount`                                  AS 'amount',
       IF(`alms`.`contract`.`note`!='', TRIM(REGEXP_REPLACE(`alms`.`contract`.`note`, '\\s+', ' ')), '') AS 'notes',
       `alms`.`contract`.`source`                                  AS 'source'
FROM `alms`.`contract`
INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id`=`alms`.`contract`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
;

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
    `number_of_floors`,
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
       0                                                                      AS 'number_of_floors',
       IF(`alms`.`base_group`.`license`!='', TRIM(REGEXP_REPLACE(`alms`.`base_group`.`license`, '\\s+', ' ')), '')       AS 'license',
       `alms`.`base_group`.`max_beds_number`                                  AS 'license_capacity',
       `alms`.`base_group`.`max_beds_number`                                  AS 'capacity'
FROM `alms`.`base_group`
WHERE `alms`.`base_group`.`discriminator` = 'FFC' AND `alms`.`base_group`.`id` IN (1);

# Facility Dining Room
INSERT INTO `db_seniorcare_migration`.`tbl_dining_room`
  (
    `id`,
    `id_facility`,
    `title`
  )
SELECT (`alms`.`residents_ffc_dining_room`.`id` + 10000)                             AS 'id',
       (`alms`.`residents_ffc_dining_room`.`id_facility` + 10000)                    AS 'id_facility',
       IF(`alms`.`residents_ffc_dining_room`.`title`!='', TRIM(REGEXP_REPLACE(`alms`.`residents_ffc_dining_room`.`title`, '\\s+', ' ')), '') AS 'title'
FROM `alms`.`residents_ffc_dining_room`
WHERE `alms`.`residents_ffc_dining_room`.`id_facility` IN (1);


# Facility Room
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
  AND `alms`.`base_sub_group`.`id_group` IN (1);

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
  AND `alms`.`base_sub_group`.`id_group` IN (1);

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
  AND `alms`.`base_sub_group`.`id_group` IN (1);


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
       INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
WHERE
  `alms`.`hosting`.`id_sub_group` NOT IN (4, 7, 64, 79, 85, 92, 130, 149);

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
       INNER JOIN `alms`.`base_resident` ON `alms`.`base_resident`.`id` = `alms`.`hosting`.`id_resident` AND `alms`.`base_resident`.`id_group` IN (1)
WHERE `alms`.`hosting`.`discharged_id` IS NOT NULL
  AND `alms`.`hosting`.`id_sub_group` NOT IN (4, 7, 64, 79, 85, 92, 130, 149);

CALL `db_seniorcare_migration`.ValidateAll();

#------------------------------------------------------------------------------------------------------------------------

### Resident Photo
SELECT JSON_OBJECT(
           'id', (`alms`.`base_resident`.`id` + 10000) COLLATE utf8_general_ci,
           'photo', CONCAT('https://alms.ciminocare.com/uploads/documents/', `alms`.`base_resident`.`photo`) COLLATE
                    utf8_general_ci
         ) AS 'item'
FROM `alms`.`base_resident`
WHERE `alms`.`base_resident`.`photo` != '' AND `alms`.`base_resident`.`id_group` IN (1);

# Use app:migrate:photos command to import these photos to SeniorCare.
# Validate Phone numbers
UPDATE `db_seniorcare_migration`.`tbl_resident_phone`           SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_responsible_person_phone` SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_physician_phone`          SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_user_phone`               SET `number`          = REGEXP_REPLACE(`number`,          '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2) ($4)-($6)');

UPDATE `db_seniorcare_migration`.`tbl_facility`                 SET `phone`           = REGEXP_REPLACE(`phone`,           '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2) ($4)-($6)');
UPDATE `db_seniorcare_migration`.`tbl_facility`                 SET `fax`             = REGEXP_REPLACE(`fax`,             '(.*)([0-9]{3})(.*)([0-9]{3})(.*)([0-9]{4})(.*)', '($2) ($4)-($6)');


DELETE FROM `sc_0015_db`.`oauth2_access_token`;
DELETE FROM `sc_0015_db`.`oauth2_auth_code`;
DELETE FROM `sc_0015_db`.`oauth2_client`;
DELETE FROM `sc_0015_db`.`oauth2_refresh_token`;
DELETE FROM `sc_0015_db`.`tbl_allergen`;
DELETE FROM `sc_0015_db`.`tbl_apartment`;
DELETE FROM `sc_0015_db`.`tbl_apartment_bed`;
DELETE FROM `sc_0015_db`.`tbl_apartment_room`;
DELETE FROM `sc_0015_db`.`tbl_assessment`;
DELETE FROM `sc_0015_db`.`tbl_assessment_assessment_row`;
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
DELETE FROM `sc_0015_db`.`tbl_lead_activity`;
DELETE FROM `sc_0015_db`.`tbl_lead_activity_status`;
DELETE FROM `sc_0015_db`.`tbl_lead_activity_type`;
DELETE FROM `sc_0015_db`.`tbl_lead_care_type`;
DELETE FROM `sc_0015_db`.`tbl_lead_facilities`;
DELETE FROM `sc_0015_db`.`tbl_lead_lead`;
DELETE FROM `sc_0015_db`.`tbl_lead_organization`;
DELETE FROM `sc_0015_db`.`tbl_lead_organization_phone`;
DELETE FROM `sc_0015_db`.`tbl_lead_referral`;
DELETE FROM `sc_0015_db`.`tbl_lead_referral_phone`;
DELETE FROM `sc_0015_db`.`tbl_lead_referrer_type`;
DELETE FROM `sc_0015_db`.`tbl_lead_state_change_reason`;
DELETE FROM `sc_0015_db`.`tbl_login_attempt`;
DELETE FROM `sc_0015_db`.`tbl_medical_history_condition`;
DELETE FROM `sc_0015_db`.`tbl_medication`;
DELETE FROM `sc_0015_db`.`tbl_medication_form_factor`;
DELETE FROM `sc_0015_db`.`tbl_payment_source`;
DELETE FROM `sc_0015_db`.`tbl_physician`;
DELETE FROM `sc_0015_db`.`tbl_physician_phone`;
DELETE FROM `sc_0015_db`.`tbl_region`;
DELETE FROM `sc_0015_db`.`tbl_relationship`;
DELETE FROM `sc_0015_db`.`tbl_report_notification`;
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
DELETE FROM `sc_0015_db`.`tbl_role`;
DELETE FROM `sc_0015_db`.`tbl_salutation`;
DELETE FROM `sc_0015_db`.`tbl_space`;
DELETE FROM `sc_0015_db`.`tbl_speciality`;
DELETE FROM `sc_0015_db`.`tbl_user`;
DELETE FROM `sc_0015_db`.`tbl_user_invite`;
DELETE FROM `sc_0015_db`.`tbl_user_invite_role`;
DELETE FROM `sc_0015_db`.`tbl_user_log`;
DELETE FROM `sc_0015_db`.`tbl_user_phone`;
DELETE FROM `sc_0015_db`.`tbl_user_role`;
