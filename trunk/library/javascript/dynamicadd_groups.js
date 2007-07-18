function addStuff() {

        var oTable = document.createElement('table');
		oTable.border = '2';
		oTable.className = 'table1';
        var oTb = document.createElement('tbody');
        var oTr1 = document.createElement('tr');
        var oTr2 = document.createElement('tr');
        var oTr3 = document.createElement('tr');
        var oTr4 = document.createElement('tr');
        var oTdTxt1 = document.createElement('td');
        var oTdInp1 = document.createElement('td');
        var oTdTxt2 = document.createElement('td');
        var oTdInp2 = document.createElement('td');
        var oTdTxt3 = document.createElement('td');
        var oTdInp3 = document.createElement('td');
        var oTdTxt4 = document.createElement('td');
        var oTdInp4 = document.createElement('td');

	var oSeperator = document.createElement('br');

	var oParagraph = document.createElement('p');
	var oParagraphTxt = document.createTextNode('New Group Details');
	oParagraph.appendChild(oParagraphTxt);

        var inpGroup = document.createElement('input');
	        inpGroup.type = 'text';
	        inpGroup.name = 'groupnameExtra[]';

        var inpAttrib = document.createElement('input');
	        inpAttrib.type = 'text';
	        inpAttrib.name = 'attributeExtra[]';

	var selOperator = document.createElement('select');
	        selOperator.name = 'opExtra[]';

	var selOperatorOpt1 = document.createElement('option');
		selOperatorOpt1.text = '==';
		selOperatorOpt1.value = '==';
	var selOperatorOpt2 = document.createElement('option');
		selOperatorOpt2.text = ':=';
		selOperatorOpt2.value = ':=';
	selOperator.appendChild(selOperatorOpt1);
	selOperator.appendChild(selOperatorOpt2);

        var inpValue = document.createElement('input');
                inpValue.type = 'text';
                inpValue.name = 'valueExtra[]';

	textNode1 = document.createTextNode("Groupname");
	textNode2 = document.createTextNode("Attribute");
	textNode3 = document.createTextNode("Operator");
	textNode4 = document.createTextNode("Value");

        var d = document.getElementById( 'mydiv' );

        oTdTxt1.appendChild (textNode1);
        oTdInp1.appendChild (inpGroup);

        oTdTxt2.appendChild (textNode2);
        oTdInp2.appendChild (inpAttrib);

        oTdTxt3.appendChild (textNode3);
        oTdInp3.appendChild (selOperator);

        oTdTxt4.appendChild (textNode4);
        oTdInp4.appendChild (inpValue);

        oTr4.appendChild(oTdTxt4);
        oTr4.appendChild(oTdInp4);

        oTr3.appendChild(oTdTxt3);
        oTr3.appendChild(oTdInp3);

        oTr2.appendChild(oTdTxt2);
        oTr2.appendChild(oTdInp2);

        oTr1.appendChild(oTdTxt1);
        oTr1.appendChild(oTdInp1);

        oTb.appendChild(oParagraph);
        oTb.appendChild(oTr1);
        oTb.appendChild(oTr2);
        oTb.appendChild(oTr3);
        oTb.appendChild(oTr4);
        oTable.appendChild(oTb);
        d.appendChild(oTable);
        d.appendChild(oSeperator);
        d.appendChild(oSeperator);

}
