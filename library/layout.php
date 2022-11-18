<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@enginx.com> All Rights Reserved.
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
 * Authors:    Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/library/layout.php') !== false) {
    header("Location: ../index.php");
    exit;
}

const DEFAULT_COMMON_CSS = array(
    "css/2.css",
    "css/form-field-tooltip.css",
    "library/js_date/datechooser.css"
);

const DEFAULT_COMMON_JS = array(
    "library/javascript/pages_common.js",
    "library/js_date/date-functions.js",
    "library/js_date/datechooser.js",
    "library/javascript/rounded-corners.js",
    "library/javascript/form-field-tooltip.js"
);

// this function can be used for printing the HTML prologue
function print_html_prologue($title, $lang='en',
                             $extra_css=array(), $extra_js=array(),
                             $inline_extra_css="", $inline_extra_js="",
                             $common_css=DEFAULT_COMMON_CSS, $common_js=DEFAULT_COMMON_JS) {
?>

<!DOCTYPE html>
<html lang="<?= strtolower($lang) ?>">
<head>
<title><?= ucfirst($title) ?> :: daloRADIUS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />

<?php
    $css = array_merge($common_css, $extra_css);
    foreach ($css as $href) {
        printf('<link rel="stylesheet" href="%s" />' . "\n", $href);
    }
    
    if (!empty($inline_extra_css)) {
        echo "<style>" . $inline_extra_css . "</style>" . "\n";
    }
    
    $js = array_merge($common_js, $extra_js);
    foreach ($js as $src) {
        printf('<script src="%s"></script>' . "\n", $src);
    }
    
    if (!empty($inline_extra_js)) {
        echo "<script>" . $inline_extra_js . "</script>" . "\n";
    } 
?>
</head>

<body>
    <div id="wrapper">
        <div id="innerwrapper">

<?php
}

function print_footer_and_html_epilogue($inline_extra_js="") {
    echo '</div><!-- #contentnorightbar -->' . "\n"
       . '<div id="footer">';
       
    include('page-footer.php');
    
    echo '</div><!-- #footer -->' . "\n"
       . '</div><!-- #innerwrapper -->' . "\n"
       . '</div><!-- #wrapper -->' . "\n";
    
    if (!empty($inline_extra_js)) {
        echo "<script>" . "\n"
           . $inline_extra_js . "\n"
           . "</script>" . "\n";
    }
       
    echo '</body>'
       . '</html>';
    
}

// this function can be used for printing pages title and help
function print_title_and_help($title, $help="") {
    $h2 = (!empty($help))
        ? sprintf('<a href="#" onclick="javascript:toggleShowDiv(' . "'helpPage'" . ')">%s<h144>&#x2754;</h144></a>', $title)
        : $title;
?>
    <h2 id="Intro" style="margin-bottom: 10px">
        <?= $h2 ?>
    </h2><!-- #Intro -->

<?php
    if (!empty($help)) {
?>
        <div id="helpPage" style="display:none; margin-bottom: 20px">
            <?= $help ?>
        </div><!-- #helpPage -->
<?php
    }
}


// prints a select field as a element of a list
function print_select_as_list_elem($name, $label, $options, $selected_value="") {
    echo '<li class="fieldset">';
    printf('<label for="%s" class="form">%s</label>', $name, $label);
    printf('<select class="form" name="%s" id="%s">', $name, $name);
    foreach ($options as $key => $elem) {
        $value = htmlspecialchars(
                                  ((!is_int($key)) ? $key : $elem),
                                  ENT_QUOTES, 'UTF-8');
        
        $caption = htmlspecialchars($elem, ENT_QUOTES, 'UTF-8');
        $selected = ($selected_value === $value) ? " selected" : "";
        printf('<option value="%s"%s>%s</option>', $value, $selected, $caption);
    }
    echo '</select>';
    echo '</li>';
}

