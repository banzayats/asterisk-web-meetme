function populate(objForm,selectIndex) {
        timeA = new Date(objForm.year.options[objForm.year.selectedIndex].text, objForm.month.options[objForm.month.selectedIndex].value,1);
        timeDifference = timeA - 86400000;
        timeB = new Date(timeDifference);
        var daysInMonth = timeB.getDate();

        for (var i = 0; i < objForm.day.length; i++) {
                objForm.day.options[0] = null;
        }
        for (var i = 0; i < daysInMonth; i++) {
                objForm.day.options[i] = new Option(i+1);
        }
        document.WMAdd.day.options[0].selected = true;
}
