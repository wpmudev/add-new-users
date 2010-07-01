<?php
/*
Plugin Name: Add New Users
Plugin URI: 
Description:
Author: Andrew Billits
Version: 1.0.2
Author URI:
WDP ID: 114
*/ 
$add_new_users_current_version = '1.0.2';
//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//

$add_new_users_supporter_only = 'no'; //Either 'yes' OR 'no'

$add_new_users_fields = $_GET['fields'];
if ($add_new_users_fields == ''){
	$add_new_users_fields = 15;
} else if ($add_new_users_fields > 50){
	$add_new_users_fields = 50;
}
//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
//check for activating
if ($_GET['page'] == 'add-new-users'){
	add_new_users_make_current();
}
add_action('admin_menu', 'add_new_users_plug_pages');
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
function add_new_users_make_current() {
	global $wpdb, $add_new_users_current_version;
	if (get_site_option( "add_new_users_version" ) == '') {
		add_site_option( 'add_new_users_version', '0.0.0' );
	}
	
	if (get_site_option( "add_new_users_version" ) == $add_new_users_current_version) {
		// do nothing
	} else {
		//update to current version
		update_site_option( "add_new_users_installed", "no" );
		update_site_option( "add_new_users_version", $add_new_users_current_version );
	}
	add_new_users_global_install();
	//--------------------------------------------------//
	if (get_option( "add_new_users_version" ) == '') {
		add_option( 'add_new_users_version', '0.0.0' );
	}
	
	if (get_option( "add_new_users_version" ) == $add_new_users_current_version) {
		// do nothing
	} else {
		//update to current version
		update_option( "add_new_users_version", $add_new_users_current_version );
		add_new_users_blog_install();
	}
}

function add_new_users_blog_install() {
	global $wpdb, $add_new_users_current_version;
	$add_new_users_hits_table = "";

	//$wpdb->query( $add_new_users_hits_table );
}

function add_new_users_global_install() {
	global $wpdb, $add_new_users_current_version;
	if (get_site_option( "add_new_users_installed" ) == '') {
		add_site_option( 'add_new_users_installed', 'no' );
	}
	
	if (get_site_option( "add_new_users_installed" ) == "yes") {
		// do nothing
	} else {
	
		$add_new_users_table1 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "add_new_users_queue` (
  `add_new_users_ID` bigint(20) unsigned NOT NULL auto_increment,
  `add_new_users_site_ID` bigint(20),
  `add_new_users_blog_ID` bigint(20),
  `add_new_users_batch_ID` varchar(255),
  `add_new_users_user_login` varchar(255),
  `add_new_users_user_email` varchar(255),
  `add_new_users_user_password` varchar(255),
  `add_new_users_user_role` varchar(255),
  PRIMARY KEY  (`add_new_users_ID`)
) ENGINE=MyISAM;";

		$wpdb->query( $add_new_users_table1 );

		update_site_option( "add_new_users_installed", "yes" );
	}
}

function add_new_users_plug_pages() {
	global $wpdb, $wp_roles, $current_user;
	if(current_user_can('manage_options')) {
		add_submenu_page('users.php', 'Add New Users', 'Add New Users', 10, 'add-new-users', 'add_new_users_page_output');
	}
}

function add_new_users_queue_insert($batch_ID,$user_login,$user_email,$user_password,$user_role) {
	global $wpdb, $current_site;
	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "add_new_users_queue (add_new_users_site_ID,add_new_users_blog_ID,add_new_users_batch_ID,add_new_users_user_email,add_new_users_user_role,add_new_users_user_login,add_new_users_user_password) VALUES ( '" . $wpdb->siteid . "','" . $wpdb->blogid . "','" . $batch_ID . "','" . $user_email . "','" . $user_role . "','" . $user_login . "','" . $user_password . "' )" );
}

