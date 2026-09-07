/***********************************************************************
 * changeInteger
 * this function implements a spinbox, it increments or decrement
 * the value in a text input (which needs to be an integer)
 *
 * dstObj        - the destination object text input
 * action        - increment or decrement
 ***********************************************************************/
function changeInteger(dstObj, action) {
    var dstElem = document.getElementById(dstObj);
    if (!dstElem) {
        return;
    }

    var val = parseInt(dstElem.value, 10);
    if (isNaN(val)) {
        val = 0;
    }

    if (action === 'increment') {
        dstElem.value = val + 1;
    } else {
        if (val <= 0) {
            dstElem.value = 0;
        } else {
            dstElem.value = val - 1;
        }
    }

    if (typeof Event === 'function') {
        dstElem.dispatchEvent(new Event('change', { bubbles: true }));
    }
}


function toggleAttributeCustom() {

    // disable the custom attributes
    var elem1 = document.getElementById('dictAttributesCustom');
    var elem2 = document.getElementById('addAttributesCustom');
    elem1.disabled = false;
    elem2.disabled = false;

    var elem3 = document.getElementById('dictVendors0');
    var elem4 = document.getElementById('reloadAttributes');
    var elem5 = document.getElementById('dictAttributesDatabase');
    var elem6 = document.getElementById('addAttributesVendor');
    elem3.disabled = true;
    elem4.disabled = true;
    elem5.disabled = true;
    elem6.disabled = true;

}


function toggleAttributeSelectbox() {

    // disable the custom attributes
    var elem1 = document.getElementById('dictAttributesCustom');
    var elem2 = document.getElementById('addAttributesCustom');
    elem1.disabled = true;
    elem2.disabled = true;

    var elem3 = document.getElementById('dictVendors0');
    var elem4 = document.getElementById('reloadAttributes');
    var elem5 = document.getElementById('dictAttributesDatabase');
    var elem6 = document.getElementById('addAttributesVendor');
    elem3.disabled = false;
    elem4.disabled = false;
    elem5.disabled = false;
    elem6.disabled = false;
}



function toggleRandomUsers() {
    var form = document.forms['batchuser'];
    if (form && form.elements['startingIndex']) {
        form.elements['startingIndex'].disabled = true;
    }
    if (form && form.elements['length_user']) {
        form.elements['length_user'].disabled = false;
    }
}


function toggleIncrementUsers() {
    var form = document.forms['batchuser'];
    if (form && form.elements['length_user']) {
        form.elements['length_user'].disabled = true;
    }
    if (form && form.elements['startingIndex']) {
        form.elements['startingIndex'].disabled = false;
    }
}


function toggleUserAuth() {
    var form = document.forms['newuser'];
    if (!form) {
        return;
    }

    var disableFields = ['macaddress', 'group_macaddress[]', 'pincode', 'group_pincode[]'];
    for (var i = 0; i < disableFields.length; i++) {
        if (form.elements[disableFields[i]]) {
            form.elements[disableFields[i]].disabled = true;
        }
    }

    var enableFields = ['username', 'password', 'passwordType', 'groups[]', 'usergroup'];
    for (var j = 0; j < enableFields.length; j++) {
        if (form.elements[enableFields[j]]) {
            form.elements[enableFields[j]].disabled = false;
        }
    }
}

function togglePinCode() {
    var form = document.forms['newuser'];
    if (!form) {
        return;
    }

    var enableFields = ['pincode', 'group_pincode[]'];
    for (var i = 0; i < enableFields.length; i++) {
        if (form.elements[enableFields[i]]) {
            form.elements[enableFields[i]].disabled = false;
        }
    }

    var disableFields = ['macaddress', 'group_macaddress[]', 'username', 'password', 'passwordType', 'groups[]', 'usergroup'];
    for (var j = 0; j < disableFields.length; j++) {
        if (form.elements[disableFields[j]]) {
            form.elements[disableFields[j]].disabled = true;
        }
    }
}

