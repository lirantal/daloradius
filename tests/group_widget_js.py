#!/usr/bin/env python3
"""R05: execute the widget's actual JS in a small synthetic DOM (not a browser)."""
import argparse
import json
import shutil
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--source', type=Path, default=ROOT / 'app/operators/include/management/groups.php')
parser.add_argument('--node', default=shutil.which('node'))
args = parser.parse_args()
if not args.node:
    raise SystemExit('Node is required for this focused JavaScript harness')
source = args.source.read_text().split('echo <<<EOF', 1)[1].split('EOF;', 1)[0]
values = {'selected_groups_js': json.dumps(['alpha', '0']), 'counter': '2',
          'disabled_users_group_js': json.dumps('daloRADIUS-Disabled-Users'),
          'disabled_users_group_priority_js': json.dumps('-1'),
          'groupLabel': 'Group', 'prorityLabel': 'Priority'}
for key, value in values.items():
    source = source.replace('{$' + key + '}', value)
source = source.replace('\\${', '${')
stub = r"""
const assert = require('node:assert/strict');
const elements = new Map();
function row(id, value) {
  const input = {value};
  return {id, input, html:'',
    setAttribute(k,v){if(k==='id')this.id=v;},
    set innerHTML(v){this.html=v;},
    querySelector(){return this.input;}}
}
const groupsDiv = {
  appendChild(item){assert(!elements.has(item.id));elements.set(item.id,item);},
  removeChild(item){elements.delete(item.id);}
};
elements.set('group-0',row('group-0','alpha'));
elements.set('group-1',row('group-1','0'));
const select = {options:[{text:''}],selectedIndex:0};
const fieldset = {style:{display:'block'}};
const document = {
  createElement(){return row('', '');},
  getElementById(id){
    if(id==='groups')return select;
    if(id==='groupsDiv')return groupsDiv;
    if(id==='associated_groups_fieldset')return fieldset;
    if(id.endsWith('-name'))return elements.get(id.slice(0,-5)).input;
    return elements.get(id);
  }
};
"""
checks = r"""
select.options[0].text='0';add_group();assert.equal(elements.size,2);
del_group('group-0');assert.deepEqual(selected_groups,['0']);
const unsafe='quote \" </script><script>bad</script>';
select.options[0].text=unsafe;add_group();
assert.equal(elements.get('group-2').input.value,unsafe);
assert(!elements.get('group-2').html.includes(unsafe));
assert(elements.has('group-1'));assert.equal(elements.size,2);
select.options[0].text=disabledUsersGroupName;add_group();
assert(elements.get('group-3').html.includes('value="-1"'));
assert(elements.get('group-3').html.includes(' readonly'));
del_group('group-1');select.options[0].text='next';add_group();
assert.deepEqual([...elements.keys()],['group-2','group-3','group-4']);
assert.deepEqual(selected_groups,[unsafe,disabledUsersGroupName,'next']);
console.log('PASS actual widget JS: zero identity, safe DOM value, reserved priority and non-reused IDs (synthetic DOM)');
"""
subprocess.run([args.node, '-e', stub + source + checks], check=True)
