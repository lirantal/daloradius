#!/usr/bin/env python3
"""Lot 3 PDO-only structural/native contracts; not installation or CI proof."""
import json
import os
import subprocess
from pathlib import Path
from residual_cleanup_audit import TOKENIZER
ROOT = Path(__file__).resolve().parents[1]
BASE = 'cb766c2315e1d2cefe7bf077eeeef16f01814b2d'
TARGETS = {
 'app/common/includes/chart.php': ['dalo_chart_overall_user_statistics'],
 'app/common/includes/functions.php': ['get_message'],
 'app/common/includes/portal_password.php': ['dalo_portal_db_sensitive_call'],
 'app/operators/include/management/functions.php': ['get_table_column_names','insert_single_attribute','hotspots_exists','user_exists','group_exists','insert_single_user_group_mapping','get_user_group_mappings','insert_multiple_user_group_mappings','prepare_fields_and_values','update_info','add_info','user_portal_password_is_set','count_sql','get_numrows'],
 'app/operators/library/attributes.php': ['is_attribute_already_present','handleAttributes'],
}
REMOVED = ['app/common/includes/'+name for name in ('db_open.php','db_close.php','db_error_handler.php')]
def php(code, input=None):
 p = subprocess.run(['docker','run','--rm','-i','--network','none','--entrypoint','php',
  '-v',str(ROOT)+':/code:ro','-w','/code',os.getenv('R28_PHP_IMAGE','lirantal/daloradius'),
  '-d','display_errors=stderr','-r',code],input=input,text=True,capture_output=True,timeout=180)
 assert p.returncode == 0 and not p.stderr, 'Native PHP probe failed: '+str(p.returncode)+' '+p.stderr[:1000]
 return p.stdout

def main():
 assert all(not (ROOT/p).exists() for p in REMOVED)
 paths = [p for p in subprocess.check_output(['git','ls-files','*.php'],cwd=ROOT,text=True).splitlines()
          if (ROOT/p).exists() and not p.endswith('daloradius.conf.php')]
 # All tracked PHP syntax is parsed in one network-disabled container, never included.
 result = php('$p=json_decode(stream_get_contents(STDIN),true);foreach($p as $f){token_get_all(file_get_contents("/code/".$f),TOKEN_PARSE);}echo count($p);',json.dumps(paths))
 assert int(result) == len(paths)
 data = json.loads(php(TOKENIZER,json.dumps(list(TARGETS))))
 for path, names in TARGETS.items():
  old = subprocess.check_output(['git','show',BASE+':'+path],cwd=ROOT,text=True)
  definitions = json.loads(php('$t=token_get_all(stream_get_contents(STDIN));$n=array();foreach($t as $i=>$v){if(is_array($v)&&$v[0]===T_FUNCTION){$j=$i+1;while(isset($t[$j])&&is_array($t[$j])&&$t[$j][0]===T_WHITESPACE)$j++;if(isset($t[$j])&&is_array($t[$j])&&$t[$j][0]===T_STRING)$n[]=$t[$j][1];}}echo json_encode($n);',old))
  assert set(data[path]['definitions']) == set(definitions), path+' lost a retained callable'
 # Native includes/reflection, no database connections and no production configuration.
 code = "$_SERVER['PHP_SELF']='lot3-probe.php';require 'app/common/includes/functions.php';require 'app/common/includes/chart.php';require 'app/common/includes/portal_password.php';require 'app/operators/include/management/functions.php';require 'app/operators/library/attributes.php';"
 names = sum(TARGETS.values(),[]) + ['get_accounting_custom_query_options','update_user_info','add_user_info','update_user_billing_info','add_user_billing_info','count_users','count_hotspots','count_nas']
 code += 'foreach('+json.dumps(names)+" as $f){$p=(new ReflectionFunction($f))->getParameters()[0];if((string)$p->getType()!=='PDO')exit(2);}"
 code += "function t($s,$k){return $k;}$dbSocket=new stdClass();try {require 'app/operators/include/management/groups.php';exit(3);}catch(InvalidArgumentException $e){}echo 'PDO-contracts-pass';"
 code += 'foreach('+json.dumps(names)+" as $f){$n=(new ReflectionFunction($f))->getNumberOfRequiredParameters();$a=array_fill(0,$n,null);$a[0]=new stdClass();try {call_user_func_array($f,$a);exit(4);}catch(TypeError $e){if(strpos($e->getMessage(),'Argument #1')===false)exit(5);}}"
 assert php(code) == 'PDO-contracts-pass'
 # Scan executable tokens, not comments or historical fixtures. Third-party Mail/SMTP is excluded.
 prod = [p for p in paths if p.startswith(('app/','contrib/')) and '/library/phpmailer/' not in p and '/library/pear/' not in p]
 check = r'''$p=json_decode(stream_get_contents(STDIN),true);$bad=array();foreach($p as $f){$t=token_get_all(file_get_contents('/code/'.$f));foreach($t as $i=>$v){if(!is_array($v))continue;if($v[0]===T_STRING&&in_array(strtolower($v[1]),array('escapesimple','fetchrow','numrows','freeprepared','getcol','pusherrorhandling','poperrorhandling','seterrorhandling','getdebuginfo','db_error','pear_error','pear_error_callback','pear_error_return','db_ok','db_fetchmode_assoc','db_fetchmode_ordered'))) $bad[]=array($f,$v[2],$v[1]);if($v[0]===T_STRING&&$v[1]==='DB')$bad[]=array($f,$v[2],'DB');if($v[0]===T_CONSTANT_ENCAPSED_STRING&&preg_match('~(?:^|/)(?:DB|db_open|db_close|db_error_handler)\.php$~',substr($v[1],1,-1)))$bad[]=array($f,$v[2],'retired-provider');}}echo json_encode($bad);'''
 assert json.loads(php(check,json.dumps(prod))) == []
 manifest = json.loads((ROOT/'tests/fixtures/database/pdo-lot3-dispositions.json').read_text())
 assert len(manifest['entries']) == sum(map(len,TARGETS.values()))+1+len(REMOVED)
 assert len({(v['file'],v['function']) for v in manifest['entries']}) == len(manifest['entries'])
 print('PASS lot 3: '+str(len(manifest['entries']))+' dispositions, exact retained function inventories, native PDO reflection/include contracts and rejected non-PDO group handle')
 print('PASS PHP TOKEN_PARSE '+str(len(paths))+' tracked files; '+str(len(prod))+' production PHP files without executable PEAR DB markers or retired loader references')
 print('Scope: static/native bootstrap only; no installation, database connection, live configuration or external service')
if __name__ == '__main__': main()
