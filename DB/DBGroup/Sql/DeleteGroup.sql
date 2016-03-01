<?php
/**
 * @file DeleteGroup.sql
 * deletes a specified group entry from %Group table
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 *
 * @package OSTEPU (https://github.com/ostepu/system)
 * @since 0.1.0
 *
 * @author Till Uhlig <till.uhlig@student.uni-halle.de>
 * @date 2014-2015
 * @author Jörg Baumgarten <kuroi.tatsu@freenet.de>
 * @date 2014
 *
 * @param int \$esid a %ExerciseSheet identifier
 * @param int \$userid a %User identifier
 * @result -
 */
?>

DELETE FROM `Group`
WHERE
    ES_id = '<?php echo $esid; ?>' and U_id_leader = '<?php echo $userid; ?>'