//~ sample $input_descriptor:
//~ $input_descriptor = array(
                            //~ "id" => "username",
                            //~ "name" => "username", (required)
                            //~ "caption" => t('all','Username'),
                            //~ "type" => "text|password|number", (required)
                            //~ "tabindex" => 100,
                            //~ "value" => "",
                            //~ "random" => true,
                            //~ "tooltipText" => t('Tooltip','usernameTooltip'),
                            //~ "pattern" => "[a-zA-Z0-9_]+",
                            //~ "disabled" => true,
                            
                            //~ "min" => 1|2018-10-30, (type=number|date specific)
                            //~ "max" => 10|2022-01-29, (type=number|date specific)
                            //~ "step" => 1, (type=number specific)
                            
                            //~ "onclick" => "javascript:..."
                            //~ "checked" => true (type=checkbox specific)
                         //~ );
function print_input_field($input_descriptor) {
    global $configValues;

    if (!array_key_exists('id', $input_descriptor) || empty($input_descriptor['id'])) {
        $input_descriptor['id'] = $input_descriptor['name'];
    }
    
    $input_descriptor['type'] = (!array_key_exists('type', $input_descriptor))
                              ? "text" : strtolower($input_descriptor['type']);
    
    if (array_key_exists('caption', $input_descriptor) && !empty($input_descriptor['caption']) &&
        !in_array($input_descriptor['type'], array('hidden', 'button', 'submit'))) {
        printf('<label for="%s" class="form">%s</label>', $input_descriptor['id'], $input_descriptor['caption']);
    }
    
    $value = (array_key_exists('value', $input_descriptor) && !empty($input_descriptor['value']))
           ? htmlspecialchars($input_descriptor['value'], ENT_QUOTES, 'UTF-8') : "";
    
    printf('<input type="%s" name="%s" id="%s"', $input_descriptor['type'],
                                                 $input_descriptor['name'],
                                                 $input_descriptor['id']);
    if (!empty($value)) {
        printf(' value="%s"', $value);
    }
    
    if ($input_descriptor['type'] == "password") {
        printf(' maxlength="%s"', $configValues['CONFIG_DB_PASSWORD_MAX_LENGTH']);
        printf(' minlength="%s"', $configValues['CONFIG_DB_PASSWORD_MIN_LENGTH']);
    }
    
    if (in_array($input_descriptor['type'], array("number", "date"))) {
        if (array_key_exists('min', $input_descriptor)) {
            printf(' min="%s"', $input_descriptor['min']);
        }
        
        if (array_key_exists('max', $input_descriptor)) {
            printf(' max="%s"', $input_descriptor['max']);
        }
        
        if ($input_descriptor['type'] == "number") {
            if (array_key_exists('step', $input_descriptor)) {
                printf(' step="%s"', $input_descriptor['step']);
            }
        }
    }
    
    if ($input_descriptor['type'] == "number") {
        if (array_key_exists('step', $input_descriptor)) {
            printf(' step="%s"', $input_descriptor['step']);
        }
    }
    
    if (in_array($input_descriptor['type'], array('button', 'submit'))) {
        echo ' class="button" style="display: block"';
    }
    
    if (in_array($input_descriptor['type'], array("checkbox", "radio"))) {
        if (array_key_exists('checked', $input_descriptor) && $input_descriptor['checked']) {
            echo ' checked';
        }
    }
    
    if (array_key_exists('disabled', $input_descriptor) && $input_descriptor['disabled']) {
        echo ' disabled';
    }
    
    if (array_key_exists('tabindex', $input_descriptor)) {
        printf(' tabindex="%s"', $input_descriptor['tabindex']);
    }
    
    if (array_key_exists('pattern', $input_descriptor)) {
        printf(' pattern="%s"', $input_descriptor['pattern']);
    }
    
    if (array_key_exists('onclick', $input_descriptor)) {
        printf(' onclick="%s"', $input_descriptor['onclick']);
    }
    
    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist'])) {
        printf(' list="%s-list"', $input_descriptor['id']);
    }
    
    if (array_key_exists('tooltipText', $input_descriptor)) {
        printf(' placeholder="%s"', strip_tags($input_descriptor['tooltipText']));
    }
    
    echo '>';

    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist'])) {
        printf('<datalist id="%s-list">', $input_descriptor['id']);
        foreach ($input_descriptor['datalist'] as $value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            printf('<option value="%s">' . "\n", $value);
        }
        echo '</datalist>';
    }

    if (array_key_exists('random', $input_descriptor) && $input_descriptor['random']) {
        $onclick = sprintf("randomAlphanumeric('%s', 8, '%s')",
                           $input_descriptor['id'], $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
        $name_and_id = $input_descriptor['id'] . "-random-button";
        printf('<input type="button" name="%s" id="%s" value="Random" class="button" onclick="%s">',
               $name_and_id, $name_and_id, $onclick);
    }
    
}
//~ $input_descriptor = array(
                            //~ "id" => "sessiontimeout",
                            //~ "name" => "sessiontimeout", (required)
                            //~ "caption" => t('all','SessionTimeout'),
                            //~ "type" => "number",
                            //~ "options" => array( "value" => "label" )