function toggleMacAuth(state) {
    var form = document.forms['newuser'];
    if (!form) {
        return;
    }

    var enableFields = ['macaddress', 'group_macaddress[]'];
    for (var i = 0; i < enableFields.length; i++) {
        if (form.elements[enableFields[i]]) {
            form.elements[enableFields[i]].disabled = false;
        }
    }

    var disableFields = ['username', 'password', 'passwordType', 'groups[]', 'usergroup', 'pincode', 'group_pincode[]'];
    for (var j = 0; j < disableFields.length; j++) {
        if (form.elements[disableFields[j]]) {
            form.elements[disableFields[j]].disabled = true;
        }
    }
}


/***********************************************************************
 * setText
 * srcObj    - an integer to be multiplied
 * dstObj    - the dstination object is multiplied by it's value
 *           and the value of the source object.
 ***********************************************************************/
function setText(srcObj,dstObj) {

    var srcElem = document.getElementById(srcObj);
    var elemVal = srcElem.options[srcElem.selectedIndex].value;

    var dstElem = document.getElementById(dstObj);
    var dstElemVal = dstElem.value;
    var res = (dstElemVal * elemVal);
    dstElem.value = res;

}


/***********************************************************************
 * setStringText
 * srcId    - the source object text
 * dstId    - the dstination object is set to the source object text
 ***********************************************************************/
function setStringText(srcId,dstId) {

    var srcElem = document.getElementById(srcId);

    if (srcElem.type == "text")
        var elemVal = srcElem.value;

    if (srcElem.type == "select-one")
        var elemVal = srcElem.options[srcElem.selectedIndex].value;

    var dstElem = document.getElementById(dstId);
    dstElem.value = elemVal;

}


/***********************************************************************
 * setStringTextMulti
 * srcId    - the source object text
 * dstId1    - the dstination object is set to the source object text
 * dstId2    -
 ***********************************************************************/
function setStringTextMulti(srcId,dstId1, dstId2) {

    var srcElem = document.getElementById(srcId);

    if (srcElem.type == "select-one")
        var elemVal = srcElem.options[srcElem.selectedIndex].value;

    var srcElemValArray = elemVal.split("||");

    var dstElem1 = document.getElementById(dstId1);
    dstElem1.value = srcElemValArray[0];

    var dstElem2 = document.getElementById(dstId2);
    dstElem2.value = srcElemValArray[1];

}



/***********************************************************************
 * toggleShowDiv
 * toggles a div on/off (visible/hidden)
 *
 * idName        - the id name of the target div to toggle on/off
 *               (visible/hidden)
 ***********************************************************************/
function toggleShowDiv(idName) {
    var divs = document.getElementsByTagName('div');
    for (var i = 0; i < divs.length; i++) {
        if (divs[i].id && divs[i].id.indexOf(idName) !== -1) {
            divs[i].style.display = (divs[i].style.display === "block") ? "none" : "block";
        }
    }
}

/***********************************************************************
 * small_window
 * opens up a small window with quick accounts information
 *
 * user        - the username
 * pass        - the password
 * time        - the credit time that is left for the user
 ***********************************************************************/
function small_window(user,pass,time) {

    var newWindow;
    var currentTime = new Date();
    var props = "scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=500,height=200";
    newWindow = window.open("about:blank","blank",props);

        newWindow.document.write("<html><body>");
    newWindow.document.write("Thank you. <br/>");
    newWindow.document.write("Your username is: ");
    newWindow.document.write(user);
    newWindow.document.write("<br/>");
    newWindow.document.write("Your password is: ");
    newWindow.document.write(pass);
    newWindow.document.write("<br/>");
    newWindow.document.write("Your timecredit is: ");
    newWindow.document.write(time);
    newWindow.document.write("<br/>");
    newWindow.document.write("<br/>");
    newWindow.document.write("Receipt produced on: ");
    newWindow.document.write(currentTime);
    newWindow.document.write("<br/>");
    newWindow.document.write("Enginx HotSpot System ");
    newWindow.document.write("<br/>");
        newWindow.document.write("</body></html>");
        newWindow.document.close();

}


/***********************************************************************
 * SetChecked
 * checks or unchecks checkboxes by name in a given form
 *
 * val         - true or false
 * chkName     - the checkbox input name
 * formname    - the form name
 ***********************************************************************/
function SetChecked(val,chkName,formname) {
    var dml = document.forms[formname];
    if (!dml) {
        return;
    }
    var len = dml.elements.length;
    for (var i = 0; i < len; i++) {
        if (dml.elements[i].name == chkName) {
            dml.elements[i].checked = val;
        }
    }
}


