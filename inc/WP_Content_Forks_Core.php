<?php
/**
 * Class WP_Content_Forks_Core
 *
 * Autoloader for the plugin
 *
 * @package   WP  Content Forks
 * @author    Frank Staude
 * @license   GPL-2.0+
 * @link      https://github.com/rpi-virtuell/wp-content-forks
 */

class WP_Content_Forks_Core
{
    /**
     * check if github user data in user meta data exists
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @param null $userid
     * @return  boolean
     */
    static public function github_userdata_exists ( $userid = null ) {
        if ( $userid == null ) {
            $userid = get_current_user_id();
        }

        if ( ( ( get_the_author_meta( 'wpcf_github_user', $userid ) != '' ) && ( get_the_author_meta( 'wpcf_github_password', $userid ) != '' || get_the_author_meta( 'wpcf_github_token', $userid ) != '' ) )  ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check if github repo url for post exists
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @param   null $postid
     * @return  boolean
     */
    static public function github_post_repo_url_exists ( $postid = null ) {
        if ( $postid == null ) {
            return false;
        }
        if ( get_post_meta( $postid, 'wpcf_github_repo_url', true ) != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * parse a string as github url
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @param $url
     * @return bool|mixed
     */
    static public function parse_github_url( $url ) {
        $parsed_url = parse_url( $url );

        if ( $parsed_url['host'] != 'github.com' ) {
            return false;
        }
        $keys = array(  'user', 'repo', 'type', 'branch', 'file' );
        $github_arr = explode( '/', $parsed_url[ 'path' ] );

        if ($github_arr[ 0 ] == '' ) {
            unset ( $github_arr[ 0 ] );
            $github_arr = array_values( $github_arr );
        }
        foreach( $github_arr as $key => $value  ) {
            $parsed_url[ $keys[ $key ] ] = $value;
        }

        return $parsed_url;
    }

    /**
     * check if github repo revision for post exists
     *
     * @since   0.0.1
     * @access  public
     * @static
     * @param   null $postid
     * @return  boolean
     */
    static public function github_post_revision_exists( $postid = null ) {
        if ( $postid == null ) {
            return false;
        }
        if ( get_post_meta( $postid, 'wpcf_github_repo_revision', true ) != '') {
            return true;
        } else {
            return false;
        }
    }
}