<?php 
class JC_Importer_Core{

    /**
     * Importer ID
     * @var integer
     */
    protected $ID;

    /**
     * File Location
     * @var string
     */
    protected $file;

    /**
     * Permissions array(create => 0, update => 0, delete => 0)
     * @var array
     */
    protected $permissions;
    protected $attachments = array();
    protected $import_type;
    protected $template;
    protected $taxonomies = array();
    protected $taxonomies_permissions = array();
    protected $groups = array();

	public function __construct($id = 0){
        
        if(intval($id) > 0){

            // escape if importer already loaded
            if(intval($id) == $this->ID)
                return true;

            $importer = ImporterModel::getImporter($id);
            if(!$importer->have_posts())
                return false;

            $this->ID = $id;
            $this->file = ImporterModel::getImportSettings($id, 'import_file');
            $this->permissions = ImporterModel::getImportSettings($id, 'permissions');
            $this->attachments = ImporterModel::getImporterMeta($id, 'attachments');
            $this->import_type = ImporterModel::getImportSettings($id, 'template_type');
            $this->template = get_import_template(ImporterModel::getImportSettings($id, 'template'));

            // load taxonomies
            $taxonomies = ImporterModel::getImporterMeta($id, 'taxonomies');
            foreach($taxonomies as $group_id => $tax_arr){

                if(!isset($tax_arr['tax']))
                    continue;
                
                foreach($tax_arr['tax'] as $key => $tax){
                    
                    if(!isset($tax_arr['term'][$key]))
                        continue;

                    $this->taxonomies[$group_id][$tax][] = $tax_arr['term'][$key];    
                    $this->taxonomies_permissions[$group_id] = isset($tax_arr['permissions'][$key]) ? $tax_arr['permissions'][$key] : 'create';
                }
            }

            // load template fields
            $fields = ImporterModel::getImporterMeta($id, 'fields');
            foreach($this->template->_field_groups as $group => $data){

                // backwards comp
                $data['group'] = $group;
                
                $output_fields = array();
                foreach($data['map'] as $id => $field_data){
                    $output_fields[$field_data['field']] = isset($fields[$data['group']][$field_data['field']]) ? $fields[$data['group']][$field_data['field']] : ''; // null; //$fields[$field_data['type']][$field_data['field']];
                }

                $this->groups[$data['group']] = array(
                    'type' => $data['field_type'],
                    'fields' => $output_fields
                );
            }
            
            // load parser specific settings
            $this->addon_settings = apply_filters( "jci/load_{$this->import_type}_settings", array(), $this->ID );

            // load parser class
            global $jcimporter;
            $this->parser = $jcimporter->parsers[$this->import_type];
        }
	}

    public function __get($key){
        
        $allowed_keys = array('ID', 'groups', 'permissions', 'taxonomies', 'taxonomies_permissions', 'import_type', 'file', 'attachments', 'addon_settings');
        if(in_array($key, $allowed_keys))
            return $this->$key;
        return null;
    }

    /**
     * Run Data Imports
     * @param  integer $import_id 
     * @param  integer  $row       Specific Row
     * @return array Response
     */
    public function run_import($row = null){

        global $jcimporter;
        $jci_file = $jcimporter->importer->file;
        $jci_template = $jcimporter->importer->template;
        $jci_import_type = $jcimporter->importer->import_type;

        $mapper = new JC_BaseMapper();
        
        $this->_parser = $jcimporter->parsers[$jci_import_type];
        $this->_parser->loadFile($jci_file);

        if($row){

            $results = $this->_parser->parse(intval($row));
            
            // escape if row doesn't exist
            if(!$results){
                return false;
            }

            $result = $mapper->process($jci_template, $results, $row);
        }else{

            $results = $this->_parser->parse();
            $result = $mapper->process($jci_template, $results);
        }

        // check result
        if(count($results) == count($result)){
            return $result;
        }
        return false;
    }

    public function get_parser($import_type){

    }
}
?>