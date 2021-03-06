<?php

class JC_Post_Template extends JC_Importer_Template {

	public $_name = 'post';

	public $_field_groups = array(

		'post' => array(
			'import_type'      => 'post', // which map to use
			'import_type_name' => 'post', // post_type
			'field_type'       => 'single', // single|repeater
			'post_status'      => 'publish', // default group publish
			'group'            => 'post', // group name, for old time sake
			'unique'           => array( 'post_name' ),
			'key'              => array( 'ID', 'post_name' ),
			'relationship'     => array(),
			'identifiers'      => array(),
			'attachments'      => 1,
			'taxonomies'       => 1,
			'map'              => array(
				array(
					'title' => 'ID',
					'field' => 'ID'
				),
				array(
					'title' => 'Title',
					'field' => 'post_title'
				),
				array(
					'title' => 'Content',
					'field' => 'post_content'
				),
				array(
					'title' => 'Excerpt',
					'field' => 'post_excerpt'
				),
				array(
					'title' => 'Slug',
					'field' => 'post_name'
				),
				array(
					'title' => 'Status',
					'field' => 'post_status',
					'options' => array('draft' => 'Draft', 'publish' => 'Published', 'pending' => 'Pending', 'future' => 'Future', 'private' => 'Private', 'trash' => 'Trash'),
					'options_default' => 'publish'
				),
				array(
					'title' => 'Author',
					'field' => 'post_author',
				),
				array(
					'title' => 'Parent',
					'field' => 'post_parent'
				),
				array(
					'title' => 'Order',
					'field' => 'menu_order'
				),
				array(
					'title' => 'Password',
					'field' => 'post_password'
				),
				array(
					'title' => 'Date',
					'field' => 'post_date'
				),
				array(
					'title'  => 'Allow Comments',
					'field'  => 'comment_status',
					'options' => array( 0 => 'Disabled', 1 => 'Enabled' ),
					'options_default' => 0
				),
				array(
					'title'  => 'Allow Pingbacks',
					'field'  => 'ping_status',
					'options' => array( 'closed' => 'Closed', 'open' => 'Open' ),
					'options_default' => 'closed'
				),
			)
		)
	);

	public function __construct() {
		parent::__construct();
		add_action( 'jci/after_template_fields', array( $this, 'field_settings' ) );
		add_action( 'jci/save_template', array( $this, 'save_template' ) );

		add_filter( sprintf('jci/log_%s_columns', $this->_name), array( $this, 'log_post_columns' ) );
		add_action( sprintf('jci/log_%s_content', $this->_name), array( $this, 'log_post_content' ), 10, 2 );

		add_action( 'jci/before_import', array( $this, 'before_import' ) );

		
		foreach( $this->_field_groups['post']['map'] as &$field){

			
			if( $field['field'] == 'post_author' ){

				/**
				 * Populate authors dropdown
				 */
				$field['options'] = jci_get_user_list();

			}elseif( $field['field'] == 'post_parent' ){

				/**
				 * Populate parent posts pages
				 */
				$field['options'] = jci_get_post_list('post');
			}
		}
	}

	/**
	 * Attach template_group filters on import only
	 *
	 * @return void
	 */
	public function before_import() {

		$_jci_ref_post_parent  = ImporterModel::getImporterMetaArr( JCI()->importer->get_ID(), array(
			'_template_settings',
			'_jci_ref_post_parent'
		) );

		if(!empty($_jci_ref_post_parent)) {
			$this->_field_groups['post']['identifiers'] = array( 'post_parent' => $_jci_ref_post_parent );
		}

		add_filter('jci/importer/get_groups', array($this, 'add_reference_fields'), 999 );
	}

