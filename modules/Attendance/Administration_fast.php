<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************
include('../../Redirect_modules.php');
if($_REQUEST['month_date'] && $_REQUEST['day_date'] && $_REQUEST['year_date'])
	$date = $_REQUEST['day_date'].'-'.$_REQUEST['month_date'].'-'.$_REQUEST['year_date'];
else
	$date = DBDate();

$current_RET = DBGet(DBQuery("SELECT ATTENDANCE_TEACHER_CODE,ATTENDANCE_CODE,ATTENDANCE_REASON,STUDENT_ID,ADMIN,COURSE_PERIOD_ID FROM ATTENDANCE_PERIOD WHERE SCHOOL_DATE='".$date."'"),array(),array('STUDENT_ID','COURSE_PERIOD_ID'));
if($_REQUEST['attendance'] && ($_POST['attendance'] || $_REQUEST['ajax']) && AllowEdit())
{
	foreach($_REQUEST['attendance'] as $student_id=>$values)
	{
		foreach($values as $period=>$columns)
		{
			if($current_RET[$student_id][$period])
			{
				$sql = "UPDATE ATTENDANCE_PERIOD SET ADMIN='Y',";
				
				foreach($columns as $column=>$value)
					$sql .= $column."='".str_replace("\'","''",$value)."',";

				$sql = substr($sql,0,-1) . " WHERE SCHOOL_DATE='".$date."' AND COURSE_PERIOD_ID='".$period."' AND STUDENT_ID='".$student_id."'";
				DBQuery($sql);
			}
			else
			{
				$period_id = DBGet(DBQuery("SELECT PERIOD_ID FROM COURSE_PERIODS WHERE COURSE_PERIOD_ID='$period'"));
				$period_id = $period_id[1]['PERIOD_ID'];

				$sql = "INSERT INTO ATTENDANCE_PERIOD ";
	
				$fields = 'STUDENT_ID,SCHOOL_DATE,PERIOD_ID,MARKING_PERIOD_ID,COURSE_PERIOD_ID,ADMIN,';
				$values = "'".$student_id."','".$date."','".$period_id."','".GetCurrentMP('QTR',$date)."','".$period."','Y',";
	
				$go = 0;
				foreach($columns as $column=>$value)
				{
					if($value)
					{
						$fields .= $column.',';
						$values .= "'".str_replace("\'","''",$value)."',";
						$go = true;
					}
				}
				$sql .= '(' . substr($fields,0,-1) . ') values(' . substr($values,0,-1) . ')';
				
				if($go)
					DBQuery($sql);
			}
		}
		UpdateAttendanceDaily($student_id,$date);
	}
	$current_RET = DBGet(DBQuery("SELECT ATTENDANCE_TEACHER_CODE,ATTENDANCE_CODE,ATTENDANCE_REASON,STUDENT_ID,ADMIN,COURSE_PERIOD_ID FROM ATTENDANCE_PERIOD WHERE SCHOOL_DATE='".$date."'"),array(),array('STUDENT_ID','COURSE_PERIOD_ID'));
	unset($_REQUEST['attendance']);
}

