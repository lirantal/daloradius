#!/usr/bin/env python3
"""R28 structural closure and native PHP include/reflection contracts.
This is source/runtime-bootstrap validation, not native SQL or installation proof.
"""
import json
import os
import subprocess
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
BASE='2753c9d1c2fb922e64cf978d889f49626042c25b'
POPULATORS=['populate_payment_type_id','populate_customer_id','populate_invoice_status_id',
 'populate_invoice_type_id','populate_hotspots','populate_plans','populate_groups',
 'populate_vendors','populate_realms','populate_proxys']
FUNCTIONS='app/operators/include/management/functions.php'
SELECTORS=['app/operators/include/management/populate_selectbox.php','app/users/include/management/populate_selectbox.php']
HANDLERS=['contrib/chilli/'+f+'/library/errorHandling.php' for f in
 ['portal1/signup-paypal','portal2/signup-2checkout','portal2/signup-free',
  'portal2/signup-paypal','portal3/signup-free','portal3/signup-paypal']]
PDO_WRAPPERS=['delete_user_group_mappings','insert_multiple_plan_group_mappings','update_user_group_mapping_priority']
TOKENIZER=r"""$paths=json_decode(stream_get_contents(STDIN),true);$out=array();foreach($paths as $p){
$t=token_get_all(file_get_contents('/code/'.$p));$defs=array();$refs=array();
foreach($t as $i=>$v){if(!is_array($v))continue;
if($v[0]===T_FUNCTION){$j=$i+1;while(isset($t[$j])&&(is_array($t[$j])&&$t[$j][0]===T_WHITESPACE||$t[$j]==='&'))$j++;
if(isset($t[$j])&&is_array($t[$j])&&$t[$j][0]===T_STRING)$defs[]=$t[$j][1];}
if($v[0]===T_STRING){$j=$i+1;while(isset($t[$j])&&is_array($t[$j])&&$t[$j][0]===T_WHITESPACE)$j++;
$k=$i-1;while($k>=0&&is_array($t[$k])&&$t[$k][0]===T_WHITESPACE)$k--;$prev=$k>=0?$t[$k]:null;
if(($t[$j]??null)==='('&&!(is_array($prev)&&in_array($prev[0],array(T_FUNCTION,T_OBJECT_OPERATOR,T_DOUBLE_COLON,T_NEW))))$refs[]=array('kind'=>'call','name'=>$v[1],'line'=>$v[2]);}
elseif($v[0]===T_CONSTANT_ENCAPSED_STRING)$refs[]=array('kind'=>'string','name'=>$v[1],'line'=>$v[2]);}
$out[$p]=array('definitions'=>$defs,'references'=>$refs);}echo json_encode($out,JSON_THROW_ON_ERROR);"""
def php(code,input=None):
 p=subprocess.run(['docker','run','--rm','-i','--network','none','--entrypoint','php',
  '-v',str(ROOT)+':/code:ro','-w','/code',os.getenv('R28_PHP_IMAGE','lirantal/daloradius'),
  '-d','display_errors=stderr','-r',code],input=input,text=True,capture_output=True,timeout=180)
 assert p.returncode==0 and not p.stderr, f'native PHP structural probe failed: exit={p.returncode}, stderr={p.stderr[:1000]}'
 return p.stdout

def main():
 paths=[p for p in subprocess.check_output(['git','ls-files','app','contrib'],cwd=ROOT,text=True).splitlines()
  if p.endswith('.php') and (ROOT/p).exists() and '/vendor/' not in p and '/library/pear/' not in p and '/library/phpmailer/' not in p]
 data=json.loads(php(TOKENIZER,json.dumps(paths)))
 manifest=json.loads((ROOT/'docs/database/residual-r28-dispositions.json').read_text())
 entries=manifest['entries'];assert len(entries)==manifest['inventory_entries']==35
 assert len({r['id'] for r in entries})==35
 removed=set(POPULATORS+['add_invoice_items'])
 for p,v in data.items():
  assert not removed.intersection(v['definitions']), 'removed declaration remains in '+p
  for r in v['references']:
   assert not (r['kind']=='call' and r['name'] in removed), 'removed call remains in '+p
   if r['kind']=='string':
    assert not any(name in r['name'] for name in removed), 'possible dynamic removed call in '+p
    assert 'errorHandling.php' not in r['name'], 'copied handler include needs review in '+p
 for p in HANDLERS:assert not (ROOT/p).exists()
 assert (ROOT/'app/operators/include/management/userBilling.php').read_bytes()==subprocess.check_output(
  ['git','show',BASE+':app/operators/include/management/userBilling.php'],cwd=ROOT)
 # Exact native function-name inventories: only the approved dead definitions disappear.
 for p,dropped in [(FUNCTIONS,{'add_invoice_items'}),*[(p,set(POPULATORS)) for p in SELECTORS]]:
  old=subprocess.check_output(['git','show',BASE+':'+p],cwd=ROOT,text=True)
  old_names=json.loads(php('$t=token_get_all(stream_get_contents(STDIN));$n=array();foreach($t as $i=>$v){if(is_array($v)&&$v[0]===T_FUNCTION){$j=$i+1;while(isset($t[$j])&&is_array($t[$j])&&$t[$j][0]===T_WHITESPACE)$j++;if(isset($t[$j])&&is_array($t[$j])&&$t[$j][0]===T_STRING)$n[]=$t[$j][1];}}echo json_encode($n);',old))
  assert set(data[p]['definitions'])==set(old_names)-dropped
 for p in SELECTORS:
  code="$_SERVER['PHP_SELF']='r28-probe.php';require "+json.dumps(p)+";"
  code+='foreach('+json.dumps(POPULATORS)+" as $f){if(function_exists($f))exit(2);}"
  code+="foreach(array('drawTables','drawOptions','drawTypes','drawRecommendedHelper') as $f){if(!function_exists($f))exit(3);}echo 'selectors-pass';"
  assert php(code)=='selectors-pass'
 code="$_SERVER['PHP_SELF']='r28-probe.php';require '"+FUNCTIONS+"';require 'app/operators/include/management/nasImportExport.php';require 'app/operators/include/management/userBilling.php';"
 code+='foreach('+json.dumps(PDO_WRAPPERS+['nas_backup_lock_name','nas_backup_acquire_lock','nas_backup_release_lock'])+" as $f){$r=new ReflectionFunction($f);if((string)$r->getParameters()[0]->getType()!=='PDO')exit(4);}"
 code+="if(!function_exists('userInvoiceAdd')||function_exists('add_invoice_items'))exit(5);if(nas_import_is_duplicate_error(new RuntimeException('duplicate entry')))exit(6);$e=new PDOException('fixture');(new ReflectionProperty(Exception::class,'code'))->setValue($e,'23000');$e->errorInfo=array('23000',1062,'fixture');if(!nas_import_is_duplicate_error($e))exit(7);echo 'providers-pass';"
 assert php(code)=='providers-pass'
 print('PASS: 35 original R28 dispositions, removed declarations/calls/strings, exact retained symbol sets, native includes/reflection and duplicate-error predicate')
 print('PASS: userInvoiceAdd provider byte-identical to R27; R28 retained-provider contracts unchanged; installation packages outside this audit')
if __name__=='__main__':main()
