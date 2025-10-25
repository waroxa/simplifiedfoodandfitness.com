<?php
if (!defined('ABSPATH')) {
    exit;
}

if (wp_script_is('sff-meal-plan-preview', 'registered')) {
    wp_enqueue_script('sff-meal-plan-preview');
} else {
    wp_enqueue_script(
        'sff-meal-plan-preview',
        SFF_PLUGIN_URL . 'assets/js/meal-plan-preview.js',
        [],
        SFF_PLUGIN_VERSION,
        true
    );
}

$localized_locale = isset($locale) ? $locale : (function_exists('get_locale') ? get_locale() : 'en_US');

wp_localize_script('sff-meal-plan-preview', 'sffMealPlanPreview', [
    'ajaxUrl'    => admin_url('admin-ajax.php'),
    'nonce'      => wp_create_nonce('sff_meal_plan_preview'),
    'locale'     => $localized_locale,
    'thresholds' => [
        'warnLow'    => 0.85,
        'dangerLow'  => 0.6,
        'warnHigh'   => 1.05,
        'dangerHigh' => 1.2,
    ],
    'i18n'       => [
        'saving'       => __('Saving changes...', 'simplified-food-fitness'),
        'savingShort'  => __('Saving…', 'simplified-food-fitness'),
        'saved'        => __('Ingredient swaps saved!', 'simplified-food-fitness'),
        'reset'        => __('Swaps reset to the original ingredients.', 'simplified-food-fitness'),
        'error'        => __('We couldn’t save those swaps right now. Please try again.', 'simplified-food-fitness'),
        'statusLabels' => [
            'good'    => __('On target', 'simplified-food-fitness'),
            'warn'    => __('Needs attention', 'simplified-food-fitness'),
            'danger'  => __('Off track', 'simplified-food-fitness'),
            'neutral' => __('No meals yet', 'simplified-food-fitness'),
        ],
        'customizedLabel' => __('Customized', 'simplified-food-fitness'),
        'emptySwaps'   => __('Add ingredients to your personal bank to enable swaps.', 'simplified-food-fitness'),
    ],
]);

$status_labels = [
    'good'    => __('On target', 'simplified-food-fitness'),
    'warn'    => __('Needs attention', 'simplified-food-fitness'),
    'danger'  => __('Off track', 'simplified-food-fitness'),
    'neutral' => __('No meals yet', 'simplified-food-fitness'),
];