/***********************************************************************
 * removeCheckbox
 * submits a form with checkbox values to a remote page
 *
 * formName    - the form name
 * pageDst    - the page destination to be submitted
 *
 ***********************************************************************/
function removeCheckbox(formName,pageDst) {

        var count = 0;
        var form = document.getElementsByTagName('input');

        for (var i=0; i < form.length; ++i) {
                var e = form[i];
                if (e.type == 'checkbox' && e.checked)
                ++count;
        }


    // if no items were checked there's no reason to submit the form
    if (count == 0) {
        alert("No items selected");
        return;
    }


        if (confirm("You are about to remove " + count + " records from database\nDo you want to continue?"))  {
        document.forms[formName].action=pageDst;
        document.forms[formName].submit();
                return true;
        }

        return false;
}




/***********************************************************************
 * disableCheckbox
 * submits a form using ajax to disable a user
 *
 * formName    - the form name
 * pageDst    - the page destination to be submitted
 *
 ***********************************************************************/
function disableCheckbox(formName, pageDst) {
    return userActionSelection(formName, "userDisable", "disable");
}


/***********************************************************************
 * mailCheckbox
 * submits a form using ajax to send email to user/users
 *
 * formName    - the form name
 * pageDst    - the page destination to be submitted
 *
 ***********************************************************************/
function mailCheckbox(formName, pageDst) {
    return userActionSelection(formName, "userMail", "send email to");
}




/***********************************************************************
 * enableCheckbox
 * submits a form using ajax to enable a user
 *
 * formName    - the form name
 * pageDst    - the page destination to be submitted
 *
 ***********************************************************************/
function enableCheckbox(formName, pageDst) {
    return userActionSelection(formName, "userEnable", "enable");
}




/***********************************************************************
 * backupRollback
 * performs rollback
 *
 ***********************************************************************/
function backupRollback(file) {

        if (confirm("Performing a rollback will wipe out your current database tables completely and re-create it from the rollback backup file\nDo you want to continue?"))  {
        window.location.href='?file='+file+'&action=rollback';
                return true;
        }

        return false;
}




/***********************************************************************
 * genericCounter
 * a generic counter function to always return an incrementing integer
 * along with a string in the form of str=N
 *
 ***********************************************************************/
var gCounter = 0;
function genericCounter(str) {
    return str+"="+gCounter++;
}







/***********************************************************************
 * refillSessionTimeCheckbox
 * submits a form using ajax to refill a user session time
 *
 * formName    - the form name
 * pageDst    - the page destination to be submitted
 *
 ***********************************************************************/
function refillSessionTimeCheckbox(formName, pageDst) {
    return userActionSelection(formName, "refillSessionTime", "refill session time for");
}










/***********************************************************************
 * refillSessionTrafficCheckbox
 * submits a form using ajax to refill a user session traffic
 *
 * formName    - the form name
 * pageDst    - the page destination to be submitted
 *
 ***********************************************************************/
function refillSessionTrafficCheckbox(formName, pageDst) {
    return userActionSelection(formName, "refillSessionTraffic", "refill session traffic for");
}





/***********************************************************************
 * copyUserBillInfo
 * copies user contact info to billing contact info input fields
 *
 ***********************************************************************/
function copyUserBillInfo(obj) {

    if (obj.checked == true) {

        // set contact name
        document.getElementById('bi_contactperson').value = document.getElementById('firstname').value + " " +
        document.getElementById('lastname').value;
        document.getElementById('bi_email').value = document.getElementById('email').value;
        document.getElementById('bi_company').value = document.getElementById('company').value;
        document.getElementById('bi_phone').value = document.getElementById('workphone').value;
        document.getElementById('bi_address').value = document.getElementById('address').value;
        document.getElementById('bi_city').value = document.getElementById('city').value;
        document.getElementById('bi_state').value = document.getElementById('state').value;
        document.getElementById('bi_zip').value = document.getElementById('zip').value;

    } else {

        document.getElementById('bi_contactperson').value = "";
        document.getElementById('bi_email').value = "";
        document.getElementById('bi_company').value = "";
        document.getElementById('bi_phone').value = "";
        document.getElementById('bi_address').value = "";
        document.getElementById('bi_city').value = "";
        document.getElementById('bi_state').value = "";
        document.getElementById('bi_zip').value = "";

    }

}

