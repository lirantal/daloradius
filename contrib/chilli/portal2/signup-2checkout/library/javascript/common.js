/***********************************************************************
 * calculatePlanCost
 * will calculate the plan cost based on the type and set the correct
 * object id "amount" to the actual cost
 *
 * srcId        - the source object id - the plan type
 ***********************************************************************/
function calculatePlanCost(srcId) {

        var srcElem = document.getElementById(srcId);
	var planCost = "";
	var itemNumber = "";

        if (srcElem.type == "select-one")
                var elemVal = srcElem.options[srcElem.selectedIndex].value;

	if (elemVal == "10-minutes-plan") {
		planCost = "10";
		itemNumber = "1";
	}

	if (elemVal == "20-minutes-plan") {
		planCost = "20";
		itemNumber = "2";
	}

	if (elemVal == "30-minutes-plan") {
		planCost = "30";
		itemNumber = "3";
	}

        var elemAmount = document.getElementById("amount");
        elemAmount.value = planCost;

        var elemItemNumber = document.getElementById("item_number");
        elemItemNumber.value = itemNumber;

}

