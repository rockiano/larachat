<?php namespace Larachat\Models;
use Larachat\Libraries\Date;
use Laravel\File;
use Laravel\Session;
use Laravel\Database as DB;
use Larachat\Models\Message;

class User {
	private $user;
	private $user_name;

	public function __construct($user, $user_name = 'name')
	{
		$this->user = $user;
		$this->user_name = $user_name;
	}

	/**
	 * Gets stored chats that tue user left open in his last session
	 * @return Array Array containing the user id and the username of each chat
	 */
	public function getStoredChatsFromCache()
	{
		$myUser = $this->user;
		// there's a cache entry for each user, stored_chats_5, for a user with
		// ID = 5
		$cacheName = 'stored_chats_' . $myUser->id;
		$chats = \Cache::get($cacheName);

		$ret = array();

		if ($chats)
		{
			// iterate through cache and store them in an array
			foreach($chats as $chat)
			{
				$ret[] = array($chat, static::findName($chat));
			}
		}

		return $ret;
	}

	/**
	 * Stores a user as a left open chat in the cache
	 * @param  int $userID The userID to store
	 */
	public function storeChatToCache($userID)
	{
		$myUser = $this->user;
		// there's a cache entry for each user, stored_chats_5, for a user with
		// ID = 5
		$cacheName = 'stored_chats_' . $myUser->id;
		$chats = \Cache::get($cacheName);

		if ($chats)
		{
			\Cache::forget($cacheName);

			// check if user has stored chat before
			foreach ($chats as $chat)
			{
				if ($chat == $userID)
				{			
				// if that ID was already stored, save cache and return		
					\Cache::forever($cacheName, $chats);
					return;
				}
			}
			// if not then add it
			$chats[] = $userID;
		} else
		{
			// cache is empty, start a new one
			$chats = array($userID);
		}

		\Cache::forever($cacheName, $chats);
		return;
	}

	/**
	 * Removes a stored ID as left open from the cache
	 * @param  int $userID The user ID to remove
	 */
	public function removeChatFromCache($userID)
	{
		$myUser = $this->user;
		$cacheName = 'stored_chats_' . $myUser->id;
		// Create new user array
		$chats = \Cache::get($cacheName);
		\Cache::forget($cacheName);
		$new_chats = array();

		if ($chats)
		{
			foreach($chats as $chat)
			{
				// Only add to new array if the user is different from
				// the specified parameter
				if ($chat != $userID)
				{
					$new_chats[] = $chat;
				}				
			}
		}
		// Store new array back in cache
		\Cache::forever($cacheName, $new_chats);
	}

	/**
	 * Adds the current user's nickname to the cache
	 */
	public function addNickToCache()
	{
		$users = \Cache::get('online_users');
		$myUser = $this->user;

		if ($users)
		{
			\Cache::forget('online_users');

			// check if nick is already stored
			foreach ($users as $user)
			{
				if ($user[0] == $myUser->id)
				{
					$user[1] = $this->user_name;
					\Cache::forever('online_users', $users);
					return;
				}
			}
		}

		$users[] = array($myUser->id, $this->user_name);
		\Cache::forever('online_users', $users);
		return;
	}

	/**
	 * Adds a nickname to the cache
	 * @param int $id   The user ID
	 * @param string $nick The user's nickname to be stored
	 */
	public static function addNick($id, $nick)
	{
		$users = \Cache::get('online_users');

		if ($users)
		{
			\Cache::forget('online_users');

			// check if nick is already stored
			foreach ($users as $user)
			{
				if ($user[0] == $id)
				{
					$user[1] = $nick;
					\Cache::forever('online_users', $users);
					return;
				}
			}
		}

		$users[] = array($id, $nick);
		\Cache::forever('online_users', $users);
		return;
	}

	/**
	 * Gets user's nick from cache
	 * @return string the user's stored nick in cache
	 */
	public function getNickFromCache()
	{
		$myUser = $this->user;
		// Get currently stored in cache nicknames
		$users = \Cache::get('online_users');

		if ($users)
		{
			foreach($users as $user)
			{
				if ($user[0] == $myUser->id)
					return $user[1];
			}
		}
		return null;
	}

	/**
	 * Gets a stored nick in the cache
	 * @param  int $id the User ID
	 * @return string     The user's nickname stored in cache
	 */
	public static function getNick($id)
	{
		// Get currently stored in cache nicknames
		$users = \Cache::get('online_users');

		if ($users)
		{
			foreach($users as $user)
			{
				if ($user[0] == $id)
					return $user[1];
			}
		}

		return null;
	}

