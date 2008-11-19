<?php
/*********************************************************************
* Name: userbillinfo.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* provides user billing information input fields
*
*********************************************************************/

echo "

<fieldset>
	
	<h302> Bill Info </h302>
	<br/>

	<h301> Billing Information </h301>
	<br/>

	<label for='contactperson' class='form'>".$l['ContactInfo']['ContactPerson']."</label>
        <input value='"; if (isset($bi_contactperson)) echo $bi_contactperson; echo "' name='bi_contactperson' tabindex=400 />
	<br/>

	<label for='company' class='form'>".$l['ContactInfo']['Company']."</label>
	<input value='"; if (isset($bi_company)) echo $bi_company; echo "' name='bi_company' tabindex=401 />
        <br/>
	
	<label for='email' class='form'>".$l['ContactInfo']['Email']."</label>
        <input value='"; if (isset($bi_email)) echo $bi_email; echo "' name='bi_email' tabindex=402 />
        <br/>

	<label for='phone' class='form'>".$l['ContactInfo']['Phone']."</label>
	<input value='"; if (isset($bi_phone)) echo $bi_phone; echo "' name='bi_phone' tabindex=403 />
        <br/>

	<label for='address' class='form'>".$l['ContactInfo']['Address']."</label>
	<input value='"; if (isset($bi_address)) echo $bi_address; echo "' name='bi_address' tabindex=404 />
        <br/>

	<label for='city' class='form'>".$l['ContactInfo']['City']."</label>
	<input value='"; if (isset($bi_city)) echo $bi_city; echo "' name='bi_city' tabindex=405 />
        <br/>

	<label for='state' class='form'>".$l['ContactInfo']['State']."</label>
	<input value='"; if (isset($bi_state)) echo $bi_state; echo "' name='bi_state' tabindex=406 />
        <br/>

	<label for='zip' class='form'>".$l['ContactInfo']['Zip']."</label>
	<input value='"; if (isset($bi_zip)) echo $bi_zip; echo "' name='bi_zip' tabindex=407 />
        <br/>

	<br/>
	<h301> Payment Details </h301>
	<br/>

	<label for='PaymentMethod' class='form'>".$l['ContactInfo']['PaymentMethod']."</label>
	<input value='"; if (isset($bi_paymentmethod)) echo $bi_paymentmethod; echo "' name='bi_paymentmethod' tabindex=411 />
        <br/>

	<label for='Cash' class='form'>".$l['ContactInfo']['Cash']."</label>
	<input value='"; if (isset($bi_cash)) echo $bi_cash; echo "' name='bi_cash' tabindex=411 />
        <br/>

	<label for='CreditCardName' class='form'>".$l['ContactInfo']['CreditCardName']."</label>
	<input value='"; if (isset($bi_creditcardname)) echo $bi_creditcardname; echo "' name='bi_creditcardname' tabindex=411 />
        <br/>

	<label for='CreditCardNumber' class='form'>".$l['ContactInfo']['CreditCardNumber']."</label>
	<input value='"; if (isset($bi_creditcardnumber)) echo $bi_creditcardnumber; echo "' name='bi_creditcardnumber' tabindex=411 />
        <br/>

	<label for='CreditCardVerificationNumber' class='form'>".$l['ContactInfo']['CreditCardVerificationNumber']."</label>
	<br/>
	<input value='"; if (isset($bi_creditcardverification)) echo $bi_creditcardverification; echo "' name='bi_creditcardverification' tabindex=411 />
        <br/>

	<label for='CreditCardType' class='form'>".$l['ContactInfo']['CreditCardType']."</label>
	<input value='"; if (isset($bi_creditcardtype)) echo $bi_creditcardtype; echo "' name='bi_creditcardtype' tabindex=411 />
        <br/>

	<label for='CreditCardExpiration' class='form'>".$l['ContactInfo']['CreditCardExpiration']."</label>
	<input value='"; if (isset($bi_creditcardexp)) echo $bi_creditcardexp; echo "' name='bi_creditcardexp' tabindex=411 />
        <br/>

	<br/><br/>
	<h301> Other </h301>
	<br/>

	<label for='notes' class='form'>".$l['ContactInfo']['Notes']."</label>
	<textarea class='form' name='bi_notes' tabindex=412 >"; if (isset($bi_notes)) echo $bi_notes; echo "</textarea> 
        <br/>

"; // breaking echo

	if ($bi_changeuserbillinfo == 1) {
		$isChecked = "checked";
		$bi_changeuserbillinfo = 0;
	} else {
		$bi_changeuserbillinfo = 1;
		$isChecked = "";
	}

echo "

	<label for='userupdate' class='form'>".$l['ContactInfo']['EnableUserUpdate']."</label>
	<input type='checkbox' class='form' name='changeUserBillInfo' value='$bi_changeuserbillinfo' $isChecked tabindex=413 />
        <br/>

	<br/>
	<label for='creationdate' class='form'>".$l['all']['CreationDate']."</label>
	<input disabled value='"; if (isset($ui_creationdate)) echo $ui_creationdate; echo "' tabindex=414 />
        <br/>

	<label for='creationby' class='form'>".$l['all']['CreationBy']."</label>
	<input disabled value='"; if (isset($ui_creationby)) echo $ui_creationby; echo "' tabindex=415 />
        <br/>

	<label for='updatedate' class='form'>".$l['all']['UpdateDate']."</label>
	<input disabled value='"; if (isset($ui_updatedate)) echo $ui_updatedate; echo "' tabindex=416 />
        <br/>

	<label for='updateby' class='form'>".$l['all']['UpdateBy']."</label>
	<input disabled value='"; if (isset($ui_updateby)) echo $ui_updateby; echo "' tabindex=417 />
        <br/>

	<br/>
	<hr><br/>

	$customApplyButton

</fieldset>

";



?>
