<?php
/**
 * Plugin Name: My Meta is Rich
 * Plugin URI:  http://www.myclientisrich.com
 * Text Domain: generate-posts
 * Domain Path: /languages
 * Description: Export/Import a CSV file of all your pages.
 * Author:      My Client is Rich
 * Author URI:  http://www.myclientisrich.com
 * Donate URI:  http://www.myclientisrich.com
 * Version:     1.0
 */

// https://gofishdigital.com/bulk-update-title-meta/

function mmir_content()
{
    // wp_enqueue_script( "adminjs", plugin_dir_url(__FILE__)."/admin.js");
    wp_enqueue_style("admincss", plugin_dir_url(__FILE__) . "/admin.css");
    include(dirname(__FILE__) . "/admin.php");
}

function mmir_addMenuPage()
{
    add_menu_page('My Meta is Rich', 'My Meta is Rich', 'read', 'mmir', 'mmir_content', 'dashicons-admin-page', 6247);
}

add_action('admin_menu', 'mmir_addMenuPage');

function mmir_exportMetas($isBackup = false)
{
    // Getting all post types
    $postTypesToExclude = array(
        'revision',
        'attachment',
        'nav_menu_item',
        'custom_css',
        'customize_changeset',
        'oembed_cache',
        'acf-field-group',
        'acf-field',
        'messages'
    );
    $allPostTypes = get_post_types();

    foreach ($postTypesToExclude as $pte) {
        unset($allPostTypes[$pte]);
    }

    // Getting all posts for each post type

    $formattedPosts = array();

    foreach ($allPostTypes as $pte) {
        // Sorting by post types

        $formattedPosts[$pte] = array();

        // Getting all posts from current post type

        $args = array('post_type' => $pte, 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC');
        $allPostsOfPostType = get_posts($args);

        // Ordering and cleaning up

        foreach ($allPostsOfPostType as $singlePost) {

            $postitem = array();
            $postitem['id'] = $singlePost->ID;
            $postitem['url'] = get_permalink($postitem['id']);

            if (defined('WPSEO_VERSION')) {
                // Meta title
                $postitem['meta_title'] = get_post_meta($postitem['id'], '_yoast_wpseo_title');
                // Cleanup
                if (!empty($postitem['meta_title'])) {
                    $postitem['meta_title'] = $postitem['meta_title'][0];
                } else {
                    $postitem['meta_title'] = '';
                }

                // Meta desc
                $postitem['meta_desc'] = get_post_meta($postitem['id'], '_yoast_wpseo_metadesc');
                // Cleanup
                if (!empty($postitem['meta_desc'])) {
                    $postitem['meta_desc'] = $postitem['meta_desc'][0];
                } else {
                    $postitem['meta_desc'] = '';
                }
            } else {
                throw new Exception("Yoast is not installed or enabled.");
                break;
            }

            $formattedPosts[$pte][] = $postitem;
        }

        // Putting it all inside a CSV file

        $csv_firstline = array("Wordpress ID", "URL", "Title", "Description");
        $csv_fields = array();
        $csv_fields[] = $csv_firstline;
        if ($isBackup) {
            $csv_file = fopen(__DIR__ . "/backup/_backup_mymetaisrich" . date('m_d_Y__H_i_s') . ".csv", "w+");
        } else {
            $csv_filename =  "/file/mymetaisrich_" . date('m_d_Y__H_i_s') . '.csv';
            $csv_file = fopen(__DIR__ . $csv_filename, "w+");
        }

        foreach ($formattedPosts as $postType) {
            foreach ($postType as $postData) {
                $csv_fields[] = $postData;
            }
        }

        foreach ($csv_fields as $line) {
            fputcsv($csv_file, $line, ';');
        }

        fclose($csv_file);
    }

    if (!$isBackup) {
        echo '<a class="export__downloadlink" href="/wp-content/plugins/mymetaisrich' . $csv_filename . '" download>Download exported file</a>';
    }
}


function mmir_importMetas()
{
    // [CRITICAL] FIRST AND FOREMOST : BACKUP

    mmir_exportMetas(true); // isBackup à true.


    // Then we read the CSV file

    $uploadedFile = wp_handle_upload($_FILES['field__import'], array('test_form' => false));
    if ($uploadedFile && !isset($uploadedFile['error'])) {
        // No error
        $imported_CSV = fopen($uploadedFile['file'], 'r');
        $header = fgetcsv($imported_CSV, 1024, ';');
        while (!feof($imported_CSV)) {
            $line = fgetcsv($imported_CSV, 1024, ';');
            if (count($line) < 4 && $line) {
                throw new Exception('CSV file is not formatted properly.');
            } else {
                $post_id = $line[0] ?? '';
                $post_meta_title = $line[2] ?? '';
                $post_meta_desc = $line[3] ?? '';

                if (!empty($post_id)) {
                    update_post_meta($post_id, '_yoast_wpseo_title', $post_meta_title);
                    update_post_meta($post_id, '_yoast_wpseo_metadesc', $post_meta_desc);
                    echo '<div class="import__processed">Post ID ' . $post_id . ' processed' . '</div>';
                }
            }
        }
    } else {
        // Eroor
        throw new Exception('Upload failed.');
    }
}