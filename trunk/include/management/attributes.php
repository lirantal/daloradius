
<fieldset>
	<h302> <?php echo $l['title']['Attributes']; ?> </h302>
	<br/>

	<label for='vendor' class='form'>Vendor:</label>
	<select id='dictVendors0' onchange="getAttributesList(this,'dictAttributesDatabase')"
		style='width: 215px' class='form' >
		<option value=''>Select Vendor...</option>
		<?php
			include 'library/opendb.php';

			$sql = "SELECT distinct(Vendor) as Vendor FROM ".
				$configValues['CONFIG_DB_TBL_DALODICTIONARY']." WHERE Vendor>'' ORDER BY Vendor ASC";
			$res = $dbSocket->query($sql);

			while($row = $res->fetchRow()) {
				echo "<option value=$row[0]>$row[0]</option>";
			}

			include 'library/closedb.php';
		?>
		</select>
		<input type='button' name='reloadAttributes' value='Reload Vendors'
			onclick="javascript:getVendorsList('dictVendors0');" class='button'>
		<br/>

		<label for='attribute' class='form'>Attribute:</label>
		<select id='dictAttributesDatabase' style='width: 270px' class='form' >
		</select>
		<input type='button' name='addAttributes' value='Add Attribute'
			onclick="javascript:parseAttribute(1);" class='button'>
		<br/>

		<label for='attribute' class='form'>Custom Attribute:</label>
		<input type='text' id='dictAttributesCustom' style='width: 264px' />
		<br/>
		
		<?php

			include_once('library/config_read.php');

			if ( (isset($configValues['CONFIG_IFACE_AUTO_COMPLETE'])) &&
				(strtolower($configValues['CONFIG_IFACE_AUTO_COMPLETE']) == "yes") ) {

				include_once("include/management/autocomplete.php");

				echo "
					<script type=\"text/javascript\">
					autoComEdit.add('dictAttributesCustom','include/management/dynamicAutocomplete.php','_large','getAjaxAutocompleteAttributes');
					</script>
				";
			}

		?>

<br/>
<input type='submit' name='submit' value='<?php echo $l['buttons']['apply'] ?>' class='button' />

</fieldset>
<br/>

<input type="hidden" value="0" id="divCounter" />
<div id="divContainer"> </div> <br/>

