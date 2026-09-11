const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const operators = path.join(root, 'app/operators');

test('service status endpoints are removed', () => {
    for (const file of ['rep-stat-services.php', 'library/extensions/radius_server_info.php']) {
        assert.equal(fs.existsSync(path.join(operators, file)), false, file);
    }
});

test('sidebars retain the other status and log destinations', () => {
    const menus = {
        'home/default.php': ['rep-stat-server.php', 'rep-lastconnect.php', 'rep-logs-radius.php', 'rep-logs-system.php'],
        'rep/stat.php': ['rep-stat-server.php', 'config-crontab.php', 'rep-stat-ups.php', 'rep-stat-raid.php'],
    };
    for (const [menu, destinations] of Object.entries(menus)) {
        const source = fs.readFileSync(path.join(operators, 'include/menu/sidebar', menu), 'utf8');
        assert.doesNotMatch(source, /rep-stat-services\.php|ServicesStatus/);
        for (const destination of destinations) {
            assert.ok(source.includes(destination), `${menu}: ${destination}`);
            assert.ok(fs.existsSync(path.join(operators, destination)), destination);
        }
    }
});

test('application PHP has no references to the removed endpoints', () => {
    function walk(directory) {
        for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
            const file = path.join(directory, entry.name);
            if (entry.isDirectory()) walk(file);
            else if (entry.name.endsWith('.php')) {
                assert.doesNotMatch(fs.readFileSync(file, 'utf8'), /rep-stat-services\.php|radius_server_info\.php/, file);
            }
        }
    }
    walk(path.join(root, 'app'));
});
