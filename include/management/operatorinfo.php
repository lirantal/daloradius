<?php
/*********************************************************************
* Name: operatorinfo.php
* Author: Liran tal <liran.tal@gmail.com>
* 
* This file extends the operators config pages and adds a section for
* operator contact information.
*
*********************************************************************/

(!isset($operator_firstname)) ? $operator_firstname = "" : NULL;
(!isset($operator_lastname)) ? $operator_lastname = "" : NULL;
(!isset($operator_title)) ? $operator_title = "" : NULL;
(!isset($operator_department)) ? $operator_department = "" : NULL;
(!isset($operator_company)) ? $operator_company = "" : NULL;
(!isset($operator_phone1)) ? $operator_phone1 = "" : NULL;
(!isset($operator_phone2)) ? $operator_phone2 = "" : NULL;
(!isset($operator_email1)) ? $operator_email1 = "" : NULL;
(!isset($operator_email2)) ? $operator_email2 = "" : NULL;
(!isset($operator_messenger1)) ? $operator_messenger1 = "" : NULL;
(!isset($operator_messenger2)) ? $operator_messenger2 = "" : NULL;
(!isset($operator_notes)) ? $operator_notes = "" : NULL;

echo "

        <fieldset>

                <h302>Operator Details</h302>
                <br/>

                <label for='operator_firstname' class='form'>Operator Firstname</label>
                <input name='operator_firstname' type='text' id='operator_firstname' 
			value='".$operator_firstname."' >
                <br/>

                <label for='operator_lastname' class='form'>Operator Lastname</label>
                <input name='operator_lastname' type='text' id='operator_lastname' 
			value='".$operator_lastname."' >
                <br/>

                <label for='operator_title' class='form'>Operator Title</label>
                <input name='operator_title' type='text' id='operator_title'
			value='".$operator_title."' >
                <br/>

                <label for='operator_department' class='form'>Operator Department</label>
                <input name='operator_department' type='text' id='operator_department'
			value='".$operator_department."' >
                <br/>

                <label for='operator_company' class='form'>Operator company</label>
                <input name='operator_company' type='text' id='operator_company'
			value='".$operator_company."' >
                <br/>

                <label for='operator_phone1' class='form'>Operator Phone1</label>
                <input name='operator_phone1' type='text' id='operator_phone1'
			value='".$operator_phone1."' >
                <br/>

                <label for='operator_phone2' class='form'>Operator Phone2</label>
                <input name='operator_phone2' type='text' id='operator_phone2'
			value='".$operator_phone2."' >
                <br/>

                <label for='operator_email1' class='form'>Operator Email1</label>
                <input name='operator_email1' type='text' id='operator_email1'
			value='".$operator_email1."' >
                <br/>

                <label for='operator_email2' class='form'>Operator Email2</label>
                <input name='operator_email2' type='text' id='operator_email2'
			value='".$operator_email2."' >
                <br/>

                <label for='operator_messenger1' class='form'>Operator Messenger1</label>
                <input name='operator_messenger1' type='text' id='operator_messenger1'
			value='".$operator_messenger1."' >
                <br/>

                <label for='operator_messenger2' class='form'>Operator Messenger2</label>
                <input name='operator_messenger2' type='text' id='operator_messenger2'
			value='".$operator_messenger2."' >
                <br/>

                <label for='operator_notes' class='form'>Operator Notes</label>
                <input name='operator_notes' type='text' id='operator_notes'
			value='".$operator_notes."' >
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='".$l['buttons']['apply']."' class='button' />

        </fieldset>

";



?>
