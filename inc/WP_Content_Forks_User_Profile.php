<?php
/**
 * Class WP_Content_Forks_User_Profile
 *
 * Autoloader for the plugin
 *
 * @package   WP  Content Forks
 * @author    Frank Staude
 * @license   GPL-2.0+
 * @link      https://github.com/rpi-virtuell/wp-content-forks
 */

class WP_Content_Forks_User_Profile
{
    /**
     * Add ne fields to user profile
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     *
     * @todo password field ***
     */
    static public function add_github_fields( $user ) {
        ?>
        <h3>GitHub Data for Contentsharing</h3>

        <table class="form-table">
            <tr>
                <th><label for="wpcf_github_user">GitHub User</label></th>
                <td><input type="text" name="wpcf_github_user" value="<?php echo esc_attr(get_the_author_meta( 'wpcf_github_user', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="wpcf_github_user">GitHub password </label></th>
                <td><input type="text" name="wpcf_github_password" value="<?php echo esc_attr(get_the_author_meta( 'wpcf_github_password', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="wpcf_github_token">GitHub token</label></th>
                <td><input type="text" name="wpcf_github_token" value="<?php echo esc_attr(get_the_author_meta( 'wpcf_github_token', $user->ID )); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php
    }

    /**
     * save the content from the new user profile fields
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     *
     * @todo check access data before save
     */
    static public function update_github_fields( $user_id  ) {
        update_user_meta( $user_id,'wpcf_github_user', sanitize_text_field( $_POST['wpcf_github_user'] ) );
        update_user_meta( $user_id,'wpcf_github_password', sanitize_text_field( $_POST['wpcf_github_password'] ) );
        update_user_meta( $user_id,'wpcf_github_token', sanitize_text_field( $_POST['wpcf_github_token'] ) );
    }

}