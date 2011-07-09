
function Validator(frmname)
{
  this.formobj=document.forms[frmname];
	if(!this.formobj)
	{
	  alert("BUG: couldnot get Form object "+frmname);
		return;
	}
	if(this.formobj.onsubmit)
	{
	 this.formobj.old_onsubmit = this.formobj.onsubmit;
	 this.formobj.onsubmit=null;
	}
	else
	{
	 this.formobj.old_onsubmit = null;
	}
	this.formobj.onsubmit=form_submit_handler;
	this.addValidation = add_validation;
	this.setAddnlValidationFunction=set_addnl_vfunction;
	this.clearAllValidations = clear_all_validations;
}
function set_addnl_vfunction(functionname)
{
  this.formobj.addnlvalidation = functionname;
}
function clear_all_validations()
{
	for(var itr=0;itr < this.formobj.elements.length;itr++)
	{
		this.formobj.elements[itr].validationset = null;
	}
}
function form_submit_handler()
{
	for(var itr=0;itr < this.elements.length;itr++)
	{
		if(this.elements[itr].validationset &&
	   !this.elements[itr].validationset.validate())
		{
		  return false;
		}
	}
	if(this.addnlvalidation)
	{
	  str =" var ret = "+this.addnlvalidation+"()";
	  eval(str);
    if(!ret) return ret;
	}
	
/*if(ajaxform(this, this.action) =='failed')
	return true;
	
	return false;*/
}
function add_validation(itemname,descriptor,errstr)
{
  if(!this.formobj)
	{
	  alert("BUG: the form object is not set properly");
		return;
	}
	var itemobj = this.formobj[itemname];
  if(!itemobj)
	{
		return;
	}
	if(!itemobj.validationset)
	{
	  itemobj.validationset = new ValidationSet(itemobj);
	}
  itemobj.validationset.add(descriptor,errstr);
}
function ValidationDesc(inputitem,desc,error)
{
  this.desc=desc;
	this.error=error;
	this.itemobj = inputitem;
	this.validate=vdesc_validate;
}
function vdesc_validate()
{
 if(!V2validateData(this.desc,this.itemobj,this.error))
 {
    this.itemobj.focus();
		return false;
 }
 return true;
}
function ValidationSet(inputitem)
{
    this.vSet=new Array();
	this.add= add_validationdesc;
	this.validate= vset_validate;
	this.itemobj = inputitem;
}
function add_validationdesc(desc,error)
{
  this.vSet[this.vSet.length]= 
	  new ValidationDesc(this.itemobj,desc,error);
}
function vset_validate()
{
   for(var itr=0;itr<this.vSet.length;itr++)
	 {
	   if(!this.vSet[itr].validate())
		 {
		   return false;
		 }
	 }
	 return true;
}
function validateEmailv2(email)
{
    if(email.length <= 0)
	{
	  return true;
	}
    var splitted = email.match("^(.+)@(.+)$");
    if(splitted == null) return false;
    if(splitted[1] != null )
    {
      var regexp_user=/^\"?[\w-_\.]*\"?$/;
      if(splitted[1].match(regexp_user) == null) return false;
    }
    if(splitted[2] != null)
    {
      var regexp_domain=/^[\w-\.]*\.[A-Za-z]{2,4}$/;
      if(splitted[2].match(regexp_domain) == null) 
      {
	    var regexp_ip =/^\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]$/;
	    if(splitted[2].match(regexp_ip) == null) return false;
      }
      return true;
    }
return false;
}


function validateurl(url)
{
    if(url.length <= 0)
	{
	  return true;
	}
    var urlRegxp = /^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}([\w]+)(.[\w]+){1,2}$/;
    if (urlRegxp.test(url) != true)
	{
		return false;
	}
	else
	{
		return true;
	}
	
return false;
}