$codes_RET = DBGet(DBQuery("SELECT ID,SHORT_NAME,TITLE FROM ATTENDANCE_CODES WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."'"));
$periods_RET = DBGet(DBQuery("SELECT PERIOD_ID,SHORT_NAME,TITLE FROM SCHOOL_PERIODS WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."' ORDER BY SORT_ORDER"));

//if(isset($_REQUEST['student_id']) && $_REQUEST['student_id']!='new')
if(isset($_REQUEST['student_id']) && optional_param('student_id','',PARAM_ALPHANUM)!='new')
{
	//if(UserStudentID() != $_REQUEST['student_id'])
	if(UserStudentID() !=optional_param('student_id','',PARAM_ALPHANUM))
	{
		//$_SESSION['student_id'] = $_REQUEST['student_id'];
		$_SESSION['student_id'] = optional_param('student_id','',PARAM_ALPHANUM);
		echo '<script language=JavaScript>parent.side.location="'.$_SESSION['Side_PHP_SELF'].'";</script>';
	}
	
	$functions = array('ATTENDANCE_CODE'=>'_makeCodePulldown','ATTENDANCE_TEACHER_CODE'=>'_makeCode','ATTENDANCE_REASON'=>'_makeReasonInput');
	/*$schedule_RET = DBGet(DBQuery("SELECT 
										s.STUDENT_ID,concat(c.TITLE, ' - ', s.COURSE_WEIGHT) AS COURSE,cp.PERIOD_ID,cp.COURSE_PERIOD_ID,p.TITLE AS PERIOD_TITLE,
										'' AS ATTENDANCE_CODE,'' AS ATTENDANCE_TEACHER_CODE,'' AS ATTENDANCE_REASON 
									FROM 
										SCHEDULE s,COURSES c,COURSE_PERIODS cp,SCHOOL_PERIODS p 
									WHERE 
										s.SYEAR='".UserSyear()."' AND s.SCHOOL_ID='".UserSchool()."' AND s.MARKING_PERIOD_ID IN (".GetAllMP('QTR',GetCurrentMP('QTR',$date)).")
										AND s.COURSE_ID=c.COURSE_ID 
										AND s.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.PERIOD_ID=p.PERIOD_ID AND cp.DOES_ATTENDANCE='Y'
										AND s.STUDENT_ID='".$_REQUEST['student_id']."' AND ('$date' BETWEEN s.START_DATE AND s.END_DATE OR s.END_DATE IS NULL)
									ORDER BY p.SORT_ORDER"),$functions);
	*/
								
	$schedule_RET = DBGet(DBQuery("SELECT 
										s.STUDENT_ID,concat(c.TITLE, ' - ', s.COURSE_WEIGHT) AS COURSE,cp.PERIOD_ID,cp.COURSE_PERIOD_ID,p.TITLE AS PERIOD_TITLE,
										'' AS ATTENDANCE_CODE,'' AS ATTENDANCE_TEACHER_CODE,'' AS ATTENDANCE_REASON 
									FROM 
										SCHEDULE s,COURSES c,COURSE_PERIODS cp,SCHOOL_PERIODS p 
									WHERE 
										s.SYEAR='".UserSyear()."' AND s.SCHOOL_ID='".UserSchool()."' AND s.MARKING_PERIOD_ID IN (".GetAllMP('QTR',GetCurrentMP('QTR',$date)).")
										AND s.COURSE_ID=c.COURSE_ID 
										AND s.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.PERIOD_ID=p.PERIOD_ID AND cp.DOES_ATTENDANCE='Y'
										AND s.STUDENT_ID='".optional_param('student_id','',PARAM_ALPHANUM)."' AND ('$date' BETWEEN s.START_DATE AND s.END_DATE OR s.END_DATE IS NULL)
									ORDER BY p.SORT_ORDER"),$functions);
									
			
	$columns = array('PERIOD_TITLE'=>'Period','COURSE'=>'Course','ATTENDANCE_CODE'=>'Attendance Code','ATTENDANCE_TEACHER_CODE'=>'Teacher\'s Entry','ATTENDANCE_REASON'=>'Comments');
	//echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=student&student_id=$_REQUEST[student_id] method=POST>";
	echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=student&student_id=".optional_param('student_id','',PARAM_ALPHANUM)." method=POST>";
	DrawHeader(ProgramTitle(),'<INPUT type=submit value=Update>');
	DrawHeader(PrepareDate($date,'_date'));
	ListOutput($schedule_RET,$columns,'Course','Courses');
	echo '</FORM>';
}
else
{
	$extra['WHERE'] = " AND EXISTS (SELECT '' FROM ATTENDANCE_PERIOD ap,ATTENDANCE_CODES ac WHERE ap.SCHOOL_DATE='".$date."' AND ap.STUDENT_ID=ssm.STUDENT_ID AND ap.ATTENDANCE_CODE=ac.ID AND ac.SCHOOL_ID=ssm.SCHOOL_ID AND ac.SYEAR=ssm.SYEAR ";
	if(count($_REQUEST['codes']))
	{
		$REQ_codes = $_REQUEST['codes'];
		foreach($REQ_codes as $key=>$value)
		{
			if(!$value)
				unset($REQ_codes[$key]);
			elseif($value=='A')
				$abs = true;
		}
	}
	else
		$abs = true;
	if(count($REQ_codes) && !$abs)
	{
		$extra['WHERE'] .= "AND ac.ID IN (";
		foreach($REQ_codes as $code)
			$extra['WHERE'] .= "'".$code."',";
		$extra2['WHERE'] = $extra['WHERE'] = substr($extra['WHERE'],0,-1) . ')';
	}
	elseif($abs)
	{
		$RET = DBGet(DBQuery("SELECT ID FROM ATTENDANCE_CODES WHERE SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' AND (DEFAULT_CODE!='Y' OR DEFAULT_CODE IS NULL)"));
		if(count($RET))
		{
			$extra['WHERE'] .= "AND ac.ID IN (";
			foreach($RET as $code)
				$extra['WHERE'] .= "'".$code['ID']."',";
		
			$extra2['WHERE'] = $extra['WHERE'] = substr($extra['WHERE'],0,-1) . ')';	
		}
	}
	$extra['WHERE'] .= ')';
	$extra2['WHERE'] .= ')';

	$extra2['SELECT'] .= ',p.PERSON_ID,p.FIRST_NAME,p.LAST_NAME,sjp.STUDENT_RELATION,pjc.TITLE,pjc.VALUE,a.PHONE,sjp.ADDRESS_ID ';
	$extra2['FROM'] .= ',ADDRESS a,PEOPLE p,PEOPLE_JOIN_CONTACTS pjc,STUDENTS_JOIN_PEOPLE sjp,STUDENTS_JOIN_ADDRESS sja ';
	$extra2['WHERE'] .= ' AND sja.STUDENT_ID=ssm.STUDENT_ID AND sjp.STUDENT_ID=sja.STUDENT_ID AND pjc.PERSON_ID=sjp.PERSON_ID AND p.PERSON_ID=sjp.PERSON_ID AND sjp.ADDRESS_ID=a.ADDRESS_ID AND (sjp.CUSTODY=\'Y\' OR sjp.EMERGENCY=\'Y\') ';
	$extra2['group'] = array('STUDENT_ID','PERSON_ID');
	$contacts_RET = GetStuList($extra2);

	$columns = array();
	$extra['SELECT'] .= ',NULL AS STATE_VALUE,NULL AS PHONE';
	$extra['functions']['PHONE'] = '_makePhone';
	$extra['functions']['STATE_VALUE'] = '_makeStateValue';
	$extra['columns_before']['PHONE'] = 'Contact';
	$extra['columns_after']['STATE_VALUE'] = 'Present';
	$extra['BackPrompt'] = false;
	$extra['Redirect'] = false;
	$extra['new'] = true;
	foreach($periods_RET as $period)
	{
		$extra['SELECT'] .= ",'' AS PERIOD_".$period['PERIOD_ID'];
		$extra['functions']['PERIOD_'.$period['PERIOD_ID']] = '_makeCodePulldown';
		$extra['columns_after']['PERIOD_'.$period['PERIOD_ID']] = $period['SHORT_NAME'];
	}

	echo "<FORM action=Modules.php?modname=$_REQUEST[modname] method=POST>";
	DrawHeader(ProgramTitle(),'<INPUT type=submit value=Update>');
	
	if($REQ_codes)
	{
		foreach($REQ_codes as $code)
			$code_pulldowns .= _makeCodeSearch($code);
	}
	elseif($abs)
		$code_pulldowns = _makeCodeSearch('A');
	else
		$code_pulldowns = _makeCodeSearch();
	if(UserStudentID())
		$current_student_link = "<A HREF=Modules.php?modname=$_REQUEST[modname]&modfunc=student&month_date=$_REQUEST[month_date]&day_date=$_REQUEST[day_date]&year_date=$_REQUEST[year_date]&student_id=".UserStudentID().">Current Student</A></TD><TD>";
	DrawHeader(PrepareDate($date,'_date'),'<TABLE><TR><TD>'.$current_student_link.button('add','',"# onclick='javascript:addHTML(\"".str_replace('"','\"',_makeCodeSearch())."\",\"code_pulldowns\"); return false;'").'</TD><TD><DIV id=code_pulldowns>'.$code_pulldowns.'</DIV></TD></TR></TABLE>');
	
	$_REQUEST['search_modfunc'] = 'list';
	Search('student_id',$extra);

	echo "</FORM>";
}

function _makePhone($value,$column)
{	global $THIS_RET,$contacts_RET;

	if(count($contacts_RET[$THIS_RET['STUDENT_ID']]))
	{
		foreach($contacts_RET[$THIS_RET['STUDENT_ID']] as $person)
		{
			if($person[1]['FIRST_NAME'] || $person[1]['LAST_NAME'])
				$tipmessage .= '<B>'.$person[1]['STUDENT_RELATION'].': '.$person[1]['FIRST_NAME'].' '.$person[1]['LAST_NAME'].'</B><BR>';
			$tipmessage .= '<TABLE>';
			if($person[1]['PHONE'])
				$tipmessage .= '<TR><TD align=right><font color=gray size=1 face=Verdana,Arial,Helvetica>Home Phone</font> </TD><TD><font size=1 face=Verdana,Arial,Helvetica>'.$person[1]['PHONE'].'</font></TD></TR>';
			foreach($person as $info)
			{
				if($info['TITLE'] || $info['VALUE'])
					$tipmessage .= '<TR><TD align=right><font color=gray size=1 face=Verdana,Arial,Helvetica>'.$info['TITLE'].'</font></TD><TD><font size=1 face=Verdana,Arial,Helvetica>'.$info['VALUE'].'</font></TD></TR>';
			}
			$tipmessage .= '</TABLE>';
		}
	}
	else
		$tipmessage = 'This student has no contact information.';
	return button('phone','','# onMouseOver=\'stm(["Contact Information","'.$tipmessage.'"],["white","#333366","","","",,"black","#e8e8ff","","","",,,,2,"#333366",2,,,,,"",,,,]);\' onMouseOut=\'htm()\'');
}

function _makeCodePulldown($value,$title)
{	global $THIS_RET,$codes_RET,$current_RET,$current_schedule_RET,$date;

	if(!is_array($current_schedule_RET[$THIS_RET['STUDENT_ID']]))
	{
		$current_schedule_RET[$THIS_RET['STUDENT_ID']] = DBGet(DBQuery("SELECT cp.PERIOD_ID,cp.COURSE_PERIOD_ID FROM SCHEDULE s,COURSE_PERIODS cp WHERE s.STUDENT_ID='".$THIS_RET['STUDENT_ID']."' AND s.SYEAR='".UserSyear()."' AND s.SCHOOL_ID='".UserSchool()."' AND cp.COURSE_PERIOD_ID = s.COURSE_PERIOD_ID AND cp.DOES_ATTENDANCE='Y' AND s.MARKING_PERIOD_ID IN (".GetAllMP('QTR',GetCurrentMP('QTR',$date)).") AND ('$date' BETWEEN s.START_DATE AND s.END_DATE OR s.END_DATE IS NULL)"),array(),array('PERIOD_ID'));
		if(!$current_schedule_RET[$THIS_RET['STUDENT_ID']])
			$current_schedule_RET[$THIS_RET['STUDENT_ID']] = array();
	}
	if($THIS_RET['COURSE'])
	{
		$period = $THIS_RET['COURSE_PERIOD_ID'];
		$period_id = $THIS_RET['PERIOD_ID'];
		
		foreach($codes_RET as $code)
			$options[$code['ID']] = $code['TITLE'];
	}
	else
	{
		$period_id = substr($title,7);
		$period = $current_schedule_RET[$THIS_RET['STUDENT_ID']][$period_id][1]['COURSE_PERIOD_ID'];
		
		foreach($codes_RET as $code)
			$options[$code['ID']] = $code['SHORT_NAME'];	
	}
	
	$val = $current_RET[$THIS_RET['STUDENT_ID']][$period][1]['ATTENDANCE_CODE'];

	if($current_schedule_RET[$THIS_RET['STUDENT_ID']][$period_id])
		return SelectInput($val,'attendance['.$THIS_RET['STUDENT_ID'].']['.$period.'][ATTENDANCE_CODE]','',$options);
	else
		return false;
}

function _makeCode($value,$title)
{	global $THIS_RET,$codes_RET,$current_RET;

	foreach($codes_RET as $code)
	{
		if($current_RET[$THIS_RET['STUDENT_ID']][$THIS_RET['COURSE_PERIOD_ID']][1]['ATTENDANCE_TEACHER_CODE']==$code['ID'])
			return $code['TITLE'];
	}
}

function _makeReasonInput($value,$title)
{	global $THIS_RET,$codes_RET,$current_RET;

	$val = $current_RET[$THIS_RET['STUDENT_ID']][$THIS_RET['COURSE_PERIOD_ID']][1]['ATTENDANCE_REASON'];

	return TextInput($val,'attendance['.$THIS_RET['STUDENT_ID'].']['.$THIS_RET['COURSE_PERIOD_ID'].'][ATTENDANCE_REASON]','',$options);
}

function _makeCodeSearch($value='')
{	global $codes_RET,$code_search_selected;

	$return = '<SELECT name=codes[]><OPTION value="">All</OPTION><OPTION value="A"'.(($value=='A')?' SELECTED':'').'>NP</OPTION>';
	if(count($codes_RET))
	{
		foreach($codes_RET as $code)
		{
			if($value==$code['ID'])
				$return .= "<OPTION value=$code[ID] SELECTED>$code[SHORT_NAME]</OPTION>";
			else
				$return .= "<OPTION value=$code[ID]>$code[SHORT_NAME]</OPTION>";
		}
	}
	$return .= '</SELECT>';
	
	return $return;
}

function _makeStateValue($value,$name)
{	global $THIS_RET,$date;
	
	$value = DBGet(DBQuery("SELECT STATE_VALUE FROM ATTENDANCE_DAY WHERE STUDENT_ID='$THIS_RET[STUDENT_ID]' AND SCHOOL_DATE='$date'"));
	$value  = $value[1]['STATE_VALUE'];
	
	if($value=='0.0')
		return 'None';
	elseif($value=='.5')
		return 'Half-Day';
	else
		return 'Full-Day';
}

?>