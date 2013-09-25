<?php include("locale.php"); ?>

<script language="JavaScript" type="text/JavaScript">
<!--
// functions.js
var browserType;	
if (document.layers) {browserType = "nn4"}
if (document.all) {browserType = "ie"}
if (window.navigator.userAgent.toLowerCase().match("gecko")) {browserType= "gecko"}


var ua = window.navigator.userAgent;
var msie = ua.indexOf ( "MSIE " );
var pu = null;

function conf_rxtxcurent(cN, cM) {
        window.open ('conf_rxtxcurent.php?channel='+cN+'&command='+cM, 'newWin', 'toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=50,height=50')
}

function conf_action(action, confno, user, channel, command)
{
        var url = '<?PHP echo WEBROOT;  ?>' + 'conf_actions.php';
        var params = 'action=' + action + '&confno=' + confno + '&user_id=' + user + '&channel=' + channel + '&command=' + command;
        new Ajax.Request(url,
                {method: 'post',
                parameters: params
                });

	if (pu)
        	pu.start();
        return false;
}

function togglePass()
{
        document.WMAdd.silPass.disabled = !document.WMAdd.silPass.disabled;
        document.WMAdd.roomPass.disabled = !document.WMAdd.roomPass.disabled;
}

function addRowToTable(tableName)
{
  var tbl = document.getElementById(tableName);
  var lastRow = tbl.rows.length;
  var iteration = lastRow;
  var row = tbl.insertRow(lastRow);
        if(lastRow==0)
        {
                oCell = row.insertCell(0);
                oCell.align = "center";
                oCell.style.fontWeight = "bold";
                oCell.innerHTML =<?php print "'"._("First name")."'"; ?>;
                oCell = row.insertCell(1);
                oCell.align = "center";
                oCell.style.fontWeight = "bold";
                oCell.innerHTML =<?php print "'"._("Last name")."'"; ?>;
                oCell = row.insertCell(2);
                oCell.align = "center";
                oCell.style.fontWeight = "bold";
                oCell.innerHTML =<?php print "'"._("Email")."'"; ?>;
                oCell = row.insertCell(3);
                oCell.align = "center";
                oCell.style.fontWeight = "bold";
                oCell.innerHTML =<?php print "'"._("Telephone")."'"; ?>;
                oCell = row.insertCell(4);
                oCell.align = "center";
                oCell.innerHTML =<?php print "'"._("Delete")."'"; ?>;
                oCell.style.fontWeight = "bold";
                lastRow = tbl.rows.length;
                row = tbl.insertRow(lastRow);
        }
  // if there's no header row in the table, then iteration = lastRow + 1


  var cell0 = row.insertCell(0);
  var el = document.createElement('input');
  el.type = 'text';
  el.name = 'fname[]';
  el.size = 12;
  cell0.appendChild(el);

  var cell1 = row.insertCell(1);
  var el = document.createElement('input');
  el.type = 'text';
  el.name = 'lname[]';
  el.size = 12;
  cell1.appendChild(el);

  var cell2 = row.insertCell(2);
  var el = document.createElement('input');
  el.type = 'text';
  el.name = 'email[]';
  el.onblur = function () {vemail(this)};
  el.size = 22;
  cell2.appendChild(el);

  var cell3 = row.insertCell(3);
  var el = document.createElement('input');
  el.type = 'text';
  el.name = 'phone[]';
  el.size = 15;
  cell3.appendChild(el);

  var cell4 = row.insertCell(4);
  var el = document.createElement('input');
  el.type = 'button';
  el.name = 'remove';
  el.setAttribute('value', <?php print "'"._("Delete")."'"; ?>);
        if(msie > 0)
        {
                 el.setAttribute('className', 'warn');
        }
        else
        {
                 el.setAttribute('class', 'warn');
        }
  el.onclick = function () {deleteCurrentRow(this)};
  cell4.appendChild(el);
}

function vemail(obj)
{
var fld=0;
var goodEmail = obj.value.match(/\b(^(\S+@).+((\.com)|(\.net)|(\.edu)|(\.mil)|(\.gov)|(\.org)|(\..{2,2}))$)\b/gi);

fld = obj;

if (goodEmail){
   good = true
} else {
   alert(<?php print "\""._("Please enter a valid email address").".\""; ?>)
setTimeout("fld.focus();",1);
setTimeout("fld.select();",1);
   good = false
}
return good;
}

function deleteCurrentRow(obj)
{
                var delRow = obj.parentNode.parentNode;
                var tbl = delRow.parentNode.parentNode;
                var rIndex = delRow.sectionRowIndex;
                var rowArray = new Array(delRow);
                deleteRows(rowArray);
                reorderRows(tbl, rIndex);
                var lastRow = tbl.rows.length;
                if(lastRow==1)
                {
                        tbl.deleteRow(0);
                }
}

function reorderRows(tbl, startingIndex)
{
                if (tbl.tBodies[0].rows[startingIndex]) {
                        var count = startingIndex + ROW_BASE;
                        for (var i=startingIndex; i<tbl.tBodies[0].rows.length; i++) {

                                // CONFIG: next line is affected by myRowObject settings
                                tbl.tBodies[0].rows[i].myRow.one.data = count; // text

                                //teElement CONFIG: next line is affected by myRowObject settings
                                tbl.tBodies[0].rows[i].myRow.two.name = INPUT_NAME_PREFIX + count; // input text

                                // CONFIG: next line is affected by myRowObject settings
                                var tempVal = tbl.tBodies[0].rows[i].myRow.two.value.split(' '); // for debug purposes
                                tbl.tBodies[0].rows[i].myRow.two.value = count + ' was' + tempVal[0]; // for debug purposes

                                // CONFIG: next line is affected by myRowObject settings
                                tbl.tBodies[0].rows[i].myRow.four.value = count; // input radio

                                // CONFIG: requires class named classy0 and classy1
                                tbl.tBodies[0].rows[i].className = 'classy' + (count % 2);

                                count++;
                        }
                }
}

function deleteRows(rowObjArray)
{
                for (var i=0; i<rowObjArray.length; i++) {
                        var rIndex = rowObjArray[i].sectionRowIndex;
                        rowObjArray[i].parentNode.deleteRow(rIndex);
                }
}





function show(d) {	  
    
    if (browserType == "gecko" )
     document.poppedLayer = eval('document.getElementById(\'' + d + '\')');
      else if (browserType == "ie")
    document.poppedLayer = eval('document.all[\'' + d + '\']');
     else {
    document.poppedLayer = eval('document.layers[\'`' + d + '\']');
     }
    document.poppedLayer.style.display = "inline";
     
}
				 

function hide(d) {

  var d;
  if (browserType == "gecko" )
     document.poppedLayer = eval('document.getElementById(\'' + d + '\')');
  else if (browserType == "ie")
     document.poppedLayer = eval('document.all[\'' + d + '\']');
  else {
     document.poppedLayer = eval('document.layers[\'`' + d + '\']');
  }
  document.poppedLayer.style.display = "none";
}

function accept(t){
  if(confirm(t))
		return true;
     else	
    	return false;    
}

function acceptShow(t, d){
if(accept(t)){
    show(d)
    return true;
}
else
 return false;
}


// end.

//-->
</script>
