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

	<label for='planname' class='form'>".t('ContactInfo','PlanName')."</label>
	<input value='"; if (isset($bi_planname)) echo $bi_planname; echo "' name='bi_planname' disabled tabindex=401 />
        <br/>

	<label for='contactperson' class='form'>".t('ContactInfo','ContactPerson')."</label>
        <input value='"; if (isset($bi_contactperson)) echo $bi_contactperson; echo "' name='bi_contactperson' id='bi_contactperson' tabindex=400 />
	<br/>

	<label for='company' class='form'>".t('ContactInfo','Company')."</label>
	<input value='"; if (isset($bi_company)) echo $bi_company; echo "' name='bi_company' id='bi_company' tabindex=401 />
        <br/>
	
	<label for='email' class='form'>".t('ContactInfo','Email')."</label>
        <input value='"; if (isset($bi_email)) echo $bi_email; echo "' name='bi_email' id='bi_email' tabindex=402 />
        <br/>

	<label for='phone' class='form'>".t('ContactInfo','Phone')."</label>
	<input value='"; if (isset($bi_phone)) echo $bi_phone; echo "' name='bi_phone' id='bi_phone' tabindex=403 />
        <br/>

	<label for='address' class='form'>".t('ContactInfo','Address')."</label>
	<input value='"; if (isset($bi_address)) echo $bi_address; echo "' name='bi_address' id='bi_address' tabindex=404 />
        <br/>

	<label for='city' class='form'>".t('ContactInfo','City')."</label>
	<input value='"; if (isset($bi_city)) echo $bi_city; echo "' name='bi_city' id='bi_city' tabindex=405 />
        <br/>

	<label for='state' class='form'>".t('ContactInfo','State')."</label>
	<input value='"; if (isset($bi_state)) echo $bi_state; echo "' name='bi_state' id='bi_state' tabindex=406 />
        <br/>
        
	<label for='country' class='form'>".t('ContactInfo','Country')."</label>
	<input value='"; if (isset($bi_country)) echo $bi_country; echo "' name='bi_country' id='bi_country' tabindex=406 />
        <br/>

	<label for='zip' class='form'>".t('ContactInfo','Zip')."</label>
	<input value='"; if (isset($bi_zip)) echo $bi_zip; echo "' name='bi_zip' id='bi_zip' tabindex=407 />
        <br/>

	<label for='PostalInvoice' class='form'>".t('all','PostalInvoice')."</label>
	<input value='"; if (isset($bi_postalinvoice)) echo $bi_postalinvoice; echo "' name='bi_postalinvoice' tabindex=411 />
        <br/>

	<label for='FaxInvoice' class='form'>".t('all','FaxInvoice')."</label>
	<input value='"; if (isset($bi_faxinvoice)) echo $bi_faxinvoice; echo "' name='bi_faxinvoice' tabindex=411 />
        <br/>

	<label for='EmailInvoice' class='form'>".t('all','EmailInvoice')."</label>
	<input value='"; if (isset($bi_emailinvoice)) echo $bi_emailinvoice; echo "' name='bi_emailinvoice' tabindex=411 />
        <br/>


	<br/>
	<h301> Payment Details </h301>
	<br/>

	<label for='PaymentMethod' class='form'>".t('ContactInfo','PaymentMethod')."</label>
	<input value='"; if (isset($bi_paymentmethod)) echo $bi_paymentmethod; echo "' name='bi_paymentmethod' tabindex=411 />
        <br/>

	<label for='Cash' class='form'>".t('ContactInfo','Cash')."</label>
	<input value='"; if (isset($bi_cash)) echo $bi_cash; echo "' name='bi_cash' tabindex=411 />
        <br/>

	<label for='CreditCardName' class='form'>".t('ContactInfo','CreditCardName')."</label>
	<input value='"; if (isset($bi_creditcardname)) echo $bi_creditcardname; echo "' name='bi_creditcardname' tabindex=411 />
        <br/>

	<label for='CreditCardNumber' class='form'>".t('ContactInfo','CreditCardNumber')."</label>
	<input value='"; if (isset($bi_creditcardnumber)) echo $bi_creditcardnumber; echo "' name='bi_creditcardnumber' tabindex=411 />
        <br/>

	<label for='CreditCardVerificationNumber' class='form'>".t('ContactInfo','CreditCardVerificationNumber')."</label>
	<br/>
	<input value='"; if (isset($bi_creditcardverification)) echo $bi_creditcardverification; echo "' name='bi_creditcardverification' tabindex=411 />
        <br/>

	<label for='CreditCardType' class='form'>".t('ContactInfo','CreditCardType')."</label>
	<select class='form' name='bi_creditcardtype'>
		<option value='"; if (isset($bi_creditcardtype)) echo $bi_creditcardtype; echo "'>";if (isset($bi_creditcardtype)) echo $bi_creditcardtype; echo "</option>
		<option value=''></option>
		<option value='VISA'>VISA</option>
		<option value='MasterCard'>MasterCard</option>
		<option value='Diners'>Diners</option>
	</select>
        <br/>

	<label for='CreditCardExpiration' class='form'>".t('ContactInfo','CreditCardExpiration')."</label>
	<input value='"; if (isset($bi_creditcardexp)) echo $bi_creditcardexp; echo "' name='bi_creditcardexp' tabindex=411 />
        <br/>



	<br/>
	<h301> Promotion Details </h301>
	<br/>

	<label for='Lead' class='form'>".t('all','Lead')."</label>
	<select class='form' name='bi_lead'>
		<option value='"; if (isset($bi_lead)) echo $bi_lead; echo "'>";if (isset($bi_lead)) echo $bi_lead; echo "</option>
		<option value=''></option>
		<option value='Internet'>Internet</option>
		<option value='Friend Referral'>Friend Referral</option>
		<option value='News Medium'>News Medium</option>
		<option value='Advertisment'>Advertisment</option>
	</select>
        <br/>

	<label for='Coupon' class='form'>".t('all','Coupon')."</label>
	<input value='"; if (isset($bi_coupon)) echo $bi_coupon; echo "' name='bi_coupon' tabindex=411 />
        <br/>

	<label for='OrderTaker' class='form'>".t('all','OrderTaker')."</label>
	<input value='"; if (isset($bi_ordertaker)) echo $bi_ordertaker; echo "' name='bi_ordertaker' tabindex=411 />
        <br/>

	<br/><br/>
	<h301> Other </h301>
	<br/>

	<label for='notes' class='form'>".t('ContactInfo','Notes')."</label>
	<textarea class='form' name='bi_notes' tabindex=412 >"; if (isset($bi_notes)) echo $bi_notes; echo "</textarea> 
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

	<label for='userupdate' class='form'>".t('ContactInfo','EnableUserUpdate')."</label>
	<input type='checkbox' class='form' name='changeUserBillInfo' value='$bi_changeuserbillinfo' $isBIChecked tabindex=413 />
        <br/>
	<br/>

	<label for='BillStatus' class='form'>".t('all','BillStatus')."</label>
	<input value='"; if (isset($bi_billstatus)) echo $bi_billstatus; echo "' name='bi_billstatus' disabled tabindex=411 />
        <br/>

	<label for='LastBill' class='form'>".t('all','LastBill')."</label>
	<input value='"; if (isset($bi_lastbill)) echo $bi_lastbill; echo "' name='bi_lastbill' disabled tabindex=411 />
        <br/>

	<label for='NextBill' class='form'>".t('all','NextBill')."</label>
	<input value='"; if (isset($bi_nextbill)) echo $bi_nextbill; echo "' name='bi_nextbill' disabled tabindex=411 />
        <br/>

	<label for='BillDue' class='form'>".t('all','BillDue')."</label>
	<input value='"; if (isset($bi_billdue)) echo $bi_billdue; echo "' name='bi_billdue' tabindex=411 />
        <br/>
        
	<label for='NextInvoiceDue' class='form'>".t('all','NextInvoiceDue')."</label>
	<input value='"; if (isset($bi_nextinvoicedue)) echo $bi_nextinvoicedue; echo "' name='bi_nextinvoicedue' tabindex=411 />
        <br/>
        
	<br/>
	<label for='creationdate' class='form'>".t('all','CreationDate')."</label>
	<input disabled value='"; if (isset($bi_creationdate)) echo $bi_creationdate; echo "' tabindex=414 />
        <br/>

	<label for='creationby' class='form'>".t('all','CreationBy')."</label>
	<input disabled value='"; if (isset($bi_creationby)) echo $bi_creationby; echo "' tabindex=415 />
        <br/>

	<label for='updatedate' class='form'>".t('all','UpdateDate')."</label>
	<input disabled value='"; if (isset($bi_updatedate)) echo $bi_updatedate; echo "' tabindex=416 />
        <br/>

	<label for='updateby' class='form'>".t('all','UpdateBy')."</label>
	<input disabled value='"; if (isset($bi_updateby)) echo $bi_updateby; echo "' tabindex=417 />
        <br/>

	<br/>
	<hr><br/>

	$customApplyButton

</fieldset>

";



?>
