const { spawnSync } = require('node:child_process');
const path = require('node:path');
const test = require('node:test');
const assert = require('node:assert/strict');

test('PHP service-status regression suite', (t) => {
  const version = spawnSync('php', ['-v'], { encoding: 'utf8' });
  if (version.error && version.error.code === 'ENOENT') {
    t.skip('PHP CLI is not installed in this test environment');
    return;
  }

  const script = path.join(__dirname, 'service-status.php');
  const result = spawnSync('php', [script], { encoding: 'utf8', timeout: 15000 });
  assert.equal(result.error, undefined, result.error && result.error.message);
  assert.equal(result.status, 0, `${result.stdout}\n${result.stderr}`);
  assert.match(result.stdout, /service status regression checks: \d+ passed/);
});
