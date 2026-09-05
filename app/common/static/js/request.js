/* Same-origin JSON reads. Mutation/CSRF support is deliberately out of scope. */
async function daloRequestJSON(path, parameters) {
    const url = new URL(path, document.baseURI);
    if (url.origin !== window.location.origin) {
        throw new Error('Invalid request destination.');
    }
    url.search = new URLSearchParams(parameters).toString();
    let response;
    try {
        response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        });
    } catch (error) {
        throw new Error('Unable to load information. Check your connection and try again.');
    }
    // checklogin.php redirects expired sessions to the HTML login page.
    if (response.redirected || response.status === 401) {
        throw new Error('Your session has expired. Please sign in again.');
    }
    if (response.status === 403) {
        throw new Error('You do not have permission to view this information.');
    }
    if (!response.ok) {
        throw new Error(`Unable to load information (HTTP ${response.status}).`);
    }
    if (!(response.headers.get('Content-Type') || '').toLowerCase().startsWith('application/json')) {
        throw new Error('Invalid response from the server.');
    }
    try {
        const data = await response.json();
        if (!data || typeof data !== 'object' || Array.isArray(data)) {
            throw new Error('Invalid JSON object');
        }
        return data;
    } catch (error) {
        throw new Error('Invalid response from the server.');
    }
}