	public function field_settings( $id ) {

		$template = ImporterModel::getImportSettings( $id, 'template' );
		if ( $template == $this->_name ) {

			$enable_id             = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_id'
				) );
			$enable_post_status    = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_post_status'
				) );
			$enable_post_author    = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_post_author'
				) );
			$enable_post_parent    = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_post_parent'
				) );
			$enable_menu_order     = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_menu_order'
				) );
			$enable_post_password  = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_post_password'
				) );
			$enable_post_date      = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_post_date'
				) );
			$enable_comment_status = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_comment_status'
				) );
			$enable_ping_status    = ImporterModel::getImporterMetaArr( $id, array(
					'_template_settings',
					'enable_ping_status'
				) );
			$_jci_ref_post_parent  = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'_jci_ref_post_parent'
			) );

			/**
			 * Field Type: Template Settings
			 */
			$field_types = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'_field_type'
			));
			
			$author_field_type = isset($field_types['post_author']) ? $field_types['post_author'] : 'id';
			$parent_field_type = isset($field_types['post_parent']) ? $field_types['post_parent'] : 'id';

			?>
			<div class="jci-group-settings jci-group-section" data-section-id="settings">
				<div id="jci_post_enable_fields">
					<h4>Fields:</h4>
					<?php
					echo JCI_FormHelper::checkbox( 'template_settings[enable_id]', array(
							'label'   => 'Enable ID Field',
							'checked' => $enable_id
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_post_status]', array(
							'label'   => 'Enable Post Status Field',
							'checked' => $enable_post_status
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_post_author]', array(
							'label'   => 'Enable Author Field',
							'checked' => $enable_post_author,
							'after' => JCI_FormHelper::select('author_field_type', array('label' => ', Using the Value', 'default' => $author_field_type , 'options' => array('id' => 'ID', 'login' => 'Login', 'email' => 'Email')))
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_post_parent]', array(
							'label'   => 'Enable Parent Field',
							'checked' => $enable_post_parent,
							'after' => JCI_FormHelper::select('parent_field_type', array('label' => ', Using the Value', 'default' => $parent_field_type, 'options' => array('id' => 'ID', 'slug' => 'Slug', 'name' => 'Name', 'column' => 'Reference Column')))
						) );

					echo '<div class="reference-column__post-parent" style="display: none;">';
					echo JCI_FormHelper::text( 'template_settings[_jci_ref_post_parent]', array(
						'label'   => 'Parent Reference Column',
						'default' => $_jci_ref_post_parent,
						'class'   => 'xml-drop jci-group',
						'after'   => ' <a href="#" class="jci-import-edit button button-small" title="Select Data To Map">Select</a>'
					) );
					echo '</div>';

					echo JCI_FormHelper::checkbox( 'template_settings[enable_menu_order]', array(
							'label'   => 'Enable Order Field',
							'checked' => $enable_menu_order
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_post_password]', array(
							'label'   => 'Enable Password Field',
							'checked' => $enable_post_password
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_post_date]', array(
							'label'   => 'Enable Date Field',
							'checked' => $enable_post_date
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_comment_status]', array(
							'label'   => 'Enable Comment Field',
							'checked' => $enable_comment_status
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_ping_status]', array(
							'label'   => 'Enable Ping Field',
							'checked' => $enable_ping_status
						) );
					?>
				</div>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {

					// show/hide input fields
					$.fn.jci_enableField('enable_id', 'post-ID');
					$.fn.jci_enableField('enable_menu_order', 'post-menu_order');
					$.fn.jci_enableField('enable_post_password', 'post-post_password');
					$.fn.jci_enableField('enable_post_date', 'post-post_date');

					// show select for post_author
					$.fn.jci_enableField('enable_post_author', '#jc-importer_author_field_type');
					$.fn.jci_enableField('enable_post_parent', '#jc-importer_parent_field_type');

					// optional selects
					$.fn.jci_enableSelectField('enable_post_parent', 'post-post_parent');
					$.fn.jci_enableSelectField('enable_post_status', 'post-post_status');
					$.fn.jci_enableSelectField('enable_post_author', 'post-post_author');
					$.fn.jci_enableSelectField('enable_comment_status', 'post-comment_status');
					$.fn.jci_enableSelectField('enable_ping_status', 'post-ping_status');

                    $('body').on('change', '#jc-importer_parent_field_type', function () {
                        if($(this).val() === 'column'){
                            $('.reference-column__post-parent').show();
                        }else{
                            $('.reference-column__post-parent').hide();
                        }
                    } )

                    $('#jc-importer_parent_field_type').trigger('change');
				});
			</script>
		<?php
		}
	}

	public function save_template( $id ) {

		$template = ImporterModel::getImportSettings( $id, 'template' );
		if ( $template == $this->_name ) {

			// get template settings
			$enable_id             = isset( $_POST['jc-importer_template_settings']['enable_id'] ) ? $_POST['jc-importer_template_settings']['enable_id'] : 0;
			$enable_post_status    = isset( $_POST['jc-importer_template_settings']['enable_post_status'] ) ? $_POST['jc-importer_template_settings']['enable_post_status'] : 0;
			$enable_post_author    = isset( $_POST['jc-importer_template_settings']['enable_post_author'] ) ? $_POST['jc-importer_template_settings']['enable_post_author'] : 0;
			$enable_post_parent    = isset( $_POST['jc-importer_template_settings']['enable_post_parent'] ) ? $_POST['jc-importer_template_settings']['enable_post_parent'] : 0;
			$enable_menu_order     = isset( $_POST['jc-importer_template_settings']['enable_menu_order'] ) ? $_POST['jc-importer_template_settings']['enable_menu_order'] : 0;
			$enable_post_password  = isset( $_POST['jc-importer_template_settings']['enable_post_password'] ) ? $_POST['jc-importer_template_settings']['enable_post_password'] : 0;
			$enable_post_date      = isset( $_POST['jc-importer_template_settings']['enable_post_date'] ) ? $_POST['jc-importer_template_settings']['enable_post_date'] : 0;
			$enable_comment_status = isset( $_POST['jc-importer_template_settings']['enable_comment_status'] ) ? $_POST['jc-importer_template_settings']['enable_comment_status'] : 0;
			$enable_ping_status    = isset( $_POST['jc-importer_template_settings']['enable_ping_status'] ) ? $_POST['jc-importer_template_settings']['enable_ping_status'] : 0;

			$_jci_ref_post_parent  = isset( $_POST['jc-importer_template_settings']['_jci_ref_post_parent'] ) ? $_POST['jc-importer_template_settings']['_jci_ref_post_parent'] : '';

			ImporterModel::setImporterMeta( $id, array( '_template_settings', '_jci_ref_post_parent' ), $_jci_ref_post_parent );

			// update template settings
			ImporterModel::setImporterMeta( $id, array( '_template_settings', 'enable_id' ), $enable_id );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_post_status'
				), $enable_post_status );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_post_author'
				), $enable_post_author );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_post_parent'
				), $enable_post_parent );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_menu_order'
				), $enable_menu_order );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_post_password'
				), $enable_post_password );
			ImporterModel::setImporterMeta( $id, array( '_template_settings', 'enable_post_date' ), $enable_post_date );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_comment_status'
				), $enable_comment_status );
			ImporterModel::setImporterMeta( $id, array(
					'_template_settings',
					'enable_ping_status'
				), $enable_ping_status );

			// save field type if post_author enabled
			$post_author_type = $enable_post_author ? $_POST['jc-importer_author_field_type'] : false;
			ImporterModel::setImporterMeta( $id, array(
				'_template_settings',
				'_field_type',
				'post_author'
			), $post_author_type );

			// save field type if parent enabled
			$post_parent_type = $enable_post_parent ? $_POST['jc-importer_parent_field_type'] : false;
			ImporterModel::setImporterMeta( $id, array(
				'_template_settings',
				'_field_type',
				'post_parent'
			), $post_parent_type );
		}
	}

	public function before_template_save( $data, $current_row ) {

		$id = JCI()->importer->get_ID();

		$this->enable_id             = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_id'
			) );
		$this->enable_post_status    = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_post_status'
			) );
		$this->enable_post_author    = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_post_author'
			) );
		$this->enable_post_parent    = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_post_parent'
			) );
		$this->enable_menu_order     = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_menu_order'
			) );
		$this->enable_post_password  = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_post_password'
			) );
		$this->enable_post_date      = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_post_date'
			) );
		$this->enable_comment_status = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_comment_status'
			) );
		$this->enable_ping_status    = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'enable_ping_status'
			) );
	}

	public function before_group_save( $data, $group_id ) {

		$id = JCI()->importer->get_ID();

		/**
		 * Clear unenabled fields
		 */
		if ( $this->enable_id == 0 ) {
			unset( $data['ID'] );
		}
		if ( $this->enable_menu_order == 0 ) {
			unset( $data['menu_order'] );
		}
		if ( $this->enable_post_password == 0 ) {
			unset( $data['post_password'] );
		}
		if ( $this->enable_post_date == 0 ) {
			unset( $data['post_date'] );
		}

		/**
		 * Check to see if post_parent
		 */
		if($this->enable_post_parent && !empty($data['post_parent'])){

			$post_parent_type = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'_field_type',
				'post_parent'
			));

			$page_id = 0;

			if($post_parent_type == 'name' || $post_parent_type == 'slug'){
				
				// name or slug
				$page = get_posts( array( 'name' => sanitize_title($data['post_parent']), 'post_type' => 'post' ) );
				if($page){
					$page_id = intval($page[0]->ID);
				}
				
			}elseif($post_parent_type == 'id'){

				// ID
				$page_id = intval($data['post_parent']);
			}elseif($post_parent_type == 'column'){

				// Reference Column
				$parent_id = $this->get_post_by_cf('post_parent', $data['post_parent'], $group_id);
				if(intval($parent_id > 0)){
					$page_id = intval($parent_id);
				}
			}

			// set post parent to int or clear
			$data['post_parent'] = $page_id;
		}

		/**
		 * Check to see if post_author
		 */
		if($this->enable_post_author && !empty($data['post_author'])){

			$post_author_type = ImporterModel::getImporterMetaArr( $id, array(
				'_template_settings',
				'_field_type',
				'post_author'
			));

			$user_id = 0;

			if($post_author_type == 'login'){
				
				// login
				$user = get_user_by( 'login', $data['post_author'] );
				if($user){
					$user_id = intval($user->ID);
				}
				

			}elseif($post_author_type == 'email'){

				// email
				$user = get_user_by( 'email', $data['post_author'] );
				if($user){
					$user_id = intval($user->ID);
				}
				
			}elseif($post_author_type == 'id'){

				// ID
				$user_id = intval($data['post_author']);
			}

			// set post parent to int or clear
			$data['post_author'] = $user_id;
		
		}

		// generate slug from title if no slug present
		if(empty($data['post_name'])){
			$data['post_name'] = sanitize_title( $data['post_title'] );
		}

		return $data;
	}

	/**
	 * Register Post Columns
	 *
	 * @param  array $columns
	 *
	 * @return array
	 */
	public function log_post_columns( $columns ) {

		$columns['post']        = 'Post';
		$columns['taxonomies']  = 'Taxonomies';
		$columns['attachments'] = 'Attachments';
		$columns['method']      = 'Method';

		return $columns;
	}

	/**
	 * Output column data
	 *
	 * @param  array $column
	 * @param  array $data
	 *
	 * @return void
	 */
	public function log_post_content( $column, $data ) {

		switch ( $column ) {
			case 'post':
				echo edit_post_link($data['post']['post_title'] . ' #' .$data['post']['ID'], '', '', $data['post']['ID']);
				break;
			case 'method':

				if ( $data['post']['_jci_type'] == 'I' ) {
					echo 'Inserted';
				} elseif ( $data['post']['_jci_type'] == 'U' ) {
					echo 'Updated';
				}
				break;
		}
	}

}

add_filter( 'jci/register_template', 'register_post_template', 10, 1 );
function register_post_template( $templates = array() ) {
	$templates['post'] = 'JC_Post_Template';

	return $templates;
}