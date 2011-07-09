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
unset($_SESSION['student_id']);

if($_openSIS['modules_search'] && $extra['force_search'])
	$_REQUEST['search_modfunc'] = '';

if(Preferences('SEARCH')!='Y' && !$extra['force_search'])
	$_REQUEST['search_modfunc'] = 'list';
if($_REQUEST['search_modfunc']=='search_fnc' || !$_REQUEST['search_modfunc'])
{
	if($_SESSION['student_id'] && User('PROFILE')=='admin' && $_REQUEST['student_id']=='new')
	{
		unset($_SESSION['student_id']);

	}

	switch(User('PROFILE'))
	{
		case 'admin':
		case 'teacher':
			echo '<BR>';
			$_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
			echo '<script language=JavaScript>parent.help.location.reload();</script>';
			if(isset($_SESSION['stu_search']['sql'])){
			unset($_SESSION['stu_search']);
			}
			PopTable('header','Find a Student');
			if($extra['pdf']!=true)
			echo "<FORM name=search id=search action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]".$extra['action']." method=POST>";
			else
			echo "<FORM name=search id=search action=for_export.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]".$extra['action']." method=POST target=_blank>";
			echo '<TABLE border=0>';
			Search('general_info');
			if($extra['search'])
				echo $extra['search'];
			Search('student_fields');
			echo '<input type=hidden name=sql_save_session value=true />';
			echo '<TABLE width=100%><TR><TD align=center><BR>';
			if(User('PROFILE')=='admin')
			{
				echo '<INPUT type=checkbox name=address_group value=Y'.(Preferences('DEFAULT_FAMILIES')=='Y'?' CHECKED':'').'>Group by Family<BR>';
				echo '<INPUT type=checkbox name=_search_all_schools value=Y'.(Preferences('DEFAULT_ALL_SCHOOLS')=='Y'?' CHECKED':'').'>Search All Schools<BR>';
			}
			echo '<INPUT type=checkbox name=include_inactive value=Y>Include Inactive Students<BR>';
			echo '<BR>';
			//echo Buttons('Submit','Reset');
			if($extra['pdf']!=true)
			echo "<INPUT type=SUBMIT class=btn_medium value='Submit' >&nbsp<INPUT type=RESET class=btn_medium value='Reset'>";
			else
			echo "<INPUT type=SUBMIT class=btn_medium value='Submit'>&nbsp<INPUT type=RESET class=btn_medium value='Reset'>";
			
			echo '</TD></TR>';
			echo '</TABLE>';
			echo '</FORM>';
			// set focus to last name text box
			echo '<script type="text/javascript"><!--
				document.search.last.focus();
				--></script>';
			PopTable('footer');
		break;

		case 'parent':
		case 'student':
			echo '<BR>';
			PopTable('header','Search');
			if($extra['pdf']!=true)
			echo "<FORM action=Modules.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]".$extra['action']." method=POST>";
			else
			echo "<FORM action=for_export.php?modname=$_REQUEST[modname]&modfunc=$_REQUEST[modfunc]&search_modfunc=list&next_modname=$_REQUEST[next_modname]".$extra['action']." method=POST target=_blank>";
			echo '<TABLE border=0>';
			if($extra['search'])
				echo $extra['search'];
			echo '<TR><TD colspan=2 align=center>';
			echo '<BR>';
			echo Buttons('Submit','Reset');
			echo '</TD></TR>';
			echo '</TABLE>';
			echo '</FORM>';
			PopTable('footer');
		break;
	}
}
//if($_REQUEST['search_modfunc']=='list')
else
{
	if(!$_REQUEST['next_modname'])
		$_REQUEST['next_modname'] = 'Students/Student.php';

	if($_REQUEST['address_group'])
	{
		$extra['SELECT'] .= ',sam.ADDRESS_ID';
		if(!($_REQUEST['expanded_view']=='true' || $_REQUEST['addr'] || $extra['addr']))
			$extra['FROM'] = " LEFT OUTER JOIN STUDENTS_JOIN_ADDRESS sam ON (sam.STUDENT_ID=ssm.STUDENT_ID AND sam.RESIDENCE='Y')".$extra['FROM'];
		$extra['group'] = array('ADDRESS_ID');
	}
	$students_RET = GetStuList($extra);
	if($_REQUEST['address_group'])
	{
		// if address_group specified but only one address returned then convert to ungrouped
		if(count($students_RET)==1)
		{
			$students_RET = $students_RET[key($students_RET)];
			unset($_REQUEST['address_group']);
		}
		else
			$extra['LO_group'] = array('ADDRESS_ID');
	}
	if($extra['array_function'] && function_exists($extra['array_function']))
		if($_REQUEST['address_group'])
			foreach($students_RET as $id=>$student_RET)
				$students_RET[$id] = $extra['array_function']($student_RET);
		else
			$students_RET = $extra['array_function']($students_RET);

	$LO_columns = array('FULL_NAME'=>'Student','STUDENT_ID'=>'Student ID','GRADE_ID'=>'Grade');
	$name_link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]";
	$name_link['FULL_NAME']['variables'] = array('student_id'=>'STUDENT_ID');
	if($_REQUEST['_search_all_schools'])
		$name_link['FULL_NAME']['variables'] += array('school_id'=>'SCHOOL_ID');

	if(is_array($extra['link']))
		$link = $extra['link'] + $name_link;
	else
		$link = $name_link;

	if(is_array($extra['columns_before']))
	{
		$columns = $extra['columns_before'] + $LO_columns;
		$LO_columns = $columns;
	}
	if(is_array($extra['columns_after']))
		$columns = $LO_columns + $extra['columns_after'];
	if(!$extra['columns_before'] && !$extra['columns_after'])
		$columns = $LO_columns;

	if(count($students_RET) > 1 || $link['add'] || !$link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt']==false && count($students_RET)==0) || ($extra['Redirect']===false && count($students_RET)==1))
	{
		$tmp_REQUEST = $_REQUEST;
		unset($tmp_REQUEST['expanded_view']);

		if($_REQUEST['expanded_view']!='true' && !UserStudentID() && count($students_RET)!=0)
			DrawHeader("<div><A HREF=".PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=true class=big_font ><img src=\"themes/Blue/expanded_view.png\" />Expanded View</A></div><div class=break ></div>",$extra['header_right']);
		elseif(!UserStudentID() && count($students_RET)!=0)
			DrawHeader("<div><A HREF=".PreparePHP_SELF($tmp_REQUEST) . "&expanded_view=false class=big_font><img src=\"themes/Blue/expanded_view.png\" />Original View</A></div><div class=break ></div>",$extra['header_right']);
		DrawHeader($extra['extra_header_left'],$extra['extra_header_right']);
		DrawHeader(str_replace('<BR>','<BR> &nbsp;',substr($_openSIS['SearchTerms'],0,-4)));
		if($_REQUEST['LO_save']!='1' && !$extra['suppress_save'])
		{
			$_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars']);
			echo '<script language=JavaScript>parent.help.location.reload();</script>';
		}
		if(!$extra['singular'] || !$extra['plural'])
			if($_REQUEST['address_group'])
			{
				$extra['singular'] = 'Family';
				$extra['plural'] = 'Families';
			}
			else
			{
				$extra['singular'] = 'Student';
				$extra['plural'] = 'Students';
			}
			
		echo "<div id='students' >";
		ListOutput($students_RET,$columns,$extra['singular'],$extra['plural'],$link,$extra['LO_group'],$extra['options']);
		echo "</div>";
	}
	elseif(count($students_RET)==1)
	{
		if(count($link['FULL_NAME']['variables']))
		{
			foreach($link['FULL_NAME']['variables'] as $var=>$val)
				$_REQUEST[$var] = $students_RET['1'][$val];
		}
		if(!is_array($students_RET[1]['STUDENT_ID']))
		{
			$_SESSION['student_id'] = $students_RET[1]['STUDENT_ID'];
			$_SESSION['UserSchool'] = $students_RET[1]['LIST_SCHOOL_ID'];
			echo '<script language=JavaScript>parent.side.location="'.$_SESSION['Side_PHP_SELF'].'?modcat="+parent.side.document.forms[0].modcat.value;</script>';
			unset($_REQUEST['search_modfunc']);
		}
		if($_REQUEST['modname']!=$_REQUEST['next_modname'])
		{
			$modname = $_REQUEST['next_modname'];
			if(strpos($modname,'?'))
				$modname = substr($_REQUEST['next_modname'],0,strpos($_REQUEST['next_modname'],'?'));
			if(strpos($modname,'&'))
				$modname = substr($_REQUEST['next_modname'],0,strpos($_REQUEST['next_modname'],'&'));
			if($_REQUEST['modname'])
				$_REQUEST['modname'] = $modname;
			include('modules/'.$modname);
		}
	}
	else
		BackPrompt('No Students were found.');
}
?>