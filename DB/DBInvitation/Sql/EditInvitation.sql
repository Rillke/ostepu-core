<?php
/**
 * @file EditInvitation.sql
 * updates a specified entry in %Invitation table
 * @author  Till Uhlig
 * @param int \$esid a %Invitation identifier
 * @param int \$userid a %Invitation identifier
 * @param int \$member a %Invitation identifier
 * @param string <?php echo $values; ?> the input data, e.g. 'a=1, b=2'
 * @result -
 */
?>

UPDATE Invitation
SET <?php echo $values; ?>
WHERE ES_id = '<?php echo $esid; ?>' and U_id_leader = '<?php echo $memberid; ?>' and U_id_member = '<?php echo $userid; ?>'