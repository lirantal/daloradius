<?php
$locale = 'en_US';
if (isset($_SESSION["locale"])) {
    $locale = $_SESSION["locale"];
} elseif (isset($_GET["locale"])) {
    $locale = $_GET["locale"];
} else {
    $locale = 'en_US';
}

putenv('LC_ALL=' . $locale);
setlocale(LC_ALL, $locale);

bindtextdomain("daloRADIUS", "./locale");
textdomain("daloRADIUS");
bind_textdomain_codeset("daloRADIUS", 'UTF-8');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
            <meta content="IE=edge" http-equiv="X-UA-Compatible">
                <meta content="width=device-width, initial-scale=1" name="viewport">
                    <title>
                        daloRadius - Development version
                    </title>
                    <link href="css/bootstrap.min.css" rel="stylesheet">
                        <link href="css/style.css" rel="stylesheet" type="text/css">
                            <!--[if lt IE 9]>
      <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
                        </link>
                    </link>
                </meta>
            </meta>
        </meta>
        <script type="text/javascript">
            function MM_jumpMenu(targ,selObj,restore){
eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
if (restore) selObj.selectedIndex=0;
}
        </script>
    </head>
</html>
