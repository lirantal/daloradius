/***********************************************************************
 * randomAlphanumeric
 * creates random characters of alpha numeric chars
 *
 * dstObj               - the destination object to copy the data to
 * charsLength          - length of random characters
 * chars                - allowed chars
 ***********************************************************************/
function randomAlphanumeric(dstObj, charsLength, chars) {
    const dstElem = document.getElementById(dstObj);
    if (!dstElem) {
        return;
    }

    const length = Number(charsLength) || 8;
    const allowedChars = (typeof chars === 'string' && chars.length > 0)
        ? chars
        : "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";

    let randomChars = "";

    if (typeof window !== 'undefined' && window.crypto && typeof window.crypto.getRandomValues === 'function') {
        const randomValues = new Uint32Array(length);
        window.crypto.getRandomValues(randomValues);
        for (let i = 0; i < length; i++) {
            randomChars += allowedChars.charAt(randomValues[i] % allowedChars.length);
        }
    } else {
        for (let i = 0; i < length; i++) {
            const index = Math.floor(Math.random() * allowedChars.length);
            randomChars += allowedChars.charAt(index);
        }
    }

    dstElem.value = randomChars;

    if (typeof Event === 'function') {
        dstElem.dispatchEvent(new Event('input', { bubbles: true }));
        dstElem.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
