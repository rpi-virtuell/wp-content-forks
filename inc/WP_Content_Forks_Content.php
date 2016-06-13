<?php
/**
 * Class WP_Content_Forks_Content
 *
 * Autoloader for the plugin
 *
 * @package   WP  Content Forks
 * @package   WP  Content Forks
 * @author    Frank Staude
 * @license   GPL-2.0+
 * @link      https://github.com/rpi-virtuell/wp-content-forks
 */

class WP_Content_Forks_Content
{
    /**
     * init
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function init_metabox() {
        add_action( 'add_meta_boxes',        array( 'WP_Content_Forks_Content', 'add_metabox' ) );
        add_action( 'save_post',             array( 'WP_Content_Forks_Content', 'save_metabox' ), 10, 2 );
    }

    /**
     * add metabox to post/page edit
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function add_metabox() {
        add_meta_box(
            'wpcf_github',
            _x( 'GitHub Content Fork', WP_Content_Forks::$textdomain, 'Post/Page metabox title' ),
            array(  'WP_Content_Forks_Content', 'render_metabox' ),
            null,
            'side',
            'default'
        );

    }

    /**
     * render the metabox
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function render_metabox( $post ) {
        // Admin notice id no github user data exists
        if ( WP_Content_Forks_Core::github_userdata_exists() === false ) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . _x( 'Please add you github user data for github commits/pull requests', WP_Content_Forks::$textdomain, 'Post/Page metabox info' ) . '</p>';
            echo '</div>';
        }

        if ( WP_Content_Forks_Core::github_post_repo_url_exists(  $post->ID ) == false ) {
            // Form fields.
            echo '<table class="form-table">';
            echo '	<tr>';
            echo '		<th><label for="wpcf_github_repo_url" class="">' . __( 'Github repo url', WP_Content_Forks::$textdomain ) . '</label></th>';
            echo '		<td>';
            echo '			<input type="url" id="wpcf_github_repo_url" name="wpcf_github_repo_url" class="regular-text" placeholder="' . esc_attr__( 'https://', WP_Content_Forks::$textdomain ) . '" value="' . esc_attr__( get_post_meta( $post->ID, 'wpcf_github_repo_url', true ) ) . '">';
            echo '			<p class="description">' . __( 'URL from the github repository', WP_Content_Forks::$textdomain ) . '</p>';
            echo '		</td>';
            echo '	</tr>';
            echo '</table>';
        }

        if ( WP_Content_Forks_Core::github_post_repo_url_exists(  $post->ID ) == true ) {
            if ( WP_Content_Forks_Core::github_post_revision_exists( $post->ID ) == false ) {
                $hash = get_post_meta( $post->ID, 'wpcf_github_commit_hash', true );
                if ( $hash == '' ) {
                    // Form fields.
                    echo '<table class="form-table">';
                    echo '	<tr>';
                    echo '		<th><label for="wpcf_github_repo_revision" class="">' . __('Github repo url', WP_Content_Forks::$textdomain) . '</label></th>';
                    echo '		<td>';
                    echo esc_attr__(get_post_meta($post->ID, 'wpcf_github_repo_url', true));

                    echo '		</td>';
                    echo '	</tr>';
                    echo '	<tr>';
                    echo '		<th><label for="wpcf_github_repo_commitmsg" class="">' . __('Github repo commit message', WP_Content_Forks::$textdomain) . '</label></th>';
                    echo '		<td>';
                    echo '			<input type="text" id="wpcf_github_repo_commitmsg" name="wpcf_github_repo_commitmsg" class="regular-text" >';
                    echo '			<p class="description">' . __('Message for initial commit to github', WP_Content_Forks::$textdomain) . '</p>';
                    echo '		</td>';
                    echo '	</tr>';
                    echo '</table>';
                    echo '	<input id="githubsave" class="button button-primary button-large" type="submit" value="' . __('Save on GitHub', WP_Content_Forks::$textdomain) . '" name="githubsave">';
                } else {
                    echo "nur noch pr oder update möglich ";
                }
            }
        }
    }

    /**
     * save metabox content
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     *
     * @todo client exceptions abfangen
     */
    static public function save_metabox( $post_id, $post ) {
        if ( WP_Content_Forks_Core::github_post_repo_url_exists(  $post->ID ) == false ) {

            if ( $_POST && $_POST[ 'wpcf_github_repo_url' ] && $_POST[ 'wpcf_github_repo_url' ] != '' ) {
                $repo_url_array = WP_Content_Forks_Core::parse_github_url( esc_url( $_POST[ 'wpcf_github_repo_url' ] ) );
                $client = new \Github\Client();

                $response = $client->getHttpClient()->get('repos/' . $repo_url_array[ 'user'] . '/' . $repo_url_array[ 'repo']  );
                $repo     = Github\HttpClient\Message\ResponseMediator::getContent($response);

                if ( is_array( $repo ) ) {
                    update_post_meta( $post_id, 'wpcf_github_repo_url', esc_url( $_POST[ 'wpcf_github_repo_url' ] ) );
                }
            }

        } else {
            if ( $_POST && $_POST[ 'githubsave' ] && $_POST[ 'githubsave' ] == __('Save on GitHub', WP_Content_Forks::$textdomain ) ) {
                $hash = get_post_meta( $post->ID, 'wpcf_github_commit_hash', true );
                if ( $hash == '' ) {
                    $user = wp_get_current_user();

                    $repo_url_array = WP_Content_Forks_Core::parse_github_url(esc_attr__(get_post_meta($post->ID, 'wpcf_github_repo_url', true)));
                    $client = new \Github\Client();
                    $gh_user = get_user_meta($user->ID, 'wpcf_github_user', true);
                    $gh_token = get_user_meta($user->ID, 'wpcf_github_token', true);
                    $client->authenticate($gh_user, $gh_token);
                    $response = $client->getHttpClient()->get('repos/' . $repo_url_array['user'] . '/' . $repo_url_array['repo']);
                    //                $repo     = Github\HttpClient\Message\ResponseMediator::getContent($response);

                    $committer = array('name' => $user->user_nicename, 'email' => $user->user_email);

                    $path = $post->post_title . '/' . $post->post_title;
                    $content = $post->post_content;
                    $commitMessage = $_POST['wpcf_github_repo_commitmsg'];
                    try {
                        $fileInfo = $client->api('repo')->contents()->create($repo_url_array['user'], $repo_url_array['repo'], $path, $content, $commitMessage, null, $committer);
                        update_post_meta($post_id, 'wpcf_github_commit_hash', esc_url($fileInfo['content']['sha']));

                    } catch (Exception $e) {
                        echo 'Exception abgefangen: ', $e->getMessage(), "\n";
                        var_dump($e);
                    }
                }
            }

        }
    }

