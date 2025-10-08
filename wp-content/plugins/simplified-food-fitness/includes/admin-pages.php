<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_register_admin_pages() {
    add_menu_page(
        __('SFF Ingredients', 'simplified-food-fitness'),
        __('SFF Ingredients', 'simplified-food-fitness'),
        'manage_options',
        'sff-ingredient-library',
        'sff_render_ingredient_library_page',
        'dashicons-carrot',
        56
    );
}
add_action('admin_menu', 'sff_register_admin_pages');

function sff_render_ingredient_library_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'submissions';
    $tab = in_array($tab, ['submissions', 'general'], true) ? $tab : 'submissions';

    $owner_filter = isset($_GET['owner']) ? intval($_GET['owner']) : 0;
    $search       = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $paged        = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

    $args = [
        'post_type'      => 'ingredient',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [],
        's'              => $search,
    ];

    if ($tab === 'submissions') {
        $args['meta_query'][] = [
            'key'     => '_sff_owner_id',
            'value'   => 0,
            'compare' => '>',
            'type'    => 'NUMERIC',
        ];
    } else {
        $args['meta_query'][] = [
            'key'     => '_sff_owner_id',
            'value'   => 0,
            'compare' => '=',
            'type'    => 'NUMERIC',
        ];
    }

    if ($owner_filter > 0) {
        $args['meta_query'][] = [
            'key'     => '_sff_owner_id',
            'value'   => $owner_filter,
            'compare' => '=',
            'type'    => 'NUMERIC',
        ];
    }

    $query = new WP_Query($args);

    $owners = sff_get_all_ingredient_owners();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Ingredient Library', 'simplified-food-fitness'); ?></h1>

        <p><?php esc_html_e('Use this dashboard to review ingredients that clients have submitted and promote them into the shared database.', 'simplified-food-fitness'); ?></p>
        <p>
            <?php esc_html_e('Need to add a new shared ingredient from a USDA search or product label? Visit the front-end form at', 'simplified-food-fitness'); ?>
            <a href="<?php echo esc_url(home_url('/add-ingredient/')); ?>" target="_blank">/add-ingredient/</a>.
        </p>

        <?php sff_render_ingredient_library_notices(); ?>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url(add_query_arg(['tab' => 'submissions', 'paged' => 1], menu_page_url('sff-ingredient-library', false))); ?>" class="nav-tab <?php echo $tab === 'submissions' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Client Submissions', 'simplified-food-fitness'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg(['tab' => 'general', 'paged' => 1, 'owner' => 0], menu_page_url('sff-ingredient-library', false))); ?>" class="nav-tab <?php echo $tab === 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('General Database', 'simplified-food-fitness'); ?>
            </a>
        </h2>

        <form method="get" class="sff-ingredient-filter">
            <input type="hidden" name="page" value="sff-ingredient-library">
            <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>">

            <label for="sff-ingredient-search" class="screen-reader-text"><?php esc_html_e('Search ingredients', 'simplified-food-fitness'); ?></label>
            <input type="search" id="sff-ingredient-search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search ingredients…', 'simplified-food-fitness'); ?>">

            <?php if ($tab === 'submissions') : ?>
                <label for="sff-owner-filter" class="screen-reader-text"><?php esc_html_e('Filter by client', 'simplified-food-fitness'); ?></label>
                <select id="sff-owner-filter" name="owner">
                    <option value="0" <?php selected(0, $owner_filter); ?>><?php esc_html_e('All clients', 'simplified-food-fitness'); ?></option>
                    <?php foreach ($owners as $owner_id => $owner_name) : ?>
                        <option value="<?php echo esc_attr($owner_id); ?>" <?php selected($owner_id, $owner_filter); ?>><?php echo esc_html($owner_name); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <button type="submit" class="button"><?php esc_html_e('Filter', 'simplified-food-fitness'); ?></button>
        </form>

        <?php if ($query->have_posts()) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Ingredient', 'simplified-food-fitness'); ?></th>
                        <th><?php esc_html_e('Category', 'simplified-food-fitness'); ?></th>
                        <th><?php esc_html_e('Owner', 'simplified-food-fitness'); ?></th>
                        <th><?php esc_html_e('Last Updated', 'simplified-food-fitness'); ?></th>
                        <th><?php esc_html_e('Actions', 'simplified-food-fitness'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        $ingredient_id = get_the_ID();
                        $owner_id      = intval(get_post_meta($ingredient_id, '_sff_owner_id', true));
                        if ($owner_id > 0) {
                            if (!isset($owners[$owner_id])) {
                                $user = get_user_by('id', $owner_id);
                                if ($user) {
                                    $owners[$owner_id] = $user->display_name ?: $user->user_login;
                                }
                            }
                            $owner_name = isset($owners[$owner_id])
                                ? $owners[$owner_id]
                                : sprintf(__('User #%d', 'simplified-food-fitness'), $owner_id);
                        } else {
                            $owner_name = __('Shared', 'simplified-food-fitness');
                        }
                        $category_terms = wp_get_post_terms($ingredient_id, 'ingredient_category', ['number' => 1]);
                        $category_name  = (!is_wp_error($category_terms) && !empty($category_terms)) ? $category_terms[0]->name : __('Uncategorized', 'simplified-food-fitness');
                        ?>
                        <tr>
                            <td>
                                <strong><a href="<?php echo esc_url(get_edit_post_link($ingredient_id)); ?>"><?php the_title(); ?></a></strong>
                            </td>
                            <td><?php echo esc_html($category_name); ?></td>
                            <td><?php echo esc_html($owner_name); ?></td>
                            <td><?php echo esc_html(get_the_modified_date()); ?></td>
                            <td>
                                <a class="button" href="<?php echo esc_url(get_edit_post_link($ingredient_id)); ?>"><?php esc_html_e('View', 'simplified-food-fitness'); ?></a>
                                <?php if ($tab === 'submissions') : ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;">
                                        <?php wp_nonce_field('sff_promote_ingredient_' . $ingredient_id); ?>
                                        <input type="hidden" name="action" value="sff_promote_ingredient">
                                        <input type="hidden" name="ingredient_id" value="<?php echo esc_attr($ingredient_id); ?>">
                                        <button type="submit" class="button button-primary" onclick="return confirm('<?php echo esc_js(__('Promote this ingredient to the shared database?', 'simplified-food-fitness')); ?>');">
                                            <?php esc_html_e('Promote to General', 'simplified-food-fitness'); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php
            $total_pages = $query->max_num_pages;
            if ($total_pages > 1) {
                $base_url = add_query_arg([
                    'page'  => 'sff-ingredient-library',
                    'tab'   => $tab,
                    'owner' => $owner_filter,
                    's'     => $search,
                ], admin_url('admin.php'));

                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links([
                    'base'      => esc_url_raw($base_url . '&paged=%#%'),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'prev_text' => __('&laquo;', 'simplified-food-fitness'),
                    'next_text' => __('&raquo;', 'simplified-food-fitness'),
                ]);
                echo '</div></div>';
            }
            ?>
        <?php else : ?>
            <p><?php esc_html_e('No ingredients found for this view.', 'simplified-food-fitness'); ?></p>
        <?php endif; ?>
    </div>
    <?php
    wp_reset_postdata();
}