$macro_display_map = [
    'calories' => [
        'label' => __('Calories', 'simplified-food-fitness'),
        'unit'  => '',
        'precision' => 0,
    ],
    'protein'  => [
        'label' => __('Protein', 'simplified-food-fitness'),
        'unit'  => __('g', 'simplified-food-fitness'),
        'precision' => 1,
    ],
    'carbs'    => [
        'label' => __('Carbs', 'simplified-food-fitness'),
        'unit'  => __('g', 'simplified-food-fitness'),
        'precision' => 1,
    ],
    'fat'      => [
        'label' => __('Fat', 'simplified-food-fitness'),
        'unit'  => __('g', 'simplified-food-fitness'),
        'precision' => 1,
    ],
];
?>
<div class="sff-meal-plan-preview">
    <div class="sff-panel-header sff-meal-plan-preview__intro">
        <div class="sff-meal-plan-preview__copy">
            <h3><?php esc_html_e('Weekly Meal Plan Preview', 'simplified-food-fitness'); ?></h3>
            <p><?php esc_html_e('Use this preview to review macro goals alongside each day type.', 'simplified-food-fitness'); ?></p>
        </div>
        <?php if (!empty($plan_title) || !empty($plan_last_modified)) : ?>
            <div class="sff-preview-plan-meta">
                <?php if (!empty($plan_title)) : ?>
                    <span class="sff-preview-plan-meta__title"><?php echo esc_html($plan_title); ?></span>
                <?php endif; ?>
                <?php if (!empty($plan_last_modified)) : ?>
                    <span class="sff-preview-plan-meta__updated"><?php printf(esc_html__('Updated %s', 'simplified-food-fitness'), esc_html($plan_last_modified)); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="sff-calendar-grid">
        <?php foreach ($calendar_days as $day_key => $day_data) :
            $target          = $day_data['target'];
            $status          = $day_data['status']['overall'];
            $macro_statuses  = $day_data['status']['macros'];
            $totals          = $day_data['totals'];
            $recipes         = $day_data['recipes'];
            $day_type_label  = isset($target['label']) ? $target['label'] : '';
            ?>
            <div
                class="sff-calendar-day sff-calendar-day--<?php echo esc_attr($status); ?>"
                data-day="<?php echo esc_attr($day_key); ?>"
                data-target-calories="<?php echo esc_attr($target['calories']); ?>"
                data-target-protein="<?php echo esc_attr($target['protein']); ?>"
                data-target-carbs="<?php echo esc_attr($target['carbs']); ?>"
                data-target-fat="<?php echo esc_attr($target['fat']); ?>"
            >
                <div class="sff-calendar-day__header">
                    <div class="sff-calendar-day__heading">
                        <span class="sff-calendar-day__title"><?php echo esc_html($day_data['label']); ?></span>
                        <?php if ($day_type_label) : ?>
                            <span class="sff-calendar-day__type"><?php echo esc_html($day_type_label); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="sff-calendar-day__status-badge" data-status="<?php echo esc_attr($status); ?>">
                        <?php echo esc_html($status_labels[$status] ?? $status_labels['neutral']); ?>
                    </span>
                </div>

                <div class="sff-calendar-day__macros">
                    <?php foreach ($macro_display_map as $metric => $meta) :
                        $value        = isset($totals[$metric]) ? floatval($totals[$metric]) : 0.0;
                        $target_value = isset($target[$metric]) ? floatval($target[$metric]) : 0.0;
                        ?>
                        <div class="sff-calendar-day__macro" data-day-metric="<?php echo esc_attr($metric); ?>" data-status="<?php echo esc_attr($macro_statuses[$metric] ?? 'neutral'); ?>">
                            <span class="sff-calendar-day__macro-label"><?php echo esc_html($meta['label']); ?></span>
                            <span class="sff-calendar-day__macro-value">
                                <span class="sff-calendar-day__macro-number"><?php echo esc_html(number_format_i18n($value, $meta['precision'])); ?></span>
                                <span class="sff-calendar-day__macro-target" data-unit="<?php echo esc_attr($meta['unit']); ?>"><?php echo esc_html('/ ' . number_format_i18n($target_value, $meta['precision']) . ($meta['unit'] ? ' ' . $meta['unit'] : '')); ?></span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="sff-calendar-day__body">
                    <?php if (!empty($recipes)) : ?>
                        <?php foreach ($recipes as $recipe) :
                            $macros = $recipe['macros'];
                            $swaps  = $recipe['swaps'];
                            ?>
                            <article class="sff-preview-meal" data-recipe-id="<?php echo esc_attr($recipe['id']); ?>" data-calories="<?php echo esc_attr($macros['calories']); ?>" data-protein="<?php echo esc_attr($macros['protein']); ?>" data-carbs="<?php echo esc_attr($macros['carbs']); ?>" data-fat="<?php echo esc_attr($macros['fat']); ?>">
                                <header class="sff-preview-meal__header">
                                    <div class="sff-preview-meal__titles">
                                        <h4 class="sff-preview-meal__title"><?php echo esc_html($recipe['title']); ?></h4>
                                        <?php if (!empty($recipe['has_customization'])) : ?>
                                            <span class="sff-preview-meal__badge"><?php esc_html_e('Customized', 'simplified-food-fitness'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <dl class="sff-preview-meal__macros">
                                        <div>
                                            <dt><?php esc_html_e('kcal', 'simplified-food-fitness'); ?></dt>
                                            <dd data-metric="calories"><?php echo esc_html(number_format_i18n($macros['calories'], 0)); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Protein', 'simplified-food-fitness'); ?></dt>
                                            <dd data-metric="protein"><?php echo esc_html(number_format_i18n($macros['protein'], 1)); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Carbs', 'simplified-food-fitness'); ?></dt>
                                            <dd data-metric="carbs"><?php echo esc_html(number_format_i18n($macros['carbs'], 1)); ?></dd>
                                        </div>
                                        <div>
                                            <dt><?php esc_html_e('Fat', 'simplified-food-fitness'); ?></dt>
                                            <dd data-metric="fat"><?php echo esc_html(number_format_i18n($macros['fat'], 1)); ?></dd>
                                        </div>
                                    </dl>
                                </header>

                                <div class="sff-preview-meal__ingredients" data-recipe-id="<?php echo esc_attr($recipe['id']); ?>">
                                    <?php echo wp_kses_post($recipe['ingredients_html']); ?>
                                </div>

                                <details class="sff-preview-meal__editor">
                                    <summary><?php esc_html_e('Swap ingredients', 'simplified-food-fitness'); ?></summary>
                                    <form class="sff-preview-swap-form" data-recipe-id="<?php echo esc_attr($recipe['id']); ?>" data-day="<?php echo esc_attr($day_key); ?>">
                                        <input type="hidden" name="recipe_id" value="<?php echo esc_attr($recipe['id']); ?>">

                                        <?php if (!empty($has_swap_options)) : ?>
                                            <?php foreach ($recipe['ingredients'] as $row) :
                                                $original_id  = intval($row['original_id']);
                                                $selected_id  = isset($swaps[$original_id]) ? intval($swaps[$original_id]) : 0;
                                                $field_id     = 'sff-preview-swap-' . $recipe['id'] . '-' . $original_id;
                                                ?>
                                                <div class="sff-preview-swap-form__row">
                                                    <label for="<?php echo esc_attr($field_id); ?>">
                                                        <?php
                                                        printf(
                                                            esc_html__('Swap “%s” for', 'simplified-food-fitness'),
                                                            esc_html($row['original_name'])
                                                        );
                                                        ?>
                                                    </label>
                                                    <select id="<?php echo esc_attr($field_id); ?>" name="swaps[<?php echo esc_attr($original_id); ?>]" data-original-name="<?php echo esc_attr($row['original_name']); ?>">
                                                        <option value=""><?php esc_html_e('Keep original ingredient', 'simplified-food-fitness'); ?></option>
                                                        <?php if (!empty($personal_options)) : ?>
                                                            <optgroup label="<?php echo esc_attr__('My ingredients', 'simplified-food-fitness'); ?>">
                                                                <?php foreach ($personal_options as $option_id => $option_name) :
                                                                    $preference_state = sff_determine_preference_state_for_name($option_name, $preferences);
                                                                    $emoji            = $preference_state === 'liked' ? '🟢 ' : ($preference_state === 'disliked' ? '🔴 ' : '');
                                                                    ?>
                                                                    <option value="<?php echo esc_attr($option_id); ?>" <?php selected($selected_id, $option_id); ?> data-preference="<?php echo esc_attr($preference_state); ?>"><?php echo esc_html($emoji . $option_name); ?></option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endif; ?>
                                                        <?php if (!empty($general_options)) : ?>
                                                            <optgroup label="<?php echo esc_attr__('General database', 'simplified-food-fitness'); ?>">
                                                                <?php foreach ($general_options as $option_id => $option_name) :
                                                                    $preference_state = sff_determine_preference_state_for_name($option_name, $preferences);
                                                                    $emoji            = $preference_state === 'liked' ? '🟢 ' : ($preference_state === 'disliked' ? '🔴 ' : '');
                                                                    ?>
                                                                    <option value="<?php echo esc_attr($option_id); ?>" <?php selected($selected_id, $option_id); ?> data-preference="<?php echo esc_attr($preference_state); ?>"><?php echo esc_html($emoji . $option_name); ?></option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>

                                            <div class="sff-preview-swap-form__actions">
                                                <button type="submit" class="button button-primary"><?php esc_html_e('Save ingredient swaps', 'simplified-food-fitness'); ?></button>
                                                <button type="button" class="button button-link sff-preview-reset" data-recipe-id="<?php echo esc_attr($recipe['id']); ?>" <?php echo empty($recipe['has_customization']) ? 'disabled aria-disabled="true"' : ''; ?>><?php esc_html_e('Reset to original', 'simplified-food-fitness'); ?></button>
                                            </div>
                                        <?php else : ?>
                                            <p class="sff-preview-feedback sff-preview-feedback--empty"><?php esc_html_e('Add ingredients to your personal bank to enable swaps.', 'simplified-food-fitness'); ?></p>
                                        <?php endif; ?>

                                        <p class="sff-preview-feedback" aria-live="polite"></p>
                                    </form>
                                </details>
                            </article>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="sff-calendar-day__empty">
                            <?php esc_html_e('No meals assigned yet. Ask your coach to add recipes to this day.', 'simplified-food-fitness'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($has_meals)) : ?>
        <div class="sff-preview-empty-plan">
            <p><?php esc_html_e('Meals assigned to your week will appear here. Once your coach builds your plan you can review macros and ingredient preferences in real time.', 'simplified-food-fitness'); ?></p>
        </div>
    <?php endif; ?>
</div>