function add_new_users_queue_process($blog_ID,$site_ID) {
	global $wpdb, $current_site, $base;


	$query = "SELECT * FROM " . $wpdb->base_prefix . "add_new_users_queue WHERE add_new_users_site_ID = '" . $site_ID . "' AND add_new_users_blog_ID = '" . $blog_ID . "' LIMIT 1";
	$add_new_users_items = $wpdb->get_results( $query, ARRAY_A );
	//------------------------------//
	if (count($add_new_users_items) > 0){
		foreach ($add_new_users_items as $add_new_users_item){
		//=====================================================================//
		$user_login = stripslashes($add_new_users_item['add_new_users_user_login']);
		$user_email = stripslashes($add_new_users_item['add_new_users_user_email']);
		$user_password = stripslashes($add_new_users_item['add_new_users_user_password']);
		
		if ( $user_password == 'empty' ) {
			$user_password = generate_random_password();
		}
		
		$user_id = wpmu_create_user($user_login, $user_password, $user_email);
		add_user_to_blog( $wpdb->blogid, $user_id, $add_new_users_item['add_new_users_user_role'] );
		//wp_new_user_notification($user_id, $user_password);
		wpmu_welcome_user_notification($user_id, $user_password, '');
		//=====================================================================//
		$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "add_new_users_queue WHERE add_new_users_blog_ID = '" . $wpdb->blogid . "' AND add_new_users_site_ID = '" . $wpdb->siteid . "' AND add_new_users_ID = '" . $add_new_users_item['add_new_users_ID'] . "'" );
		}
	}
}

