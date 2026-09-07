/* Read-only tooltip content. Server values are text, never executable HTML/JS. */
const daloInfo = (() => {
    const pending = new WeakMap();

    async function load(id, path, parameters, fields, description = false) {
        const target = document.getElementById(id);
        if (!target) {
            return;
        }
        const request = {};
        pending.set(target, request);
        target.textContent = 'Loading...';
        target.setAttribute('aria-busy', 'true');
        target.setAttribute('aria-live', 'polite');
        try {
            const data = await daloRequestJSON(path, parameters);
            if (pending.get(target) !== request || !target.isConnected) {
                return;
            }
            const content = document.createDocumentFragment();
            fields.forEach(([key, label], index) => {
                if (typeof data[key] !== 'string' && typeof data[key] !== 'number') {
                    throw new Error('Invalid response from the server.');
                }
                if (index > 0) {
                    content.appendChild(document.createElement('br'));
                }
                const normal = document.createElement('span');
                normal.style.fontWeight = 'normal';
                normal.textContent = description ? String(data[key]) : label;
                if (description) {
                    content.appendChild(document.createTextNode(label + ' '));
                    content.appendChild(normal);
                } else {
                    content.appendChild(normal);
                    content.appendChild(document.createTextNode(' ' + data[key]));
                }
            });
            target.replaceChildren(content);
        } catch (error) {
            if (pending.get(target) === request && target.isConnected) {
                target.textContent = error.message;
            }
        } finally {
            if (pending.get(target) === request) {
                target.removeAttribute('aria-busy');
                pending.delete(target);
            }
        }
    }

    return {
        user: (id, parameters) => load(id, 'library/ajax/user_info.php', parameters,
            [['upload', 'Upload:'], ['download', 'Download:']]),
        hotspot: (id, parameters) => load(id, 'library/ajax/hotspot_info.php', parameters,
            [['upload', 'Total Uploads:'], ['download', 'Total Downloads:'], ['hits', 'Total Hits:']]),
        attribute: (id, parameters) => load(id, 'library/ajax/vendor_attribute_info.php', parameters,
            [['description', 'Description:']], true)
    };
})();
