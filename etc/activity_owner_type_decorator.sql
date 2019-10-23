DROP FUNCTION IF EXISTS `udf_ActivityOwnerTypeDecorator`;
DELIMITER ;;
CREATE FUNCTION `udf_ActivityOwnerTypeDecorator`(input VARCHAR(4000)) RETURNS JSON
  DETERMINISTIC
BEGIN
  DECLARE owner_type JSON DEFAULT '[]';
  DECLARE owner_type_value INT;
  DECLARE i INT DEFAULT 0;

  WHILE i < JSON_LENGTH(input) DO
  SELECT JSON_EXTRACT(input, CONCAT('$[', i, ']')) INTO owner_type_value;

  IF owner_type_value IS NOT NULL THEN
    SELECT JSON_ARRAY_APPEND(owner_type, '$',
      CASE
        WHEN owner_type_value = 1 THEN 'Lead'
        WHEN owner_type_value = 2 THEN 'Referral'
        WHEN owner_type_value = 3 THEN 'Organization'
        WHEN owner_type_value = 4 THEN 'Outreach'
        WHEN owner_type_value = 5 THEN 'Contact'
        ELSE ''
      END) INTO owner_type;
  END IF;

  SELECT i + 1 INTO i;
  END WHILE;

  RETURN owner_type;
END
;;
DELIMITER ;