function sff_get_all_ingredient_owners() {
    global $wpdb;

    $meta_key = '_sff_owner_id';
    $results  = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> '' AND meta_value <> '0'", $meta_key));

    if (empty($results)) {
        return [];
    }

    $ids = array_map('intval', $results);
    $ids = array_filter($ids, static function ($id) {
        return $id > 0;
    });

    if (empty($ids)) {
        return [];
    }

    $users = get_users([
        'include' => $ids,
        'fields'  => ['ID', 'display_name', 'user_login'],
    ]);

    $owners = [];
    foreach ($users as $user) {
        $owners[$user->ID] = $user->display_name ? $user->display_name : $user->user_login;
    }

    return $owners;
}

function sff_handle_promote_ingredient() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'simplified-food-fitness'));
    }

    $ingredient_id = isset($_POST['ingredient_id']) ? intval($_POST['ingredient_id']) : 0;
    if (!$ingredient_id) {
        wp_die(__('Invalid ingredient.', 'simplified-food-fitness'));
    }

    check_admin_referer('sff_promote_ingredient_' . $ingredient_id);

    update_post_meta($ingredient_id, '_sff_owner_id', 0);

    $redirect = add_query_arg(
        [
            'page'          => 'sff-ingredient-library',
            'tab'           => 'submissions',
            'sff_promoted'  => 1,
            'ingredient_id' => $ingredient_id,
        ],
        admin_url('admin.php')
    );

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_sff_promote_ingredient', 'sff_handle_promote_ingredient');

function sff_render_ingredient_library_notices() {
    if (empty($_GET['sff_promoted'])) {
        return;
    }

    $ingredient_id = isset($_GET['ingredient_id']) ? intval($_GET['ingredient_id']) : 0;
    $title         = $ingredient_id ? get_the_title($ingredient_id) : '';
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <?php
            if ($title) {
                printf(
                    esc_html__('"%s" has been promoted to the shared ingredient database.', 'simplified-food-fitness'),
                    esc_html($title)
                );
            } else {
                esc_html_e('Ingredient promoted to the shared database.', 'simplified-food-fitness');
            }
            ?>
        </p>
    </div>
    <?php
}
