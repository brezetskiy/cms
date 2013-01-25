<?php
$Structure = new Structure($this->table['table_name']);
$new_url = $DB->result("SELECT url FROM cms_structure WHERE id='".$this->NEW['id']."'");
$Structure->move($this->OLD['url'], $new_url);
