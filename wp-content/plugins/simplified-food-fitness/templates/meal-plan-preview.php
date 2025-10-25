<?php
/** @var array $day_targets */
/** @var array $day_labels */
/** @var array $day_schedule */
/** @var int   $calorie_target */
?>
<div class="sff-meal-plan-admin">
    <div class="sff-card sff-meal-plan-calendar">
        <div class="sff-panel-header">
            <h3><?php esc_html_e('Weekly Meal Plan Preview', 'simplified-food-fitness'); ?></h3>
            <p><?php esc_html_e('Use this preview to review macro goals alongside each day type.', 'simplified-food-fitness'); ?></p>
        </div>
        <div class="sff-calendar-grid">
            <?php foreach ($day_schedule as $day => $type_slug) :
                $target = $day_targets[$type_slug] ?? null;
                ?>
                <div class="sff-calendar-day" data-day="<?php echo esc_attr($day); ?>">
                    <div class="sff-calendar-day__header">
                        <span class="sff-calendar-day__title"><?php echo esc_html($day_labels[$day]); ?></span>
                        <span class="sff-calendar-day__type"><?php echo esc_html($target['label']); ?></span>
                        <span class="sff-calendar-day__count"><?php esc_html_e('Preview', 'simplified-food-fitness'); ?></span>
                    </div>
                    <div class="sff-calendar-day__body">
                        <div class="sff-recipe-item sff-recipe-item--placeholder">
                            <div class="sff-recipe-item__header">
                                <span class="sff-recipe-item__title"><?php esc_html_e('Drag meals here in the editor', 'simplified-food-fitness'); ?></span>
                            </div>
                            <?php if ($target) : ?>
                                <div class="sff-recipe-item__meta">
                                    <div class="sff-recipe-item__macro sff-recipe-item__macro--calories"><?php echo esc_html(number_format_i18n($target['calories'])); ?> <?php esc_html_e('kcal goal', 'simplified-food-fitness'); ?></div>
                                    <div class="sff-recipe-item__macro sff-recipe-item__macro--split">
                                        <span><?php printf(__('P %sg', 'simplified-food-fitness'), number_format_i18n($target['protein'], 1)); ?></span>
                                        <span><?php printf(__('C %sg', 'simplified-food-fitness'), number_format_i18n($target['carbs'], 1)); ?></span>
                                        <span><?php printf(__('F %sg', 'simplified-food-fitness'), number_format_i18n($target['fat'], 1)); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="sff-calendar-day__empty"><?php esc_html_e('Drop recipes in the editor view', 'simplified-food-fitness'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="sff-card sff-macro-summary">
        <div class="sff-macro-summary__header">
            <h3><?php esc_html_e('Day Type Macro Goals', 'simplified-food-fitness'); ?></h3>
            <p><?php printf(esc_html__('Calorie target: %s kcal', 'simplified-food-fitness'), esc_html(number_format_i18n($calorie_target))); ?></p>
        </div>
        <div class="sff-macro-summary__grid">
            <?php foreach ($day_targets as $slug => $target) : ?>
                <div class="sff-macro-card">
                    <div class="sff-macro-card__day">
                        <span><?php echo esc_html($target['label']); ?></span>
                        <span class="sff-macro-card__type"><?php printf(__('%s%% P / %s%% C / %s%% F', 'simplified-food-fitness'), number_format_i18n($target['percentages']['protein'], 0), number_format_i18n($target['percentages']['carbs'], 0), number_format_i18n($target['percentages']['fat'], 0)); ?></span>
                    </div>
                    <div class="sff-macro-card__metrics">
                        <div class="sff-macro-metric">
                            <span><?php esc_html_e('Calories', 'simplified-food-fitness'); ?></span>
                            <span><?php echo esc_html(number_format_i18n($target['calories'])); ?></span>
                        </div>
                        <div class="sff-macro-metric">
                            <span><?php esc_html_e('Protein', 'simplified-food-fitness'); ?></span>
                            <span><?php echo esc_html(number_format_i18n($target['protein'], 1)); ?>g</span>
                        </div>
                        <div class="sff-macro-metric">
                            <span><?php esc_html_e('Carbs', 'simplified-food-fitness'); ?></span>
                            <span><?php echo esc_html(number_format_i18n($target['carbs'], 1)); ?>g</span>
                        </div>
                        <div class="sff-macro-metric">
                            <span><?php esc_html_e('Fat', 'simplified-food-fitness'); ?></span>
                            <span><?php echo esc_html(number_format_i18n($target['fat'], 1)); ?>g</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
