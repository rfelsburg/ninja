<?php defined('SYSPATH') OR die('No direct access allowed.');

/*
TODO: Authorized for.
*/

abstract class Auth_User_Model extends Model {

	// Relationships
	protected $has_many = array('user_tokens');
	protected $has_and_belongs_to_many = array('roles');
	#protected $loadwith = array('roles');

	// Columns to ignore
	protected $ignored_columns = array('password_confirm');

	public function __set($key, $value)
	{
		if ($key === 'password')
		{
			// Use Auth to hash the password
			$value = Auth::instance()->hash_password($value);
		}

		parent::__set($key, $value);
	}

	/**
	* @param 	string 		authorization point
	* @return 	boolean 	
	*/
	abstract public function authorized_for($auth_point);

	/**
	 * Validates and optionally saves a new user record from an array.
	 *
	 * @param  array    values to check
	 * @param  boolean  save the record when validation succeeds
	 * @return boolean
	 */
	public function validate(array & $array, $save = FALSE)
	{
		$min_username_chars = Kohana::config('auth.min_username_chars');
		$min_username_chars !=0 ? $min_username_chars : 2; # use minimum 2 chars if not set
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('realname', 'required', 'length[3,50]')
			->add_rules('username', 'required', 'length['.$min_username_chars.',32]', 'chars[a-zA-Z0-9_.]', array($this, 'username_available'))
			->add_rules('password', 'required', 'length[5,42]')
			->add_rules('password_confirm', 'matches[password]');

		return parent::validate($array, $save);
	}

	/**
	 * Validates login information from an array, and optionally redirects
	 * after a successful login.
	 *
	 * @param  array    values to check
	 * @param  string   URI or URL to redirect to
	 * @return boolean
	 */
	public function login(array & $array, $redirect = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('username', 'required', 'length[4,127]')
			->add_rules('password', 'required', 'length[5,42]');

		// Login starts out invalid
		$status = FALSE;

		if ($array->validate())
		{
			// Attempt to load the user
			$this->find($array['username']);

			if ($this->loaded AND Auth::instance()->login($this, $array['password']))
			{
				if (is_string($redirect))
				{
					// Redirect after a successful login
					url::redirect($redirect);
				}

				// Login is successful
				$status = TRUE;
			}
			else
			{
				$array->add_error('username', 'invalid');
			}
		}

		return $status;
	}

	/**
	 * Validates an array for a matching password and password_confirm field.
	 *
	 * @param  array    values to check
	 * @param  string   save the user if
	 * @return boolean
	 */
	public function change_password(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('password', 'required', 'length[5,127]')
			->add_rules('password_confirm', 'matches[password]');

		if ($status = $array->validate())
		{
			// Change the password
			$this->password = $array['password'];

			if ($save !== FALSE AND $status = $this->save())
			{
				if (is_string($save))
				{
					// Redirect to the success page
					url::redirect($save);
				}
			}
		}

		return $status;
	}

	/**
	 * Allows a model to be loaded by username or email address.
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return valid::email($id) ? 'email' : 'username';
		}

		return parent::unique_key($id);
	}

} // End Auth User Model
