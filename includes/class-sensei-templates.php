<?php
if ( ! defined( 'ABSPATH' ) ) exit; // security check, don't load file outside WP
/**
 * Sensei Template Class
 *
 * Handles all Template loading and redirecting functionality.
 *
 * @package Sensei
 * @category Templates
 * @since 1.9.0
 */
class Sensei_Templates {

    /**
     *  Load the template files from within sensei/templates/ or the the theme if overrided within the theme.
     *
     * @since 1.9.0
     * @param string $slug
     * @param string $name default: ''
     *
     * @return void
     */
    public static function get_part(  $slug, $name = ''  ){

        $template = '';
        $plugin_template_url = Sensei()->template_url;
        $plugin_template_path = Sensei()->plugin_path() . "/templates/";

        // Look in yourtheme/slug-name.php and yourtheme/sensei/slug-name.php
        if ( $name ){

            $template = locate_template( array ( "{$slug}-{$name}.php", "{$plugin_template_url}{$slug}-{$name}.php" ) );

        }

        // Get default slug-name.php
        if ( ! $template && $name && file_exists( $plugin_template_path . "{$slug}-{$name}.php" ) ){

            $template = $plugin_template_path . "{$slug}-{$name}.php";

        }


        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/sensei/slug.php
        if ( !$template ){

            $template = locate_template( array ( "{$slug}.php", "{$plugin_template_url}{$slug}.php" ) );

        }


        if ( $template ){

            load_template( $template, false );

        }

    } // end get part

    /**
     * Get the template.
     *
     * @since 1.9.0
     *
     * @param $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     */
    public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

        if ( $args && is_array($args) )
            extract( $args );

        $located = self::locate_template( $template_name, $template_path, $default_path );

