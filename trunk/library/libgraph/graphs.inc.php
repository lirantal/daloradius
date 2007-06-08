<?
/*
 +-------------------------------------------------------------------+
 |                   H T M L - G R A P H S   (v4.1)                  |
 |                                                                   |
 | Copyright Gerd Tentler               www.gerd-tentler.de/tools    |
 | Created: Sep. 17, 2002               Last modified: Nov. 29, 2006 |
 +-------------------------------------------------------------------+
 | This program may be used and hosted free of charge by anyone for  |
 | personal purpose as long as this copyright notice remains intact. |
 |                                                                   |
 | Obtain permission before selling the code for this program or     |
 | hosting this software on a commercial website or redistributing   |
 | this software over the Internet or in any other medium. In all    |
 | cases copyright must remain intact.                               |
 +-------------------------------------------------------------------+

======================================================================================================
 Example:

   $graph = new BAR_GRAPH("hBar");
   $graph->values = array(234, 125, 289, 147, 190);
   echo $graph->create();

 Returns HTML code
======================================================================================================
*/
  class BAR_GRAPH {
//----------------------------------------------------------------------------------------------------
// Configuration
//----------------------------------------------------------------------------------------------------
    var $type = 'hBar';                        // graph type: "hBar", "vBar", "pBar", or "fader"
    var $values;                               // graph data: array or string with comma-separated values

    var $graphBGColor = '';                    // graph background color: string
    var $graphBorder = '';                     // graph border: string (CSS specification; doesn't work with NN4)
    var $graphPadding = 0;                     // graph padding: integer (pixels)

    var $labels;                               // label names: array or string with comma-separated values
    var $labelColor = 'black';                 // label font color: string
    var $labelBGColor = '#C0E0FF';             // label background color: string
    var $labelBorder = '2px groove white';     // label border: string (CSS specification; doesn't work with NN4)
    var $labelFont = 'Arial, Helvetica';       // label font family: string (CSS specification)
    var $labelSize = 12;                       // label font size: integer (pixels)
    var $labelSpace = 0;                       // additional space between labels: integer (pixels)

    var $barWidth = 20;                        // bar width: integer (pixels)
    var $barLength = 1.0;                      // bar length ratio: float (from 0.1 to 2.9)
    var $barColors;                            // bar colors OR bar images: array or string with comma-separated values
    var $barBGColor;                           // bar background color: string
    var $barBorder = '2px outset white';       // bar border: string (CSS specification; doesn't work with NN4)
    var $barLevelColors;                       // bar level colors: ascending array (bLevel, bColor[,...]); draw bars >= bLevel with bColor

    var $showValues = 0;                       // show values: 0 = % only, 1 = abs. and %, 2 = abs. only, 3 = none

    var $absValuesColor = 'black';             // abs. values font color: string
    var $absValuesBGColor = '#C0E0FF';         // abs. values background color: string
    var $absValuesBorder = '2px groove white'; // abs. values border: string (CSS specification; doesn't work with NN4)
    var $absValuesFont = 'Arial, Helvetica';   // abs. values font family: string (CSS specification)
    var $absValuesSize = 12;                   // abs. values font size: integer (pixels)
    var $absValuesPrefix = '';                 // abs. values prefix: string (e.g. "$")
    var $absValuesSuffix = '';                 // abs. values suffix: string (e.g. " kg")

    var $percValuesColor = 'black';            // perc. values font color: string
    var $percValuesFont = 'Arial, Helvetica';  // perc. values font family: string (CSS specification)
    var $percValuesSize = 12;                  // perc. values font size: integer (pixels)
    var $percValuesDecimals = 0;               // perc. values number of decimals: integer

    var $charts = 1;                           // number of charts: integer

    // hBar/vBar only:
    var $legend;                               // legend items: array or string with comma-separated values
    var $legendColor = 'black';                // legend font color: string
    var $legendBGColor = '#F0F0F0';            // legend background color: string
    var $legendBorder = '2px groove white';    // legend border: string (CSS specification; doesn't work with NN4)
    var $legendFont = 'Arial, Helvetica';      // legend font family: string (CSS specification)
    var $legendSize = 12;                      // legend font size: integer (pixels)

    // debug mode: false = off, true = on; just shows some extra information
    var $debug = false;

    // default bar colors; only used if $barColors isn't set
    var $colors = array('#0000FF', '#FF0000', '#00E000', '#A0A0FF', '#FFA0A0', '#00A000');

    // error messages
    var $err_type = 'ERROR: Type must be "hBar", "vBar", "pBar", or "fader"';

    // CSS names (don't change)
    var $cssGRAPH = '';
    var $cssBAR = '';
    var $cssBARBG = '';
    var $cssLABEL = '';
    var $cssLABELBG = '';
    var $cssLEGEND = '';
    var $cssLEGENDBG = '';
    var $cssABSVALUES = '';
    var $cssPERCVALUES = '';

//----------------------------------------------------------------------------------------------------
// Class Methods
//----------------------------------------------------------------------------------------------------
    function BAR_GRAPH($type = '') {
      if($type) $this->type = $type;
    }

    function set_styles() {
      if($this->graphBGColor) $this->cssGRAPH .= 'background-color:' . $this->graphBGColor . ';';
      if($this->graphBorder) $this->cssGRAPH .= 'border:' . $this->graphBorder . ';';
      if($this->barBorder) $this->cssBAR .= 'border:' . $this->barBorder . ';';
      if($this->barBGColor) $this->cssBARBG .= 'background-color:' . $this->barBGColor . ';';
      if($this->labelColor) $this->cssLABEL .= 'color:' . $this->labelColor . ';';
      if($this->labelBGColor) $this->cssLABEL .= 'background-color:' . $this->labelBGColor . ';';
      if($this->labelBorder) $this->cssLABEL .= 'border:' . $this->labelBorder . ';';
      if($this->labelFont) $this->cssLABEL .= 'font-family:' . $this->labelFont . ';';
      if($this->labelSize) $this->cssLABEL .= 'font-size:' . $this->labelSize . 'px;';
      if($this->labelBGColor) $this->cssLABELBG .= 'background-color:' . $this->labelBGColor . ';';
      if($this->legendColor) $this->cssLEGEND .= 'color:' . $this->legendColor . ';';
      if($this->legendFont) $this->cssLEGEND .= 'font-family:' . $this->legendFont . ';';
      if($this->legendSize) $this->cssLEGEND .= 'font-size:' . $this->legendSize . 'px;';
      if($this->legendBGColor) $this->cssLEGENDBG .= 'background-color:' . $this->legendBGColor . ';';
      if($this->legendBorder) $this->cssLEGENDBG .= 'border:' . $this->legendBorder . ';';
      if($this->absValuesColor) $this->cssABSVALUES .= 'color:' . $this->absValuesColor . ';';
      if($this->absValuesBGColor) $this->cssABSVALUES .= 'background-color:' . $this->absValuesBGColor . ';';
      if($this->absValuesBorder) $this->cssABSVALUES .= 'border:' . $this->absValuesBorder . ';';
      if($this->absValuesFont) $this->cssABSVALUES .= 'font-family:' . $this->absValuesFont . ';';
      if($this->absValuesSize) $this->cssABSVALUES .= 'font-size:' . $this->absValuesSize . 'px;';
      if($this->percValuesColor) $this->cssPERCVALUES .= 'color:' . $this->percValuesColor . ';';
      if($this->percValuesFont) $this->cssPERCVALUES .= 'font-family:' . $this->percValuesFont . ';';
      if($this->percValuesSize) $this->cssPERCVALUES .= 'font-size:' . $this->percValuesSize . 'px;';
    }

    function level_color($value, $color) {
      if($this->barLevelColors) {
        for($i = 0; $i < count($this->barLevelColors); $i += 2) {
          if($i+1 < count($this->barLevelColors)) {
            if(($this->barLevelColors[$i] > 0 && $value >= $this->barLevelColors[$i]) ||
               ($this->barLevelColors[$i] < 0 && $value <= $this->barLevelColors[$i])) {
              $color = $this->barLevelColors[$i+1];
            }
          }
        }
      }
      return $color;
    }

    function build_bar($value, $width, $height, $color) {
      $title = $this->absValuesPrefix . $value . $this->absValuesSuffix;
      $bg = eregi('\.(jpg|jpeg|jpe|gif|png)$', $color) ? 'background' : 'bgcolor';
      $bar = '<table border=0 cellspacing=0 cellpadding=0><tr>';
      $bar .= '<td style="' . $this->cssBAR . '" ' . $bg . '="' . $color . '"';
      $bar .= ($value != '') ? ' title="' . $title . '">' : '>';
      $bar .= '<table border=0 cellspacing=0 cellpadding=0><tr>';
      $bar .= '<td width=' . $width . ' height=' . $height . '></td>';
      $bar .= '</tr></table>';
      $bar .= '</td></tr></table>';
      return $bar;
    }

    function build_fader($value, $width, $height, $x, $color) {
      $fader = '<table border=0 cellspacing=0 cellpadding=0><tr>';
      $x -= round($width / 2);
      if($x > 0) $fader .= '<td width=' . $x . '></td>';
      $fader .= '<td>' . $this->build_bar($value, $width, $height, $color) . '</td>';
      $fader .= '</tr></table>';
      return $fader;
    }

    function build_value($val, $max_dec, $sum = 0, $align = '') {
      $val = number_format($val, $max_dec);
      if($sum) $sum = number_format($sum, $max_dec);
      $value = '<td style="' . $this->cssABSVALUES . '"';
      if($align) $value .= ' align=' . $align;
      $value .= ' nowrap>';
      $value .= '&nbsp;' . $this->absValuesPrefix . $val . $this->absValuesSuffix;
      if($sum) $value .= ' / ' . $this->absValuesPrefix . $sum . $this->absValuesSuffix;
      $value .= '&nbsp;</td>';
      return $value;
    }

    function build_legend($barColors) {
      $legend = '<table border=0 cellspacing=0 cellpadding=0><tr>';
      $legend .= '<td style="' . $this->cssLEGENDBG . '">';
      $legend .= '<table border=0 cellspacing=4 cellpadding=0>';
      $l = (is_array($this->legend)) ? $this->legend : explode(',', $this->legend);

      for($i = 0; $i < count($barColors); $i++) {
        $legend .= '<tr>';
        $legend .= '<td>' . $this->build_bar('', $this->barWidth, $this->barWidth, $barColors[$i]) . '</td>';
        $legend .= '<td style="' . $this->cssLEGEND . '" nowrap>' . trim($l[$i]) . '</td>';
        $legend .= '</tr>';
      }
      $legend .= '</table></td></tr></table>';
      return $legend;
    }

    function create_hBar($value, $percent, $mPerc, $mPerc_neg, $max_neg, $mul, $valSpace, $bColor, $border, $spacer, $spacer_neg) {
      $bar = '<table border=0 cellspacing=0 cellpadding=0 height=100%><tr>';

      if($percent < 0) {
        $percent *= -1;
        $bar .= '<td style="' . $this->cssLABELBG . '" height=' . $this->barWidth . ' width=' . round(($mPerc_neg - $percent) * $mul + $valSpace) . ' align=right nowrap>';
        if($this->showValues < 2) $bar .= '<span style="' . $this->cssPERCVALUES . '">' . number_format($percent, $this->percValuesDecimals) . '%</span>';
        $bar .= '&nbsp;</td><td style="' . $this->cssLABELBG . '">';
        $bar .= $this->build_bar($value, round($percent * $mul), $this->barWidth, $bColor);
        $bar .= '</td><td width=' . $spacer . '></td>';
      }
      else {
        if($max_neg) {
          $bar .= '<td style="' . $this->cssLABELBG . '" width=' . $spacer_neg . '>';
          $bar .= '<table border=0 cellspacing=0 cellpadding=0><tr><td></td></tr></table></td>';
        }
        if($percent) {
          $bar .= '<td>';
          $bar .= $this->build_bar($value, round($percent * $mul), $this->barWidth, $bColor);
          $bar .= '</td>';
        }
        else $bar .= '<td><img width=1 height=' . ($this->barWidth + ($border * 2)) . '></td>';
        $bar .= '<td style="' . $this->cssPERCVALUES . '" width=' . round(($mPerc - $percent) * $mul + $valSpace) . ' align=left nowrap>';
        if($this->showValues < 2) $bar .= '&nbsp;' . number_format($percent, $this->percValuesDecimals) . '%';
        $bar .= '&nbsp;</td>';
      }
      $bar .= '</tr></table>';

      return $bar;
    }

    function create_vBar($value, $percent, $mPerc, $mPerc_neg, $max_neg, $mul, $valSpace, $bColor, $border, $spacer, $spacer_neg) {
      $bar = '<table border=0 cellspacing=0 cellpadding=0 width=100%><tr align=center>';

      if($percent < 0) {
        $percent *= -1;
        $bar .= '<td height=' . $spacer . '></td></tr><tr align=center valign=top><td style="' . $this->cssLABELBG . '">';
        $bar .= $this->build_bar($value, $this->barWidth, round($percent * $mul), $bColor);
        $bar .= '</td></tr><tr align=center valign=top>';
        $bar .= '<td style="' . $this->cssLABELBG . '" height=' . round(($mPerc_neg - $percent) * $mul + $valSpace) . ' nowrap>';
        $bar .= ($this->showValues < 2) ? '<span style="' . $this->cssPERCVALUES . '">' . number_format($percent, $this->percValuesDecimals) . '%</span>' : '&nbsp;';
        $bar .= '</td>';
      }
      else {
        $bar .= '<td style="' . $this->cssPERCVALUES . '" valign=bottom height=' . round(($mPerc - $percent) * $mul + $valSpace) . ' nowrap>';
        if($this->showValues < 2) $bar .= number_format($percent, $this->percValuesDecimals) . '%';
        $bar .= '</td>';
        if($percent) {
          $bar .= '</tr><tr align=center valign=bottom><td>';
          $bar .= $this->build_bar($value, $this->barWidth, round($percent * $mul), $bColor);
          $bar .= '</td>';
        }
        else $bar .= '</tr><tr><td><img width=' . ($this->barWidth + ($border * 2)) . ' height=1></td>';
        if($max_neg) {
          $bar .= '</tr><tr><td style="' . $this->cssLABELBG . '" height=' . $spacer_neg . '>';
          $bar .= '<table border=0 cellspacing=0 cellpadding=0><tr><td></td></tr></table></td>';
        }
      }
      $bar .= '</tr></table>';

      return $bar;
    }

    function create() {
      error_reporting(E_WARNING);

      $this->type = strtolower($this->type);
      $d = (is_array($this->values)) ? $this->values : explode(',', $this->values);
      if(is_array($this->labels)) $r = $this->labels;
      else $r = (strlen($this->labels) > 1) ? explode(',', $this->labels) : array();
      if($this->barColors) $drc = (is_array($this->barColors)) ? $this->barColors : explode(',', $this->barColors);
      else $drc = array();
      $val = $bc = array();
      if($this->barLength < 0.1) $this->barLength = 0.1;
      else if($this->barLength > 2.9) $this->barLength = 2.9;
      $bars = (count($d) > count($r)) ? count($d) : count($r);

      if($this->type == 'pbar' || $this->type == 'fader') {
        if(!$this->barBGColor) $this->barBGColor = $this->labelBGColor;
        if($this->labelBGColor == $this->barBGColor) {
          $this->labelBGColor = '';
          $this->labelBorder = '';
        }
      }

      $this->set_styles();

      $graph = '<table border=0 cellspacing=0 cellpadding=' . $this->graphPadding . '><tr>';
      $graph .= '<td' . ($this->cssGRAPH ? ' style="' . $this->cssGRAPH . '"' : '') . '>';

      if($this->legend && $this->type != 'pbar' && $this->type != 'fader')
        $graph .= '<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td>';

      if($this->charts > 1) {
        $divide = ceil($bars / $this->charts);
        $graph .= '<table border=0 cellspacing=0 cellpadding=6><tr valign=top><td>';
      }
      else $divide = 0;

      for($i = $sum = $max = $max_neg = $max_dec = $ccnt = $lcnt = $chart = 0; $i < $bars; $i++) {
        if($divide && $i && !($i % $divide)) {
          $lcnt = 0;
          $chart++;
        }
        $drv = explode(';', $d[$i]);

        for($j = $dec = 0; $j < count($drv); $j++) {
          $val[$chart][$lcnt][$j] = $v = trim(str_replace(',', '.', $drv[$j]));

          if($v > $max) $max = $v;
          else if($v < $max_neg) $max_neg = $v;

          if($v < 0) $v *= -1;
          $sum += $v;

          if(strstr($v, '.')) {
            $dec = strlen(substr($v, strrpos($v, '.') + 1));
            if($dec > $max_dec) $max_dec = $dec;
          }

          if(!$bc[$j]) {
            if($ccnt >= count($this->colors)) $ccnt = 0;
            $bc[$j] = (!$drc[$j] || strlen($drc[$j]) < 3) ? $this->colors[$ccnt++] : trim($drc[$j]);
          }
        }
        $lcnt++;
      }

      $border = (int) $this->barBorder;
      $mPerc = $sum ? round($max * 100 / $sum) : 0;
      if($this->type == 'pbar' || $this->type == 'fader') $mul = 2;
      else $mul = $mPerc ? 100 / $mPerc : 1;
      $mul *= $this->barLength;

      if($this->showValues < 2) {
        if($this->type == 'hbar')
          $valSpace = ($this->percValuesDecimals * ($this->percValuesSize / 1.6)) + ($this->percValuesSize * 3.2);
        else $valSpace = $this->percValuesSize * 1.2;
      }
      else $valSpace = $this->percValuesSize;
      $spacer = $maxSize = round($mPerc * $mul + $valSpace + $border * 2);

      if($max_neg) {
        $mPerc_neg = $sum ? round(-$max_neg * 100 / $sum) : 0;
        $spacer_neg = round($mPerc_neg * $mul + $valSpace + $border * 2);
        $maxSize += $spacer_neg;
      }

      for($chart = $lcnt = 0; $chart < count($val); $chart++) {
        $graph .= '<table border=0 cellspacing=2 cellpadding=0>';

        if($this->type == 'hbar') {
          for($i = 0; $i < count($val[$chart]); $i++, $lcnt++) {
            $label = ($lcnt < count($r)) ? trim($r[$lcnt]) : $lcnt+1;
            $rowspan = count($val[$chart][$i]);
            $graph .= '<tr><td style="' . $this->cssLABEL . '"' . (($rowspan > 1) ? ' rowspan=' . $rowspan : '') . ' align=center>';
            $graph .= '&nbsp;' . $label . '&nbsp;</td>';

            for($j = 0; $j < count($val[$chart][$i]); $j++) {
              $percent = $sum ? $val[$chart][$i][$j] * 100 / $sum : 0;
              $value = number_format($val[$chart][$i][$j], $max_dec);
              $bColor = $this->level_color($val[$chart][$i][$j], $bc[$j]);

              if($this->showValues == 1 || $this->showValues == 2)
                $graph .= $this->build_value($val[$chart][$i][$j], $max_dec, 0, 'right');

              $graph .= '<td' . ($this->cssBARBG ? ' style="' . $this->cssBARBG . '"' : '') . ' height=100% width=' . $maxSize . '>';
              $graph .= $this->create_hBar($value, $percent, $mPerc, $mPerc_neg, $max_neg, $mul, $valSpace, $bColor, $border, $spacer, $spacer_neg);
              $graph .= '</td></tr>';
              if($j < count($val[$chart][$i]) - 1) $graph .= '<tr>';
            }
            if($this->labelSpace && $i < count($val[$chart])-1) $graph .= '<tr><td colspan=3 height=' . $this->labelSpace . '></td></tr>';
          }
        }
        else if($this->type == 'vbar') {
          $graph .= '<tr align=center valign=bottom>';

          for($i = 0; $i < count($val[$chart]); $i++) {
            for($j = 0; $j < count($val[$chart][$i]); $j++) {
              $percent = $sum ? $val[$chart][$i][$j] * 100 / $sum : 0;
              $value = number_format($val[$chart][$i][$j], $max_dec);
              $bColor = $this->level_color($val[$chart][$i][$j], $bc[$j]);

              $graph .= '<td' . ($this->cssBARBG ? ' style="' . $this->cssBARBG . '"' : '') . '>';
              $graph .= $this->create_vBar($value, $percent, $mPerc, $mPerc_neg, $max_neg, $mul, $valSpace, $bColor, $border, $spacer, $spacer_neg);
              $graph .= '</td>';
            }
            if($this->labelSpace) $graph .= '<td width=' . $this->labelSpace . '></td>';
          }
          if($this->showValues == 1 || $this->showValues == 2) {
            $graph .= '</tr><tr align=center>';

            for($i = 0; $i < count($val[$chart]); $i++) {
              for($j = 0; $j < count($val[$chart][$i]); $j++) {
                $graph .= $this->build_value($val[$chart][$i][$j], $max_dec);
              }
              if($this->labelSpace) $graph .= '<td width=' . $this->labelSpace . '></td>';
            }
          }
          $graph .= '</tr><tr align=center>';

          for($i = 0; $i < count($val[$chart]); $i++, $lcnt++) {
            $label = ($lcnt < count($r)) ? trim($r[$lcnt]) : $lcnt+1;
            $colspan = count($val[$chart][$i]);
            $graph .= '<td style="' . $this->cssLABEL . '"' . (($colspan > 1) ? ' colspan=' . $colspan : '') . '>';
            $graph .= '&nbsp;' . $label . '&nbsp;</td>';
            if($this->labelSpace) $graph .= '<td width=' . $this->labelSpace . '></td>';
          }
          $graph .= '</tr>';
        }
        else if($this->type == 'pbar' || $this->type == 'fader') {
          for($i = 0; $i < count($val[$chart]); $i++, $lcnt++) {
            $label = ($lcnt < count($r)) ? trim($r[$lcnt]) : '';
            $graph .= '<tr>';

            if($label) {
              $graph .= '<td style="' . $this->cssLABEL . '" align=right>';
              $graph .= '&nbsp;' . $label . '&nbsp;</td>';
            }
            $sum = (float) $val[$chart][$i][1];
            $percent = $sum ? $val[$chart][$i][0] * 100 / $sum : 0;
            $value = number_format($val[$chart][$i][0], $max_dec);

            if($this->showValues == 1 || $this->showValues == 2)
              $graph .= $this->build_value($val[$chart][$i][0], $max_dec, $sum, 'right');

            $graph .= '<td' . ($this->cssBARBG ? ' style="' . $this->cssBARBG . '"' : '') . '>';

            $this->barColors = $drc[$i] ? trim($drc[$i]) : $this->colors[0];
            $bColor = $this->level_color($val[$chart][$i][0], $this->barColors);
            $graph .= '<table border=0 cellspacing=0 cellpadding=0><tr><td>';
            if($this->type == 'fader') $graph .= $this->build_fader($value, round($this->barWidth / 2), $this->barWidth, round($percent * $mul), $bColor);
            else $graph .= $this->build_bar($value, round($percent * $mul), $this->barWidth, $bColor);
            $graph .= '</td><td width=' . round((100 - $percent) * $mul) . '></td>';
            $graph .= '</tr></table></td>';
            if($this->showValues < 2) $graph .= '<td style="' . $this->cssPERCVALUES . '" nowrap>&nbsp;' . number_format($percent, $this->percValuesDecimals) . '%</td>';
            $graph .= '</tr>';
            if($this->labelSpace && $i < count($val[$chart])-1) $graph .= '<td colspan=3 height=' . $this->labelSpace . '></td>';
          }
        }
        else $graph .= '<tr><td>' . $this->err_type . '</td></tr>';

        $graph .= '</table>';

        if($chart < $this->charts - 1 && count($val[$chart+1])) {
          $graph .= '</td>';
          if($this->type == 'vbar') $graph .= '</tr><tr valign=top>';
          $graph .= '<td>';
        }
      }

      if($this->charts > 1) $graph .= '</td></tr></table>';

      if($this->legend && $this->type != 'pbar' && $this->type != 'fader') {
        $graph .= '</td><td width=10>&nbsp;</td><td>';
        $graph .= $this->build_legend($bc);
        $graph .= '</td></tr></table>';
      }

      if($this->debug) {
        $graph .= "<br>sum=$sum max=$max max_neg=$max_neg max_dec=$max_dec ";
        $graph .= "mPerc=$mPerc mPerc_neg=$mPerc_neg mul=$mul valSpace=$valSpace";
      }

      $graph .= '</td></tr></table>';

      return $graph;
    }
  }
?>
