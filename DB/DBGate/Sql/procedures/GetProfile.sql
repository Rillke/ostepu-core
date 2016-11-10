DROP PROCEDURE IF EXISTS `DBGateGetGateProfile`;
CREATE PROCEDURE `DBGateGetGateProfile` (IN profile varchar(30), IN authProfile varchar(30), IN ruleProfile varchar(30), IN gpid INT)
READS SQL DATA
begin
SET @s = concat("
select SQL_CACHE
    GP.*,
    GR.*,
    GA.*
FROM
    `GateProfile",profile,"` GP
        left join
    `GateAuth",authProfile,"` GA ON (GP.GP_id = GA.GP_id)
        left join
    `GateRule",ruleProfile,"` GR ON (GP.GP_id = GR.GP_id)
WHERE
    GP.GP_id = '",gpid,"';");
PREPARE stmt1 FROM @s;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;
end;