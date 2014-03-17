<?php 
/*
Plugin Name: JC Importer
Description: Wordpress CSV/XML Importer Plugin
Author: James Collings <james@jclabs.co.uk>
Version: 0.0.1
*/

require_once 'app/core/exceptions.php';
require_once 'app/parse/parser.php';

// attachments
require_once 'app/attachment/attachment.php';
require_once 'app/attachment/attachment-ftp.php';
require_once 'app/attachment/attachment-curl.php';
require_once 'app/attachment/attachment-upload.php';
require_once 'app/attachment/attachment-string.php';

// mappers
require_once 'app/mapper/Mapper.php';
require_once 'app/mapper/PostMapper.php';
require_once 'app/mapper/TableMapper.php';
require_once 'app/mapper/UserMapper.php';
require_once 'app/mapper/VirtualMapper.php';

// parsers
require_once 'app/parse/data-csv.php';
require_once 'app/parse/data-xml.php';

// templates
require_once 'app/templates/template.php';
require_once 'app/templates/template-user.php';
require_once 'app/templates/template-post.php';
require_once 'app/templates/template-page.php';


require_once 'app/helpers/form.php';
require_once 'app/functions.php';

class JC_Importer{

	var $version = '0.0.1';
	var $plugin_dir = false;
	var $plugin_url = false;
	var $templates = array();
	var $db_version = 1;
	var $core_version = 1;

	public function __construct(){

		$this->plugin_dir =  plugin_dir_path( __FILE__ );
		$this->plugin_url = plugins_url( '/', __FILE__ );

		add_action('init', array($this, 'init'));

		$this->parsers = apply_filters( 'jci/register_parser', array());

		// activation
        register_activation_hook( __FILE__, array($this, 'activation') );
        add_action('admin_init',array($this, 'load_plugin'));
	}

	public function init(){

		$this->register_post_types();

		// core files
		require_once 'app/core/template.php';
		new JC_Template_Core($this);
		
		// load importer
		require_once 'app/core/importer.php';
		// $this->importer = new JC_Importer_Core();

		// core models
		require_once 'app/models/importer.php';
		require_once 'app/models/log.php';

		if(is_admin()){
			
			require_once 'app/admin.php';
			new JC_Importer_Admin($this);	

			require_once 'app/ajax.php';
			new JC_Importer_Ajax($this);
		}

		ImporterModel::init($this);
		ImportLog::init($this);
		JCI_FormHelper::init($this);

		do_action('jci/init');
	}

	/**
	 * Register custom post types
	 * @return void
	 */
	function register_post_types(){

		register_post_type( 'jc-imports', array(
			'public' => false,
			'has_archive' => false,
			'show_in_nav_menus' => false,
			'label' => 'Template'
		));
	}

	/**
	 * Set Plugin Activation
	 * @return void
	 */
	function activation(){
        add_option('Activated_Plugin','jcimporter');
    }

    /**
     * Run Activation Functions
     * @return void
     */
    function load_plugin() {

        if(is_admin() && get_option('Activated_Plugin') == 'jcimporter') {

			delete_option('Activated_Plugin');
			ImportLog::scaffold();
        }
    }
}

$GLOBALS['jcimporter'] = new JC_Importer();
?>