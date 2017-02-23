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

	<label for='planname' class='form'>".$l['ContactInfo']['PlanName']."</label>
	<input value='"; if (isset($bi_planname)) echo htmlspecialchars($bi_planname, ENT_QUOTES); echo "' name='bi_planname' disabled tabindex=401 />
        <br/>

	<label for='contactperson' class='form'>".$l['ContactInfo']['ContactPerson']."</label>
        <input value='"; if (isset($bi_contactperson)) echo htmlspecialchars($bi_contactperson, ENT_QUOTES); echo "' name='bi_contactperson' id='bi_contactperson' tabindex=400 />
	<br/>

	<label for='company' class='form'>".$l['ContactInfo']['Company']."</label>
	<input value='"; if (isset($bi_company)) echo htmlspecialchars($bi_company, ENT_QUOTES); echo "' name='bi_company' id='bi_company' tabindex=401 />
        <br/>
	
	<label for='email' class='form'>".$l['ContactInfo']['Email']."</label>
        <input value='"; if (isset($bi_email)) echo htmlspecialchars($bi_email, ENT_QUOTES); echo "' name='bi_email' id='bi_email' tabindex=402 />
        <br/>

	<label for='phone' class='form'>".$l['ContactInfo']['Phone']."</label>
	<input value='"; if (isset($bi_phone)) echo htmlspecialchars($bi_phone, ENT_QUOTES); echo "' name='bi_phone' id='bi_phone' tabindex=403 />
        <br/>

	<label for='address' class='form'>".$l['ContactInfo']['Address']."</label>
	<input value='"; if (isset($bi_address)) echo htmlspecialchars($bi_address, ENT_QUOTES); echo "' name='bi_address' id='bi_address' tabindex=404 />
        <br/>

	<label for='city' class='form'>".$l['ContactInfo']['City']."</label>
	<input value='"; if (isset($bi_city)) echo htmlspecialchars($bi_city, ENT_QUOTES); echo "' name='bi_city' id='bi_city' tabindex=405 />
        <br/>

	<label for='state' class='form'>".$l['ContactInfo']['State']."</label>
	<input value='"; if (isset($bi_state)) echo htmlspecialchars($bi_state, ENT_QUOTES); echo "' name='bi_state' id='bi_state' tabindex=406 />
        <br/>
        
	<label for='country' class='form'>".$l['ContactInfo']['Country']."</label>
	<input value='"; if (isset($bi_country)) echo htmlspecialchars($bi_country, ENT_QUOTES); echo "' name='bi_country' id='bi_country' tabindex=406 />
        <br/>

	<label for='zip' class='form'>".$l['ContactInfo']['Zip']."</label>
	<input value='"; if (isset($bi_zip)) echo htmlspecialchars($bi_zip, ENT_QUOTES); echo "' name='bi_zip' id='bi_zip' tabindex=407 />
        <br/>

	<label for='PostalInvoice' class='form'>".$l['all']['PostalInvoice']."</label>
	<input value='"; if (isset($bi_postalinvoice)) echo htmlspecialchars($bi_postalinvoice, ENT_QUOTES); echo "' name='bi_postalinvoice' tabindex=411 />
        <br/>

	<label for='FaxInvoice' class='form'>".$l['all']['FaxInvoice']."</label>
	<input value='"; if (isset($bi_faxinvoice)) echo htmlspecialchars($bi_faxinvoice, ENT_QUOTES); echo "' name='bi_faxinvoice' tabindex=411 />
        <br/>

	<label for='EmailInvoice' class='form'>".$l['all']['EmailInvoice']."</label>
	<input value='"; if (isset($bi_emailinvoice)) echo htmlspecialchars($bi_emailinvoice, ENT_QUOTES); echo "' name='bi_emailinvoice' tabindex=411 />
        <br/>


	<br/>
	<h301> Payment Details </h301>
	<br/>

	<label for='PaymentMethod' class='form'>".$l['ContactInfo']['PaymentMethod']."</label>
	<input value='"; if (isset($bi_paymentmethod)) echo htmlspecialchars($bi_paymentmethod, ENT_QUOTES); echo "' name='bi_paymentmethod' tabindex=411 />
        <br/>

	<label for='Cash' class='form'>".$l['ContactInfo']['Cash']."</label>
	<input value='"; if (isset($bi_cash)) echo htmlspecialchars($bi_cash, ENT_QUOTES); echo "' name='bi_cash' tabindex=411 />
        <br/>

	<label for='CreditCardName' class='form'>".$l['ContactInfo']['CreditCardName']."</label>
	<input value='"; if (isset($bi_creditcardname)) echo htmlspecialchars($bi_creditcardname, ENT_QUOTES); echo "' name='bi_creditcardname' tabindex=411 />
        <br/>

	<label for='CreditCardNumber' class='form'>".$l['ContactInfo']['CreditCardNumber']."</label>
	<input value='"; if (isset($bi_creditcardnumber)) echo htmlspecialchars($bi_creditcardnumber, ENT_QUOTES); echo "' name='bi_creditcardnumber' tabindex=411 />
        <br/>

	<label for='CreditCardVerificationNumber' class='form'>".$l['ContactInfo']['CreditCardVerificationNumber']."</label>
	<br/>
	<input value='"; if (isset($bi_creditcardverification)) echo htmlspecialchars($bi_creditcardverification, ENT_QUOTES); echo "' name='bi_creditcardverification' tabindex=411 />
        <br/>

	<label for='CreditCardType' class='form'>".$l['ContactInfo']['CreditCardType']."</label>
	<select class='form' name='bi_creditcardtype'>
		<option value='"; if (isset($bi_creditcardtype)) echo htmlspecialchars($bi_creditcardtype, ENT_QUOTES); echo "'>";if (isset($bi_creditcardtype)) echo htmlspecialchars($bi_creditcardtype, ENT_QUOTES); echo "</option>
		<option value=''></option>
		<option value='VISA'>VISA</option>
		<option value='MasterCard'>MasterCard</option>
		<option value='Diners'>Diners</option>
	</select>
        <br/>

	<label for='CreditCardExpiration' class='form'>".$l['ContactInfo']['CreditCardExpiration']."</label>
	<input value='"; if (isset($bi_creditcardexp)) echo htmlspecialchars($bi_creditcardexp, ENT_QUOTES); echo "' name='bi_creditcardexp' tabindex=411 />
        <br/>



	<br/>
	<h301> Promotion Details </h301>
	<br/>

	<label for='Lead' class='form'>".$l['all']['Lead']."</label>
	<select class='form' name='bi_lead'>
		<option value='"; if (isset($bi_lead)) echo htmlspecialchars($bi_lead, ENT_QUOTES); echo "'>";if (isset($bi_lead)) echo htmlspecialchars($bi_lead, ENT_QUOTES); echo "</option>
		<option value=''></option>
		<option value='Internet'>Internet</option>
		<option value='Friend Referral'>Friend Referral</option>
		<option value='News Medium'>News Medium</option>
		<option value='Advertisment'>Advertisment</option>
	</select>
        <br/>

	<label for='Coupon' class='form'>".$l['all']['Coupon']."</label>
	<input value='"; if (isset($bi_coupon)) echo htmlspecialchars($bi_coupon, ENT_QUOTES); echo "' name='bi_coupon' tabindex=411 />
        <br/>

	<label for='OrderTaker' class='form'>".$l['all']['OrderTaker']."</label>
	<input value='"; if (isset($bi_ordertaker)) echo htmlspecialchars($bi_ordertaker, ENT_QUOTES); echo "' name='bi_ordertaker' tabindex=411 />
        <br/>

	<br/><br/>
	<h301> Other </h301>
	<br/>

	<label for='notes' class='form'>".$l['ContactInfo']['Notes']."</label>
	<textarea class='form' name='bi_notes' tabindex=412 >"; if (isset($bi_notes)) echo htmlspecialchars($bi_notes, ENT_QUOTES); echo "</textarea> 
        <br/>

