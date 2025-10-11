(function($){
    function getMacroGroups(){
        var groups = (typeof sff_ajax_obj !== 'undefined' && sff_ajax_obj.macro_groups) ? sff_ajax_obj.macro_groups : {};
        return {
            macros: groups.macros || [],
            micros: groups.micros || []
        };
    }

    function formatLabel(field){
        return field.replace(/_/g, ' ').replace(/\b\w/g, function(char){
            return char.toUpperCase();
        }).replace('B12', 'B12').replace('B6', 'B6');
    }

    function formatValue(field, value){
        var number = parseFloat(value);
        if (!isFinite(number)) {
            return value;
        }

        if (field === 'cost') {
            return '$' + number.toFixed(2);
        }

        if (field === 'calories') {
            return Math.round(number).toString();
        }

        var rounded = Math.round(number * 100) / 100;
        if (Math.abs(rounded - Math.round(rounded)) < 0.01) {
            return Math.round(rounded).toString();
        }

        return rounded.toFixed(2);
    }

    function getEmptyText(){
        if (typeof sff_ajax_obj !== 'undefined' && sff_ajax_obj.recipe_empty_text) {
            return sff_ajax_obj.recipe_empty_text;
        }
        return 'Select ingredients to see nutrition details.';
    }

    function buildNutrientCards(data){
        if (!data) {
            return '<p class="sff-nutrient-empty">' + getEmptyText() + '</p>';
        }

        var groups = getMacroGroups();
        var order = [].concat(groups.macros, groups.micros);
        if (typeof data.cost !== 'undefined') {
            order.push('cost');
        }

        var html = '';

        order.forEach(function(field){
            if (typeof data[field] === 'undefined') {
                return;
            }

            var groupClass = 'meta';
            if (groups.macros.indexOf(field) !== -1) {
                groupClass = 'macro';
            } else if (groups.micros.indexOf(field) !== -1) {
                groupClass = 'micro';
            } else if (field === 'cost') {
                groupClass = 'cost';
            }

            html += '<div class="sff-nutrient-card sff-nutrient-card--' + groupClass + '">' +
                '<span class="sff-nutrient-label">' + formatLabel(field) + '</span>' +
                '<span class="sff-nutrient-value">' + formatValue(field, data[field]) + '</span>' +
                '</div>';
        });

        if (!html) {
            return '<p class="sff-nutrient-empty">' + getEmptyText() + '</p>';
        }

        return html;
    }

    function updateSelectedCount(){
        var count = $('select[name="sff_recipe_ingredients[]"] option:selected').length;
        var labelSet = (typeof sff_ajax_obj !== 'undefined' && sff_ajax_obj.recipe_labels) ? sff_ajax_obj.recipe_labels : {};
        var singular = labelSet.ingredient_single || 'ingredient selected';
        var plural = labelSet.ingredient_plural || 'ingredients selected';
        var label = count === 1 ? singular : plural;
        $('#sff-recipe-selected-count').text(count);
        $('#sff-recipe-selected-label').text(label);
    }

    function recalcRecipe(){
        var ids = $('select[name="sff_recipe_ingredients[]"]').val() || [];
        var servings = parseInt($('input[name="sff_recipe_servings"]').val(),10) || 1;

        $.post(sff_ajax_obj.ajax_url, {
            action: 'sff_recalc_recipe_nutrition',
            security: sff_ajax_obj.nonce,
            ingredient_ids: ids,
            servings: servings
        }, function(resp){
            if(resp.success){
                $('#sff-recipe-nutrients-per-serving').html(buildNutrientCards(resp.data.per_serving));
                $('#sff-recipe-nutrients-total').html(buildNutrientCards(resp.data.total));
            }
        });
    }

    function filterIngredients(){
        var term = ($('#sff-recipe-ingredient-filter').val() || '').toLowerCase();
        var $options = $('select[name="sff_recipe_ingredients[]"] option');
        var hasVisible = false;

        $options.each(function(){
            var $option = $(this);
            var text = $option.text().toLowerCase();
            var match = !term || text.indexOf(term) !== -1;
            $option.prop('hidden', !match);
            if (match) {
                hasVisible = true;
            }
        });

        $('#sff-recipe-no-results').toggle(!hasVisible);
    }

    $(document).on('change', 'select[name="sff_recipe_ingredients[]"]', function(){
        updateSelectedCount();
        recalcRecipe();
    });

    $(document).on('change input', 'input[name="sff_recipe_servings"]', recalcRecipe);
    $(document).on('input', '#sff-recipe-ingredient-filter', filterIngredients);

    $(function(){
        updateSelectedCount();
        filterIngredients();
    });
})(jQuery);
