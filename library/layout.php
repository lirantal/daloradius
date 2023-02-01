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
    "static/css/2.css",
    "static/css/form-field-tooltip.css",
);

const DEFAULT_COMMON_JS = array(
    "static/js/pages_common.js",
    "static/js/rounded-corners.js",
    "static/js/form-field-tooltip.js"
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
        print_topbar();
}


function print_header() {
    global $_SESSION;
?>
    <span id="login_data">
        Welcome, <strong><?= htmlspecialchars($_SESSION['operator_user'], ENT_QUOTES, 'UTF-8') ?></strong>.
        <a href="logout.php" title="Logout" style="padding: 1px; background-color: #FF8040; color: white">
            <strong>&times;</strong>
        </a>
        <br>
        Location: <strong><?= htmlspecialchars($_SESSION['location_name'], ENT_QUOTES, 'UTF-8') ?></strong>.
    </span><!-- #login_data -->

    <span class="sep">&nbsp;</span>

    <form action="mng-search.php" method="GET">
        <input name="username" value="" placeholder="<?= t('button','SearchUsers') ?>"
            title="<?= strip_tags(t('Tooltip','Username') . '. ' . t('Tooltip','UsernameWildcard')) ?>">
    </form>

    <span class="sep">&nbsp;</span>

    <h1>
        <a title="<?= strip_tags(t('menu','Home')) ?>" href="index.php">
            <img style="border: 0" src="static/images/daloradius_small.png">
        </a>
    </h1>
    <h2><?= t('all','copyright1') ?></h2>
    <a name="top"></a>
<?php
}


function print_topbar() {
    echo '<div id="header">';
    print_header();

    // print nav items
    include_once("include/menu/nav.php");

    // print subnav items
    include_once("include/menu/subnav.php");
    echo '</div><!-- #header -->' . "\n";
}