        if( ! empty( $located ) ){

            do_action( 'sensei_before_template_part', $template_name, $template_path, $located );

            include( $located );

            do_action( 'sensei_after_template_part', $template_name, $template_path, $located );

        }

    } // end get template

    /**
     * Check if the template file exists. A wrapper for WP locate_template.
     *
     * @since 1.9.0
     *
     * @param $template_name
     * @param string $template_path
     * @param string $default_path
     *
     * @return mixed|void
     */
    public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {

        if ( ! $template_path ) $template_path = Sensei()->template_url;
        if ( ! $default_path ) $default_path = Sensei()->plugin_path() . '/templates/';

        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
            )
        );

        // Get default template
        if ( ! $template ){

            $template = $default_path . $template_name;

        }
        // return nothing for file that do not exist
        if( !file_exists( $template ) ){
            $template = '';
        }

        // Return what we found
        return apply_filters( 'sensei_locate_template', $template, $template_name, $template_path );

    } // end locate

    /**
     * Determine which Sensei template to load based on the
     * current page context.
     *
     * @since 1.0
     *
     * @param string $template
     * @return string $template
     */
    public static function template_loader ( $template = '' ) {

        global $wp_query, $email_template;

        $find = array( 'woothemes-sensei.php' );
        $file = '';

        if ( isset( $email_template ) && $email_template ) {

            $file 	= 'emails/' . $email_template;
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } elseif ( is_single() && get_post_type() == 'course' ) {

            if ( Sensei()->check_user_permissions( 'course-single' ) ) {

                // possible backward compatible template include if theme overrides content-single-course.php
                // this template was removed in 1.9.0 and code all moved into the main single-course.php file
                self::locate_and_load_template_overrides( Sensei()->template_url . 'content-single-course.php', true );

                $file 	= 'single-course.php';
                $find[] = $file;
                $find[] = Sensei()->template_url . $file;

            } else {

                // No Permissions Page
                return self::get_no_permission_template();

            } // End If Statement

        } elseif ( is_single() && get_post_type() == 'lesson' ) {

            if ( Sensei()->check_user_permissions( 'lesson-single' ) ) {

                // possible backward compatible template include if theme overrides content-single-lesson.php
                // this template was removed in 1.9.0 and code all moved into the main single-lesson.php file
                self::locate_and_load_template_overrides( Sensei()->template_url . 'content-single-lesson.php', true );

                $file 	= 'single-lesson.php';
                $find[] = $file;
                $find[] = Sensei()->template_url . $file;

            } else {

                // No Permissions Page
                return self::get_no_permission_template();

            } // End If Statement

        } elseif ( is_single() && get_post_type() == 'quiz' ) {

            if ( Sensei()->check_user_permissions( 'quiz-single' ) ) {

                // possible backward compatible template include if theme overrides content-single-quiz.php
                // this template was removed in 1.9.0 and code all moved into the main single-quiz.php file
                self::locate_and_load_template_overrides( Sensei()->template_url . 'content-single-quiz.php' , true);

                $file 	= 'single-quiz.php';
                $find[] = $file;
                $find[] = Sensei()->template_url . $file;

            } else {

                // No Permissions Page
                return self::get_no_permission_template();

            } // End If Statement

        } elseif ( is_single() && get_post_type() == 'sensei_message' ) {

            // possible backward compatible template include if theme overrides content-single-message.php
            // this template was removed in 1.9.0 and code all moved into the main single-message.php file
            self::locate_and_load_template_overrides( Sensei()->template_url . 'content-single-message.php', true );

            $file 	= 'single-message.php';
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } elseif ( is_post_type_archive( 'course' )
                    || is_page( Sensei()->get_page_id( 'courses' ) )
                    || is_tax( 'course-category' )) {

            // possible backward compatible template include if theme overrides 'taxonomy-course-category'
            // this template was removed in 1.9.0 and replaced by archive-course.php
            self::locate_and_load_template_overrides( Sensei()->template_url . 'taxonomy-course-category.php');

            $file 	= 'archive-course.php';
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } elseif ( is_post_type_archive( 'sensei_message' ) ) {

            $file 	= 'archive-message.php';
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } elseif( is_tax( 'lesson-tag' ) ) {

            // possible backward compatible template include if theme overrides 'taxonomy-lesson-tag.php'
            // this template was removed in 1.9.0 and replaced by archive-lesson.php
            self::locate_and_load_template_overrides( Sensei()->template_url . 'taxonomy-lesson-tag.php' );

            $file 	= 'archive-lesson.php';
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } elseif ( isset( $wp_query->query_vars['learner_profile'] ) ) {

            // Override for sites with static home page
            $wp_query->is_home = false;

            $file 	= 'learner-profile.php';
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } elseif ( isset( $wp_query->query_vars['course_results'] ) ) {

            // Override for sites with static home page
            $wp_query->is_home = false;

            $file 	= 'course-results.php';
            $find[] = $file;
            $find[] = Sensei()->template_url . $file;

        } // Load the template file

        if ( $file ) {

            $template = locate_template( $find );
            if ( ! $template ) $template = Sensei()->plugin_path() . '/templates/' . $file;

        } // End If Statement

        return $template;

    } // End template_loader()

    /**
     * This function loads the no-permissions template for users with no access
     * if a Sensei template was loaded.
     *
     * This function doesn't determine the permissions. Permissions must be determined
     * before loading this function as it only gets the template.
     *
     * This function also checks the user theme for overrides to ensure the right template
     * file is returned.
     *
     * @since 1.9.0
     */
    public static function get_no_permission_template( ){

        // possible backward compatible template loading
        // this template was removed in 1.9.0 and code all moved into the no-permissions.php file
        self::locate_and_load_template_overrides( Sensei()->template_url . 'content-no-permissions.php', true );

        $file 	= 'no-permissions.php';
        $find[] = $file;
        $find[] = Sensei()->template_url . $file;

        $template = locate_template( $find );
        if ( ! $template ) $template = Sensei()->plugin_path() . '/templates/' . $file;

        return $template;

    }

    /**
     * This function is specifically created for loading template files from the theme.
     *
     * This function checks if the user has overwritten the templates like in their theme. If they have it in their theme it will load the header and the footer
     * around the singular content file from their theme and exit.
     *
     * If none is found this function will do nothing. If a template is found this funciton
     * will exit execution of the script an not continue.
     *
     * @since 1.9.0
     * @param string $template
     * @param bool $load_header_footer should the file be wrapped in between header and footer? Default: true
     */
    public static function locate_and_load_template_overrides( $template = '', $load_header_footer = false ){

        $found_template = locate_template( array( $template ) );
        if( $found_template ){

            if( $load_header_footer ){

                get_sensei_header();
                include( $found_template );
                get_sensei_footer();

            }else{

                include( $found_template );

            }

            exit;

        }

    }


    /**
     * Hooks the deprecated archive content hook into the hook again just in
     * case other developers have used it.
     *
     * @deprecated since 1.9.0
     */
    public static function deprecated_archive_course_content_hook(){

        sensei_do_deprecated_action( 'sensei_course_archive_main_content','1.9.0', 'sensei_loop_course_before' );

    }// end deprecated_archive_hook

    /**
     * A generic function for echoing the post title
     *
     * @since 1.9.0
     * @param  WP_Post $post
     */
    public static function the_title( $post ){

        /**
         * Filter the template html tag for the title
         *
         * @since 1.9.0
         *
         * @param $title_html_tag default is 'h3'
         */
        $title_html_tag = apply_filters('sensei_the_title_html_tag','h3');

        /**
         * Filter the title classes
         *
         * @since 1.9.0
         * @param string $title_classes defaults to $post_type-title
         */
        $title_classes = apply_filters('sensei_the_title_classes', $post->post_type . '-title' );

        $html= '';
        $html .= '<a href="' . get_permalink( $post->ID ) . '" >';
        $html .= '<'. $title_html_tag .' class="'. $title_classes .'" >';
        $html .= $post->post_title . '</'. $title_html_tag. '>';
        $html .= '</a>';
        echo $html;

    }// end the title

    /**
     * This function adds the hooks inside and above the single course content for
     * backwards compatibility sake.
     *
     * @since 1.9.0
     * @deprecated 1.9.0
     */
    public static function deprecated_single_course_inside_before_hooks(){

        sensei_do_deprecated_action('sensei_course_image','1.9.0', 'sensei_single_course_content_inside_before', array( get_the_ID()) );
        sensei_do_deprecated_action('sensei_course_single_title','1.9.0', 'sensei_single_course_content_inside_before' );
        sensei_do_deprecated_action('sensei_course_single_meta','1.9.0', 'sensei_single_course_content_inside_before' );

    }// end deprecated_single_course_inside_before_hooks

    /**
     * This function adds the hooks to sensei_course_single_lessons for
     * backwards compatibility sake.  and provides developers with an alternative.
     *
     * @since 1.9.0
     * @deprecated 1.9.0
     */
    public static function deprecate_sensei_course_single_lessons_hook(){

        sensei_do_deprecated_action('sensei_course_single_lessons','1.9.0', 'sensei_single_course_content_inside_after');

    }// deprecate_sensei_course_single_lessons_hook

    /**
     * Deprecated all deprecated_single_main_content_hook hooked actions.
     *
     * The content must be dealt with inside the respective templates.
     *
     * @since 1.9.0
     * @deprecated 1.9.0
     */
    public static function deprecated_single_main_content_hook(){

            if( is_singular( 'course' ) ) {

                sensei_do_deprecated_action('sensei_single_main_content', '1.9.0', 'sensei_single_course_content_inside_before or sensei_single_course_content_inside_after');

            } elseif( is_singular( 'message' ) ){

                sensei_do_deprecated_action('sensei_single_main_content', '1.9.0', 'sensei_single_message_content_inside_before or sensei_single_message_content_inside_after');
            }

    }// end deprecated_single_course_single_main_content_hook

    /**
     * Deprecate the  old sensei modules
     * @since 1.9.0
     * @deprecated since 1.9.0
     */
    public static function deprecate_module_before_hook(){

        sensei_do_deprecated_action('sensei_modules_page_before', '1.9.0','sensei_single_course_modules_after' );

    }

    /**
     * Deprecate the  old sensei modules after hooks
     * @since 1.9.0
     * @deprecated since 1.9.0
     */
    public static function deprecate_module_after_hook(){

        sensei_do_deprecated_action('sensei_modules_page_after', '1.9.0','sensei_single_course_modules_after' );

    }

    /**
     * Deprecate the single message hooks for post types.
     *
     * @since 1.9.0
     * @deprecated since 1.9.0
     */
    public static function deprecate_all_post_type_single_title_hooks(){

        if( is_singular( 'sensei_message' ) ){

            sensei_do_deprecated_action( 'sensei_message_single_title', '1.9.0', 'sensei_single_message_content_inside_before' );

        }

    }

    /**
     * course_single_meta function.
     *
     * @access public
     * @return void
     * @deprecated since 1.9.0
     */
    public static function deprecate_course_single_meta_hooks() {

        // deprecate all these hooks
        sensei_do_deprecated_action('sensei_course_start','1.9.0', 'sensei_single_course_content_inside_before' );
        sensei_do_deprecated_action('sensei_woocommerce_in_cart_message','1.9.0', 'sensei_single_course_content_inside_before' );
        sensei_do_deprecated_action('sensei_course_meta','1.9.0', 'sensei_single_course_content_inside_before' );
        sensei_do_deprecated_action('sensei_course_meta_video','1.9.0', 'sensei_single_course_content_inside_before' );

    } // End deprecate_course_single_meta_hooks

    /**
     * Run the deprecated hooks on the single lesson page
     * @deprecated since 1.9.0
     */
    public static function deprecate_single_lesson_breadcrumbs_and_comments_hooks() {

        if( is_singular( 'lesson' ) ){

            sensei_do_deprecated_action( 'sensei_breadcrumb','1.9.0','sensei_after_main_content',  get_the_ID() );
            sensei_do_deprecated_action( 'sensei_comments','1.9.0','sensei_after_main_content',  get_the_ID() );

        }

    }// end sensei_deprecate_single_lesson_breadcrumbs_and_comments_hooks

    /**
     * Deprecate the hook sensei_lesson_course_signup.
     *
     * The hook content will be linked directly on the recommended
     * sensei_single_lesson_content_inside_after
     *
     * @deprecated since 1.9.0
     */
    public static function deprecate_sensei_lesson_course_signup_hook(){

        $lesson_course_id = get_post_meta( get_the_ID(), '_lesson_course', true );
        $user_taking_course = WooThemes_Sensei_Utils::user_started_course( $lesson_course_id, get_current_user_id() );

        if(  !$user_taking_course ) {

            sensei_do_deprecated_action( 'sensei_lesson_course_signup','1.9.0', 'sensei_single_lesson_content_inside_after', $lesson_course_id );

        }
    }// end deprecate_sensei_lesson_course_signup_hook

    /**
     * Running the deprecated hook: sensei_lesson_single_meta
     *
     * @since 1.9.0
     * @deprecated since 1.9.0
     */
    public static function deprecate_sensei_lesson_single_meta_hook(){

        if ( sensei_can_user_view_lesson()  ) {

            sensei_do_deprecated_action( 'sensei_lesson_single_meta', '1.9.0', 'sensei_single_lesson_content_inside_after' );


        }

    }// end deprecate_sensei_lesson_single_meta_hook

    /**
     * Deprecate the sensei lesson single title hook
     * @deprecated since 1.9.0
     */
    public static function deprecate_sensei_lesson_single_title(){

        sensei_do_deprecated_action( 'sensei_lesson_single_title', '1.9.0', 'sensei_single_lesson_content_inside_before', get_the_ID() );

    }// end deprecate_sensei_lesson_single_title

    /**
     * hook in the deperecated single main content to the lesson
     * @deprecated since 1.9.0
     */
    public  static function deprecate_lesson_single_main_content_hook(){

        sensei_do_deprecated_action( 'sensei_single_main_content', '1.9.0', 'sensei_single_lesson_content_inside_before' );

    }// end sensei_deprecate_lesson_single_main_content_hook

    /**
     * hook in the deperecated single main content to the lesson
     * @deprecated since 1.9.0
     */
    public  static function deprecate_lesson_image_hook(){

        sensei_do_deprecated_action( 'sensei_lesson_image', '1.9.0', 'sensei_single_lesson_content_inside_before', get_the_ID() );

    }// end sensei_deprecate_lesson_single_main_content_hook

    /**
     * hook in the deprecated sensei_login_form hook for backwards
     * compatibility
     *
     * @since 1.9.0
     * @deprecated since 1.9.0
     */
    public static function deprecate_sensei_login_form_hook(){

        sensei_do_deprecated_action( 'sensei_login_form', '1.9.0', 'sensei_login_form_before' );

    } // end deprecate_sensei_login_form_hook

    /**
     * Fire the sensei_complete_course action.
     *
     * This is just a backwards compatible function to add the action
     * to a template. This should not be used as the function from this
     * hook will be added directly to my-courses page via one of the hooks there.
     *
     * @since 1.9.0
     */
    public static function  fire_sensei_complete_course_hook(){

        do_action( 'sensei_complete_course' );

    } //fire_sensei_complete_course_hook

    /**
     * Fire the frontend message hook
     *
     * @since 1.9.0
     */
    public static function  fire_frontend_messages_hook(){

        do_action( 'sensei_frontend_messages' );

    }// end sensei_complete_course_action

    /**
     * deprecate the sensei_before_user_course_content hook in favor
     * of sensei_my_courses_content_inside_before.
     *
     * @deprected since 1.9.0
     */
    public static function  deprecate_sensei_before_user_course_content_hook(){

        sensei_do_deprecated_action( 'sensei_before_user_course_content','1.9.0', 'sensei_my_courses_content_inside_before' , wp_get_current_user() );

    }// deprecate_sensei_before_user_course_content_hook

    /**
     * deprecate the sensei_before_user_course_content hook in favor
     * of sensei_my_courses_content_inside_after hook.
     *
     * @deprected since 1.9.0
     */
    public static function  deprecate_sensei_after_user_course_content_hook(){

        sensei_do_deprecated_action( 'sensei_after_user_course_content','1.9.0', 'sensei_my_courses_content_inside_after' , wp_get_current_user() );

    }// deprecate_sensei_after_user_course_content_hook

    /**
     * Deprecate the 2 main hooks on the archive message template
     *
     * @deprecated since 1.9.0
     * @since 1.9.0
     */
    public static function deprecated_archive_message_hooks (){

        sensei_do_deprecated_action('sensei_message_archive_main_content', '1.9.0', 'sensei_archive_before_message_loop OR sensei_archive_after_message_loop' );
        sensei_do_deprecated_action('sensei_message_archive_header', '1.9.0', 'sensei_archive_before_message_loop' );

    }

}//end class