    /**
     * add import page on page menu
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function add_submenu_page() {
        add_submenu_page(
            'edit.php?post_type=page',
            __( 'Import from GitHub', WP_Content_Forks::$textdomain ),
            __( 'Import from GitHub', WP_Content_Forks::$textdomain ),
            'manage_options',
            'import-page-from-github',
            array( 'WP_Content_Forks_Content','import_page')
        );
    }

    /**
     * add import page on post menu
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function add_submenu_post() {
        add_submenu_page(
            'edit.php',
            __( 'Import from GitHub', WP_Content_Forks::$textdomain ),
            __( 'Import from GitHub', WP_Content_Forks::$textdomain ),
            'manage_options',
            'import-post-from-github',
            array( 'WP_Content_Forks_Content','import_post')
        );
    }

    /**
     * import post
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     *
     * @todo fix redirect
     */
    static public function import_post() {
        if ( isset( $_POST[ "githubimport" ] ) && isset( $_POST[ "wpcf_github_repo_url" ] ) ) {
            $repo_url_array = WP_Content_Forks_Core::parse_github_url(esc_attr__( $_POST[ "wpcf_github_repo_url" ]  ));
            $client = new \Github\Client();
            $user = wp_get_current_user();
            $gh_user = get_user_meta($user->ID, 'wpcf_github_user', true);
            $gh_token = get_user_meta($user->ID, 'wpcf_github_token', true);
            $client->authenticate($gh_user, $gh_token);
            $fileContent = $client->api('repo')->contents()->download( $repo_url_array[ 'user' ], $repo_url_array[ 'repo' ], urldecode_deep( $repo_url_array[ 'file' ] ) .'/' .  urldecode_deep( $repo_url_array[ 'file' ] ));

            $mypost = array(
                'post_title'    => urldecode_deep( $repo_url_array[ 'file' ] ),
                'post_content'  => $fileContent,
                'post_status'   => 'draft',
                'post_type'     => 'post'
            );

            $post_id = wp_insert_post( $mypost );
            wp_redirect( admin_url( "post.php?post=". $post_id  ."&action=edit" ) ) ;
            exit;
        }
        ?>
        <div class="wrap">
            <form method="POST" action="">
            <h1><?php _e( 'Import Post from Github', WP_Content_Forks::$textdomain ); ?></h1>
            <p><?php _e( 'Here you can import an post from a github repositoy', WP_Content_Forks::$textdomain ); ?></p>
            <?php

            echo '<table class="form-table">';
            echo '	<tr>';
            echo '		<th><label for="wpcf_github_repo_url" class="">' . __( 'Github repo url', WP_Content_Forks::$textdomain ) . '</label></th>';
            echo '		<td>';
            echo '			<input type="url" id="wpcf_github_repo_url" name="wpcf_github_repo_url" class="regular-text" placeholder="' . esc_attr__( 'https://', WP_Content_Forks::$textdomain ) . '" value="' . esc_attr__( get_post_meta( $post->ID, 'wpcf_github_repo_url', true ) ) . '">';
            echo '			<p class="description">' . __( 'URL from the github repository', WP_Content_Forks::$textdomain ) . '</p>';
            echo '		</td>';
            echo '	</tr>';
            echo '</table>';
            echo '	<input id="githubimport" class="button button-primary button-large" type="submit" value="' . __('Import', WP_Content_Forks::$textdomain) . '" name="githubimport">';
        ?>
            </form>
        </div>
        <?php
    }