function add_new_users_is_supporter() {
	if ( function_exists('is_supporter') ) {
		return is_supporter();
	} else {
		return false;
	}
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function add_new_users_page_output() {
	global $wpdb, $wp_roles, $current_user, $user_ID, $current_site, $add_new_users_fields, $add_new_users_supporter_only;

	if(!current_user_can('manage_options')) {
		?>
		<p><?php _e('You do not have permission to access this page') ?></p>
        <?php
		return;
	}

	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	if ( !add_new_users_is_supporter() && $add_new_users_supporter_only == 'yes' ) {
		supporter_feature_notice();
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			?>
			<h2><?php _e('Add New Users') ?></h2>
            <p>This tool allows you to create new users on this site and add them to this blog.</p>
			<?php if ( function_exists('add_users_plug_pages') ) { ?>
            <p>To add users that have already been created, please use the <a href="users.php?page=add-users">Add Users functionality here</a>.</p>
            <?php } ?>
            <p>To add the new users simply enter the username you'd like to give them (please choose carefully as it cannot be changed), their email address and - should you so choose - a password for them.</p>
            <p>You may also select the level that you wish them to access to the site - you can find out more about different levels of access <a href="http://help.edublogs.org/2009/08/24/what-are-the-different-roles-of-users/">here</a>.</p>
            <p>If you do not enter a password a random one will be generated for them.</p>
            <p>All new users will receive an email containing their new username, password and login link.</p>
            <?php
			if ($_GET['fields'] != ''){
				?>
				<form name="form1" method="POST" action="users.php?page=add-new-users&action=process&fields=<?php echo $_GET['fields'];?>">
				<?php
			} else {
				?>
				<form name="form1" method="POST" action="users.php?page=add-new-users&action=process">
				<?php
			}
			?>
            <?php
				for ( $counter = 1; $counter <= $add_new_users_fields; $counter += 1) {
					if ($counter == 1 || $counter == 6 || $counter == 11 || $counter == 16 || $counter == 21 || $counter == 26 || $counter == 31 || $counter == 36 || $counter == 41 || $counter == 46){
					?>
                    	<!---
                        <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Next') ?>" />
                        </p>
		                <p style="text-align:right;"><?php _e('This may take some time so please be patient.') ?></p>
                        --->
                    <?php
					}
					//==================================================================================================================//
					//==================================================================================================================//
					?>
					<h3><?php _e($counter . ':') ?></h3>
						<table class="form-table">
						<tr valign="top">
						<th scope="row"><?php _e('Username') ?></th>
						<td><input <?php if ( !add_new_users_is_supporter() && $add_new_users_supporter_only == 'yes' ) { echo 'disabled="disabled"'; } ?> type="text" name="user_login_<?php echo $counter; ?>" id="user_login_<?php echo $counter; ?>" style="width: 95%"  maxlength="200" value="<?php echo $_POST['user_login_' . $counter]; ?>" />
						<br />
						<?php _e('Required') ?></td> 
						</tr>
						<tr valign="top">
						<th scope="row"><?php _e('User Email') ?></th>
						<td><input <?php if ( !add_new_users_is_supporter() && $add_new_users_supporter_only == 'yes' ) { echo 'disabled="disabled"'; } ?> type="text" name="user_email_<?php echo $counter; ?>" id="user_email_<?php echo $counter; ?>" style="width: 95%"  maxlength="200" value="<?php echo $_POST['user_email_' . $counter]; ?>" />
						<br />
						<?php _e('Required') ?></td> 
						</tr>
						<tr valign="top">
						<th scope="row"><?php _e('User Password') ?></th>
						<td><input <?php if ( !add_new_users_is_supporter() && $add_new_users_supporter_only == 'yes' ) { echo 'disabled="disabled"'; } ?> type="text" name="user_password_<?php echo $counter; ?>" id="user_password_<?php echo $counter; ?>" style="width: 95%"  maxlength="200" value="<?php echo $_POST['user_password_' . $counter]; ?>" />
						<br />
						<?php _e('If no password is entered here a random password will be generated and emailed to the user') ?></td> 
						</tr>
						<tr valign="top"> 
						<th scope="row"><?php _e('User Role') ?></th> 
						<td><select <?php if ( !add_new_users_is_supporter() && $add_new_users_supporter_only == 'yes' ) { echo 'disabled="disabled"'; } ?> name="user_role_<?php echo $counter; ?>" style="width: 25%;">
							<?php
                            foreach($wp_roles->role_names as $role => $name) {
								$name = str_replace("|User role","",$name);
                                $selected = '';
                                if($_POST['user_role_' . $counter] == $role){
                                    $selected = 'selected="selected"';
								} else if ($role == 'subscriber') {
                                    $selected = 'selected="selected"';
								}
                                echo "<option {$selected} value=\"{$role}\">{$name}</option>";
                            }
                            ?>
						</select>
						<br />
						<?php //_e('') ?></td> 
						</tr>
						</table>
					<?php
					//==================================================================================================================//
					//==================================================================================================================//
				}
			?>
			<p class="submit">
			<input <?php if ( !add_new_users_is_supporter() && $add_new_users_supporter_only == 'yes' ) { echo 'disabled="disabled"'; } ?> type="submit" name="Submit" value="<?php _e('Next') ?>" />
			</p>
            <p style="text-align:right;"><?php _e('This may take some time so please be patient.') ?></p>
			</form>
			<?php
		break;
		//---------------------------------------------------//
		case "process":
			if ( isset($_POST['Cancel']) ) {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='users.php?page=add-new-users';
				</script>
				";
			}
			$batch_ID = md5($wpdb->blogid . time() . '0420i203zm');
			$errors = '';
			$error_fields = '';
			$error_messages = '';
			$global_errors = 0;
			$add_new_users_items = '';
			
			for ( $counter = 1; $counter <= $add_new_users_fields; $counter += 1) {
				$user_login = $_POST['user_login_' . $counter];
				$user_email = $_POST['user_email_' . $counter];
				$user_password = $_POST['user_password_' . $counter];
				$user_role = $_POST['user_role_' . $counter];
				$error = 0;
				$error_field = '';				
				$error_msg = '';
				
				if (empty($user_email) && empty($user_login)){
					//blank fields - skip this one
				} else {
					//User / Email
					//========================================//
					$email_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users WHERE user_email = '" . $user_email . "'" );
					if ($email_count > 0){
						$error = 1;
						$error_field = 'user_email';
						$error_msg = __("A user with that email address already exists");
					}
					
					$user_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users WHERE user_login = '" . $user_login . "'" );
					if ($user_count > 0){
						$error = 1;
						$error_field = 'user_login';
						if ( function_exists('add_users_plug_pages') ) {
							$error_msg = __("A user by that name already exists - please use the <a href='users.php?page=add-users'>Add Existing Users</a> form for users already registered at this site.");
						} else {
							$error_msg = __("A user by that name already exists");
						}
					}
					
					$signup = $wpdb->get_row("SELECT * FROM $wpdb->signups WHERE user_login = '" . $user_login . "'");
					if ( $signup != null ) {
						$registered_at =  mysql2date('U', $signup->registered);
						$now = current_time( 'timestamp', true );
						$diff = $now - $registered_at;
						// If registered more than two days ago, cancel registration and let this signup go through.
						if ( $diff > 172800 ) {
							$wpdb->query("DELETE FROM $wpdb->signups WHERE user_login = '" . $user_login . "'");
						} else {
							$error = 1;
							$error_field = 'user_login';
							$error_msg = __("That username is currently reserved but may be available in a couple of days");
						}
					}

					preg_match( "/[a-z0-9]+/", $user_login, $maybe );
					if( $user_login != $maybe[0] ) {
						$error = 1;
						$error_field = 'user_login';
						$error_msg = __("Only lowercase letters and numbers allowed in usernames");
					}

					preg_match( '/[0-9]*/', $user_login, $match );
					if ( $match[0] == $user_login ){
						$error = 1;
						$error_field = 'user_login';
						$error_msg = __("Usernames must have letters too");
					}
					if( strlen( $user_login ) < 4 ) {
						$error = 1;
						$error_field = 'user_login';
						$error_msg = __("Usernames must be at least 4 characters");
					}
					if ( strpos( " " . $user_login, "_" ) != false ){
						$error = 1;
						$error_field = 'user_login';
						$error_msg = __("Usernames may not contain the character '_'");
					}

					$illegal_names = get_site_option( "illegal_names" );
					if( is_array( $illegal_names ) == false ) {
						$illegal_names = array(  "www", "web", "root", "admin", "main", "invite", "administrator" );
						add_site_option( "illegal_names", $illegal_names );
					}
					if( in_array( $user_login, $illegal_names ) == true ) {
						$error = 1;
						$error_field = 'user_login';
						$error_msg = __("That username is not allowed");
					}
					
					if (empty($user_login)){
						$error = 1;
						$error_field = 'user_login';
						$error_msg = __("You must provide a username");
					}
					
					if (is_email_address_unsafe($user_email)){
						$error = 1;
						$error_field = 'user_email';
						$error_msg = __("You cannot use that email address. Please use another email provider.");
					}
					if (!is_email($user_email)){
						$error = 1;
						$error_field = 'user_email';
						$error_msg = __("Please enter a correct email address");
					}
					if (!validate_email($user_email)){
						$error = 1;
						$error_field = 'user_email';
						$error_msg = __("That email address is not valid");
					}
					$limited_email_domains = get_site_option( 'limited_email_domains' );
					if ( is_array( $limited_email_domains ) && empty( $limited_email_domains ) == false ) {
						$emaildomain = substr( $user_email, 1 + strpos( $user_email, '@' ) );
						if( in_array( $emaildomain, $limited_email_domains ) == false ) {
							$error = 1;
							$error_field = 'user_email';
							$error_msg = __("That email address is not allowed");
						}
					}
					
					if (empty($user_email)){
						$error = 1;
						$error_field = 'user_email';
						$error_msg = __("You must provide an email address");
					}
					
					//Password
					//========================================//
					if ( !empty( $user_password ) ) {
						$tmp_password = str_replace(" ", "", $user_password);
						if( $tmp_password != $user_password ) {
							$error = 1;
							$error_field = 'user_password';
							$error_msg = __("Passwords cannot contain spaces");
						}
					}
					//========================================//
					if ( empty( $user_password ) ) {
						$user_password = 'empty';
					}
					$add_new_users_items[$counter]['user_login'] = $user_login;
					$add_new_users_items[$counter]['user_email'] = $user_email;
					$add_new_users_items[$counter]['user_password'] = $user_password;
					$add_new_users_items[$counter]['user_role'] = $user_role;
					
					$errors[$counter] = $error;
					$error_fields[$counter] = $error_field;
					$error_messages[$counter] = $error_msg;
					if ($error == 1){
						$global_errors = $global_errors + 1;
					}
				}
			}
			if ($global_errors > 0){
				//========================================//
				//houston... we have error(s)
				?>
				<h2><?php _e('Add New Users') ?></h2>

				<p><?php _e('Errors were found. Please fix the errors and hit Next.') ?></p>
				
				<?php
                if ($_GET['fields'] != ''){
                    ?>
                    <form name="form1" method="POST" action="users.php?page=add-new-users&action=process&fields=<?php echo $_GET['fields'];?>">
                    <?php
                } else {
                    ?>
                    <form name="form1" method="POST" action="users.php?page=add-new-users&action=process">
                    <?php
                }
                ?>
				<?php
					for ( $counter = 1; $counter <= $add_new_users_fields; $counter += 1) {
						if ($counter == 1 || $counter == 6 || $counter == 11 || $counter == 16 || $counter == 21 || $counter == 26 || $counter == 31 || $counter == 36 || $counter == 41 || $counter == 46){
						?>
                        	<!---
							<p class="submit">
							<input type="submit" name="Submit" value="<?php _e('Next') ?>" />
			                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" /> 
							</p>
			                <p style="text-align:right;"><?php _e('This may take some time so please be patient.') ?></p>
                            --->
						<?php
						}
						//==================================================================================================================//
						//==================================================================================================================//
						if ($errors[$counter] == 1){
							?>
							<h3 style="background-color:#F79696; padding:5px 5px 5px 5px;"><?php _e($counter . ': ') ?><?php echo $error_messages[$counter]; ?></h3>
                            <?php						
						} else {
							?>
							<h3><?php _e($counter . ':') ?></h3>
                            <?php
						}
						?>
							<table class="form-table">
                            <tr valign="top">
                            <th scope="row"><?php _e('Username') ?></th>
                            <td><input type="text" name="user_login_<?php echo $counter; ?>" id="user_login_<?php echo $counter; ?>" style="width: 95%<?php if ($error_fields[$counter] == 'user_login'){ echo ' background-color:#F79696;'; } ?>"  maxlength="200" value="<?php echo $_POST['user_login_' . $counter]; ?>" />
                            <br />
                            <?php _e('Required') ?></td> 
                            </tr>
							<tr valign="top">
							<th scope="row"><?php _e('User Email') ?></th>
							<td><input type="text" name="user_email_<?php echo $counter; ?>" id="user_email_<?php echo $counter; ?>" style="width: 95%;<?php if ($error_fields[$counter] == 'user_email'){ echo ' background-color:#F79696;'; } ?>"  maxlength="200" value="<?php echo $_POST['user_email_' . $counter]; ?>" />
							<br />
							<?php _e('Required') ?></td> 
							</tr>
							<tr valign="top">
							<th scope="row"><?php _e('User Password') ?></th>
							<td><input type="text" name="user_password_<?php echo $counter; ?>" id="user_password_<?php echo $counter; ?>" style="width: 95%;<?php if ($error_fields[$counter] == 'user_password'){ echo ' background-color:#F79696;'; } ?>"  maxlength="200" value="<?php echo $_POST['user_password_' . $counter]; ?>" />
							<br />
							<?php _e('If no password is entered here a random password will be generated and emailed to the user') ?></td> 
							</tr>
                            <tr valign="top"> 
                            <th scope="row"><?php _e('User Role') ?></th> 
                            <td><select name="user_role_<?php echo $counter; ?>" style="width: 25%;">
                                <?php
                                foreach($wp_roles->role_names as $role => $name) {
                                    $selected = '';
                                    if($_POST['user_role_' . $counter] == $role){
                                        $selected = 'selected="selected"';
                                    }
                                    echo "<option {$selected} value=\"{$role}\">{$name}</option>";
                                }
                                ?>
                            </select>
                            <br />
                            <?php //_e('') ?></td> 
                            </tr>
							</table>
						<?php
						//==================================================================================================================//
						//==================================================================================================================//
					}
				?>
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Next') ?>" />
                <input type="submit" name="Cancel" value="<?php _e('Cancel') ?>" /> 
				</p>
                <p style="text-align:right;"><?php _e('This may take some time so please be patient.') ?></p>
				</form>
				<?php
				//========================================//
			} else {
				//========================================//
				//Process
				if ( count( $add_new_users_items ) > 0 && is_array($add_new_users_items) ) {
					echo '<p>' . __('Adding Users...') . '</p>';
					foreach ($add_new_users_items as $add_new_users_item){
						add_new_users_queue_insert($batch_ID,addslashes($add_new_users_item['user_login']),addslashes($add_new_users_item['user_email']),addslashes($add_new_users_item['user_password']),$add_new_users_item['user_role']);
					}
				}
				$queue_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "add_new_users_queue WHERE add_new_users_site_ID = '" . $wpdb->siteid . "' AND add_new_users_blog_ID = '" . $wpdb->blogid . "'" );
				if ($queue_count > 0){
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='users.php?page=add-new-users&action=process_queue';
					</script>
					";				
				} else {
					echo "
					<SCRIPT LANGUAGE='JavaScript'>
					window.location='users.php?page=add-new-users';
					</script>
					";
				}
				//========================================//
			}
		break;
		//---------------------------------------------------//
		case "process_queue":
			echo '<p>' . __('Adding Users...') . '</p>';
			$queue_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "add_new_users_queue WHERE add_new_users_site_ID = '" . $wpdb->siteid . "' AND add_new_users_blog_ID = '" . $wpdb->blogid . "'" );
			add_new_users_queue_process($wpdb->blogid,$wpdb->siteid);

			if ($queue_count > 0){
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='users.php?page=add-new-users&action=process_queue';
				</script>
				";				
			} else {
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='users.php?page=add-new-users&updated=true&updatedmsg=" . urlencode(__('Users Added.')) . "';
				</script>
				";
			}
			

		break;
		//---------------------------------------------------//
		case "test":
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

?>