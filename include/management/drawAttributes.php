<?php
/*********************************************************************
* Name: drawAttributes.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file's purpose in life is to serve the mng-new.php new user page
* and basically to draw all available attributes to the page
*
*********************************************************************/
?>


<h4> Session Attributes </h4>
                       <?php if (trim($maxallsession) == "") { echo "<font color='#FF0000'>";  }?>
                       <input type="checkbox" onclick="javascript:toggleShowDiv('attributesMaxAllSession')">
                       <b><?php echo $l[FormField][all][MaxAllSession] ?></b> <br/>
<div id="attributesMaxAllSession" style="display:none;visibility:visible" >
                                 <input value="<?php echo $maxallsession ?>" id="maxallsession" name="maxallsession">
<select onChange="javascript:setText(this.id,'maxallsession')" id="option1">
<option value="86400">1day(s)</option>
<option value="259200">3day(s)</option>
<option value="604800">1week(s)</option>
</select>
                 <br/><br/>
                 </font>
</div>
