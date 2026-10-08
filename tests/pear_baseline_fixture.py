"""Historical PEAR bootstrap materialization for disposable test trees only.
Never restore these providers to production or a PDO-only candidate tree.
"""
from pathlib import Path
import subprocess
BOOTSTRAP = ('db_open.php', 'db_close.php', 'db_error_handler.php')
def restore_pear_bootstrap(tree, ref):
    tree = Path(tree)
    includes = tree / 'app/common/includes'
    includes.mkdir(parents=True, exist_ok=True)
    root = Path(__file__).resolve().parents[1]
    for name in BOOTSTRAP:
        source = subprocess.check_output(['git', 'show', ref + ':app/common/includes/' + name], cwd=root)
        (includes / name).write_bytes(source)
