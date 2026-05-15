<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Description:    this file extends user management pages
 *                 (specifically edit user page) to allow group management.
 *                 Essentially, this extention populates groups into tables
 * 
 * Authors:        Liran Tal <liran@lirantal.com>
 *                 Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/management/groups.php') !== false) {
    header('Location: ../../index.php');
    exit;
}


if (!isset($groupTerminology)) {
    $groupTerminology = "Group";
    $groupTerminologyPriority = "GroupPriority";
}

$groupLabel = t('all',$groupTerminology);
$prorityLabel = t('all',$groupTerminologyPriority);


$sql = sprintf("SELECT groupname, priority FROM %s WHERE username='%s' ORDER BY priority ASC",
               $configValues['CONFIG_DB_TBL_RADUSERGROUP'], $dbSocket->escapeSimple($username));
$res = $dbSocket->query($sql);

$_groups = array();

while ($row = $res->fetchRow()) {
    list($group_name, $group_priority) = $row;
    $group_priority = intval($group_priority);
    if (array_key_exists($group_name, $_groups)) {
        continue;
    }
    
    $_groups[$group_name] = $group_priority;
}

echo '<div class="container">';

$_fieldset0_descriptor = array(
                                "title" => "Associate with new groups",
                             );

open_fieldset($_fieldset0_descriptor);

include_once('include/management/populate_selectbox.php');
$options = get_groups();
array_unshift($options, '');
print_form_component(array(
                                        "type" =>"select",
                                        "name" => "groups",
                                        "id" => "groups",
                                        "caption" => $groupLabel,
                                        "options" => $options,
                                        "tooltipText" => t('Tooltip','groupTooltip')
                                     ));

echo '<button class="btn btn-success" type="button" id="group-%d-add" onclick="add_group()">Add</button>';

close_fieldset();

$_fieldset1_descriptor = array(
                                "title" => "Already associated groups",
                                "hidden" => (count($_groups) == 0),
                                "id" => "associated_groups_fieldset"
                             );

open_fieldset($_fieldset1_descriptor);

echo '<div id="groupsDiv">';

$counter = 0;
foreach ($_groups as $g => $p) {
    $_onclick = sprintf("del_group('group-%d')", $counter);
    
    printf('<div class="d-flex flex-row justify-content-center align-items-center gap-2 my-1" id="group-%d">', $counter);
    
    echo '<div class="align-self-end">';
    printf('<a id="group-%d-delete" onclick="%s" class="mx-1" href="#" data-bs-toggle="tooltip" ', $counter, $_onclick);
    echo 'data-bs-placement="top" data-bs-title="Remove"><i class="bi bi-x-circle-fill text-danger"></i></a>';
    echo '</div>';
    
    echo '<div>';
    printf('<label for="group-%d-name">%s</label>', $counter, $groupLabel);
    printf('<input class="form-control" type="text" value="%s" name="groups[%d][0]" id="group-%d-name">', $g, $counter, $counter);
    echo '</div>';
    
    echo '<div>';
    printf('<label for="group-%d-priority">%s</label>', $counter, $prorityLabel);
    $is_disabled_users_group = is_disabled_users_group($g);
    $p = normalize_user_group_priority($g, $p);
    printf('<input class="form-control%s" type="number" min="%d" value="%d" name="groups[%d][1]" id="group-%d-priority"%s>',
           ($is_disabled_users_group ? ' bg-body-secondary text-muted' : ''),
           ($is_disabled_users_group ? DALO_DISABLED_USERS_GROUP_PRIORITY : 0), $p, $counter, $counter,
           ($is_disabled_users_group ? ' readonly title="Reserved disabled-users group; priority is locked to -1 so it is evaluated before normal groups."' : ''));
    echo '</div>';
    
    
    echo '</div>';
    
    $counter++;
}

echo '</div>';

close_fieldset();

echo '<small class="mt-4 d-block">You can also manage all user-group mappings for this user '
   . sprintf('<a href="mng-rad-usergroup-list-user.php?username=%s">here</a>.', htmlspecialchars($username, ENT_QUOTES, 'UTF-8'))
   . '</small>';

echo '</div><!-- .container -->';

echo "<script>" . "\n";

$selected_groups_js = json_encode(array_keys($_groups));
$disabled_users_group_js = json_encode(DALO_DISABLED_USERS_GROUP);
$disabled_users_group_priority_js = json_encode((string)DALO_DISABLED_USERS_GROUP_PRIORITY);

echo <<<EOF
var selected_groups = {$selected_groups_js};
var disabledUsersGroupName = {$disabled_users_group_js};
var disabledUsersGroupPriority = {$disabled_users_group_priority_js};

function add_group() {
    var sel = document.getElementById('groups'),
        selected_group = sel.options[sel.selectedIndex].text;
    
    if (selected_group === "" || selected_groups.includes(selected_group)) {
        return;
    }
    
    // inner html
    var num = selected_groups.length;
    
    var onclick = `del_group('group-\${num}')`;
    
    var content = '<div class="align-self-end">'
                + `<a id="group-\${num}-delete" onclick="\${onclick}" class="mx-1" href="#" data-bs-toggle="tooltip" `
                + 'data-bs-placement="top" data-bs-title="Remove"><i class="bi bi-x-circle-fill text-danger"></i></a>'
                + '</div>';
    
    content += '<div>'
            +  `<label for="group-\${num}-name">{$groupLabel}</label>`
            +  `<input class="form-control" type="text" value="\${selected_group}" name="groups[\${num}][0]" id="group-\${num}-name">`
            +  '</div>';
    
    var disabledUsersGroup = selected_group === disabledUsersGroupName;
    var priorityValue = disabledUsersGroup ? disabledUsersGroupPriority : '0';
    var priorityMin = disabledUsersGroup ? disabledUsersGroupPriority : '0';
    var priorityLock = disabledUsersGroup
        ? ' readonly title="Reserved disabled-users group; priority is locked to -1 so it is evaluated before normal groups."'
        : '';
    var priorityClass = disabledUsersGroup ? ' bg-body-secondary text-muted' : '';

    content += '<div>'
            +  `<label for="group-\${num}-priority">{$prorityLabel}</label>`
            +  `<input class="form-control\${priorityClass}" type="number" min="\${priorityMin}" value="\${priorityValue}" name="groups[\${num}][1]" id="group-\${num}-priority"\${priorityLock}>`
            +  '</div>';
    
    var id = "group-" + num;
    var groupDiv = document.createElement('div');
    groupDiv.setAttribute('id', id);
    groupDiv.setAttribute('class', 'd-flex flex-row justify-content-center align-items-center gap-2 my-1');
    
    groupDiv.innerHTML = content;
    
    var groupsDiv = document.getElementById('groupsDiv');
    groupsDiv.appendChild(groupDiv);
    
    selected_groups.push(selected_group);
    
    document.getElementById('associated_groups_fieldset').style.display = "block";
}

function del_group(id) {
    var groupName = document.getElementById(id + "-name").value;
    
    const index = selected_groups.indexOf(groupName);
    if (index > -1) {
        selected_groups.splice(index, 1);
    }
    
    var groupDiv = document.getElementById(id);
    var groupsDiv = document.getElementById('groupsDiv');
    groupsDiv.removeChild(groupDiv);
    
    if (selected_groups.length == 0) {
        document.getElementById('associated_groups_fieldset').style.display = "none";
    }
}
EOF;

echo "</script>";


?>
