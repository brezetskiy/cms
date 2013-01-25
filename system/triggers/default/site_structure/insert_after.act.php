<?php
// Добавляем сайт
if (empty($this->NEW['structure_id'])) {
	Structure::createSite($this->NEW['id'], $this->NEW['uniq_name'], $this->NEW['template_id']);
}
