<?php
/* loadwiki.php version 0.1
Jim Hu at the Hackathon

takes text from Eric's modware dump and 
1) creates page from template in the wiki if needed
2) makes xml for update, including table - updates table in parallel
*/
$wiki_dir = "/var/www/wiki";
$tmp_dir = "tmp";
require_once "$wiki_dir/maintenance/commandLine.inc";
require_once "common/wiki.php";
require_once "common/mysql.php";

# argv[0] is the script name
if (!isset($argv[1])){
	echo "USAGE:
	php loadwiki.php -p page_template -t table_template -f input_filename
\n\n";
	exit;
}
$options = getopt('f:t:p:');
$file = $options['f'];
$page_template = $options['p'];
$table_template = $options['t'];
$file = $options['f'];

$infile = fopen ($file, 'r');
if (!$infile) die ("can't open $file\n");

$page_template_text = get_wiki_text($page_template,NS_TEMPLATE);
$table_template_text = get_wiki_text($table_template,NS_TEMPLATE);
#echo "$page_template: $page_template_text\n";
$gene_count = 0;
$change_count = 0;

$uid = 0; # need to do user later
while (!feof($infile)){
	$line = fgets($infile, 4096);
	$wanted = parse_line($line, $template);
	$data['metadata'] = "basic info from bot";
	$data['row_data'] = $wanted['data'];
	if (trim($wanted['page_name']) =='') continue;
	$title = Title::newFromText($wanted['page_name']);
 	if ( !$title->exists() || get_wiki_text($wanted['page_name']) == '' ) {
		echo "adding a page for ".$wanted['page_name']."\n";
		$box_text = make_box($wanted['page_name'], $table_template, $data);
		$new_page = str_replace("{{{".strtoupper($table_template)."}}}", $box_text, $page_template_text );
                $article = new Article($title);
                if ( !$title->exists()){
			$edit = EDIT_NEW;
		}else{
			$edit = EDIT_UPDATE;
		}

		$article->doEdit( $new_page, 'Added by wikibot', $edit | EDIT_FORCE_BOT );
               	$change_count++;
               	echo "$gene_count genes processed: $gene_name is item $change_count  added to xml file $xml_file_name\n";
        }else{
		echo $wanted['page_name']." already exists\n";

		$box_id = get_wikibox_id($wanted['page_name'], $table_template);
		$box_uid = get_wikibox_uid($box_id);
		$box = new wikiBox();
		$box->box_uid = $box_uid;
		$box->template = $table_template;
		$box->set_from_DB();
		$rows = get_wikibox_rows($box, $uid, $data['metadata']);
 		if (count($rows) == 0){
 			$row = $box->insert_row('',$uid);
               		$rows[] = $row->row_index; #echo "adding new row row_index = ".$row->row_index."\n";
              		$row->db_save_row();
		}
#               print_r($rows);
                # usually this should only happen once, but it comes as an array.
		$function = "do_".$table_template;
                foreach ($rows as $index=>$row_index){
			$box->rows[$row_index]->row_data = $function($box, $box->rows[$row_index], $data);
			$box->rows[$row_index]->db_save_row();
			insert_row_metadata($box->rows[$row_index], $data['metadata']);
 		}
                print_r($box->rows);
		if ($box){
			$tableEdit = new TableEdit();
			$title = Title::newFromID($box->page_uid);
 			$tableEdit->save_to_page($title, $box);
                	unset($box);
        	}
 	}
}

echo "done!\n";

# ============ functions =========================
function parse_line($line){
	$tmp = explode("\t",$line);
	$pages['page_name'] = $tmp[0];
	$pages['data'] = $tmp[1];
	return $pages;
}

function do_gene_info_table($box, $row, $data){
	return $data['row_data'];
}
?>