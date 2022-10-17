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

                <label for='firstname' class='form'>Operator Firstname</label>
                <input name='firstname' type='text' id='firstname' 
			value='".$operator_firstname."' >
                <br/>

                <label for='lastname' class='form'>Operator Lastname</label>
                <input name='lastname' type='text' id='lastname' 
			value='".$operator_lastname."' >
                <br/>

                <label for='title' class='form'>Operator Title</label>
                <input name='title' type='text' id='title'
			value='".$operator_title."' >
                <br/>

                <label for='department' class='form'>Operator Department</label>
                <input name='department' type='text' id='department'
			value='".$operator_department."' >
                <br/>

                <label for='company' class='form'>Operator Company</label>
                <input name='company' type='text' id='company'
			value='".$operator_company."' >
                <br/>

                <label for='phone1' class='form'>Operator Phone1</label>
                <input name='phone1' type='text' id='phone1'
			value='".$operator_phone1."' >
                <br/>

                <label for='phone2' class='form'>Operator Phone2</label>
                <input name='phone2' type='text' id='phone2'
			value='".$operator_phone2."' >
                <br/>

                <label for='email1' class='form'>Operator Email1</label>
                <input name='email1' type='text' id='email1'
			value='".$operator_email1."' >
                <br/>

                <label for='email2' class='form'>Operator Email2</label>
                <input name='email2' type='text' id='email2'
			value='".$operator_email2."' >
                <br/>

                <label for='messenger1' class='form'>Operator Messenger1</label>
                <input name='messenger1' type='text' id='messenger1'
			value='".$operator_messenger1."' >
                <br/>

                <label for='messenger2' class='form'>Operator Messenger2</label>
                <input name='messenger2' type='text' id='messenger2'
			value='".$operator_messenger2."' >
                <br/>

                <label for='notes' class='form'>Operator Notes</label>
	        <textarea class='form' name='notes' id='notes'>".$operator_notes."</textarea>
                <br/>

		<br/>
                <label for='operator_lastlogin' class='form'>Operator Last Login</label>
                <input disabled type='text' value='"; if (isset($operator_lastlogin)) 
			echo $operator_lastlogin; echo "' />
	        <br/>
	        <label for='creationdate' class='form'>".t('all','CreationDate')."</label>
	        <input disabled type='text' value='"; if (isset($operator_creationdate)) 
			echo $operator_creationdate; echo "' />
	        <br/>
	        <label for='creationby' class='form'>".t('all','CreationBy')."</label>
	        <input disabled type='text' value='"; if (isset($operator_creationby)) 
			echo $operator_creationby; echo "' />
	        <br/>
                <label for='updatedate' class='form'>".t('all','UpdateDate')."</label>
                <input disabled type='text' value='"; if (isset($operator_updatedate))
                        echo $operator_updatedate; echo "' />
                <br/>
                <label for='updateby' class='form'>".t('all','UpdateBy')."</label>
                <input disabled type='text' value='"; if (isset($operator_updateby))
                        echo $operator_updateby; echo "' />
                <br/>

                <br/><br/>
                <hr><br/>

                <input type='submit' name='submit' value='".t('buttons','apply')."' class='button' />

        </fieldset>

";



?>
