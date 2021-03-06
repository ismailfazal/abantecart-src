<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2013 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}
class ModelSettingSetting extends Model {
	public function getGroups() {
		$data = array();
		$query = $this->db->query("SELECT DISTINCT `group` FROM " . DB_PREFIX . "settings");
		foreach ($query->rows as $result) {
			$data[] = $result['group'];
		}
		return $data;
	}

	public function getSettingGroup( $setting_key, $store_id = 0 ) {
		$data = array();
		$query = $this->db->query("SELECT DISTINCT `group` FROM " . DB_PREFIX . "settings 
						  WHERE `key` = '" . $this->db->escape($setting_key) . "'
						  AND `store_id` = '".$store_id."'");
		
		foreach ($query->rows as $result) {
			$data[] = $result['group'];
		}
		return $data;
	}

	public function getAllSettings($data = array(), $mode = 'default') {

		if ($mode == 'total_only') {
			$total_sql = 'count(*) as total';
		}
		else {
			$total_sql = 's.*, COALESCE(st.alias, \''.$this->language->get('text_default').'\' ) as alias';
		}
		
		$sql = "SELECT $total_sql
				FROM " . DB_PREFIX . "settings s
				LEFT JOIN  " . DB_PREFIX . "stores st ON st.store_id = s.store_id
                WHERE s.group IN ('".implode("', '",$this->config->groups)."') ";

        if(isset( $data['store_id'] )){
            $sql .= " AND s.store_id = '".$data['store_id']."'";
        }

		if (!empty($data['subsql_filter'])) {
			$sql .= " AND ".$data['subsql_filter'];
		}

		//If for total, we done bulding the query
		if ($mode == 'total_only') {
		    $query = $this->db->query($sql);
		    return $query->row['total'];
		}

		$sort_data = array(	'group','key' );

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `group`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalSettings($data = array()) {
		return $this->getAllSettings($data, 'total_only');
	}

	public function getSetting($group,$store_id=0) {
		$data = array();

		$query = $this->db->query(
			"SELECT *
			FROM " . DB_PREFIX . "settings
			WHERE `group` = '" . $this->db->escape($group) . "'
					AND store_id = '".(int)$store_id."'" );
		foreach ($query->rows as $result) {
			$data[$result['key']] = $result['value'];
		}
		return $data;
	}
	
	public function editSetting($group, $data, $store_id=null) {
		$store_id = is_null($store_id) ? ($this->config->get('config_store_id')) : (int)$store_id;
		$languages = $this->language->getAvailableLanguages();
		// check what is it - update or insert of setting

		$edit_type = 'insert';
		foreach($languages as $language){
			if( $this->config->get('config_description_'.$language['language_id'])) {
				$edit_type = 'update';
				break;
			}
		}
		$src_lang_id = $this->language->getLanguageIdByCode($this->config->get('translate_src_lang_code'));
		// if override - edit type is insert
		if(isset($data['config_description_'.$src_lang_id]) && $this->config->get('translate_override_existing')){
			$edit_type = 'insert';
		}

		$locales = array();
		foreach($languages as $language){
			// if update and not override - skip
			if(!$this->config->get('translate_override_existing') && $edit_type=='update'){
				continue;
			}
			$locale = $this->language->getLanguageCodeByLocale($language['locale']);
			if($locale != $this->config->get('translate_src_lang_code') && $edit_type=='insert'){
				$locales[$language['language_id']] = $locale;
			}
		}

		// if need translate
		if($locales){


			if($src_lang_id){
				$src_text =  isset($data['config_description_'.$src_lang_id]) ? $data['config_description_'.$src_lang_id] : $this->config->get('config_description_'.$src_lang_id);
				foreach($locales as $dst_lang_id=>$dst_code){
					$data['config_description_'.$dst_lang_id] = $this->language->translate ($this->config->get('translate_src_lang_code'), $src_text, $dst_code);
				}
			}
		}

		foreach ($data as $key => $value) {
			$sql = "DELETE FROM " . DB_PREFIX . "settings
					WHERE `group` = '" . $this->db->escape($group) . "'
							AND `key` = '" . $this->db->escape($key) . "'
							AND `store_id` = '" . $store_id . "'";
			$this->db->query($sql);

			$sql = "INSERT INTO " . DB_PREFIX . "settings
					( `store_id`, `group`, `key`, `value`)
				VALUES (  '".$store_id."',
				          '" . $this->db->escape($group) . "',
				          '" . $this->db->escape($key) . "',
				          '" . $this->db->escape($value) . "')";
			$this->db->query($sql);
		}
		$this->cache->delete('settings');
		$this->cache->delete('stores');
	}

	
	public function deleteSetting($group, $store_id=0) {
		$store_id = (int)$store_id;
		$this->db->query("DELETE FROM " . DB_PREFIX . "settings
						  WHERE `group` = '" . $this->db->escape($group) . "'
						  AND `store_id` = '".$store_id."'");

		$this->cache->delete('settings');
		$this->cache->delete('stores');
	}
}
?>