	/**
	 * Removes the user's nick from the cache
	 */
	public function removeNickFromCache()
	{
		$myUser = $this->user;
		// Create new user array
		$users = \Cache::get('online_users');
		\Cache::forget('online_users');
		$new_users;

		if ($users)
		{
			foreach($users as $user)
			{
				// Only add to new array if the user is different from
				// the specified parameter
				if ($user[0] != $this->id)
					$new_users[] = $user;
			}
		}
		// Store new array back in cache
		\Cache::forever('online_users', $new_users);
	}
	/**
	 * Removes a stored nick from cache
	 * @param  int $id The user's id to remove
	 */
	public static function removeNick($id)
	{
		// Create new user array
		$users = \Cache::get('online_users');
		\Cache::forget('online_users');
		$new_users = array();

		if ($users)
		{
			foreach($users as $user)
			{
				// Only add to new array if the user is different from
				// the specified parameter
				if ($user[0] != $id)
					$new_users[] = $user;
			}
		}
		// Store new array back in cache
		\Cache::forever('online_users', $new_users);
	}

	/**
	 * Updates the user's timestamps
	 */
	public function updateTimestamps()
	{
		$myUser = $this->user;
		// Find the User object and update its timestamp
		$user = \User::find($myUser->id);
		$user->timestamp();
		$user->save();
	}
	/**
	 * Updates the timestamps of the specified User id
	 * @param  int $id The user
	 */
	public static function updateTimestamp($id)
	{
		// Find the User object and update its timestamp
		$user = \User::find($id);
		$user->timestamp();
		$user->save();
	}

	/**
	 * Gets the online users' IDs
	 * @return User[] An array with the user objects of the logged on users
	 */
	public static function getOnlineUsers()
	{
		$users = array();

		if (\Cache::has('online_users'))
		{
			// Get active users from cache
			$online_users = \Cache::get('online_users');

			foreach($online_users as $user)
			{
				// Get user object
				$temp = \User::find($user[0]);
				$now = Date::forge();
				$diff = Date::diff($now, $temp->updated_at);

				// check timestamp for 5 minutes
				if ($diff->i > 5 ||
					$diff->y > 0 ||
					$diff->m > 0 ||
					$diff->d > 0 ||
					$diff->h > 0)
				{
					// If user hasn't been active for the last 5 minutes
					// remove from cache
					static::removeNick($temp->id);
				} else
				{
					$temp->nick = $user[1];
					$users[] = $temp;
				}
			}
		}

		return $users;
	}

	/**
	 * Returns online Users, currently returns Eloquent User, in future should
	 * return Larachat User
	 * @return User[] Array with online users
	 */
	public static function getOnline()
	{
		$users = array();

		if (\Cache::has('online_users'))
		{
			// Get active users from cache
			$online_users = \Cache::get('online_users');

			foreach($online_users as $user)
			{
				// Get user object
				$temp = \User::find($user[0]);
				$now = Date::forge();
				$diff = Date::diff($now, $temp->updated_at);

				// check timestamp for 5 minutes
				if ($diff->i > 5 ||
					$diff->y > 0 ||
					$diff->m > 0 ||
					$diff->d > 0 ||
					$diff->h > 0)
				{
					// If user hasn't been active for the last 5 minutes
					// remove from cache
					// TODO: change this to new non-static function
					static::removeNick($temp->id);
				} else
				{					
					$temp->nick = $user[1];
					$users[] = $temp;
				}
			}
		}
		return $users;
	}

	/**
	 * Returns whether user is online or not
	 * @return boolean True if user is currently logged into the chat app
	 */
	public function isOnline()
	{
		// Get online users from cache
		if (\Cache::has('online_users'))
		{
			$online = \Cache::get('online_users');
			// for each user stored in cache where
			// cache[0] = userID
			// cache[1] = user's nickname
			foreach($online as $online_user)
			{
				$user = \User::find($online_user[0]);
				if ($this->user == $user)
				{
					// User IS online, return true
					return true;
				}
			}
		} else
		{
			// if there's no cache, immediately return false
			return false;
		}
		// user is not online, return false
		return false;
	}

	/**
	 * Returns offline Users, currently returns Eloquent user, should return
	 * larachat user in near future
	 * @return User[] Array with offline users
	 */
	public static function getOffline()
	{
		// Get all users in DB
		$users = \User::all();
		// Array to return with results
		$offline_users = array();

		foreach($users as $user)
		{
			// Obtain larachat user from Eloquent user
			$tempLaraUser = new static($user, $user->name);
			// If not online, add to result array
			if (!$tempLaraUser->isOnline())
			{
				// add name as nick
				$user->nick = $user->name;
				$offline_users[] = $user;
			}
		}
		return $offline_users;
	}

