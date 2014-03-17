<?php 
class ImporterModel{

	static $config;
	static $settings = false;
	static $meta = false;

	static function init(&$config){
		self::$config = $config;
	}

	static function getImporters(){

		$query = new WP_Query(array(
			'post_type' => 'jc-imports',
		));

		return $query;

	}

	static function getImporter($id = 0){

		if($id > 0){
			$query = new WP_Query(array(
				'post_type' => 'jc-imports',
				'p' => $id,
			));
		}else{
			$query = false;
		}

		return $query;

	}

	static function setImportFile($id, $file){

		if(empty($file))
			return false;

		$old_value = get_post_meta( $id, '_import_settings', true );

		if(empty($file['type']) || $file['type'] != $old_value['template_type'])
			return false;
		
		$value = $old_value;
		$value['import_file'] = $file['id'];
                                
        if ( $value && '' == $old_value ){
            add_post_meta( $id, '_import_settings', $value );
        }elseif ( $value && $value != $old_value ){
            update_post_meta( $id, '_import_settings', $value );
        }elseif ( '' == $value && $old_value ){
            delete_post_meta( $id, '_import_settings', $value );
        }
	}

	static function insertImporter($post_id, $data = array()){

		$args = array(
			'post_title' => $data['name'],
			'post_status' => 'publish',
			'post_type' => 'jc-imports',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
		);
		$meta = array();

		if($post_id > 0){
			$args['ID'] = $post_id;
		}else{
			$args['post_name'] = substr( md5(time()), 0, rand( 5, 10 ) );
		}

		$meta['_import_settings'] = isset($data['settings']) ? $data['settings'] : array();
		$meta['_mapped_fields'] = isset($data['fields']) ? $data['fields'] : array();
		$meta['_setting_addons'] = isset($data['setting_addons']) ? $data['setting_addons'] : array();
		$meta['_field_addons'] = isset($data['field_addons']) ? $data['field_addons'] : array();
		$meta['_attachments'] = isset($data['attachments']) ? $data['attachments'] : array();
		$meta['_taxonomies'] = isset($data['taxonomies']) ? $data['taxonomies'] : array();	

		$post_id = wp_insert_post($args);

		foreach($meta as $key => $value){

			$old_value = get_post_meta( $post_id, $key, true);
                                
            if ( $value && '' == $old_value ){
                    add_post_meta( $post_id, $key, $value );
            }elseif ( $value && $value != $old_value ){
                    update_post_meta( $post_id, $key, $value );
            }elseif ( '' == $value && $old_value ){
                    delete_post_meta( $post_id, $key, $value );
            }
		}

		return $post_id;
	}

	static function clearImportSettings(){
		self::$meta = false;
	}

	static function getImportSettings($post_id, $section = null){

		$settings = self::getImporterMeta($post_id, 'settings');

		switch($section){

			// get ftp settings
			// TODO: remove to ftp addon
			case 'ftp':
				$settings = array(
					'ftp_loc' => isset($settings['general']['ftp_loc']) ? $settings['general']['ftp_loc'] : '',
				);
			break;

			// get remote settings
			case 'remote':
				$settings = array(
					'remote_url' => isset($settings['general']['remote_url']) ? $settings['general']['remote_url'] : '',
				);
			break;
			
			// get post settings
			// TODO: remove to post addon
			case 'post':
				$settings = array(
					'post_field' => isset($settings['general']['post_field']) ? $settings['general']['post_field'] : '',
					'post_key' => isset($settings['general']['post_key']) ? $settings['general']['post_key'] : '',
					'post_field_type' => isset($settings['general']['post_field_type']) ? $settings['general']['post_field_type'] : '',
				);
			break;

			// indevidual settings
			case 'template':
				$settings = $settings['template'];
				$settings = (string)$settings;
			break;
			case 'template_type':
				$settings = isset($settings['template_type']) && !empty($settings['template_type']) ? $settings['template_type'] : 'csv';
				$settings = (string)$settings;
			break;
			case 'import_type':
				$settings = $settings['import_type'];
				$settings = (string)$settings;
			break;
			case 'start_line':
				$settings = isset($settings['start_line']) ? $settings['start_line'] : 1;
			break;
			case 'row_count':
				$settings = isset($settings['row_count']) ? $settings['row_count'] : 0;
			break;
			case 'import_file':
				if(intval($settings['import_file']) > 0){
					$settings = get_attached_file( intval($settings['import_file']) );
				}else{
					$settings = isset($settings['import_file']) ? $settings['import_file'] : '';	
				}
			break;
			case 'permissions':
				$settings = isset($settings['permissions']) ? $settings['permissions'] : array();
			break;
		}

		return $settings;
	}