    /**
     * import page
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function import_page() {
        if ( isset( $_POST[ "githubimport" ] ) && isset( $_POST[ "wpcf_github_repo_url" ] ) ) {
            $repo_url_array = WP_Content_Forks_Core::parse_github_url(esc_attr__( $_POST[ "wpcf_github_repo_url" ]  ));
            $client = new \Github\Client();
            $user = wp_get_current_user();
            $gh_user = get_user_meta($user->ID, 'wpcf_github_user', true);
            $gh_token = get_user_meta($user->ID, 'wpcf_github_token', true);
            $client->authenticate($gh_user, $gh_token);
            $fileContent = $client->api('repo')->contents()->download( $repo_url_array[ 'user' ], $repo_url_array[ 'repo' ], urldecode_deep( $repo_url_array[ 'file' ] ) .'/' .  urldecode_deep( $repo_url_array[ 'file' ] ));

            $mypost = array(
                'post_title'    => urldecode_deep( $repo_url_array[ 'file' ] ),
                'post_content'  => $fileContent,
                'post_status'   => 'draft',
                'post_type'     => 'page'
            );

            $post_id = wp_insert_post( $mypost );
            wp_redirect( admin_url( "post.php?post=". $post_id  ."&action=edit" ) ) ;
            exit;
        }
        ?>
        <div class="wrap">
            <form method="POST" action="">
                <h1><?php _e( 'Import Page from Github', WP_Content_Forks::$textdomain ); ?></h1>
                <p><?php _e( 'Here you can import an post from a github repositoy', WP_Content_Forks::$textdomain ); ?></p>
                <?php

                echo '<table class="form-table">';
                echo '	<tr>';
                echo '		<th><label for="wpcf_github_repo_url" class="">' . __( 'Github repo url', WP_Content_Forks::$textdomain ) . '</label></th>';
                echo '		<td>';
                echo '			<input type="url" id="wpcf_github_repo_url" name="wpcf_github_repo_url" class="regular-text" placeholder="' . esc_attr__( 'https://', WP_Content_Forks::$textdomain ) . '" value="' . esc_attr__( get_post_meta( $post->ID, 'wpcf_github_repo_url', true ) ) . '">';
                echo '			<p class="description">' . __( 'URL from the github repository', WP_Content_Forks::$textdomain ) . '</p>';
                echo '		</td>';
                echo '	</tr>';
                echo '</table>';
                echo '	<input id="githubimport" class="button button-primary button-large" type="submit" value="' . __('Import', WP_Content_Forks::$textdomain) . '" name="githubimport">';
                ?>
            </form>
        </div>
        <?php
        
    }

}