function print_footer_and_html_epilogue($inline_extra_js="") {
    echo '</div><!-- #contentnorightbar -->' . "\n"
       . '<div id="footer">';

    include('page-footer.php');

    echo '</div><!-- #footer -->' . "\n"
       . '</div><!-- #innerwrapper -->' . "\n"
       . '</div><!-- #wrapper -->' . "\n";

    echo "<script>" . "\n";
    echo <<<EOF
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();

EOF;

    if (!empty($inline_extra_js)) {
        echo $inline_extra_js . "\n";
    }
    
    echo "</script>" . "\n";

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

    printf('<input type="%s" name="%s" id="%s"', $input_descriptor['type'],
                                                 $input_descriptor['name'],
                                                 $input_descriptor['id']);


    if (array_key_exists('value', $input_descriptor) &&
        (!empty(trim($input_descriptor['value'])) || trim($input_descriptor['value']) == "0")
       ) {
        $value = htmlspecialchars(trim($input_descriptor['value']), ENT_QUOTES, 'UTF-8');
    }

    if (isset($value)) {
        printf(' value="%s"', $value);
    }

    if ($input_descriptor['type'] == "password") {
        printf(' maxlength="%s"', $configValues['CONFIG_DB_PASSWORD_MAX_LENGTH']);
        printf(' minlength="%s"', $configValues['CONFIG_DB_PASSWORD_MIN_LENGTH']);
    }

    if (in_array($input_descriptor['type'], array("number", "date"))) {
        if (array_key_exists('min', $input_descriptor) &&
            (!empty(trim($input_descriptor['min'])) || trim($input_descriptor['min']) == "0")
           ) {
            printf(' min="%s"', trim($input_descriptor['min']));
        }

        if (array_key_exists('max', $input_descriptor) &&
            (!empty(trim($input_descriptor['max'])) || trim($input_descriptor['max']) == "0")
           ) {
            printf(' max="%s"', trim($input_descriptor['max']));
        }

        if ($input_descriptor['type'] == "number") {
            if (array_key_exists('step', $input_descriptor) &&
                (!empty(trim($input_descriptor['step'])) || trim($input_descriptor['step']) == "0")
           ) {
                printf(' step="%s"', trim($input_descriptor['step']));
            }
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

    if (array_key_exists('required', $input_descriptor) && $input_descriptor['required']) {
        echo ' required';
    }

    if (array_key_exists('tabindex', $input_descriptor)) {
        printf(' tabindex="%s"', $input_descriptor['tabindex']);
    }

    if (array_key_exists('pattern', $input_descriptor)) {
        printf(' pattern="%s"', $input_descriptor['pattern']);
    }

    if (array_key_exists('title', $input_descriptor)) {
        $title = str_replace("\n", " ", strip_tags($input_descriptor['title']));
        printf(' title="%s"', preg_replace('/\s+/', ' ', $title));
    }

    if (array_key_exists('onclick', $input_descriptor)) {
        printf(' onclick="%s"', $input_descriptor['onclick']);
    }

    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist'])) {
        $datalist_id = sprintf("%s-%d-list", $input_descriptor['id'], rand());
        printf(' list="%s"', $datalist_id);
    }

    if (array_key_exists('tooltipText', $input_descriptor)) {
        $tooltipText = str_replace('"', "'", strip_tags($input_descriptor['tooltipText']));
        
        printf(' placeholder="%s"', $tooltipText);
        
        if (array_key_exists('sidebar', $input_descriptor) && $input_descriptor['sidebar'] !== false) {
            printf(' tooltipText="%s"', $tooltipText);
        }
    }

    echo '>';

    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist'])) {
        printf('<datalist id="%s">', $datalist_id);
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

    $class = (array_key_exists('class', $textarea_descriptor) && !empty(trim($textarea_descriptor['class'])))
           ? trim($textarea_descriptor['class']) : "form";

    printf('<label for="%s" class="form">%s</label>', $textarea_descriptor['id'], $textarea_descriptor['caption']);
    printf('<textarea class="%s" name="%s" id="%s"', $class, $textarea_descriptor['name'], $textarea_descriptor['id']);

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
//~ "selected_value" => "value", "caption" => ..., "integer_value" => true
function print_select($select_descriptor) {
    if (!array_key_exists('id', $select_descriptor) || empty($select_descriptor['id'])) {
        $select_descriptor['id'] = $select_descriptor['name'];
    }

    if (isset($select_descriptor['caption'])) {
        printf('<label for="%s" class="form">%s</label>', $select_descriptor['id'], $select_descriptor['caption']);
    }
    printf('<select class="form" name="%s" id="%s"', $select_descriptor['name'], $select_descriptor['id']);

    if (array_key_exists('onchange', $select_descriptor)) {
        printf(' onchange="%s"', $select_descriptor['onchange']);
    }

    if (array_key_exists('multiple', $select_descriptor) && $select_descriptor['multiple'] !== false) {
        echo ' multiple';
    }

    if (array_key_exists('title', $select_descriptor)) {
        $title = str_replace("\n", " ", strip_tags($select_descriptor['title']));
        printf(' title="%s"', preg_replace('/\s+/', ' ', $title));
    }

    if (array_key_exists('size', $select_descriptor) && intval($select_descriptor['size']) > 0) {
        printf(' size="%s"', $select_descriptor['size']);
    }

    if (!array_key_exists('options', $select_descriptor) || !is_array($select_descriptor['options'])) {
        echo ' disabled';
    }

    if (array_key_exists('tooltipText', $input_descriptor)) {
        $tooltipText = str_replace('"', "'", strip_tags($input_descriptor['tooltipText']));
        
        if (!empty($tooltipText)) {        
            printf(' placeholder="%s"', $tooltipText);
            
            if (array_key_exists('sidebar', $input_descriptor) && $input_descriptor['sidebar'] !== false) {
                printf(' tooltipText="%s"', $tooltipText);
            }
        }
    }

    echo '>';

    if (array_key_exists('options', $select_descriptor) && is_array($select_descriptor['options'])) {
        foreach ($select_descriptor['options'] as $key => $elem) {

            if (array_key_exists('integer_value', $select_descriptor) && $select_descriptor['integer_value'] !== false) {
                $value = intval($key);
            } else {
                $value = ((!is_int($key)) ? $key : $elem);
            }
            
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
    }
    echo '</select>';
    
    if (array_key_exists('show_controls', $select_descriptor) && $select_descriptor['show_controls'] !== false &&
        array_key_exists('multiple', $select_descriptor) && $select_descriptor['multiple'] !== false) {
        $id = $select_descriptor['id'];
        $js_function_name = sprintf("select_%d", rand());
        
        echo <<<EOF
<a style="display: inline" href="#" onclick="{$js_function_name}('all')">Select All</a>
<a style="display: inline" href="#" onclick="{$js_function_name}('none')">Select None</a>

<script>
    function {$js_function_name}(what) {
        var selected = (what == 'all'),
            sqlfields = document.getElementById('{$id}');
    
        for (var i = 0; i < sqlfields.options.length; i++) {
            sqlfields.options[i].selected = selected;
        }
    }
</script>

EOF;
    }
}

// wrapper for printing form components
// if exists the keyword 'sidebar' we don't show tooltip icon
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

    if (array_key_exists('tooltipText', $descriptor) && !empty($descriptor['tooltipText']) &&
        (!array_key_exists('sidebar', $descriptor) || $descriptor['sidebar'] === false)) {
        $tooltip_box_id = sprintf('%s-%d-tooltip', $descriptor['id'], rand());
        $onclick = sprintf("javascript:toggleShowDiv('%s')", $tooltip_box_id);
        printf('<a href="#" onclick="%s"><img src="static/images/icons/comment.png" alt="Tip"></a>', $onclick);
        printf('<div id="%s" style="display:none; visibility:visible" class="ToolTip">%s</div>',
               $tooltip_box_id, $descriptor['tooltipText']);
    }

    //~ if (array_key_exists('tooltipText', $descriptor) && !empty($descriptor['tooltipText'])) {
        //~ $tooltip_box_id = sprintf('%s-tooltip', $descriptor['id']);
        //~ $onclick = sprintf("javascript:toggleShowDiv('%s')", $tooltip_box_id);



        //~ echo '<div class="tooltip">';
        //~ printf('<a href="#" onclick="%s"><img src="static/images/icons/comment.png" alt="Tip" style="vertical-align: middle"></a>', $onclick);
        //~ printf('<span id="%s" class="tooltiptext">%s</span>', $tooltip_box_id, $descriptor['tooltipText']);
        //~ echo '</div>';
    //~ }

    if (!in_array($descriptor['type'], array('hidden', 'button', 'submit'))) {
        echo '</li>' . "\n";
    }
}

// prints the back to previous session link
function print_back_to_previous_page() {
    global $_SESSION;

    if (array_key_exists('PREV_LIST_PAGE', $_SESSION) && !empty(trim($_SESSION['PREV_LIST_PAGE']))) {
        echo '<div style="float: right; text-align: right; margin: 0; font-size: small">';
        printf('<a href="%s" title="Back to Previous Page">Back to Previous Page</a>', trim($_SESSION['PREV_LIST_PAGE']));
        echo '</div>';

        unset($_SESSION['PREV_LIST_PAGE']);
    }
}

function open_form($descriptor=array()) {
    if (!array_key_exists('name', $descriptor) || empty($descriptor['name'])) {
        $descriptor['name'] = "form-" . rand();
        $descriptor['id'] = $descriptor['name'];
    } else {
        if (!array_key_exists('id', $descriptor) || empty($descriptor['id'])) {
            $descriptor['id'] = $descriptor['name'];
        }
    }

    printf('<form name="%s" id="%s"', $descriptor['name'], $descriptor['id']);

    if (array_key_exists('disabled', $descriptor) && $descriptor['disabled']) {
        echo ' disabled';
    }

    if (array_key_exists('hidden', $descriptor) && $descriptor['hidden']) {
        echo ' style="display: none"';
    }

    if (array_key_exists('action', $descriptor) && !empty($descriptor['action'])) {
        printf(' action="%s"', $descriptor['action']);
    }

    if (array_key_exists('method', $descriptor) && !empty($descriptor['method'])) {
        $descriptor['method'] = strtoupper($descriptor['method']);
    } else {
        $descriptor['method'] = "POST";
    }

    printf(' method="%s">', strtoupper($descriptor['method']));
}

function close_form() {
    echo "</form>";
}

function open_fieldset($descriptor=array()) {
    echo "<fieldset";

    if (array_key_exists('id', $descriptor) && !empty($descriptor['id'])) {
        printf(' id="%s"', strip_tags(trim($descriptor['id'])));
    }

    if (array_key_exists('disabled', $descriptor) && $descriptor['disabled']) {
        echo ' disabled';
    }

    echo ">";

    if (array_key_exists('title', $descriptor) && !empty($descriptor['title'])) {
        printf('<h302>%s</h302>', strip_tags(trim($descriptor['title'])));
    }

    echo '<ul style="margin: 10px auto">';
}

function close_fieldset() {
    echo '</ul>'
       . '</fieldset>';
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

function print_tab_header($keywords=array(), $active=0) {
    if (is_array($keywords) && count($keywords) > 0) {
        echo '<div class="tab">';

        $count = 0;
        foreach ($keywords as $key) {
            if (is_array($key)) {
                $tab_id = strtolower($key[0]) . "-tab";
                $button_id = strtolower($key[0]) . "-button";
                $button_caption = $key[1];
            } else {
                $tab_id = strtolower("$key-tab");
                $button_id = strtolower("$key-button");
                $button_caption = t('title', $key);
            }

            $onclick = sprintf("openTab(event, '%s')", $tab_id);

            printf('<button id="%s" class="tablinks%s" onclick="%s">%s</button>',
                   $button_id, (($count == $active) ? " active" : ""), $onclick, strip_tags($button_caption));

            $count++;
        }

        echo '</div>' . "\n";


    }
}

function open_tab($keywords=array(), $index=0, $display=false) {
    if (array_key_exists($index, $keywords) && !empty($keywords[$index])) {
        $key = $keywords[$index];

        if (is_array($key)) {
            $tab_id = strtolower($key[0]) . "-tab";
            $tab_title = $key[1];
        } else {
            $tab_id = strtolower("$key-tab");
            $tab_title = t('title', $key);
        }

        printf('<div id="%s" class="tabcontent"', $tab_id);

        if ($display) {
            echo ' style="display: block"';
        }

        echo '>' . "\n";
    }
}

function close_tab($keywords=array(), $index=0) {
    echo '</div>';

    if (array_key_exists($index, $keywords) && !empty($keywords[$index])) {
        $key = $keywords[$index];

        if (is_array($key)) {
            $tab_id = strtolower($key[0]) . "-tab";
        } else {
            $tab_id = strtolower("$key-tab");
        }

        echo "<!-- #$tab_id -->";
    }

    echo "\n";
}


// menu layout

function menu_open($title) {
    printf('<div id="sidebar"><h2>%s</h2>' . "\n", $title);
}

function menu_close() {
    echo '</div><!-- #sidebar -->' . "\n";
}

function menu_open_section($title) {
    printf('<h3>%s</h3><ul class="subnav">' . "\n", $title);
    
}

function menu_close_section() {
    echo '</ul><!-- .subnav -->' . "\n";
    
}

function menu_print_textarea($descriptor) {
    echo '<li>'
       . '<p class="news">' . "\n"
       . $descriptor['content']
       . '</p><!-- .news -->' . "\n";
    
    if (array_key_exists('readmore', $descriptor)) {
        $readmore = $descriptor['readmore'];
        
        printf('<a style="margin-top: 20px; text-align: right" target="_blank" href="%s" title="%s" class="more">%s</a>',
               $readmore['href'], $readmore['title'], $readmore['label']);
    }
    
    echo '</li>' . "\n";
}

function menu_print_form($descriptor) {
    $keys = array( 'title', 'action', 'method' );
    foreach ($keys as $key) {
        $descriptor[$key] = strip_tags(trim($descriptor[$key]));
    }
    
    $descriptor['action'] = $descriptor['action'];
    
    $label = '<b>&raquo;</b>';
    if (isset($descriptor['img']['src'])) {
        $label .= sprintf('<img style="margin-right:5px; border:0" src="%s">', $descriptor['img']['src']);
    }
    $label .= $descriptor['title'];
    
    $form_name = "form_" . rand();
    
    echo '<li>';
    
    printf('<a title="%s" href="javascript:document.%s.submit()">%s</a>', $descriptor['title'], $form_name, $label);
    printf('<form name="%s" action="%s" method="%s" class="sidebar">',
           $form_name, urlencode($descriptor['action']), strtoupper($descriptor['method']));
    
    foreach ($descriptor['form_components'] as $form_component) {
        print_form_component($form_component);
    }
    
    echo '</form><!-- .sidebar -->' . "\n"
       . '</li>' . "\n";
}

function menu_print_link($descriptor) {
    $descriptor['label'] = strip_tags(trim($descriptor['label']));
    
    if (!isset($descriptor['title'])) {
        $descriptor['title'] = $descriptor['label'];
    } else {
        $descriptor['title'] = strip_tags(trim($descriptor['title']));
    }
    
    $label = '<b>&raquo;</b>';
    if (isset($descriptor['img']['src'])) {
        $label .= sprintf('<img style="margin-right:5px; border:0" src="%s">', $descriptor['img']['src']);
    }
    $label .= $descriptor['label'];
    
    printf('<li><a href="%s" title="%s">%s</a></li>' . "\n", $descriptor['href'], $descriptor['title'], $label);
}

function menu_print_item($descriptor) {
    
    switch ($descriptor['type']) {
        default:
        case 'link':
            menu_print_link($descriptor);
            break;
            
        case 'textarea':
            menu_print_textarea($descriptor);
            break;
            
        case 'form':
            menu_print_form($descriptor);
            break;
    }
    
}

function menu_print($menu) {
    
    // open menu
    menu_open($menu['title']);
    
    // get sections
    $sections = $menu['sections'];
    
    foreach ($sections as $section) {
        
        // open current section
        menu_open_section($section['title']);
        
        // get descriptors for the current section
        $descriptors = $section['descriptors'];
        
        foreach ($descriptors as $descriptor) {
            menu_print_item($descriptor);
        }
        
        // close current section
        menu_close_section();
        
    }
    
    // close menu
    menu_close();
    
}

?>