	/**
	 * Get All Importer Metadata
	 * @param  integer $post_id 
	 * @param  string $section 
	 * @return array
	 */
	static function getImporterMeta($post_id, $section = null){

		if(!self::$meta){
			$importer_meta = get_metadata( 'post', $post_id , '', true);

			$settings = isset($importer_meta['_import_settings']) ? unserialize($importer_meta['_import_settings'][0]) : array();
	        $fields = isset($importer_meta['_mapped_fields']) ? unserialize($importer_meta['_mapped_fields'][0]) : array();
	        $attachments = isset($importer_meta['_attachments']) ? unserialize($importer_meta['_attachments'][0]) : array();
	        $taxonomies = isset($importer_meta['_taxonomies']) ? unserialize($importer_meta['_taxonomies'][0]) : array();
	        $addon_settings = isset($importer_meta['_setting_addons']) ? unserialize($importer_meta['_setting_addons'][0]) : array();
	        $addon_fields = isset($importer_meta['_field_addons']) ? unserialize($importer_meta['_field_addons'][0]) : array();

			self::$meta = array(
				'settings' => $settings,
				'fields' => $fields,
				'attachments' => $attachments,
				'taxonomies' => $taxonomies,
				'addon_settings' => $addon_settings,
				'addon_fields' => $addon_fields,
			);
		}

		$meta = self::$meta;

		switch($section){
			case 'settings':
				$meta = $meta['settings'];
			break;
			case 'fields':
				$meta = $meta['fields'];
			break;
			case 'attachments':
				$meta = $meta['attachments'];
			break;
			case 'taxonomies':
				$meta = $meta['taxonomies'];
			break;
			case 'addon_settings':
				$meta = $meta['addon_settings'];
			break;
			case 'addon_fields':
				$meta = $meta['addon_fields'];
			break;
		}

		return $meta;
	}

	private static function get_key(&$arr, $keys = array(), $value = '', $counter = 0){

		$key = $keys[$counter];
		$counter++;
		if(isset($arr[$key])){

			// if keys exist
			if($counter == count($keys)){
				$arr[$key] = $value;
			}else{
				$arr[$key] = self::get_key($arr[$key], $keys, $value, $counter);
			}
		}else{

			// create keys
			$arr[$key] = array();
			if($counter == count($keys)){
				$arr[$key] = $value;
			}else{
				$arr[$key] = self::get_key($arr[$key], $keys, $value, $counter);
			}

		}

		return $arr;
	}

	static function getImporterMetaArr($post_id, $keys){

		if(is_null($keys) || empty($keys))
			return false;

		if(is_array($keys)){
			
			$key = array_shift($keys);
			$old_value = get_post_meta($post_id, $key, true );

			$temp = $old_value;
			foreach($keys as $k){
				if(isset($temp[$k])){
					$temp = $temp[$k];
				}else{
					return '';
				}
			}
			return $temp;

		}elseif(is_string($keys)){
			
			$key = $keys;
			return get_post_meta($post_id, $key, true );
		}

	}