function setupAccordion() {
    var acc = document.querySelectorAll(".accordion");

    for (var i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("accordion-active");

            var panel = this.nextElementSibling,
                display = panel.style.display === "block";

            panel.style.display = (display) ? "none" : "block";
        });
    }
}

// A single in-flight action per page prevents duplicate mutations, including
// activation while the initial disabled-state check is still running.
var userActionPending = false;

async function userAction(action, usernames, form = null) {
    const target = document.getElementById('returnMessages');
    if (!target || userActionPending) return false;
    const render = (message, level) => {
        target.replaceChildren();
        if (!message) return;
        const alert = document.createElement('div');
        alert.className = 'alert alert-' + level;
        alert.setAttribute('role', 'alert');
        alert.textContent = message;
        target.appendChild(alert);
    };
    if (!usernames.length) {
        render('No users selected.', 'danger');
        return false;
    }
    const readOnly = action === 'checkDisabled';
    const parameters = new URLSearchParams({ action });
    usernames.forEach(username => parameters.append('username[]', username));
    if (!readOnly) {
        const token = (form || document).querySelector('input[name="csrf_token"]');
        if (!token || !token.value) {
            render('Missing CSRF token. Reload the page before trying again.', 'danger');
            return false;
        }
        parameters.set('csrf_token', token.value);
    }
    userActionPending = true;
    // Include toolbar buttons outside the selection form; preserve disabled state.
    const buttons = [...document.querySelectorAll('[onclick]')].filter(button =>
        /(?:disableUser|enableUser|refillSession(?:Time|Traffic)(?:Checkbox)?|disableCheckbox|enableCheckbox|mailCheckbox)\(/.test(button.getAttribute('onclick'))
    );
    const states = buttons.map(button => [button, button.disabled]);
    states.forEach(([button]) => { button.disabled = true; });
    target.setAttribute('aria-busy', 'true');
    render('Processing...', 'info');
    const uncertain = 'The result could not be confirmed. Check the user and billing records before trying again; the action has not been retried.';
    try {
        const url = new URL('library/ajax/user_actions.php', document.baseURI);
        const options = {
            method: readOnly ? 'GET' : 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        };
        if (readOnly) url.search = parameters.toString();
        else options.body = parameters;
        // Deliberately no retry, timeout or cancellation of mutations.
        const response = await fetch(url, options);
        if (response.redirected || response.status === 401) {
            throw new Error('Your session has expired. Please sign in again.');
        }
        if (response.status === 403) {
            throw new Error('Permission denied or invalid CSRF token. Reload the page and check your permissions.');
        }
        if (!(response.headers.get('Content-Type') || '').toLowerCase().startsWith('application/json')) {
            throw new Error(uncertain);
        }
        const result = await response.json();
        if (!result || typeof result.success !== 'boolean' || typeof result.message !== 'string'
                || !['success', 'danger', 'warning', 'info'].includes(result.level)) {
            throw new Error(uncertain);
        }
        render(result.message, response.ok && result.success ? result.level : 'danger');
        return response.ok && result.success;
    } catch (error) {
        render(error.message === uncertain || /^(Your session|Permission denied)/.test(error.message)
            ? error.message : uncertain, 'danger');
        return false;
    } finally {
        userActionPending = false;
        states.forEach(([button, disabled]) => { button.disabled = disabled; });
        target.removeAttribute('aria-busy');
    }
}

function userActionSelection(formName, action, verb) {
    if (userActionPending) return false;
    const form = document.forms[formName];
    if (!form) return false;
    const usernames = [...form.querySelectorAll('input[type="checkbox"][name="username[]"]:checked')]
        .map(input => input.value);
    if (!usernames.length) {
        alert('No items selected');
        return false;
    }
    const billing = action.startsWith('refill') ? '\n\nSuch action will also bill the user!' : '';
    if (confirm(`You are about to ${verb} ${usernames.length} users\nDo you want to continue?${billing}`)) {
        userAction(action, usernames, form);
    }
    return false;
}
