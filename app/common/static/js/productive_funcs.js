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

    const length = Number(charsLength);
    // Web Crypto limits each getRandomValues() call to 65,536 bytes.
    const maxLength = 65536 / Uint32Array.BYTES_PER_ELEMENT;
    if (!Number.isSafeInteger(length) || length < 0 || length > maxLength) {
        return;
    }

    const allowedChars = (typeof chars === 'string' && chars.length > 0)
        ? chars
        : "abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";

    let randomChars = "";

    // Never downgrade generated credentials to predictable Math.random() output.
    if (length > 0 && (typeof window === 'undefined' || !window.crypto
            || typeof window.crypto.getRandomValues !== 'function')) {
        return;
    }

    const uint32Range = 0x100000000;
    const unbiasedLimit = Math.floor(uint32Range / allowedChars.length) * allowedChars.length;

    while (randomChars.length < length) {
        const randomValues = new Uint32Array(length - randomChars.length);
        window.crypto.getRandomValues(randomValues);

        for (let i = 0; i < randomValues.length && randomChars.length < length; i++) {
            if (randomValues[i] < unbiasedLimit) {
                randomChars += allowedChars.charAt(randomValues[i] % allowedChars.length);
            }
        }
    }

    dstElem.value = randomChars;

    if (typeof Event === 'function') {
        dstElem.dispatchEvent(new Event('input', { bubbles: true }));
        dstElem.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
