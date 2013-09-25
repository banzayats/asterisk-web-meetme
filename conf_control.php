<?PHP
include ("./lib/defines.php");
include ("locale.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
	<meta http-equiv="Content-Type" content = "text/html;charset=iso-8859-1" />
	<script type = "text/javascript" src = "./lib/prototype.js"></script>
	<?PHP include ("./lib/functions_js.php"); ?>
	<script type = "text/javascript">


function conf_init()
{
	var url_action = 'conf_async.php?confno=' + <?PHP echo $_REQUEST["confno"] ?>;
	pu = new Ajax.PeriodicalUpdater('confdisplay', url_action, {asynchronous: true, frequency: <?PHP print MON_REFRESH; ?>});
}

                if (window.addEventListener) {
                        window.addEventListener("load", conf_init, false);
                } else if (window.attachEvent) {
                        window.attachEvent("onload", conf_init)
                } else if (document.getElementById) {
                        window.onload = conf_init;
                }

	</script>
    </head>
    <body>
	<div id = "confdisplay"></div>
    </body>
</html>