function V2validateData(strValidateStr,objValue,strError) 
{ 
    var epos = strValidateStr.search("="); 
    var  command  = ""; 
    var  cmdvalue = ""; 
    if(epos >= 0) 
    { 
     command  = strValidateStr.substring(0,epos); 
     cmdvalue = strValidateStr.substr(epos+1); 
    } 
    else 
    { 
     command = strValidateStr; 
    } 
	strError=escape(strError);
	strError=unescape(strError);
    switch(command) 
    { 
        case "req": 
        case "required": 
         { 
           if(eval(objValue.value.length) == 0) 
           { 
              if(!strError || strError.length ==0) 
              { 
                strError = objValue.name + " : Required Field"; 
              }
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
              return false; 
           }
		   else
		   {
		   var flag=0;
		   var strarr=new Array();
		   strarr=objValue.value.split(" ");
		   for(var i=0;i<=objValue.value.length;i++)
		   {
			if(strarr[i]=="")
			{
		     flag=1;
			 break;
			}
		   }
		   if(flag==1)
		   {
			if(!strError || strError.length ==0) 
              { 
                strError = objValue.name + " : Required Field"; 
              }
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
              return false;    
		   }
		   }
           break;             
         }
        case "maxlength": 
        case "maxlen": 
          { 
             if(eval(objValue.value.length) >  eval(cmdvalue)) 
             { 
               if(!strError || strError.length ==0) 
               { 
                 strError = objValue.name + " : "+cmdvalue+" characters maximum "; 
               }
			   document.getElementById('divErr').innerHTML="<b><font color=red>"+ strError + "\n[Current length = " + objValue.value.length + " ]" +"</font></b>";
               return false; 
             }
             break; 
          }
        case "minlength": 
        case "minlen": 
           { 
             if(eval(objValue.value.length) <  eval(cmdvalue)) 
             { 
               if(!strError || strError.length ==0) 
               { 
                 strError = objValue.name + " : " + cmdvalue + " characters minimum  "; 
               }      
			   document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n[Current length = " + objValue.value.length + " ]"+"</font></b>";
               return false;                 
             }
             break; 
            }
        case "alnum": 
        case "alphanumeric": 
           { 
              var charpos = objValue.value.search("[^A-Za-z0-9. ]"); 
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
               if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only alpha-numeric characters allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break; 
           }
		case "grade_title": 
           { 
              var charpos = objValue.value.search("[^A-Za-z0-9.+- ]"); 
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
               if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only alpha-numeric characters allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break; 
           }
        case "num": 
        case "numeric": 
           { 
              var charpos = objValue.value.search("[^0-9]"); 
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
                if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only digits allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break;               
           }
        case "dec": 
        case "decimal": 
           { 
              var charpos = objValue.value.search("[^0-9.]"); 
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
                if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only digits allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break;               
           }
        case "ph":
        case "phone":
           { 
              var charpos = objValue.value.search("[^0-9-\(\)\, ]");
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
                if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only valid phone number allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break;               
           }
        case "alphabetic": 
        case "alpha": 
           { 
			 var charpos = objValue.value.search("[^A-Za-z ]");
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
                  if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only alphabetic characters allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break; 
           }
        case "alphaspchar": 
           { 
			 var charpos = objValue.value.search("[^A-Za-z\-\' ]");
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
                  if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": Only alphabetic characters allowed "; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
              break; 
           }
		case "alnumhyphen":
			{
              var charpos = objValue.value.search("[^A-Za-z0-9\-_]"); 
              if(objValue.value.length > 0 &&  charpos >= 0) 
              { 
                  if(!strError || strError.length ==0) 
                { 
                  strError = objValue.name+": characters allowed are A-Z,a-z,0-9,- and _"; 
                }
				document.getElementById('divErr').innerHTML="<b><font color=red>"+strError + "\n [Error character position " + eval(charpos+1)+"]"+"</font></b>";
                return false; 
              }
			break;
			}
        case "email": 
          { 
               if(!validateEmailv2(objValue.value)) 
               { 
                 if(!strError || strError.length ==0) 
                 { 
                    strError = "Enter a valid Email address "; 
                 }
				 document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
                 return false; 
               }
           break; 
          }
		  
		
		case "url": 
          { 
               if(!validateurl(objValue.value)) 
               { 
                 if(!strError || strError.length ==0) 
                 { 
                    strError = "Enter a valid URL "; 
                 }
				 document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
                 return false; 
               }
           break; 
          }
		  
		
		  
        case "lt": 
        case "lessthan": 
         { 
            if(isNaN(objValue.value)) 
            { 
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+objValue.name+": Should be a number "+"</font></b>";
              return false; 
            }
            if(eval(objValue.value) >=  eval(cmdvalue)) 
            { 
              if(!strError || strError.length ==0) 
              { 
                strError = objValue.name + " : value should be less than "+ cmdvalue; 
              }
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
              return false;                 
             }
            break; 
         }
        case "gt": 
        case "greaterthan": 
         { 
            if(isNaN(objValue.value)) 
            { 
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+objValue.name+": Should be a number "+"</font></b>";
              return false; 
            }
             if(eval(objValue.value) <=  eval(cmdvalue)) 
             { 
               if(!strError || strError.length ==0) 
               { 
                 strError = objValue.name + " : value should be greater than "+ cmdvalue; 
               }
			   document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
               return false;                 
             }
            break; 
         }
        case "regexp": 
         { 
		 	if(objValue.value.length > 0)
			{
	            if(!objValue.value.match(cmdvalue)) 
	            { 
	              if(!strError || strError.length ==0) 
	              { 
	                strError = objValue.name+": Invalid characters found "; 
	              }
				  document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
	              return false;                   
	            }
			}
           break; 
         }
        case "dontselect": 
         { 
            if(objValue.selectedIndex == null) 
            { 
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+"BUG: dontselect command for non-select Item"+"</font></b>";
              return false; 
            } 
            if(objValue.selectedIndex == eval(cmdvalue)) 
            { 
             if(!strError || strError.length ==0) 
              { 
              strError = objValue.name+": Please Select one option "; 
              }
			  document.getElementById('divErr').innerHTML="<b><font color=red>"+strError+"</font></b>";
              return false;                                   
             } 
             break; 
         }
    }
    return true; 
}