	static function setImporterMeta($post_id, $keys = null, $value = null){

		if(is_null($keys) || is_null($value))
			return false;

		if(is_array($keys)){

			//settings/test/test1 = test
			$key = array_shift($keys);
			$old_value = get_post_meta($post_id, $key, true );

			$temp = $old_value;
			$value = self::get_key($temp, $keys, $value);			

		}elseif(is_string($keys)){
			$key = $keys;
			$old_value = get_post_meta($post_id, $key, true );
		}

		if ( $value && '' == $old_value ){
            add_post_meta( $post_id, $key, $value );
        }elseif ( $value && $value != $old_value ){
            update_post_meta( $post_id, $key, $value );
        }elseif ( '' == $value && $old_value ){
            delete_post_meta( $post_id, $key, $value );
        }
	}

	static function update($post_id, $data = array()){

		$meta['_mapped_fields'] = isset($data['fields']) ? $data['fields'] : array();
		$meta['_attachments'] = isset($data['attachments']) ? $data['attachments'] : array();
		$meta['_taxonomies'] = isset($data['taxonomies']) ? $data['taxonomies'] : array();
		$meta['_field_addons'] = isset($data['addon_fields']) ? $data['addon_fields'] : array();
		$meta['_setting_addons'] = isset($data['addon_settings']) ? $data['addon_settings'] : array();

		$settings = get_post_meta( $post_id, '_import_settings', true );
		$settings['start_line'] = isset($data['settings']['start_line']) ? $data['settings']['start_line'] : 1;
		$settings['row_count'] = isset($data['settings']['row_count']) ? $data['settings']['row_count'] : 0;

		if(isset($data['settings']['template_type']) && in_array($data['settings']['template_type'], array('csv','xml'))){
			$settings['template_type'] = $data['settings']['template_type'];
		}
		// $settings['template_type'] = isset($data['settings']['template_type']) ? $data['settings']['template_type'] : 0;

		if(isset($data['settings']['import_file']) && !empty($data['settings']['import_file'])){
			$settings['import_file'] = $data['settings']['import_file'];
		}

		$permissions = isset($data['settings']['permissions']) ? $data['settings']['permissions'] : array();

		// permissions
		$permission_keys = array('create', 'update', 'delete');
		$settings['permissions'] = array();
		foreach($permission_keys as $key){
			if(isset($permissions[$key])){
				$settings['permissions'][$key] = $permissions[$key];
			}else{
				$settings['permissions'][$key] = 0;
			}
		}


		// validate row start/count
		global $jcimporter;
		$template_type = ImporterModel::getImportSettings($post_id, 'template_type');

		$importer_settings = ImporterModel::getImportSettings($post_id);

		// set specific import_type settings
		$settings = apply_filters( "jci/importer_save", $settings, $importer_settings['import_type'], $data);

		

		// TODO: remove tie to post datasource
		if($importer_settings['import_type'] != 'post'){
			
			// if file exists get total rows
			$parser = $jcimporter->parsers[$template_type];
			$meta2 = get_metadata( 'post', $post_id , '', true);
			$row_count = $parser->get_total_rows($post_id);	
		
			if($settings['start_line'] > $row_count){
				if($settings['row_count'] > $row_count){
					$settings['start_line'] = 1;
					$settings['row_count'] = 0;
				}else{
					if($settings['row_count'] > 0){
						$settings['start_line'] = $row_count - ($settings['row_count']-1);		
					}else{
						$settings['start_line'] = 1;		
					}
				}
			}elseif($settings['start_line'] + $settings['row_count'] > ($row_count+1)){
				$settings['row_count'] = $row_count - ($settings['start_line'] - 1);
			}
			if($settings['start_line'] <= 0){
				$settings['start_line'] = 1;
			}
			if($settings['row_count'] < 0){
				$settings['row_count'] = 0;
			}
		}

		// save settings
		$meta['_import_settings'] = $settings;

		foreach($meta as $key => $value){

			$old_value = get_post_meta( $post_id, $key, true);
                                
            if ( $value && '' == $old_value ){
                    add_post_meta( $post_id, $key, $value );
            }elseif ( $value && $value != $old_value ){
                    update_post_meta( $post_id, $key, $value );
            }elseif ( '' == $value && $old_value ){
                    delete_post_meta( $post_id, $key, $value );
            }
		}

		return $post_id;
	}
}
?>