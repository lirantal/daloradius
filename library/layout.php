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
    "css/1.css",
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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= strtolower($lang) ?>" lang="<?= strtolower($lang) ?>">
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

<?php
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
function print_select_as_list_elem($name, $label, $options, $selected_value) {
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
