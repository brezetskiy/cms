<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.8.2.js"></script>
    <script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css" />
    <script>
    $(function() {
        $( ".date" ).datepicker({dateFormat:"dd.mm.yy"});
    });
    $(document).ready(function(){
        
        $('a.projects').click(function() {
            
            var date_begin = $('.date_begin').val();
            var date_end = $('.date_end').val();
            
            if ( (date_begin=='') || (date_end=='') )
            {
                alert("Заполните даты");
                return false;
            };
            
            var href = $(this).attr('href');
            href = href + '&date_begin=' + date_begin + '&date_end=' + date_end;
            
            location.href = href;
            
            return false;
        });
        
    });
    </script>
<?php
print_R( date() );
/*
 * require main sape class
 */

require('../../libs/Sape.php');
//create sape user and save cookie in txt file
$sape = new SapeClient('c_format', 'ihbkfyrf', 'cookie.txt');

$user = $sape->get_user();

/*
 * projects
 */ 
echo('<center>');
echo ('List of projects');
echo('<table>');
echo('<tr><td>');
echo('<input type="text" name="date_begin" class="date date_begin" />');
echo('<input type="text" name="date_end" class="date date_end" />');
echo('</td></tr>');
$projects = $sape->get_projects();
$projects = array_reverse( $projects );
reset($projects);
while (list(,$row)=each($projects))
{
	echo ('<tr><td>');
        $row['name'] = iconv("UTF-8", "CP1251", $row['name']);
	echo ('<a class="projects" href="country.php?project_id=' . $row['id'] . '"</a>' . $row['name'] . '</a>');
        
        //print_R($row);
	echo ('</td></tr>');
};
echo('</table>');
echo('</center>');



?>