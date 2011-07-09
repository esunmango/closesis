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
if($_REQUEST['From'] && $_REQUEST['to'])
{
   $From= $_REQUEST['From'];
   $to = $_REQUEST['to'];
}
elseif($_REQUEST['month_From'] && $_REQUEST['day_From'] && $_REQUEST['year_From'])
	 {
		$_REQUEST['placed_From'] = $_REQUEST['day_From'].'-'.$_REQUEST['month_From'].'-'.$_REQUEST['year_From'];
		$From=(date('Y-m-d',strtotime($_REQUEST['placed_From'])));
	}

      if($_REQUEST['month_to'] && $_REQUEST['day_to'] && $_REQUEST['year_to'])
	 {
		$_REQUEST['placed_to'] = $_REQUEST['day_to'].'-'.$_REQUEST['month_to'].'-'.$_REQUEST['year_to'];
		$to=(date('Y-m-d',strtotime($_REQUEST['placed_to'])));
	}



 elseif($to=='')
     $to=date('Y-m-d',strtotime(DBDate()));
   $extra['WHERE1']  = " AND acc.SCHOOL_DATE>='".$From."' AND acc.SCHOOL_DATE<'".$to."'";


if(User('PROFILE')=='admin')
{
		if(!$_REQUEST['next_modname'])
			$_REQUEST['next_modname'] = 'Users/User.php';

                $ERR="";
                if($_REQUEST['day_From']=='' && $_REQUEST['day_to'])
                $ERR="please select from date";
                else if(($_REQUEST['day_From'] && $_REQUEST['day_to'])|| ($From && $to))
                $staff_RET = GetStaffList_Miss_Atn($extra);
		#if(!isset($_openSIS['DrawHeader'])) DrawHeaderHome('Please select a user');
		echo '<div><STRONG><FONT color=red>'. $ERR. '</font></strong></DIV>';
                echo "<FORM name=missingatten id=missingatten action=Modules.php?modname=$_REQUEST[modname]&func=save method=POST>";
                $header = '<table width=100%><TR>';
                $header .= '<TD style="width:30px; vertical-align:top; text-align:left; padding-top:8px;">From</TD><td valign="top" style="width:230px;">'.PrepareDate($From,'_From',true,array('short'=>true)).'</TD>';
                $header .='<TD style="width:20px; vertical-align:top; text-align:left; padding-top:8px;">To</TD><td valign="top" style="width:230px;">'.PrepareDate($to,'_to',true,array('short'=>true)).'</TD>';
                $header .= '<td align=left><INPUT type=submit class=btn_medium name=go value=Go ></td>';
                $header .= '</tr></table>';
                DrawHeaderHome($header);
                echo '</form>';
               
		if($extra['profile'])
		{
			$options = array('admin'=>'Administrator','teacher'=>'Teacher','parent'=>'Parent','none'=>'No Access');
			$singular = $options[$extra['profile']];
			$plural = $singular.($options[$extra['profile']]=='none'?'es':'s');
			$columns = array('FULL_NAME'=>$singular,'STAFF_ID'=>'Staff ID');
		}
		else
		{
			$singular = 'User';
			$plural = 'Users';
			$columns = array('FULL_NAME'=>'Staff Member','PROFILE'=>'Profile','STAFF_ID'=>'Staff ID');
		}
		if(is_array($extra['columns_before']))
			$columns = $extra['columns_before'] + $columns;
		if(is_array($extra['columns_after']))
			$columns += $extra['columns_after'];
		if(is_array($extra['link']))
			$link = $extra['link'];
		else
		{
			$link['FULL_NAME']['link'] = "Modules.php?modname=$_REQUEST[next_modname]&From=$From&to=$to";
			$link['FULL_NAME']['variables'] = array('staff_id'=>'STAFF_ID');
		}
		if(count($staff_RET))
		{
            echo '<font color=red><b>Following teachers have missing attendance!</b></font>';
            ListOutput($staff_RET,$columns,$singular,$plural,$link,false,$extra['options']);
        }
		else
		{
			echo '<font color=red><b>No teachers have missing attendance for this school!</b></font>';
		}
 }


?>
