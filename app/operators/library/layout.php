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

const DEFAULT_COMMON_PROLOGUE_CSS = array(
    "static/css/bootstrap.min.css",
    "static/css/icons/bootstrap-icons.css",
);

const DEFAULT_COMMON_PROLOGUE_JS = array(
    "static/js/pages_common.js",
);

const DEFAULT_COMMON_EPILOGUE_JS = array(
    "static/js/bootstrap.bundle.min.js",
);


/*
 * GENERIC LAYOUT FUNCTIONS
 */

// this function prints the HTML prologue, including:
// - the top bar (nav bar)
// - the subnav bar
// - the sidebar
function print_html_prologue($title, $lang='en', $extra_css=array(), $extra_js=array(), $inline_extra_css="", $inline_extra_js="",
                             $common_css=DEFAULT_COMMON_PROLOGUE_CSS, $common_js=DEFAULT_COMMON_PROLOGUE_JS) {
    global $configValues;

    $lang = strtolower($lang);
    $dir = ($lang === 'ar') ? "rtl" : "ltr";
    $title = ucfirst($title) . " :: daloRADIUS";

    echo <<<EOF
<!DOCTYPE html>
<html lang="{$lang}" dir="{$dir}">
<head>
<title>{$title}</title>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="copyright" content="Liran Tal & Filippo Lauria">
<meta name="robots" content="noindex">

<link rel="apple-touch-icon" sizes="180x180" href="static/images/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="static/images/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="static/images/favicon/favicon-16x16.png">
<link rel="manifest" href="static/images/favicon/site.webmanifest">

EOF;

    $css = array_merge($common_css, $extra_css);
    foreach ($css as $href) {
        printf('<link rel="stylesheet" href="%s">' . "\n", $href);
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

    // closing html head section,
    // opening html body section
    echo <<<EOF
</head>

<body>
    <div class="row">

EOF;
    // printing nav bar
    include_once("include/menu/nav.php");

    // printing subnav bar
    include_once("include/menu/subnav.php");

    // opening main wrapper container
    echo <<<EOF

        <div class="container">
            <div class="row m-0 p-0">
                <div id="sidebar" class="min-vh-100 col-sm-2 p-3 bg-light text-dark border-end">
EOF;

    // printing sidebar
    $sidebar_file = "include/menu/sidebar.php";
    if (file_exists($sidebar_file) && is_readable($sidebar_file)) {
        include_once($sidebar_file);
    }

    // closing sidebar col
    // opening main content col
    echo <<<EOF
                </div><!-- .col-sm-3 -->

                <div class="col-sm-10 p-3 bg-white text-dark">

EOF;
}

// this function prints the HTML epilogue, including:
// - the footer
// - some "useful javascript file inclusion tag"
// - some useful javscript code
function print_footer_and_html_epilogue($inline_extra_js="", $extra_js=array(), $common_js=DEFAULT_COMMON_EPILOGUE_JS) {

    // closing main content col and
    // main wrapper container
    echo <<<EOF
                </div><!-- -col-sm-9 -->
            </div><!-- .row -->
        </div><!-- .container -->

EOF;

    // printing footer
    echo <<<EOF
        <div class="p-4 text-center text-bg-light border-top border-bottom">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 text-bg-white">
                    <img src="static/images/daloradius_small.png">
                </div>
                <div class="flex-grow-1 ms-3">
EOF;

    include("page-footer.php");

    echo <<<EOF
                </div>
            </div>
        </div>
    </div>
EOF;

    // if needed we include, at the bottom of the body,
    // some javascript file...
    $js = array_merge($common_js, $extra_js);
    foreach ($js as $src) {
        printf('<script src="%s"></script>' . "\n", $src);
    }


    echo '<script>' . "\n";
    
    // ...along with the javascript code
    // for initing tooltips
    if (!empty($inline_extra_js)) {
        echo $inline_extra_js . "\n";
    }
    

    echo <<<EOF
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')),
    tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

EOF;

    // we close the html document
    echo '</body></html>';
}


// this function can be used for printing pages title and help
function print_title_and_help($title, $help="") {
    printf('<h3>%s', $title);

    if (empty($help)) {
        echo '</h3>' . "\n";
        return;
    }

    $elem_id = "modal_" . rand();

    echo <<<EOF
<button type="button" class="btn btn-light ms-2" data-bs-toggle="modal" data-bs-target="#{$elem_id}">
    <i class="bi bi-question-circle"></i></button></h3>

<div class="modal fade" id="{$elem_id}" tabindex="-1" aria-labelledby="{$elem_id}_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="{$elem_id}_label">{$title} :: help</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
            {$help}
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

EOF;

}


// this function can be used for "opening" a form
// the descriptor takes some form attributes such as
// name, id, disable, hidden, action, method
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


// this function can be used for "closing" a form
function close_form() {
    echo "</form>";
}


// this function can be used for opening a fieldset (in a form)
function open_fieldset($descriptor=array()) {
    echo '<fieldset class="mt-2"';
    
    $display = (array_key_exists('hidden', $descriptor) && $descriptor['hidden']) ? 'none' : 'block';
    printf(' style="display: %s"', $display);
    
    if (array_key_exists('id', $descriptor) && !empty($descriptor['id'])) {
        printf(' id="%s"', strip_tags(trim($descriptor['id'])));
    }

    if (array_key_exists('disabled', $descriptor) && $descriptor['disabled']) {
        echo ' disabled';
    }

    echo '>';

    if (array_key_exists('title', $descriptor) && !empty($descriptor['title'])) {
        printf('<h5>%s</h5>', strip_tags(trim(html_entity_decode($descriptor['title']))));
    }

    echo '<div class="row">';
}


// this function can be used for closing a fieldset (in a form)
function close_fieldset() {
    echo '</div>'
       . '</fieldset>';
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



/*
 * TABLE LAYOUT FUNCTIONS
 */

// this function can be used for printing contextual actions or information
// the "subject" is a label inserted in an "a" element for which a custom "onclick" action can be specified too.
// the descriptors than contains some "actions". Each of those contains an "href" and a "label"
// if specified the ajax_id is used for getting information via ajax call (i.e. triggered within the "onclick" event)
function get_tooltip_list_str($descriptor) {

    $subject = $descriptor['subject'];
    $onclick = (isset($descriptor['onclick'])) ? sprintf('onclick="%s"', $descriptor['onclick']) : "";

    $result = <<<EOF
    <div class="dropdown">
        <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" {$onclick}
            data-bs-toggle="dropdown" aria-expanded="false">{$subject}</a>

        <ul class="dropdown-menu text-small">
EOF;

    if (isset($descriptor['actions']) && is_array($descriptor['actions']) && count($descriptor['actions']) > 0) {
        foreach ($descriptor['actions'] as $actions) {
            $result .= sprintf('<li><a class="dropdown-item" href="%s">%s</a></li>', $actions['href'], $actions['label']);
        }
        
        if (isset($descriptor['ajax_id']) || isset($descriptor['content'])) {
            $result .= '<li><hr class="dropdown-divider"></li>';
        }
    }

    if (isset($descriptor['ajax_id'])) {
        $result .= sprintf('<li><span class="dropdown-item" id="%s"><i class="bi bi-hourglass-split"></i></span></li>', $descriptor['ajax_id']);
    } else if (isset($descriptor['content'])) {
        $result .= sprintf('<li><span class="dropdown-item">%s</span></li>', $descriptor['content']);
    }

    $result .= '</ul>' . '</div>';

    return $result;
}


function print_tooltip_list($descriptor) {
    echo get_tooltip_list_str($descriptor);
}

// this functions can be used for printing controls that are in common for most of the listing tables
// i.e. select all and select none. This sould not be used alone but, it is called by the print_table_prologue() func.
function print_common_controls($name) {

    $js_function_name = "select_" . rand();

    echo <<<EOF
<div class="btn-group btn-group-sm" role="group">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="{$js_function_name}('all')">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="{$js_function_name}('none')">Select None</button>
</div>

<script>
    function {$js_function_name}(what) {
        var selected = (what == 'all'),
            boxes = document.getElementsByName('{$name}');

        for (var i=0; i < boxes.length; i++) {
            if (boxes[i].type == 'checkbox') {
                boxes[i].checked = selected;
            }
        }
    }
</script>

EOF;
}


function print_additional_controls($descriptors) {
    foreach ($descriptors as $d) {
        $class = (isset($d['class'])) ? $d['class'] : "btn-primary";
        printf('<button class="btn btn-sm %s ms-1" type="button" onclick="%s">%s</button>', $class, $d['onclick'], $d['label']);
    }
}


function get_checkbox_str($descriptor) {
    $result = "";

    $id = (isset($descriptor['id']))
        ? $descriptor['id'] : "checkbox-" . rand();

    $result .= '<div class="form-check">';
    $result .= sprintf('<input class="form-check-input" type="checkbox" value="%s" name="%s" id="%s">',
                       $descriptor['value'], $descriptor['name'], $id);

    if (array_key_exists('label', $descriptor) && !empty($descriptor['label'])) {
        $label = $descriptor['label'];
        $result .= sprintf('<label class="form-check-label" for="%s">%s</label>', $id, $label);
    }

    $result .= '</div>';

    return $result;
}


// this functions can be used for printing checkboxes in table listings.
function print_checkbox($descriptor) {
    echo get_checkbox_str($descriptor);
}

// this wrapper function prints the table prologue.
// The prologue is divided in three section:
// - start => contains the common controls (select all, select none)
// - center => contains the page numbering controls
// - end => contains additional controls (CSV export)
function print_table_prologue($descriptors) {
    echo '<div class="d-flex justify-content-between">';

    if (isset($descriptors['start']) && is_array($descriptors['start'])) {
        echo '<div>';
        $start = $descriptors['start'];

        if (isset($start['common_controls'])) {
            print_common_controls($start['common_controls']);
        }

        if (isset($start['additional_controls']) && is_array($start['additional_controls'])) {
            print_additional_controls($start['additional_controls']);
        }
        echo '</div>';
    }

    if (isset($descriptors['center']) && is_array($descriptors['center'])) {
        $center = $descriptors['center'];

        if (isset($center['draw']) && $center['draw']) {
            echo '<div>';
            print_page_numbering($center['params']);
            echo '</div>';
        }
    }

    if (isset($descriptors['end']) && is_array($descriptors['end'])) {
        echo '<div>';
        print_additional_controls($descriptors['end']);
        echo '</div>';
    }

    echo '</div>';
}


function print_simple_table($table) {
    if (isset($table['title']) && isset($table['rows']) && count($table['rows']) > 0) {
        echo '<div class="container mb-4">';
        echo '<div class="col-8 offset-2">';
        printf('<h5>%s</h5>', $table['title']);
        echo '<table class="table table-striped">';
        
        foreach ($table['rows'] as $row) {
            list($key, $value) = $row;
            
            echo '<tr class="row">';
            printf('<th scope="row" class="col-6 text-end">%s</th>', $key);
            printf('<td class="col-6 text-start">%s</td>', $value);
            echo '</tr>';
        }
        
        echo '</table>';
        
        echo '</div></div>';
        
    }
    
}


// opens table header (if needed wraps it with a form)
function print_table_top($descriptor=array()) {
    if (isset($descriptor['form'])) {
        open_form($descriptor['form']);
    }

    echo <<<EOF

<table class="table table-striped table-hover">
    <thead>
        <tr>

EOF;
}


// closes table header, opens table body
function print_table_middle() {
    echo <<<EOF

        </tr>
    </thead>

    <tbody>

EOF;
}

// prints table foot
function print_table_foot($params) {

    $num_rows = $params['num_rows'];
    $rows_per_page = $params['rows_per_page'];
    $colspan = $params['colspan'];
    $multiple_pages = $params['multiple_pages'];

    echo <<<EOF
        <tfoot>
            <tr>
                <td colspan="{$colspan}">
                    displayed <strong>{$rows_per_page}</strong> record(s)
EOF;
                    if ($multiple_pages) {
                        printf(' out of <strong>%s</strong>', $num_rows);
                    }

    echo <<<EOF
                </th>
            </tr>
        </tfoot>

EOF;

}

function print_table_bottom($descriptor=array()) {
    echo '</tbody>' . "\n";

    if (isset($descriptor['table_foot'])) {
        print_table_foot($descriptor['table_foot']);
    }

    echo '</table>' . "\n";

    if (isset($descriptor['form'])) {
        $csrf = array(
                        "name" => "csrf_token",
                        "type" => "hidden",
                        "value" => dalo_csrf_token(),
                     );
        print_form_component($csrf);
        close_form();
    }
}


// print table row
function print_table_row($table_row) {
    echo '<tr>';
    foreach ($table_row as $item) {
        printf('<td>%s</td>', $item);
    }
    echo '</tr>';
}



/*
 * FORM COMPONENTS LAYOUT
 */

// this function can be used for printing an input field
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
        $class = (in_array($input_descriptor['type'], array( 'radio', 'checkbox' ))) ? "form-check-label" : "form-label";
        printf('<label for="%s" class="%s mx-1 mb-1">%s</label>', $input_descriptor['id'], $class, $input_descriptor['caption']);
    }

    if (array_key_exists('random', $input_descriptor) && $input_descriptor['random']) {
        echo '<div class="input-group">';
    }

    $class = (in_array($input_descriptor['type'], array( 'radio', 'checkbox' ))) ? "form-check-input" : "form-control";
    printf('<input class="%s" type="%s" name="%s" id="%s"', $class, $input_descriptor['type'],
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
        echo ' class="btn btn-primary"';
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

    if (array_key_exists('tooltipText', $input_descriptor) && !empty($input_descriptor['tooltipText'])) {
        $describedby_id = $input_descriptor['id'] .  '-help';
        printf(' aria-describedby="%s"', $describedby_id);
    }

    echo '>';

    if (array_key_exists('random', $input_descriptor) && $input_descriptor['random']) {
        $onclick = sprintf("randomAlphanumeric('%s', 8, '%s')",
                           $input_descriptor['id'], $configValues['CONFIG_USER_ALLOWEDRANDOMCHARS']);
        
        echo '<span class="input-group-text"><button class="btn btn-link btn-sm" type="button"';
        printf(' onclick="%s" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Random">', $onclick);
        echo '<i class="bi bi-shuffle"></i>'
           . '</button>'
           . '</span>'
           . '</div>';

    }

    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist'])) {
        printf('<datalist id="%s">', $datalist_id);
        foreach ($input_descriptor['datalist'] as $value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            printf('<option value="%s">' . "\n", $value);
        }
        echo '</datalist>';
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

    printf('<label for="%s" class="form-label mb-1">%s</label>', $select_descriptor['id'], $select_descriptor['caption']);
    echo '<div class="input-group">';
    printf('<input type="%s" class="form-control" name="%s" id="%s"', $select_descriptor['type'],
                                                 $select_descriptor['name'],
                                                 $select_descriptor['id']);

    if (array_key_exists('tabindex', $select_descriptor)) {
        printf(' tabindex="%s"', $select_descriptor['tabindex']);
    }

    echo '>';

    echo '<span class="input-group-text">&lt;</span>';

    $onchange = sprintf("javascript:setText(this.id, '%s')", $select_descriptor['id']);

    printf('<select class="form-select" onchange="%s" id="option-%d" class="form">', $onchange,  rand());

    foreach ($select_descriptor['options'] as $value => $label) {
        printf('<option value="%s">%s</option>' . "\n", $value, $label);
    }

    echo '</select>';
    echo '</div>';
    //~ echo '</li>' . "\n";
}


// this function can be used for printing a textarea field
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
           ? trim($textarea_descriptor['class']) : "form-control";

    printf('<label for="%s" class="form-label mb-1">%s</label>', $textarea_descriptor['id'], $textarea_descriptor['caption']);
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


// this function can be used for printing a select field
//~ descriptor array( "type" => "select", "id" => ..., "name" => ..., "options" => array( "value" => "caption", ..)
//~ "selected_value" => "value", "caption" => ..., "integer_value" => true
function print_select($select_descriptor) {
    if (!array_key_exists('id', $select_descriptor) || empty($select_descriptor['id'])) {
        $select_descriptor['id'] = $select_descriptor['name'];
    }

    if (isset($select_descriptor['caption'])) {
        printf('<label for="%s" class="form-label mb-1">%s</label>', $select_descriptor['id'], $select_descriptor['caption']);
    }
    printf('<select class="form-select" name="%s" id="%s"', $select_descriptor['name'], $select_descriptor['id']);

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


    if (isset($select_descriptor['tooltipText'])) {
        $tooltipText = str_replace('"', "'", strip_tags($select_descriptor['tooltipText']));

        if (!empty($tooltipText)) {
            printf(' placeholder="%s"', $tooltipText);

            if (array_key_exists('sidebar', $select_descriptor) && $select_descriptor['sidebar'] !== false) {
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

//~ $descriptor = array( 'onclick' => $onclick, 'attribute' => $row[0], 'select_name' => $name, 'selected_option' => $row[1],
                        //~ 'id__attribute' => $id_attribute, 'type' => $type, 'value' => $row[2], 'name' => $name, 'attr_type' => $row[3], 'attr_desc' => $row[4]);
function print_edit_attribute($descriptor) {
    global $valid_ops;

    echo '<div class="d-flex flex-row justify-content-center align-items-center gap-2 my-1">';
    
    echo '<div>';
    printf('<a href="#" onclick="%s">', $descriptor['onclick']);
    echo '<i class="bi bi-x-circle-fill text-danger me-2"></i></a>';
    printf('<strong>%s</strong>', $descriptor['attribute']);
    echo '</div>';
                
    echo '<div class="flex-fill">';
    printf('<input type="hidden" name="%s" value="%s">', $descriptor['name'], $descriptor['id__attribute']);            
    printf('<input class="form-control" type="%s" value="%s" name="%s">', $descriptor['type'], $descriptor['value'], $descriptor['name']);
    echo '</div>';
                
    echo '<div>';
    printf('<select name="%s" class="form-select">', $descriptor['name']);
    
    foreach ($valid_ops as $op) {
        $selected = ($op == $descriptor['selected_option']) ? " selected" : "";
        printf('<option value="%s"%s>%s</option>', $op, $selected, $op);
    }
    echo '</select>';
    echo '</div>';
                
    printf('<input type="hidden" name="%s" value="%s">', $descriptor['name'], $descriptor['table']);
                
    if (isset($descriptor['attr_type']) || isset($descriptor['attr_desc'])) {
        $tooltipText = "";
        
        $descriptor['attr_type'] = strip_tags(trim($descriptor['attr_type']));
        $descriptor['attr_desc'] = strip_tags(trim($descriptor['attr_desc']));
        
        if (!empty($descriptor['attr_type'])) {
            $tooltipText .= sprintf('Type: %s.', strip_tags($descriptor['attr_type']));
        }
                
        if (!empty($descriptor['attr_desc'])) {
            $tooltipText .= sprintf("\n" . 'Tooltip Description: %s.', $descriptor['attr_desc']);
        }
                
        if (!empty(trim($tooltipText))) {
            echo '<div>';
            printf('<i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="%s"></i>', $tooltipText);
            echo '</div>';
        }
    }
                
    echo '</div><!-- .row -->';
}

// "id", "name", "class", "icon", "value", "large"
function print_button($descriptor) {
    if (!array_key_exists('id', $descriptor) || empty($descriptor['id'])) {
        $descriptor['id'] = $descriptor['name'];
    }
    
    $class = (isset($descriptor['class'])) ? trim($descriptor['class']) : "btn-primary";
    $type = (isset($descriptor['type'])) ? strtolower(trim($descriptor['type'])) : "button";
    //~ $icon = (isset($descriptor['icon'])) ? trim($descriptor['icon']) : "save-fill";
    
    echo '<div class="my-3';
    
    if ($type === "submit" || (isset($descriptor['large']) && $descriptor['large'])) {
        echo ' d-grid';
    }
    
    echo '">';
    
    printf('<button type="%s" class="btn %s"', $type, $class);
    if (isset($descriptor['onclick'])) {
        printf(' onclick="%s"', trim($descriptor['onclick']));
    }
    echo '>';
    
    if (isset($descriptor['icon'])) {
        printf('<i class="bi bi-%s mx-1"></i>', trim($descriptor['icon']));
    }
    
    if (isset($descriptor['value'])) {
        echo trim($descriptor['value']);
    }
    
    echo '</button>';
    echo '</div>';
    
}

// this wrapper function tries to call the right function
// according to the "type" of the component read in the $descriptor
function print_form_component($descriptor) {

    if (!array_key_exists('id', $descriptor) || empty($descriptor['id'])) {
        $descriptor['id'] = $descriptor['name'];
    }

    if (!in_array($descriptor['type'], array('hidden', 'button', 'submit'))) {
        $class = (isset($descriptor['class'])) ? trim($descriptor['class']) : "";
        printf('<div class="mb-1 %s">' . "\n", $class);
    }

    if ($descriptor['type'] == 'textarea') {
        print_textarea($descriptor);
    } else  if ($descriptor['type'] == 'select') {
        print_select($descriptor);
    } else  if ($descriptor['type'] == 'submit' || $descriptor['type'] == 'button') {
        print_button($descriptor);
    } else {
        print_input_field($descriptor);
    }

    if (array_key_exists('tooltipText', $descriptor) && !empty($descriptor['tooltipText']) &&
        (!array_key_exists('sidebar', $descriptor) || $descriptor['sidebar'] === false)) {
        
        $tooltip_box_id = sprintf('%s-%d-tooltip', $descriptor['id'], rand());
        $describedby_id = $descriptor['id'] .  '-help';
        
        $tooltipText = preg_replace('/\n/', '', strip_tags(html_entity_decode($descriptor['tooltipText'])));
        $tooltipText = preg_replace('/\s+/', ' ', trim($tooltipText));
        
        printf('<div id="%s" class="form-text">%s</div>', $describedby_id, $tooltipText);
    }

    if (!in_array($descriptor['type'], array('hidden', 'button', 'submit'))) {
        echo '</div>' . "\n";
    }
}



/*
 * MENU FORM COMPONENTS LAYOUT
 */
// this function can be used for printing, in the menu (ie. sidebar), a select field
function menu_print_select($select_descriptor) {
    if (!array_key_exists('id', $select_descriptor) || empty($select_descriptor['id'])) {
        $select_descriptor['id'] = $select_descriptor['name'];
    }

    echo '<div class="text-start">';
    if (isset($select_descriptor['caption'])) {
        printf('<label for="%s" class="form-label d-inline ms-1"><small>%s</small></label>', $select_descriptor['id'], $select_descriptor['caption']);
    }
    printf('<select class="form-select form-select-sm" name="%s" id="%s"', $select_descriptor['name'], $select_descriptor['id']);

    if (array_key_exists('onchange', $select_descriptor)) {
        printf(' onchange="%s"', $select_descriptor['onchange']);
    }

    if (array_key_exists('multiple', $select_descriptor) && $select_descriptor['multiple'] !== false) {
        echo ' multiple';
    }

    if (array_key_exists('size', $select_descriptor) && intval($select_descriptor['size']) > 0) {
        printf(' size="%s"', $select_descriptor['size']);
    }

    if (!array_key_exists('options', $select_descriptor) || !is_array($select_descriptor['options'])) {
        echo ' disabled';
    }

    if (array_key_exists('tooltipText', $select_descriptor)) {
        $tooltipText = str_replace('"', "'", strip_tags($select_descriptor['tooltipText']));
        printf(' data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="%s"', $tooltipText);
        printf(' placeholder="%s"', $tooltipText);
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

<div class="btn-group btn-group-sm mt-1 d-flex" role="group">
  <button type="button" class="btn btn-outline-primary btn-sm" onclick="{$js_function_name}('all')">Select All</button>
  <button type="button" class="btn btn-outline-danger btn-sm" onclick="{$js_function_name}('none')">Select None</button>
</div>

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

    echo '</div>';
}


// this function can be used for printing, in the menu (ie. sidebar), an input field
function menu_print_input_field($input_descriptor) {
    global $configValues;

    if (!array_key_exists('id', $input_descriptor) || empty($input_descriptor['id'])) {
        $input_descriptor['id'] = $input_descriptor['name'];
    }

    $input_descriptor['type'] = (!array_key_exists('type', $input_descriptor))
                              ? "text" : strtolower($input_descriptor['type']);

    echo '<div class="text-start">';
    if (array_key_exists('caption', $input_descriptor) && !empty($input_descriptor['caption']) &&
        !in_array($input_descriptor['type'], array('hidden', 'button', 'submit'))) {
        printf('<label for="%s" class="form-label d-inline ms-1"><small>%s</small></label>', $input_descriptor['id'], $input_descriptor['caption']);
    }

    printf('<input class="form-control form-control-sm" type="%s" name="%s" id="%s"', $input_descriptor['type'],
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

    if (array_key_exists('disabled', $input_descriptor) && $input_descriptor['disabled']) {
        echo ' disabled';
    }

    if (array_key_exists('required', $input_descriptor) && $input_descriptor['required']) {
        echo ' required';
    }

    if (array_key_exists('pattern', $input_descriptor)) {
        printf(' pattern="%s"', $input_descriptor['pattern']);
    }

    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist']) &&
        !empty($input_descriptor['datalist'])) {
        $datalist_id = sprintf("%s-%d-list", $input_descriptor['id'], rand());
        printf(' list="%s"', $datalist_id);
    }

    if (array_key_exists('tooltipText', $input_descriptor)) {
        $tooltipText = str_replace('"', "'", strip_tags($input_descriptor['tooltipText']));
        printf(' data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="%s"', $tooltipText);
        printf(' placeholder="%s"', $tooltipText);
    }

    echo '>';

    if (array_key_exists('datalist', $input_descriptor) && is_array($input_descriptor['datalist']) &&
        !empty($input_descriptor['datalist'])) {
        printf('<datalist id="%s">', $datalist_id);
        foreach ($input_descriptor['datalist'] as $value) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            printf('<option value="%s">' . "\n", $value);
        }
        echo '</datalist>';
    }

    echo '</div>';
}


// when rendering the sidebar,
// this wrapper function tries to call the right function
// according to the "type" of the component read in the $descriptor
function menu_print_form_component($descriptor) {
    if (!array_key_exists('id', $descriptor) || empty($descriptor['id'])) {
        $descriptor['id'] = $descriptor['name'];
    }

    $descriptor['type'] = (!array_key_exists('type', $descriptor))
                              ? "text" : strtolower($descriptor['type']);

    switch ($descriptor['type']) {

        case 'select':
            menu_print_select($descriptor);
            break;

        default:
        case 'text':
            menu_print_input_field($descriptor);
            break;
    }
}


// opens the menu bar
function menu_open($title) {
    printf('<h4>%s</h4>', $title);
}


// closes the menu bar
function menu_close() {
    return;
}


// opens a menu session specifing a give $title
function menu_open_section($title) {
    printf('<h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted text-uppercase"><span>%s</span></h6>', $title);
    echo '<ul class="nav nav-pills nav-fill flex-column">';
}


// closes a menu session specifing a give $title
function menu_close_section() {
    echo '</ul><!-- .nav -->' . "\n";

}

// prints a menu link
function menu_print_link($descriptor) {
    $descriptor['label'] = strip_tags(trim($descriptor['label']));

    $label = "";
    if (isset($descriptor['icon'])) {
        $label .= sprintf('<i class="bi bi-%s me-1"></i>', $descriptor['icon']);
    }

    $label .= $descriptor['label'];

    $class = ($descriptor['href'] === basename($_SERVER['PHP_SELF'])) ? "active" : "";

    printf('<li class="nav-item"><a class="nav-link %s" href="%s"%s>%s</a></li>' . "\n",
           $class, $descriptor['href'], (!empty($class)) ? ' aria-current="page"' : "", $label);
}


// according to the "type" prints the menu item
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


// prints the menu
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


// this function can be used for printing a textarea field, in a menu section
function menu_print_textarea($descriptor) {

     echo '<li class="nav-item">'
       . '<div class="card">'
       . '<div class="card-body">'
       . '<div class="d-grid gap-1">';

    printf('<small class="card-text">%s</small>', $descriptor['content']);

    if (array_key_exists('readmore', $descriptor)) {
        $readmore = $descriptor['readmore'];

        printf('<a target="_blank" href="%s" class="btn btn-sm btn-outline-dark">%s</a>',
               $readmore['href'], $readmore['label']);
    }

    echo '</div></div>'
       . '</div>'
       . '</li>';
}


// this function can be used for printing a form, in a menu section
function menu_print_form($descriptor) {
    $keys = array( 'title', 'action', 'method' );
    foreach ($keys as $key) {
        $descriptor[$key] = strip_tags(trim($descriptor[$key]));
    }

    $card_class = ($descriptor['action'] === basename($_SERVER['PHP_SELF'])) ? " border border-primary" : "";
    $button_class = ($descriptor['action'] === basename($_SERVER['PHP_SELF'])) ? "btn-primary" : "btn-secondary";

    $form_name = (isset($descriptor['form_name'])) ? $descriptor['form_name'] : "form_" . rand();

    echo '<li class="nav-item">';
    printf('<div class="card p-1 my-1%s">', $card_class);
    printf('<form name="%s" action="%s" method="%s" class="d-grid gap-2">',
           $form_name, urlencode($descriptor['action']), strtoupper($descriptor['method']));

    $label = "";
    if (isset($descriptor['icon'])) {
        $label .= sprintf('<i class="bi bi-%s me-1"></i>', $descriptor['icon']);
    }

    $label .= $descriptor['title'];

    printf('<button type="submit" class="btn %s btn-sm">%s</button>', $button_class, $label);

    foreach ($descriptor['form_components'] as $form_component) {
        menu_print_form_component($form_component);
    }

    echo '</form>'
       . '</div>'
       . '</li>';
}



/*
 * TABS LAYOUT FUNCTIONS
 */

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
        echo '<ul class="nav nav-tabs" role="tablist">';
        
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
            $active_class = ($count == $active) ? ' active' : "";
            $active_elem = ($count == $active) ? ' aria-selected="true"' : "";
            
            echo '<li class="nav-item" role="presentation">';
            echo '<button type="button" role="tab" data-bs-toggle="tab"';
            printf(' class="nav-link%s" id="%s" data-bs-target="#%s" aria-controls="%s"%s>%s</button>',
                   $active_class, $button_id, $tab_id, $tab_id, $active_elem, strip_tags($button_caption));
            echo '</li>' . "\n";

            $count++;
        }
        
        echo '</ul>' . "\n";
    }
}

function open_tab_wrapper() {
    echo '<div class="tab-content my-1">';
}

function close_tab_wrapper() {
    echo '</div><!-- .tab-content -->' . "\n";
}

function open_tab($keywords=array(), $index=0, $display=false) {
    if (array_key_exists($index, $keywords) && !empty($keywords[$index])) {
        $key = $keywords[$index];

        if (is_array($key)) {
            $tab_labelledby = strtolower($key[0]);
            $tab_id = strtolower($key[0]) . "-tab";
            $tab_title = $key[1];
        } else {
            $tab_labelledby = strtolower($key);
            $tab_id = strtolower("$key-tab");
            $tab_title = t('title', $key);
        }

        $tab_class = "tab-pane fade";
        if ($display) {
            $tab_class .= " show active";
        }

        printf('<div id="%s" class="%s" role="tabpanel" aria-labelledby="%s" tabindex="%d">' . "\n",
              $tab_id, $tab_class, $tab_labelledby, $index);
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


?>