function print_calculated_select($select_descriptor) {

    if (!array_key_exists('id', $select_descriptor) || empty($select_descriptor['id'])) {
        $select_descriptor['id'] = $select_descriptor['name'];
    }

    echo '<li class="fieldset">' . "\n";
    printf('<label for="%s" class="form">%s</label>', $select_descriptor['id'], $select_descriptor['caption']);
    printf('<input type="%s" name="%s" id="%s"', $select_descriptor['type'],
                                                 $select_descriptor['name'],
                                                 $select_descriptor['id']);
    
    if (array_key_exists('tabindex', $select_descriptor)) {
        printf(' tabindex="%s"', $select_descriptor['tabindex']);
    }
    
    echo '>';
    
    $onchange = sprintf("javascript:setText(this.id, '%s')", $select_descriptor['id']);
    
    printf('<select onchange="%s" id="option-%d" class="form">', $onchange,  rand());
    
    foreach ($select_descriptor['options'] as $value => $label) {
        printf('<option value="%s">%s</option>' . "\n", $value, $label);
    }

    echo '</select>';
    echo '</li>' . "\n";
}

//~ $textarea_descriptor = array(
                            //~ "id" => "notes",
                            //~ "name" => "notes", (required)
                            //~ "caption" => t('ContactInfo','Notes'),
                            //~ tabindex => 100,
                            //~ "content" => xxx
function print_textarea($textarea_descriptor) {

    if (!array_key_exists('id', $textarea_descriptor) || empty($textarea_descriptor['id'])) {
        $textarea_descriptor['id'] = $textarea_descriptor['name'];
    }

    printf('<label for="%s" class="form">%s</label>', $textarea_descriptor['id'], $textarea_descriptor['caption']);
    printf('<textarea class="form" name="%s" id="%s"', $textarea_descriptor['name'], $textarea_descriptor['id']);
    
    if (array_key_exists('tabindex', $textarea_descriptor)) {
        printf(' tabindex="%s"', $textarea_descriptor['tabindex']);
    }
    
    echo '>';
    
    if (isset($textarea_descriptor['content'])) {
        echo htmlspecialchars($textarea_descriptor['content'], ENT_QUOTES, 'UTF-8');
    }
    
    echo '</textarea>';
}

