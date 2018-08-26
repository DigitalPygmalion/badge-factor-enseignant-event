<?php

/**
 * Plugin Name:       Badge Factor Enseignant Invitation
 * Plugin URI:
 * Description:       Plugin qui permet à un type d'utilisateur de géré leur invitation et badges
 * Version:           1.0.0
 * Author:            horizon-cumulus
 * Author URI:        https://horizon-cumulus.ca/
 * License:           MIT
 * Text Domain:       badge-factor-enseignant-envent
 * Domain Path:       /languages
 */


class BadgeFactorEnseignantEvent
{
    /**
     * BadgeFactorCertificates Version
     *
     * @var string
     */
    public static $version = '1.0.0';


    /**
     * Holds any blocker error messages stopping plugin running
     *
     * @var array
     *
     * @since 1.0.0
     */
    private $notices = array();


    /**
     * The plugin's required WordPress version
     *
     * @var string
     *
     * @since 1.0.0
     */
    public $required_bf_version = '1.0.0';


    /**
     * BadgeFactorCertificates constructor.
     */
    function __construct()
    {
        // Plugin constants
        $this->basename = plugin_basename(__FILE__);
        $this->directory_path = plugin_dir_path(__FILE__);
        $this->directory_url = plugin_dir_url(__FILE__);

        // Load translations
        // load_plugin_textdomain('badgefactor_notification', false, basename(dirname(__FILE__)) . '/languages');

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));


        add_action('init', array($this, 'init_plugin'));

        add_action('pre_get_posts', array($this, 'filter_invitation_badges_user'));
        add_action('acf/save_post', array($this, 'new_invitation_badge_acf'), 5);

        add_action('admin_head', array($this, 'hide_update_notice_to_all_but_admin_users'), 1);


        add_filter('acf/upload_prefilter/key=field_5ac5367d2e388', array($this, 'my_acf_upload_prefilter'), 10, 3);

        add_action('add_meta_boxes', array($this, 'meta_box_inscriptions'));

        add_filter('single_template', array($this, 'inscription_badge_template'));

        add_action('wp_ajax_inscrire_badge_utilisateur', array($this, 'inscrire_badge_utilisateur'));

        add_action('admin_print_styles', array($this, 'plugin_styles'));

        add_action('admin_menu', array($this, 'remove_menus'), 999);
        add_filter('acf/fields/post_object/query/key=field_5ac537c4a7642', array($this, 'filter_organisation'),10, 3);
        add_filter('acf/fields/relationship/query/key=field_5ac537f2a7643', array($this, 'filter_badge'),10, 3);
        global $pagenow;

        if ($_GET["post_type"] == 'inscription_badge' && 'post-new.php' == $pagenow) {
            add_action('admin_enqueue_scripts', array($this, 'plugin_scripts'));
        }

    }

    function inscription_badge_template($single)
    {

        global $wp_query, $post;

        if ($post->post_type == 'inscription_badge') {
            if (file_exists($this->directory_path . '/templates/single-inscription_badge.php')) {
                return $this->directory_path . '/templates/single-inscription_badge.php';
            }
        }

        return $single;

    }

    public function activate()
    {
    }


    public function deactivate()
    {
    }

    public function plugin_styles()
    {
        wp_enqueue_style('bf-enseignant', $this->directory_url . '/css/styles.css');
    }

    public function plugin_scripts()
    {
        wp_enqueue_script('bf-enseignant-script', $this->directory_url . '/js/script.js', array('jquery'), false);
    }


    function init_plugin()
    {

        register_post_type('inscription_badge', array(
            'labels' => array(
                'name' => __('Nomination par enseignant et conseiller pédagogique', 'badgefactor_ensevent'),
                'singular_name' => __('Nomination par enseignant et conseiller pédagogique', 'badgefactor_ensevent'),
                'add_new' => __('Ajouter', 'badgefactor_ensevent'),
                'add_new_item' => __('Ajouter une Nomination enseignant', 'badgefactor_ensevent'),
                'edit_item' => __('Modifier Nomination enseignant', 'badgefactor_ensevent'),
                'new_item' => __('Ajouter Nomination enseignant', 'badgefactor_ensevent'),
                'all_items' => __('Nomination enseignant', 'badgefactor_ensevent'),
                'view_item' => __('Voir page de nomination', 'badgefactor_ensevent'),
                'search_items' => __('rechercher Nomination enseignant', 'badgefactor_ensevent'),
                'not_found' => __('Aucune Nomination enseignant trouvé', 'badgefactor_ensevent'),
                'not_found_in_trash' => __('Aucune Nomination enseignant trouvé dans la corbeille', 'badgefactor_ensevent'),
                'parent_item_colon' => '',
                'menu_name' => 'Nomination enseignant',
            ),
            'rewrite' => array(
                'slug' => 'inscription_badge',
            ),
            'public' => true,
            'publicly_queryable' => true,
            //'show_ui' => current_user_can(badgeos_get_manager_capability()),
            //'show_in_menu' => 'badgeos_badgeos',
            'query_var' => true,
            'exclude_from_search' => true,
            'capability_type' => 'inscription_badge',
            'capabilities' => array(
                'edit_post' => 'edit_inscription_badge',
                'read_post' => 'read_inscription_badge',
                'delete_post' => 'delete_inscription_badge',

                'edit_posts' => 'edit_inscription_badges',
                'edit_others_posts' => 'edit_others_inscription_badges',
                'publish_posts' => 'publish_inscription_badge',
                'read_private_posts' => 'read_private_inscription_badges',

                'create_posts' => 'create_inscription_badges',
            ),
            'map_meta_cap' => false,
            'hierarchical' => true,
            'menu_position' => null,
            'supports' => array('title', 'editor')
        ));


        //remove_role('enseignants');
        add_role(
            'enseignants',
            'Enseignant/CP',
            [
                'edit_inscription_badge' => true,
                'read_inscription_badge' => true,
                'delete_inscription_badge' => false,

                'edit_inscription_badges' => true,
                'edit_others_inscription_badges' => false,
                'publish_inscription_badges' => false,
                'read_private_inscription_badges' => false,

                'read' => true,
                'delete_inscription_badges' => false,
                'delete_private_inscription_badges' => false,
                'delete_published_inscription_badges' => false,
                'delete_others_inscription_badges' => false,
                'edit_private_inscription_badges' => false,
                'edit_published_inscription_badges' => false,

                'create_inscription_badges' => true,
                'upload_files' => false,
            ]
        );

        $role = get_role('administrator');
        $role->add_cap('edit_inscription_badge');
        $role->add_cap('read_inscription_badge');
        $role->add_cap('delete_inscription_badge');
        $role->add_cap('edit_inscription_badges');
        $role->add_cap('edit_others_inscription_badges');
        $role->add_cap('publish_inscription_badges');
        $role->add_cap('read_private_inscription_badges');
        $role->add_cap('delete_inscription_badges');
        $role->add_cap('delete_private_inscription_badges');
        $role->add_cap('delete_published_inscription_badges');
        $role->add_cap('delete_others_inscription_badges');
        $role->add_cap('edit_private_inscription_badges');
        $role->add_cap('edit_published_inscription_badges');
        $role->add_cap('create_inscription_badges');

        acf_add_options_sub_page(array('page_title' => 'Créer une page d\'invitation',
            'menu_title' => 'Créer une page d\'invitation',
            'parent_slug' => "edit.php?post_type=inscription_badge",));

        if (function_exists('acf_add_local_field_group')):

            acf_add_local_field_group(array(
                'key' => 'group_5ac536651799a',
                'title' => 'Invitation',
                'fields' => array(
                    array(
                        'key' => 'field_5ac5367d2e388',
                        'label' => 'Liste des étudiants',
                        'name' => 'liste',
                        'type' => 'file',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'return_format' => 'id',
                        'library' => 'uploadedTo',
                        'min_size' => '',
                        'max_size' => '',
                        'mime_types' => '.csv',
                    ),
                    array(
                        'key' => 'field_5ac537c4a7642',
                        'label' => 'Organisation',
                        'name' => 'organisation',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'organisation',
                        ),
                        'taxonomy' => array(),
                        'allow_null' => 0,
                        'multiple' => 0,
                        'return_format' => 'object',
                        'ui' => 1,
                    ),
                    array(
                        'key' => 'field_5ac537f2a7643',
                        'label' => 'Badges',
                        'name' => 'badges',
                        'type' => 'relationship',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'badges',
                        ),
                        'taxonomy' => array(),
                        'filters' => array(
                            0 => 'search',
                        ),
                        'elements' => array(
                            0 => 'featured_image',
                        ),
                        'min' => 1,
                        'max' => 5,
                        'return_format' => 'object',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'inscription_badge',
                        ),
                        array(
                            'param' => 'post_status',
                            'operator' => '==',
                            'value' => 'draft',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => 1,
                'description' => '',
            ));

            acf_add_local_field_group(array(
                'key' => 'group_5acd1f13f3c3d',
                'title' => 'Organisation',
                'fields' => array(
                    array(
                        'key' => 'field_5acd207595cc9',
                        'label' => 'Organisation',
                        'name' => 'badge_organisation',
                        'type' => 'relationship',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'organisation',
                        ),
                        'taxonomy' => array(),
                        'filters' => '',
                        'elements' => '',
                        'min' => '',
                        'max' => '',
                        'return_format' => 'id',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'enseignants',
                        ),
                        array(
                            'param' => 'current_user_role',
                            'operator' => '==',
                            'value' => 'administrator',
                        ),
                    ),
                    array(
                        array(
                            'param' => 'user_role',
                            'operator' => '==',
                            'value' => 'subscriber',
                        ),
                        array(
                            'param' => 'current_user_role',
                            'operator' => '==',
                            'value' => 'administrator',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => 1,
                'description' => '',
            ));


        endif;

    }

    function meta_box_inscriptions()
    {

        global $wp_query, $post;
        if ($post->post_status == "publish") {

            add_meta_box(
                'inscriptions_box_id',           // Unique ID
                'Inscriptions',  // Box title
                array($this, 'meta_box_inscriptions_html'),
                'inscription_badge'
            );
        }

    }

    function meta_box_inscriptions_html()
    {
        include("admin/inscriptions_box.tpl.php");
    }


    function hide_update_notice_to_all_but_admin_users()
    {
        if (!current_user_can('managa_options')) {
            remove_all_actions('admin_notices');
        }
    }


    function filter_invitation_badges_user($wp_query_obj)
    {
        // Front end, do nothing
        if (!is_admin())
            return;

        global $current_user, $pagenow;
        get_currentuserinfo();

        if (!is_a($current_user, 'WP_User'))
            return;

        if (in_array('enseignants', (array)$current_user->roles)) {
            if ('index.php' == $pagenow) {
                wp_redirect("edit.php?post_type=inscription_badge", 302);
                exit;
            }

            if ('post-new.php' == $pagenow) {
                // wp_redirect("edit.php?post_type=inscription_badge",302);
                // exit;
            }

            if ('post.php' == $pagenow) {
                if (isset($_GET["post"]) && $_GET["post"]) {
                    $post = get_post($_GET["post"]);
                    if ($post->post_type == 'inscription_badge' && $post->post_author != $current_user->ID) {

                        header('HTTP/1.0 403 Forbidden');
                        die('You are not allowed to access this file.');
                    }
                }
            }
            if ('inscription_badge' == $wp_query_obj->query['post_type'] && 'edit.php' == $pagenow) {
                $wp_query_obj->set('author', $current_user->ID);
            }

        }
    }


    function new_invitation_badge_acf($post_id)
    {

        wp_update_post(array("ID" => $post_id, "post_status" => "publish"));
        //   exit;
    }

    //acf/fields/relationship/query

    function my_acf_upload_prefilter($errors, $file, $field)
    {

        $data = $this->csv_to_array($file["tmp_name"], ";");

        if ($data) {
            foreach ($data as $row_id => $row) {
                foreach ($row as $col => $val) {
                    $data[$row_id][$col] = utf8_encode($val);
                }
            }

            foreach ($data as $row) {

                if (!isset($row["nom"])) {
                    $errors[] = "Colonne nom manquante";
                }
                if (!isset($row["prenom"])) {
                    $errors[] = "Colonne prenom manquante";
                }

                if (!isset($row["matricule"])) {
                    $errors[] = "Colonne matricule manquante";
                } elseif (empty($row["matricule"])) {
                    $errors[] = "Le matricule est vide";
                }

                if (!isset($row["courriel"])) {
                    $errors[] = "Colonne courriel manquante";
                } elseif (empty($row["courriel"])) {
                    $errors[] = "Le courriel est vide";
                }

            }
        } else {
            $errors[] = "Le fichier n'est pas valide";
        }

        if (!$errors) {
            update_post_meta(get_the_ID(), "inscriptions", $data);

            $errors[] = "OK";
        } else {
            $errors[] = "Impossible d'importer";
            die(json_encode($errors));
        }
        // return
        return $errors;

    }


    function csv_to_array($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    function inscrire_badge_utilisateur()
    {
        $current_user = wp_get_current_user();

        $badge = get_post($_POST["badge_id"]);
        $user = get_user_by("ID", $_POST["user_id"]);


        $nomination = $GLOBALS['badgefactor']->get_submission($badge->ID, $user);
        if (!isset($nomination)) {
            badgeos_create_nomination(
                $badge->ID,
                $badge->post_title,
                '',
                $user->ID,
                $current_user->ID
            );
            $nomination = $GLOBALS['badgefactor']->get_submission($badge->ID, $user);
        }

        badgeos_set_submission_status($nomination->ID, 'approved', array(
            "submission_type" => "nomination",
            'achievement_id' => $badge->ID,
            'user_id' => $user->ID));

        $organisation = get_field("organisation", $badge->ID);
        $orgs = get_field("organisation", "user_" . $user->ID);
        if (!in_array($organisation->ID, $orgs)) {
            $orgs[] = $organisation->ID;
            update_user_meta($user->ID, "organisation", $orgs);
        }

        die(json_encode($nomination));
    }


    function filter_organisation($args, $field, $post_id)
    {
        global $current_user;
        get_currentuserinfo();

        $args["post__in"] = get_field("badge_organisation", "user_" . $current_user->ID);

        return $args;
    }

    function filter_badge($args, $field, $post_id)
    {

        $args["meta_query"] = array(

            array(
                "key" => "organisation", //field_579f78d2049
                "value" => $_POST["organisme"],
                "compare" => '='
            )
        );

        return $args;
    }

    function remove_menus()
    {

        global $current_user;
        get_currentuserinfo();
        if (in_array('enseignants', (array)$current_user->roles)) {
            remove_menu_page('vc-welcome');
            remove_menu_page('profile.php');
            remove_menu_page('index.php');
        }
    }

}

function load_badgefactor_enseignant_event()
{
    $GLOBALS['badgefactor']->notification = new BadgeFactorEnseignantEvent();
}

add_action('plugins_loaded', 'load_badgefactor_enseignant_event');