	public function getName()
	{
		$user_name = $this->user_name;
		return $this->user->$user_name;
	}
	public function getOpenChats()
	{
		// TODO: Remove hack
		$chats = Session::get('chats', array(2)); // Testing hack
		$chats_content = array();
		foreach ($chats as $chat) {
			$chats_content[$chat] = $this->messages($chat)->get();
		}
		return $chats_content;
	}

	public function unread($participant = 0)
	{
		// TODO: add participant filter
		// Status 1 = unread
		return $this->incoming()->where_to($this->id)->where_status('1');
	}
	public function messages($arguments)
	{

		$date = Date::forge('now - 3 hours'); // Default history 3 hours
		$own_id = $this->id;
		$query = DB::table('messages')->where(function($query) use ($own_id){
			$query->where_from($own_id);
			$query->or_where('to', '=', $own_id);
		});
		if(is_array($arguments)) {
			// TODO: various arguments
		} else {
			// If only one argument, assume it's the participant id
			$participant = $arguments;
			$query->where(function ($query) use ($participant)
			{
				$query->where_from($participant);
				$query->or_where('to', '=',$participant);
			});
		}
		 $query->where('created_at', '>=', $date->format('datetime'));
		 $query->order_by('created_at', 'asc');
		return $query;
	}

	/**
	 * Gets the users' ids from which the current user has unread messages from
	 * @return int[] Array with the users' IDs
	 */
	public static function getUnreadUsers()
	{
		$myId = \Auth::user()->id;

		// Get all unread messages directed to me
		$messages = DB::table('messages')->where('status', '=', 'false')
										 ->where(function($query) use ($myId) {			
			$query->or_where('to', '=', $myId);
		})->get();

		$users = array();

		foreach($messages as $message)
		{
			$users[] = $message->from;
			// $users['nick'] = $message->nick;
		}

		return array_unique($users);
	}

	/**
	 * Gets user's unread private messages
	 * @return Message[] Array with unread private messages
	 */
	public function getPrivateUnread()
	{
		$myUser = $this->user;
		$messages = Message::where('status', '=', false)
							 ->where('to', '=', $myUser->id)							 
							 ->get();

		return $messages;
	}

	/**
	 * Marks all private messages from a specified user as read, up until 
	 * the message ID passed as a parameter
	 * @param  int $from      The other user's ID
	 * @param  id $messageid The message ID up until which to mark messages as read
	 */
	public function markAsReadFromUntilID($from, $messageid)
	{
		$myUser = $this->user;
		$myId = $myUser->id;

		$affected = Message::where('from', '=', $from)
										 ->where('to', '=', $myId)
										 ->where('id', '<=', $messageid)
										 ->update(array('status' => true));
	}

	public function getPrivateFromUpTo($from, $messageid)
	{
		$myUser = $this->user;
		$myId = $myUser->id;

		$messages = \DB::table('messages')->where('id', '<', $messageid)
										  ->where('to', '=', $from)
										  ->where('from', '=', $myId)
										  ->get();

		$messages2 = \DB::table('messages')->where('id', '<', $messageid)
										  ->where('to', '=', $myId)
										  ->where('from', '=', $from)
										  ->get();	

		return $messages + $messages2;
	}

	public function getLastTenFrom($from)
	{
		$myUser = $this->user;
		$myId = $myUser->id;

		$messages = Message::where(function($query) use ($from, $myId) {
			$query->where('to', '=', $from);
			$query->where('from', '=', $myId);
		})->or_where(function ($query) use ($from, $myId) {
			$query->where('to', '=', $myId);
			$query->where('from', '=', $from);
		})->order_by('id', 'desc')
		  ->take(10)
		  ->get();

		  return $messages;
	}

	public function incoming()
	{
		return $this->user->has_many('Larachat\\Models\\Message', 'to');
	}

	public function outgoing()
	{
		return $this->user->has_many('Larachat\\Models\\Message', 'from');
	}

	public function __get($name)
	{
		if($name == 'incoming') {
			return $this->incoming()->get();
		} else if($name == 'outgoing') {
			return $this->outgoing()->get();
		} else {
			return $this->user->$name;
		}
	}

	/**
	 * Finds the name of a user ID in the Laravel User model
	 * @param  int $id The id to look up
	 * @return String     The name for that ID
	 */
	public static function findName($id)
	{
		$user = \User::find($id);
		return $user->name;
	}

}
