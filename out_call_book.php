<?php
include ("./lib/defines.php");
include ("./lib/functions.php");

//include ("locale.php");

getpost_ifset(array('confno','book'));

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
        <title><?php print GUI_TITLE; ?> <?php print _("control"); ?></title>
	<!--<meta http-equiv="Content-Type" content="text/html">//-->
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <link rel="stylesheet" type="text/css">
        <style type="text/css" media="screen">

                @import url("css/content.css");
                @import url("css/docbook.css");
        </style>

        <script language="JavaScript" type="text/JavaScript">
        <!--
        function MM_openBrWindow(theURL,winName,features) { //v2.0
          window.open(theURL,winName,features);
        }

        //-->
        </script>

        <script language="JavaScript" type="text/JavaScript">
        <!--
function getXmlHttp(){
  var xmlhttp;
  try {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      xmlhttp = false;
    }
  }
  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
    xmlhttp = new XMLHttpRequest();
  }
  return xmlhttp;
}
        //-->
        </script>

        <script language="JavaScript" type="text/JavaScript">
        <!--
// javascript-код голосования из примера
function call_add(nM,nU,rX,tX,cN,bI) {
	// (1) создать объект для запроса к серверу
	var req = getXmlHttp()  
       
        // (2)
	// span рядом с кнопкой
	// в нем будем отображать ход выполнения
	var statusElem = document.getElementById(nU) 
	
	req.onreadystatechange = function() {  
        // onreadystatechange активируется при получении ответа сервера

		if (req.readyState == 4) { 
            // если запрос закончил выполняться

			statusElem.innerHTML = req.statusText // показать статус (Not Found, ОК..)

			if(req.status == 200) { 
                 // если статус 200 (ОК) - выдать ответ пользователю
				alert("Ответ сервера: "+req.responseText);
			}
			// тут можно добавить else с обработкой ошибок запроса
		}

	}

       // (3) задать адрес подключения
	req.open('GET', 'call_operator_add.php?name='+nM+'&invite_num='+nU+'&action=quickcall&data='+cN+'&bookid='+bI+'&rx='+rX+'&tx='+tX, true);  

	// объект запроса подготовлен: указан адрес и создана функция onreadystatechange
	// для обработки ответа сервера
	 
        // (4)
	req.send(null);  // отослать запрос
  
        // (5)
	statusElem.innerHTML = 'Ожидание...' 
}
        //-->
        </script>


</head>

<body bgcolor="#acbdee">

<?php 
include ("./lib/database.php");
include("./lib/addressbook.php"); 
?>


</body>
</html>