"; // breaking echo

	if ($bi_changeuserbillinfo == 1) {
		$isBIChecked = "checked='yes'";
		$bi_changeuserbillinfo = 1;
	} else {
		$bi_changeuserbillinfo = 1;
		$isBIChecked = "";
	}

echo "

	<label for='userupdate' class='form'>".$l['ContactInfo']['EnableUserUpdate']."</label>
	<input type='checkbox' class='form' name='changeUserBillInfo' value='" . htmlspecialchars($bi_changeuserbillinfo, ENT_QUOTES) . "' " . htmlspecialchars($isBIChecked, ENT_QUOTES) . " tabindex=413 />
        <br/>
	<br/>

	<label for='BillStatus' class='form'>".$l['all']['BillStatus']."</label>
	<input value='"; if (isset($bi_billstatus)) echo htmlspecialchars($bi_billstatus, ENT_QUOTES); echo "' name='bi_billstatus' disabled tabindex=411 />
        <br/>

	<label for='LastBill' class='form'>".$l['all']['LastBill']."</label>
	<input value='"; if (isset($bi_lastbill)) echo htmlspecialchars($bi_lastbill, ENT_QUOTES); echo "' name='bi_lastbill' disabled tabindex=411 />
        <br/>

	<label for='NextBill' class='form'>".$l['all']['NextBill']."</label>
	<input value='"; if (isset($bi_nextbill)) echo htmlspecialchars($bi_nextbill, ENT_QUOTES); echo "' name='bi_nextbill' disabled tabindex=411 />
        <br/>

	<label for='BillDue' class='form'>".$l['all']['BillDue']."</label>
	<input value='"; if (isset($bi_billdue)) echo htmlspecialchars($bi_billdue, ENT_QUOTES); echo "' name='bi_billdue' tabindex=411 />
        <br/>
        
	<label for='NextInvoiceDue' class='form'>".$l['all']['NextInvoiceDue']."</label>
	<input value='"; if (isset($bi_nextinvoicedue)) echo htmlspecialchars($bi_nextinvoicedue, ENT_QUOTES); echo "' name='bi_nextinvoicedue' tabindex=411 />
        <br/>
        
	<br/>
	<label for='creationdate' class='form'>".$l['all']['CreationDate']."</label>
	<input disabled value='"; if (isset($ui_creationdate)) echo htmlspecialchars($ui_creationdate, ENT_QUOTES); echo "' tabindex=414 />
        <br/>

	<label for='creationby' class='form'>".$l['all']['CreationBy']."</label>
	<input disabled value='"; if (isset($ui_creationby)) echo htmlspecialchars($ui_creationby, ENT_QUOTES); echo "' tabindex=415 />
        <br/>

	<label for='updatedate' class='form'>".$l['all']['UpdateDate']."</label>
	<input disabled value='"; if (isset($ui_updatedate)) echo htmlspecialchars($ui_updatedate, ENT_QUOTES); echo "' tabindex=416 />
        <br/>

	<label for='updateby' class='form'>".$l['all']['UpdateBy']."</label>
	<input disabled value='"; if (isset($ui_updateby)) echo htmlspecialchars($ui_updateby, ENT_QUOTES); echo "' tabindex=417 />
        <br/>

	<br/>
	<hr><br/>

	$customApplyButton

</fieldset>

";



?>
