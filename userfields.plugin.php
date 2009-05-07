<?php
/**
 * User Fields - A plugin to display additional fields on the user page
 *
 * echo $post->author->info->userfield_{your field}
 **/
class userfields extends Plugin
{
	public function info()
	{
		return array(
			'name' => 'User Fields',
			'version' => '0.01',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'license' => 'Apache License 2.0',
			'description' => 'Allows site administrators to create new user data fields.',
			'copyright' => '2009'
		);
	}

	/**
	* Add actions to the plugin page for this plugin
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ( $plugin_id == $this->plugin_id() ){
			$actions[ 'Configure' ] = _t( 'Configure' );
		}

		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()) {
			if ( $action == 'Configure' ) {
				$ui = new FormUI('userfields');
				$ui->append('static', 'typelabel', _t( 'Fields to add to Users' ) );
				$ui->append('textmulti', 'fields', 'userfields__fields' , 'Additional Fields:');
				$ui->append('submit', 'submit', 'Submit');
				$ui->out();
			}
		}
	}

	/**
	* Add additional controls to the User page
	*
	* @param FormUI $form The form that is used on the User page
	* @param Post $post The user being edited
	**/
	public function action_form_user( $form, $edit_user )
	{
		$fields = Options::get('userfields__fields' );
		if(!is_array($fields) || count($fields) == 0) {
			return;
		}

		$userfields = $form->append( 'wrapper', 'userfields', 'User Fields');
		$userfields->class = 'container settings';
		$userfields->append( 'static', 'userfields', _t( '<h2>Additional fields</h2>' ) );

		foreach($fields as $field) {
			$fieldname = "userfield_{$field}";
			$customfield = $userfields->append('text', $fieldname, 'null:null', $field);
			$customfield->value = isset($edit_user->info->{$field}) ? $edit_user->info->{$field} : '';
			$customfield->class[] = 'important item clear';
			$customfield->template = 'optionscontrol_text';
			$userfields->append('static','wtf' . $fieldname , $fieldname );
		}
		$form->move_after( $userfields, $form->user_info );
	}

	/**
	 * Add the Additional User Fields to the list of valid field names.
	 * This causes adminhandler to recognize the fields and
	 * to set the userinfo record appropriately
	**/
	public function filter_adminhandler_post_user_fields( $fields )
	{
		
		$userfields = Options::get( 'userfields__fields' );
		if ( !is_array($userfields) || count( $userfields ) == 0 ) {
			return;
		}

		foreach($userfields as $field) {
			$fields[ $field ] = "userfield_{$field}";

		}
		return $fields;
	}
	
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'User Fields', 'a847f8d0-e63e-4501-9bd0-e9a0013c0e2b', $this->info->version );
	}
}
?>
