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
					'field' => 'post_status'
				),
				array(
					'title' => 'Author',
					'field' => 'post_author'
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
					'values' => array( 0, 1 )
				),
				array(
					'title'  => 'Allow Pingbacks',
					'field'  => 'ping_status',
					'values' => array( 'closed', 'open' )
				),
			)
		)
	);

	public function __construct() {
		parent::__construct();
		add_action( 'jci/after_template_fields', array( $this, 'field_settings' ) );
		add_action( 'jci/save_template', array( $this, 'save_template' ) );

		add_filter( 'jci/log_post_columns', array( $this, 'log_post_columns' ) );
		add_action( 'jci/log_post_content', array( $this, 'log_post_content' ), 10, 2 );
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
							'checked' => $enable_post_author
						) );
					echo JCI_FormHelper::checkbox( 'template_settings[enable_post_parent]', array(
							'label'   => 'Enable Parent Field',
							'checked' => $enable_post_parent
						) );
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

					$.fn.jci_enableField('enable_id', 'post-ID');
					$.fn.jci_enableField('enable_post_status', 'post-post_status');
					$.fn.jci_enableField('enable_post_author', 'post-post_author');
					$.fn.jci_enableField('enable_post_parent', 'post-post_parent');
					$.fn.jci_enableField('enable_menu_order', 'post-menu_order');
					$.fn.jci_enableField('enable_post_password', 'post-post_password');
					$.fn.jci_enableField('enable_post_date', 'post-post_date');
					$.fn.jci_enableField('enable_comment_status', 'post-comment_status');
					$.fn.jci_enableField('enable_ping_status', 'post-ping_status');

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
		}
	}

	public function before_template_save( $data, $current_row ) {

		global $jcimporter;
		$id = $jcimporter->importer->ID;

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

		global $jcimporter;
		$id = $jcimporter->importer->ID;

		if ( $this->enable_id == 0 ) {
			unset( $data['ID'] );
		}
		if ( $this->enable_post_status == 0 ) {

			// set default post status if none present
			$data['post_status'] = $this->_field_groups[ $group_id ]['post_status'];
		}
		if ( $this->enable_post_author == 0 ) {
			unset( $data['post_author'] );
		}
		if ( $this->enable_post_parent == 0 ) {
			unset( $data['post_parent'] );
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
		if ( $this->enable_comment_status == 0 ) {
			unset( $data['comment_status'] );
		}
		if ( $this->enable_ping_status == 0 ) {
			unset( $data['ping_status'] );
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
				echo $data['post']['post_title'];
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