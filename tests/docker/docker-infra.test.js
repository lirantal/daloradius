const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const root = path.resolve(__dirname, "..", "..");

function read(relativePath) {
  return fs.readFileSync(path.join(root, relativePath), "utf8");
}

test("FreeRADIUS image uses one executable startup directive", () => {
  const dockerfile = read("Dockerfile-freeradius");

  assert.match(dockerfile, /^ENTRYPOINT \["\/app\/init-freeradius\.sh"\]$/m);
  assert.doesNotMatch(dockerfile, /^CMD \["\/app\/init-freeradius\.sh"\]$/m);
});