function doDateCheck(from, to) {

if (Date.parse(from) >= Date.parse(to)) {
document.getElementById('divErr').innerHTML="<b><font color=red>"+"End date must occur after the Start date."+"</font></b>";
return false;
}
else
{
return true;
}
}



function isDate(fm,fd,fy)
{
var strdate;
strdate = Date.parse(fm.value + " " + fd.value +" " + ChangeYear(fy.value));

if(isNaN(strdate))
{

return false;
}
else
{
return true;
}
}



function ChangeYear(year)
{
var strYear;
strYear = year;
 
if (strYear.length == 2) {
if (00 <= strYear && strYear <25)
{
strYear = '20' + strYear;
}
else
{
strYear = '19' + strYear;
}
} 

return strYear;

}



function CheckDate(fm, fd, fy, tm, td, ty)
{
var from;
var to;


from = fm.value + " " + fd.value +" " + ChangeYear(fy.value);
to = tm.value + " " + td.value +" " + ChangeYear(ty.value);
if (false==doDateCheck(from, to))
	return false;
else
	return true;

}

/* *************************************************** Check Time Start ****************************************************** */

function CheckTime(fd, fh, fm, fp, td, th, tm, tp)
{
var from;
var to;
var p1;
var p2;

if(fp.value=='AM')
	p1=1;
if(fp.value=='PM')
	p1=2;
if(tp.value=='AM')
	p2=1;
if(tp.value=='PM')
	p2=2;

if(parseFloat(fd.value) == parseFloat(td.value))
{
	if(p1 > p2)
	{
		document.getElementById('divErr').innerHTML="<b><font color=red>"+"Starting time must occur after the ending date."+"</font></b>";
		return false;
	}

	if(p1 == p2)
	{
		if((parseFloat(fh.value) > parseFloat(th.value)))
		{
			document.getElementById('divErr').innerHTML="<b><font color=red>"+"Starting time must occur after the ending date."+"</font></b>";
			return false;
		}
		
		if(parseFloat(fh.value) == parseFloat(th.value))
		{
			if(parseFloat(fm.value) > parseFloat(tm.value))
			{
				document.getElementById('divErr').innerHTML="<b><font color=red>"+"Starting time must occur after the ending date."+"</font></b>";
				return false;
			}
		}
	}
	
}
return true;
}
/* **************************************************** Check Time End ****************************************************** */

/******************************************  For SchoolSetup Marking Periods Start  ********************************************/

function doDateCheckMar(from, to) {

if (Date.parse(from) > Date.parse(to)) {
document.getElementById('divErr').innerHTML="<b><font color=red>"+"Grade Posting Begins date can not be occur before the Begins date."+"</font></b>";
return false;
}
else
{
return true;
}
}


function CheckDateMar(fm, fd, fy, tm, td, ty)
{
var from;
var to;


from = fm.value + " " + fd.value +" " + ChangeYear(fy.value);
to = tm.value + " " + td.value +" " + ChangeYear(ty.value);
if (false==doDateCheckMar(from, to))
	return false;
else
	return true;

}



function doDateCheckMarEnd(from, to) {

if (Date.parse(from) > Date.parse(to)) {
document.getElementById('divErr').innerHTML="<b><font color=red>"+"Grade Posting End date can not be occur after the End date."+"</font></b>";
return false;
}
else
{
return true;
}
}


function CheckDateMarEnd(fm, fd, fy, tm, td, ty)
{
var from;
var to;


from = fm.value + " " + fd.value +" " + ChangeYear(fy.value);
to = tm.value + " " + td.value +" " + ChangeYear(ty.value);
if (false==doDateCheckMarEnd(from, to))
	return false;
else
	return true;

}

function CheckBirthDate(fm,fd,fy) {
var strdate;
strdate = Date.parse(fm.value + " " + fd.value +" " + ChangeYear(fy.value));
var today = new Date();

if(isNaN(strdate))
{
document.getElementById('divErr').innerHTML="<b><font color=red>"+"Enter a valid Date of Birth"+"</font></b>";
return false;
}
else
{
if (strdate > Date.parse(today)) 
{
document.getElementById('divErr').innerHTML="<b><font color=red>"+"Invalid Birth Date"+"</font></b>";
return false;
}
else
return true;
}

}

function numberOnly(event) {
	var keynum = event.keyCode || e.which;
	if (((keynum > 47) && (keynum < 58)) || (keynum == 8) || (keynum == 9) || (keynum == 46) || ((keynum > 95) && (keynum < 106)) || ((keynum > 36) && (keynum < 41)))
	return true;
	else
	return false;
}

function validate_chk(chk_box)
{
  var boolIsChecked = chk_box.checked;
  
  if (boolIsChecked == false)
  {
  	return false;
  }
  else
  {
  	return true;
  }
  
}