//~ descriptor array( "type" => "select", "id" => ..., "name" => ..., "options" => array( "value" => "caption", ..)
//~ "selected_value" => "value", "caption" => ...
function print_select($select_descriptor) {
    if (!array_key_exists('id', $select_descriptor) || empty($select_descriptor['id'])) {
        $select_descriptor['id'] = $select_descriptor['name'];
    }

    printf('<label for="%s" class="form">%s</label>', $select_descriptor['id'], $select_descriptor['caption']);
    printf('<select class="form" name="%s" id="%s"', $select_descriptor['name'], $select_descriptor['id']);
    
    if (array_key_exists('onchange', $select_descriptor)) {
        printf(' onchange="%s"', $select_descriptor['onchange']);
    }
    
    if (array_key_exists('multiple', $select_descriptor)) {
        echo ' multiple';
    }
    
    if (array_key_exists('size', $select_descriptor) && intval($select_descriptor['size']) > 0) {
        printf(' size="%s"', $select_descriptor['size']);
    }
    
    echo '>';
    
    foreach ($select_descriptor['options'] as $key => $elem) {
        
        $value = ((!is_int($key)) ? $key : $elem);
        $caption = htmlspecialchars($elem, ENT_QUOTES, 'UTF-8');
        
        printf('<option value="%s"', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        
        if (array_key_exists('selected_value', $select_descriptor) && !empty($select_descriptor['selected_value'])) {
            
            $selected_values = (!is_array($select_descriptor['selected_value']))
                             ? array( $select_descriptor['selected_value'] )
                             : $select_descriptor['selected_value'];
            
            foreach ($selected_values as $selected_value) {
                if ($selected_value === $value) {
                    echo ' selected';
                    break;
                }
            }
        }
        
        printf('>%s</option>', $caption);
    }
    echo '</select>';
}

// wrapper for printing form components
function print_form_component($descriptor) {

    if (!array_key_exists('id', $descriptor) || empty($descriptor['id'])) {
        $descriptor['id'] = $descriptor['name'];
    }

    if (!in_array($descriptor['type'], array('hidden', 'button', 'submit'))) {
        echo '<li class="fieldset">' . "\n";
    }

    if ($descriptor['type'] == 'textarea') {
        print_textarea($descriptor);
    } else  if ($descriptor['type'] == 'select') {
        print_select($descriptor);
    } else {
        print_input_field($descriptor);
    }
    
    if (array_key_exists('tooltipText', $descriptor) && !empty($descriptor['tooltipText'])) {
        $tooltip_box_id = sprintf('%s-tooltip', $descriptor['id']);
        $onclick = sprintf("javascript:toggleShowDiv('%s')", $tooltip_box_id);
        printf('<a href="#" onclick="%s"><img src="images/icons/comment.png" alt="Tip"></a>', $onclick);
        printf('<div id="%s" style="display:none; visibility:visible" class="ToolTip">%s</div>',
               $tooltip_box_id, $descriptor['tooltipText']);
    }
    
    //~ if (array_key_exists('tooltipText', $descriptor) && !empty($descriptor['tooltipText'])) {
        //~ $tooltip_box_id = sprintf('%s-tooltip', $descriptor['id']);
        //~ $onclick = sprintf("javascript:toggleShowDiv('%s')", $tooltip_box_id);
        

        
        //~ echo '<div class="tooltip">';
        //~ printf('<a href="#" onclick="%s"><img src="images/icons/comment.png" alt="Tip" style="vertical-align: middle"></a>', $onclick);
        //~ printf('<span id="%s" class="tooltiptext">%s</span>', $tooltip_box_id, $descriptor['tooltipText']);
        //~ echo '</div>';
    //~ }
    
    if (!in_array($descriptor['type'], array('hidden', 'button', 'submit'))) {
        echo '</li>' . "\n";
    }
}

// $button_descriptors = array( 'tab-id' => 'button-caption' )
function print_tab_navbuttons($button_descriptors) {
    if (is_array($button_descriptors) && count($button_descriptors)) {
        echo '<div class="tab">';
        
        $count = 0;
        foreach ($button_descriptors as $tab_id => $button_caption) {
            $onclick = sprintf("openTab(event, '%s')", $tab_id);
            
            printf('<button id="%s-button" class="tablinks%s" onclick="%s">%s</button>',
                   $tab_id, (($count == 0) ? " active" : ""), $onclick, strip_tags($button_caption));

            $count++;
        }
        
        
        echo '</div>' . "\n";
    }
}
?>
