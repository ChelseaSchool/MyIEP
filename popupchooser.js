
    var fixedAtX = -1             // x position (-1 if to appear below control)
    var fixedAtY = -1         // y position (-1 if to appear below control)
    var startAtDay = 1         // 0 - sunday ; 1 - monday
    var shouldShowWeekNumber = 0  // 0 - don't show; 1 - show
    var shouldShowToday = 1       // 0 - don't show; 1 - show
    var imageDir = "../images/"         // directory for images ... e.g. var imageDir="/img/"

    //var gotoString = "Go To Current Month"
    //var todayString = "<font color=white>Today is</font>"
    //var weekString = "Wk"
    //var scrollLeftMessage = "Click to scroll to previous month. Hold mouse button to scroll automatically."
    //var scrollRightMessage = "Click to scroll to next month. Hold mouse button to scroll automatically."
    //var selectMonthMessage = "Click to select a month."
    //var selectYearMessage = "Click to select a year."
    //var selectDateMessage = "Select [date] as date." // do not replace [date], it will be replaced by date.

    //var crossobj, crossMonthObj, crossYearObj, monthSelected, yearSelected, dateSelected, omonthSelected, oyearSelected, odateSelected, monthConstructed, yearConstructed, intervalID1, intervalID2, timeoutID1, timeoutID2, ctlToPlaceValue, ctlNow, dateFormat, nStartingYear
    var crossobject
    var crossMonthObject
    var crossYearObject
    var rowSelected
    var selectValue
    var ctlcur

    var bIsPageLoaded=false

    var is_ie=document.all
    var is_dom=document.getElementById
    var is_ns4=document.layers

    var todays_date = new Date()
    var current_day  = todays_date.getDate()
    var current_month = todays_date.getMonth()
    var current_year  = todays_date.getYear()
    var image_src = new Array("drop1.gif","drop2.gif","left1.gif","left2.gif","right1.gif","right2.gif")
    var image = new Array()

    var boolShow = false;

    function closeChooser() {
        //var sTmp

        hideChooser();
        selectValue.value = rowSelected; //'boo yeah'; //constructDate(dateSelected,monthSelected,yearSelected)
    }

    /* hides <select> and <applet> objects (for IE only) */
    function chooser_hideElement( elmID, overDiv )
    {
      if( is_ie )
      {
        for( i = 0; i < document.all.tags( elmID ).length; i++ )
        {
          obj = document.all.tags( elmID )[i];
          if( !obj || !obj.offsetParent )
          {
            continue;
          }
      
          // Find the element's offsetTop and offsetLeft relative to the BODY tag.
          objLeft   = obj.offsetLeft;
          objTop    = obj.offsetTop;
          objParent = obj.offsetParent;
          
          while( objParent.tagName.toUpperCase() != "BODY" )
          {
            objLeft  += objParent.offsetLeft;
            objTop   += objParent.offsetTop;
            objParent = objParent.offsetParent;
          }
      
          objHeight = obj.offsetHeight;
          objWidth = obj.offsetWidth;
      
          if(( overDiv.offsetLeft + overDiv.offsetWidth ) <= objLeft );
          else if(( overDiv.offsetTop + overDiv.offsetHeight ) <= objTop );
          else if( overDiv.offsetTop >= ( objTop + objHeight ));
          else if( overDiv.offsetLeft >= ( objLeft + objWidth ));
          else
          {
            obj.style.visibility = "hidden";
          }
        }
      }
    }
     
    /*
    * unhides <select> and <applet> objects (for IE only)
    */
    function chooser_showElement( elmID )
    {
      if( is_ie )
      {
        for( i = 0; i < document.all.tags( elmID ).length; i++ )
        {
          obj = document.all.tags( elmID )[i];
          
          if( !obj || !obj.offsetParent )
          {
            continue;
          }
        
          obj.style.visibility = "";
        }
      }
    }

    /*
    function chooser_HolidayRec (d, m, y, desc)
    {
        this.d = d
        this.m = m
        this.y = y
        this.desc = desc
    }
    */

    var chooser_HolidaysCounter = 0
    var chooser_Holidays = new Array()

    /*
    function chooser_addHoliday (d, m, y, desc)
    {
        chooser_Holidays[chooser_HolidaysCounter++] = new chooser_HolidayRec ( d, m, y, desc )
    }
    */

    if (is_dom)
    {
        for (h=0;h<image_src.length;h++)
        {
            image[h] = new Image
            image[h].src = imageDir + image_src[h]
        }

        document.write ("<div onclick='boolShow=true'  id='chooser' style='z-index:+900;position:absolute;visibility:hidden;'>");
        document.write ("<table cellpadding=0 cellspacing=0 width='250' style='font-family:arial;font-size:11px;border-width:1px;border-style:solid;border-color: #666666;font-family:arial; font-size:11px}' bgcolor='#ffffff'>");
        document.write ("<tr bgcolor='#D4D0C8'><td>");
        document.write ("<table cellpadding=2 cellspacing=0 width='100%' ><tr><td nowrap style='padding:2px;font-family:arial; font-size:11px;' align=left><font color='#000000'><B><span id='header'>Choose...</span></B></font></td><td nowrap align=right><a href='javascript:hideChooser()' onmousedown=\"document.all.close3.src='"+imageDir+"close4.gif'\"><IMG id=close3 SRC='"+imageDir+"close3.gif' WIDTH='16' HEIGHT='14' BORDER='0' ALT='Close the Chooser' align='absmiddle'></a>&nbsp;</td></tr></table>");
        //document.write ("</td></tr><tr><td style='padding:5px' bgcolor=#ffffff><span id='menu'></span></td></tr>");
        document.write ("</td></tr><tr><td style='padding:0px' bgcolor=#ffffff><div id='menu' style='border : none; margin: 0px; background :white; padding : 2px; width : 99%; height : 80px; overflow :auto; '></div></td></tr>");
        //document.write ("<div onclick='boolShow=true'  id='chooser' style='z-index:+900;position:absolute;visibility:visible;'><table cellpadding=0 cellspacing=0 width='250' style='font-family:arial;font-size:11px;border-width:1px;border-style:solid;border-color: #666666;font-family:arial; font-size:11px}' bgcolor='#ffffff'><tr bgcolor='#D4D0C8'><td>hello</td></tr><tr><td><table cellpadding=2 cellspacing=0 width='"+((shouldShowWeekNumber==1)?248:218)+"' ><tr><td nowrap style='padding:2px;font-family:arial; font-size:11px;' align=left><font color='#000000'><B><span id='header'></span></B></font></td><td nowrap align=right><a href='javascript:hideChooser()' onmousedown=\"document.all.close2.src='"+imageDir+"close3.gif'\"><IMG id=close2 SRC='"+imageDir+"close.gif' WIDTH='16' HEIGHT='14' BORDER='0' ALT='Close the Calendar' align='absmiddle'></a>&nbsp;</td></tr></table></td></tr><tr><td style='padding:5px' bgcolor=#ffffff><span id='menu'></span></td></tr>")
        //document.write ("<div onclick='boolShow=true'  id='chooser' style='z-index:+900;position:absolute;visibility:visible;'><table cellpadding=0 cellspacing=0 width='250' style='font-family:arial;font-size:11px;border-width:1px;border-style:solid;border-color: #666666;font-family:arial; font-size:11px}' bgcolor='#ffffff'><tr bgcolor='#D4D0C8'><td>hello</td></tr>")
        //document.write ("<div onclick='boolShow=true'  id='chooser' onmouseover=\"document.all.close2.src='"+imageDir+"close.gif'\" onmouseup=\"document.all.close2.src='"+imageDir+"close.gif'\"   style='z-index:+999;position:absolute;visibility:hidden;'><table cellpadding=0 cellspacing=0 width="+((shouldShowWeekNumber==1)?250:200)+" style='font-family:arial;font-size:11px;border-width:1px;border-style:solid;border-color: #666666;font-family:arial; font-size:11px}' bgcolor='#ffffff'><tr bgcolor='#D4D0C8'><td><table cellpadding=2 cellspacing=0 width='"+((shouldShowWeekNumber==1)?248:218)+"' ><tr><td nowrap style='padding:2px;font-family:arial; font-size:11px;' align=left><font color='#000000'><B><span id='header'></span></B></font></td><td nowrap align=right><a href='javascript:hideChooser()' onmousedown=\"document.all.close2.src='"+imageDir+"close2.gif'\"><IMG id=close2 SRC='"+imageDir+"close.gif' WIDTH='16' HEIGHT='14' BORDER='0' ALT='Close the Calendar' align='absmiddle'></a>&nbsp;</td></tr></table></td></tr><tr><td style='padding:5px' bgcolor=#ffffff><span id='menu'></span></td></tr>")
            
        //if (shouldShowToday==1)
        //{
        //    document.write ("<tr bgcolor=#666666><td style='padding:5px' align=center><span id='lblToday'></span></td></tr>")
        //}
            
        document.write ("</table></div>"); //<div id='selectMonth' onmouseover=\"document.all.spanMonth.style.borderColor='#666666';\" style='z-index:+999;position:absolute;visibility:hidden;'></div><div id='selectYear' onmouseover=\"document.all.spanYear.style.borderColor='#666666';\" style='z-index:+999;position:absolute;visibility:hidden;'></div>");
    }

    //var chooser_monthName = new Array("January","February","March","April","May","June","July","August","September","October","November","December")
    //if (startAtDay==0)
    //{
    //    chooser_dayName = new Array ("Sun","Mon","Tue","Wed","Thu","Fri","Sat")
    //}
    //else
    //{
    //    chooser_dayName = new Array ("Mon","Tue","Wed","Thu","Fri","Sat","Sun")
    //}
    //var styleAnchor="text-decoration:none;color:black;"
    //var styleLightBorder="border-style:solid;border-width:1px;border-color:#666666;"


    function init_chooser() {
        if (!is_ns4)
        {
            //if (!is_ie) { current_year += 1900  }

            crossobject=(is_dom)?document.getElementById("chooser").style : is_ie? document.all.chooser : document.chooser
            //crossobject= //(is_dom)?document.getElementById("chooser").style : is_ie? document.all.calendar : document.calendar
            
            hideChooser()

            //crossMonthObject=(is_dom)?document.getElementById("selectMonth").style : is_ie? document.all.selectMonth : document.selectMonth

            //crossYearObject=(is_dom)?document.getElementById("selectYear").style : is_ie? document.all.selectYear : document.selectYear

            //monthConstructed=false;
            //yearConstructed=false;

            //if (shouldShowToday==1)
            //{
            //    document.getElementById("lblToday").innerHTML = todayString + " <a onmousemove='window.status=\""+gotoString+"\"' onmouseout='window.status=\"\"' title='"+gotoString+"' style='color: white' href='javascript:monthSelected=current_month;yearSelected=current_year;constructCalendar();'>"+chooser_dayName[(todays_date.getDay()-startAt==-1)?6:(todays_date.getDay()-startAt)]+", " + current_day + " " + chooser_monthName[current_month].substring(0,3)   + " " + current_year + "</a>"
            //}

            //sHTML1="&nbsp;<span id='spanLeft' style='border-style:solid;border-width:1;border-color:#D4D0C8;cursor:pointer' onmouseover='swapImages(\"changeLeft\",\"left2.gif\");this.style.borderColor=\"#666666\";window.status=\""+scrollLeftMessage+"\"; popDownYear(); popDownMonth();' onclick='javascript:decMonth()' onmouseout='clearInterval(intervalID1);swapImages(\"changeLeft\",\"left1.gif\");this.style.borderColor=\"#D4D0C8\";window.status=\"\"' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout(\"StartDecMonth()\",500)'    onmouseup='clearTimeout(timeoutID1);clearInterval(intervalID1)'>&nbsp<IMG id='changeLeft' SRC='"+imageDir+"left1.gif' width=10 height=11 align=middle BORDER=0>&nbsp</span>&nbsp;"
            //sHTML1+="&nbsp;<span id='spanRight' style='border-style:solid;border-width:1;border-color:#D4D0C8;cursor:pointer'   onmouseover='swapImages(\"changeRight\",\"right2.gif\");this.style.borderColor=\"#666666\";window.status=\""+scrollRightMessage+"\"; popDownYear(); popDownMonth();' onmouseout='clearInterval(intervalID1);swapImages(\"changeRight\",\"right1.gif\");this.style.borderColor=\"#D4D0C8\";window.status=\"\"' onclick='incMonth()' onmousedown='clearTimeout(timeoutID1);timeoutID1=setTimeout(\"StartIncMonth()\",500)'  onmouseup='clearTimeout(timeoutID1);clearInterval(intervalID1)'>&nbsp<IMG id='changeRight' SRC='"+imageDir+"right1.gif'   width=10 height=11  align=middle BORDER=0>&nbsp</span>&nbsp"
            //sHTML1+="&nbsp;<span id='spanMonth' style='width: 82px;border-style:solid;border-width:1;border-color:#D4D0C8;cursor:pointer'   onmouseover='swapImages(\"changeMonth\",\"drop2.gif\");this.style.borderColor=\"#666666\";window.status=\""+selectMonthMessage+"\"; popDownYear();' onmouseout='swapImages(\"changeMonth\",\"drop1.gif\");this.style.borderColor=\"#D4D0C8\";window.status=\"\"' onclick='popUpMonth()'></span>&nbsp;"
            //sHTML1+="&nbsp;<span id='spanYear' style='border-style:solid;border-width:1;border-color:#D4D0C8;cursor:pointer' onmouseover='swapImages(\"changeYear\",\"drop2.gif\");this.style.borderColor=\"#666666\";window.status=\""+selectYearMessage+"\"; popDownMonth();' onmouseout='swapImages(\"changeYear\",\"drop1.gif\");this.style.borderColor=\"#D4D0C8\";window.status=\"\"'   onclick='popUpYear()'></span>&nbsp;"
            
            //document.getElementById("header").innerHTML  = "Choose..." // sHTML1

            bIsPageLoaded=true
        }
    }

    function hideChooser() {
        crossobject.visibility="hidden"
        //if (crossMonthObject != null){crossMonthObject.visibility="hidden"}
        //if (crossYearObject != null){crossYearObject.visibility="hidden"}

        chooser_showElement( 'SELECT' );
        chooser_showElement( 'APPLET' );
    }



    function constructMenu () {
        /*
        var aNumDays = Array (31,0,31,30,31,30,31,31,30,31,30,31)

        var dateMessage
        var startDate = new Date (yearSelected,monthSelected,1)
        var endDate

        if (monthSelected==1)
        {
            endDate = new Date (yearSelected,monthSelected+1,1);
            endDate = new Date (endDate - (24*60*60*1000));
            numDaysInMonth = endDate.getDate()
        }
        else
        {
            numDaysInMonth = aNumDays[monthSelected];
        }

        datePointer = 0
        dayPointer = startDate.getDay() - startAtDay
        
        if (dayPointer<0)
        {
            dayPointer = 6
        }

        sHTML = "<table  border=0 style='font-family:verdana;font-size:10px;'><tr>"

        if (shouldShowWeekNumber==1)
        {
            sHTML += "<td width=27><b>" + weekString + "</b></td><td width=1 rowspan=7 bgcolor='#d0d0d0' style='padding:0px'><img src='"+imageDir+"divider.gif' width=1></td>"
        }

        for (i=0; i<7; i++) {
            sHTML += "<td width='27' align='right'><B>"+ chooser_dayName[i]+"</B></td>"
        }
        sHTML +="</tr><tr>"
        
        if (shouldShowWeekNumber==1)
        {
            sHTML += "<td align=right>" + WeekNbr(startDate) + "&nbsp;</td>"
        }

        for ( var i=1; i<=dayPointer;i++ )
        {
            sHTML += "<td>&nbsp;</td>"
        }
    
        for ( datePointer=1; datePointer<=numDaysInMonth; datePointer++ )
        {
            dayPointer++;
            sHTML += "<td align=right>"
            sStyle=styleAnchor
            if ((datePointer==odateSelected) && (monthSelected==omonthSelected) && (yearSelected==oyearSelected))
            { sStyle+=styleLightBorder }

            sHint = ""
            for (k=0;k<chooser_HolidaysCounter;k++)
            {
                if ((parseInt(chooser_Holidays[k].d)==datePointer)&&(parseInt(chooser_Holidays[k].m)==(monthSelected+1)))
                {
                    if ((parseInt(chooser_Holidays[k].y)==0)||((parseInt(chooser_Holidays[k].y)==yearSelected)&&(parseInt(chooser_Holidays[k].y)!=0)))
                    {
                        sStyle+="background-color:#FFDDDD;"
                        sHint+=sHint==""?chooser_Holidays[k].desc:"\n"+chooser_Holidays[k].desc
                    }
                }
            }

            var regexp= /\"/g
            sHint=sHint.replace(regexp,"&quot;")

            dateMessage = "onmousemove='window.status=\"Boo\"' onmouseout='window.status=\"\"' "

            if ((datePointer==current_day)&&(monthSelected==current_month)&&(yearSelected==current_year))
            { sHTML += "<b><a "+dateMessage+" title=\"" + sHint + "\" style='"+sStyle+"' href='javascript:dateSelected="+datePointer+";closeChooser();'><font color=#ff0000>&nbsp;" + datePointer + "</font>&nbsp;</a></b>"}
            else if (dayPointer % 7 == (startAtDay * -1)+1)
            { sHTML += "<a "+dateMessage+" title=\"" + sHint + "\" style='"+sStyle+"' href='javascript:dateSelected="+datePointer + ";closeChooser();'>&nbsp;<font color=#909090>" + datePointer + "</font>&nbsp;</a>" }
            else
            { sHTML += "<a "+dateMessage+" title=\"" + sHint + "\" style='"+sStyle+"' href='javascript:dateSelected="+datePointer + ";closeChooser();'>&nbsp;" + datePointer + "&nbsp;</a>" }

            sHTML += ""
            if ((dayPointer+startAtDay) % 7 == startAtDay) {
                sHTML += "</tr><tr>" 
                if ((shouldShowWeekNumber==1)&&(datePointer<numDaysInMonth))
                {
                    sHTML += "<td align=right>" + (WeekNbr(new Date(yearSelected,monthSelected,datePointer+1))) + "&nbsp;</td>"
                }
            }
        }
        "<a "+dateMessage+" title=\"" + sHint + "\" style='"+sStyle+"' href='javascript:dateSelected="+datePointer + ";closeCalendar();'>&nbsp;<font color=#909090>" + datePointer + "</font>&nbsp;</a>"
        */
        var list ="";
        if(popuplist.length > 40) num_elements=40; else num_elements=popuplist.length;
        list = list + '<ul style="list-style-type: square; ;padding: 5px;">'
        for(count=0; count < num_elements; count++) {
          list = list + '<li><a title="Service" href="javascript:rowSelected=\''+popuplist[count].replace('\'',"\\\'")+'\';closeChooser();">' + popuplist[count] + '</a></li>';
        }
        list = list + '</ul>'
        document.getElementById("menu").innerHTML   = list; //"hi there"; //sHTML
        //document.getElementById("spanMonth").innerHTML = "&nbsp;" + chooser_monthName[monthSelected] + "&nbsp;<IMG id='changeMonth' SRC='"+imageDir+"drop1.gif' WIDTH='12' HEIGHT='10' BORDER=0 align=absmiddle>"
        //document.getElementById("spanYear").innerHTML = "&nbsp;" + yearSelected + "&nbsp;<IMG id='changeYear' SRC='"+imageDir+"drop1.gif' WIDTH='12' HEIGHT='10' BORDER=0 align=absmiddle>"
    }

    function popUpChooser(ctl_chooser, ctl_chooser2) {
        var leftpos=0
        var toppos=0
        var format="yyyy-m-dd"
        var frameOffSetLeft=-1;
        var frameOffSetTop=-1;
        var aTag="";
                
        if (bIsPageLoaded)
        {
            if ( crossobject.visibility == "hidden" ) {
                selectValue = ctl_chooser2
                dateFormat=format;

                formatChar = " "
                aFormat = dateFormat.split(formatChar)
                if (aFormat.length<3)
                {
                    formatChar = "/"
                    aFormat = dateFormat.split(formatChar)
                    if (aFormat.length<3)
                    {
                        formatChar = "."
                        aFormat = dateFormat.split(formatChar)
                        if (aFormat.length<3)
                        {
                            formatChar = "-"
                            aFormat = dateFormat.split(formatChar)
                            if (aFormat.length<3)
                            {
                                // invalid date format
                                formatChar=""
                            }
                        }
                    }
                }

                tokensChanged = 0
                if ( formatChar != "" )
                {
                    // use user's date
                    aData = ctl_chooser2.value.split(formatChar)

                    for (i=0;i<3;i++)
                    {
                        if ((aFormat[i]=="d") || (aFormat[i]=="dd"))
                        {
                            dateSelected = parseInt(aData[i], 10)
                            tokensChanged ++
                        }
                        else if ((aFormat[i]=="m") || (aFormat[i]=="mm"))
                        {
                            monthSelected = parseInt(aData[i], 10) - 1
                            tokensChanged ++
                        }
                        else if (aFormat[i]=="yyyy")
                        {
                            yearSelected = parseInt(aData[i], 10)
                            tokensChanged ++
                        }
                        else if (aFormat[i]=="mmm")
                        {
                            for (j=0; j<12; j++)
                            {
                                if (aData[i]==chooser_monthName[j])
                                {
                                    monthSelected=j
                                    tokensChanged ++
                                }
                            }
                        }
                    }
                }

                if ((tokensChanged!=3)||isNaN(dateSelected)||isNaN(monthSelected)||isNaN(yearSelected))
                {
                    dateSelected = current_day
                    monthSelected = current_month
                    yearSelected = current_year
                }

                odateSelected=dateSelected
                omonthSelected=monthSelected
                oyearSelected=yearSelected

                aTag = ctl_chooser
                do {
                    aTag = aTag.offsetParent;
                    leftpos += aTag.offsetLeft;
                    toppos += aTag.offsetTop;
                } while(aTag.tagName!="BODY");

                crossobject.left = fixedAtX==-1 ? ctl_chooser.offsetLeft + leftpos + frameOffSetLeft : fixedAtX
                crossobject.top = fixedAtX==-1 ? ctl_chooser.offsetTop + toppos + frameOffSetTop + ctl_chooser.offsetHeight : fixedAtX
                constructMenu (); //(1, monthSelected, yearSelected);
                crossobject.visibility=(is_dom||is_ie)? "visible" : "show"

                chooser_hideElement( 'SELECT', document.getElementById("calendar") );
                chooser_hideElement( 'APPLET', document.getElementById("calendar") );

                boolShow = true;
            }
            else
            {
                hideChooser()
                if (ctlcur!=ctl_chooser) {popUpChooser(ctl_chooser, ctl_chooser2)}
            }
            ctlcur = ctl_chooser
        }
    }

    //document.onkeypress = function hidechoose1 () {
    //    if (event.keyCode==27)
    //    {
    //        hideChooser()
            //hideCalendar()
     //   }
    //}
    //document.onclick = function hidechoose2 () {
    //    if (!boolShow)
    //    {
    //        hideChooser()
            //hideCalendar()
    //    }
    //    boolShow = false
    //}


    if(is_ie)
    {
        init_chooser()
    }
    else
    {
        init_chooser() //window.onload=init_chooser
    }