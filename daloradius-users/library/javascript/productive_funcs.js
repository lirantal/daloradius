/***********************************************************************
 * randomAlphanmeric
 * creates random characters of alpha numeric chars
 *
 * dstObj               - the destination object to copy the data to
 * charsLength          - length of random characters
 ***********************************************************************/
function randomAlphanumeric(dstObj,charsLength) {

	var dstElem = document.getElementById(dstObj);

	var length = charsLength;
	var chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ23456789";
	var randomChars = "";

	for(x=0; x<length; x++) {
		var i = Math.floor(Math.random() * 62);
		randomChars += chars.charAt(i);
	}

	dstElem.value = randomChars;
}
