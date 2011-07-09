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
$start_end_RET = DBGet(DBQuery("SELECT TITLE,VALUE FROM PROGRAM_CONFIG WHERE SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' AND PROGRAM='eligibility' AND TITLE IN ('START_DAY','END_DAY')"));
if(count($start_end_RET))
{
	foreach($start_end_RET as $value)
		$$value['TITLE'] = $value['VALUE'];
}

switch(date('D'))
{
	case 'Mon':
	$today = 1;
	break;
	case 'Tue':
	$today = 2;
	break;
	case 'Wed':
	$today = 3;
	break;
	case 'Thu':
	$today = 4;
	break;
	case 'Fri':
	$today = 5;
	break;
	case 'Sat':
	$today = 6;
	break;
	case 'Sun':
	$today = 7;
	break;
}

$start = time() - ($today-$START_DAY)*60*60*24;
$end = time();

if(!$_REQUEST['start_date'])
{
	$start_time = $start;
	$start_date = strtoupper(date('d-M-y',$start_time));
	$end_date = strtoupper(date('d-M-y',$end));
}
else
{
	$start_time = $_REQUEST['start_date'];
	$start_date = strtoupper(date('d-M-y',$start_time));
	$end_date = strtoupper(date('d-M-y',$start_time+60*60*24*7));
}


DrawBC("Eligibility > ".ProgramTitle());
if($_REQUEST['search_modfunc'] || User('PROFILE')=='parent' || User('PROFILE')=='student')
{
	$tmp_PHP_SELF = PreparePHP_SELF();
	echo "<FORM name=stud_list id=stud_list action=$tmp_PHP_SELF method=POST>";

	$begin_year = DBGet(DBQuery("SELECT min(unix_timestamp(SCHOOL_DATE)) as SCHOOL_DATE FROM ATTENDANCE_CALENDAR WHERE SCHOOL_ID='".UserSchool()."' AND SYEAR='".UserSyear()."'"));
	$begin_year = $begin_year[1]['SCHOOL_DATE'];
	
	$date_select = "<OPTION value=$start>".date('M d, Y',$start).' - '.date('M d, Y',$end).'</OPTION>';
	
	if($begin_year != "" || !begin_year)
	{
	for($i=$start-(60*60*24*7);$i>=$begin_year;$i-=(60*60*24*7))
		$date_select .= "<OPTION value=$i".(($i+86400>=$start_time && $i-86400<=$start_time)?' SELECTED':'').">".date('M d, Y',$i).' - '.date('M d, Y',($i+1+(($END_DAY-$START_DAY))*60*60*24)).'</OPTION>';
	}
	
	DrawHeaderHome('<SELECT name=start_date>'.$date_select.'</SELECT>'.$period_select,'<INPUT type=submit class=btn_medium value=Go onclick=\'formload_ajax("stud_list");\'>');
	echo '</FORM>';
}

$extra['SELECT'] = ",e.ELIGIBILITY_CODE,c.TITLE as COURSE_TITLE";
$extra['FROM'] = ",ELIGIBILITY e,COURSES c,COURSE_PERIODS cp";
$extra['WHERE'] = " AND e.STUDENT_ID=ssm.STUDENT_ID AND e.COURSE_PERIOD_ID=cp.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND e.SCHOOL_DATE BETWEEN '$start_date' AND '$end_date'";

$extra['functions'] = array('ELIGIBILITY_CODE'=>'_makeLower');
$extra['group']	= array('STUDENT_ID');

Widgets('eligibility');
Widgets('activity');
Widgets('course');

if(!$_REQUEST['search_modfunc'] && User('PROFILE')!='parent' && User('PROFILE')!='student')
{
	$extra['new'] = true;
	Search('student_id',$extra);
}
else
{
	$RET = GetStuList($extra);
	
	$columns = array('FULL_NAME'=>'Student','COURSE_TITLE'=>'Course','ELIGIBILITY_CODE'=>'Grade');
	ListOutput($RET,$columns,'','',array(),array('STUDENT_ID'=>array('FULL_NAME','STUDENT_ID')));
}

function _makeLower($word)
{
	return ucwords(strtolower($word));
}
?>