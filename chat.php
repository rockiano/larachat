<? namespace LaraChat;

/**
 * --------------------------------------------------------------------------
 * What we can use in this class
 * --------------------------------------------------------------------------
 */
use Laravel\Session;
use Laravel\View;
use LaraChat\Models\User;

/**
 * --------------------------------------------------------------------------
 * Lara Cart
 * --------------------------------------------------------------------------
 *
 * A Shopping Cart based on the Cart library from CodeIgniter for use with
 * the Laravel Framework.
 *
 * @package  Lara-Chat
 * @version  1.0
 * @author   Marco Rivadeneyra <mark@20d.mx>
 * @link     https://github.com/rockiano/Lara-Chat
 */
class Chat
{
	public static function create($user = null)
	{
		if(is_object($user)) {
			$user = new User($user);
			return View::make('larachat::index')->with('user', $user);
		} else {
			return 'Invalid user';
		}
	}
}