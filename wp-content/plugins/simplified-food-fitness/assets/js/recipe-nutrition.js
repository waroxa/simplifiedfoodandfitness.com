(function($){
    var recalcTimer = null;

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

    function normalizeQuantityInput($input){
        var value = parseFloat($input.val());
        if (!isFinite(value) || value <= 0) {
            value = 1;
        }
        $input.val(value);
        return value;
    }

    function collectIngredientState(){
        var map = {};

        $('.sff-recipe-ingredient-item').each(function(){
            var $item = $(this);
            var id = parseInt($item.data('id'), 10);
            if (!id) {
                return;
            }

            var qty = normalizeQuantityInput($item.find('.sff-recipe-ingredient-item__quantity'));
            map[id] = qty;
        });

        return {
            ids: Object.keys(map),
            quantities: map
        };
    }

    function syncHiddenSelect(state){
        var $hiddenSelect = $('#sff_recipe_ingredients');
        state = state || collectIngredientState();

        if ($hiddenSelect.length) {
            $hiddenSelect.empty();
            state.ids.forEach(function(id){
                $('<option>', {
                    value: id,
                    selected: 'selected'
                }).appendTo($hiddenSelect);
            });
        }

        $('.sff-recipe-ingredient-item').each(function(){
            var $item = $(this);
            var id = parseInt($item.data('id'), 10);
            if (!id) {
                return;
            }

            if (typeof state.quantities[id] !== 'undefined') {
                $item.find('.sff-recipe-ingredient-item__hidden').val(state.quantities[id]);
            }
        });

        return state;
    }

    function getIngredientLabel(count){
        var labelSet = (typeof sff_ajax_obj !== 'undefined' && sff_ajax_obj.recipe_labels) ? sff_ajax_obj.recipe_labels : {};
        var singular = labelSet.ingredient_single || 'ingredient selected';
        var plural = labelSet.ingredient_plural || 'ingredients selected';
        return count === 1 ? singular : plural;
    }

    function toggleEmptyState(count){
        if (typeof count === 'undefined') {
            count = $('.sff-recipe-ingredient-item').length;
        }
        $('#sff-recipe-ingredient-empty').toggle(count === 0);
    }

    function updateSelectedCount(){
        var state = collectIngredientState();
        var count = state.ids.length;
        $('#sff-recipe-selected-count').text(count);
        $('#sff-recipe-selected-label').text(getIngredientLabel(count));
        toggleEmptyState(count);
        updateIngredientSummary({ ingredient_count: count });
    }

    function updateIngredientSummary(summary){
        summary = summary || {};
        if (typeof summary.ingredient_count === 'undefined') {
            summary.ingredient_count = $('.sff-recipe-ingredient-item').length;
        }
        $('#sff-recipe-ingredient-count').text(summary.ingredient_count);

        if (typeof summary.total_cost !== 'undefined') {
            var costValue = parseFloat(summary.total_cost);
            if (!isFinite(costValue)) {
                costValue = 0;
            }
            $('#sff-recipe-ingredient-cost')
                .text(formatValue('cost', costValue))
                .attr('data-raw', costValue);
        }
    }

    function recalcRecipe(){
        var state = syncHiddenSelect();
        var servings = parseInt($('input[name="sff_recipe_servings"]').val(), 10) || 1;

        $.post(sff_ajax_obj.ajax_url, {
            action: 'sff_recalc_recipe_nutrition',
            security: sff_ajax_obj.nonce,
            ingredient_ids: state.ids,
            ingredient_quantities: JSON.stringify(state.quantities),
            servings: servings
        }, function(resp){
            if (resp && resp.success) {
                $('#sff-recipe-nutrients-per-serving').html(buildNutrientCards(resp.data.per_serving));
                $('#sff-recipe-nutrients-total').html(buildNutrientCards(resp.data.total));
                updateIngredientSummary(resp.data.summary);
            }
        });
    }

    function scheduleRecalc(){
        clearTimeout(recalcTimer);
        recalcTimer = setTimeout(recalcRecipe, 200);
    }

    function filterIngredients(){
        var term = ($('#sff-recipe-ingredient-filter').val() || '').toLowerCase();
        var $options = $('#sff_recipe_ingredient_picker option');
        var hasVisible = false;

        $options.each(function(){
            var $option = $(this);
            if ($option.val() === '') {
                $option.prop('hidden', false);
                return;
            }

            var match = !term || $option.text().toLowerCase().indexOf(term) !== -1;
            $option.prop('hidden', !match);
            if (match) {
                hasVisible = true;
            }
        });

        $('#sff-recipe-no-results').toggle(!hasVisible && term.length > 0);
    }

    function addIngredientToList(ingredientId, servings, $option){
        var $list = $('#sff-recipe-ingredient-list');
        var servingsLabel = $list.data('servingsLabel') || 'Servings';
        var removeLabel = $list.data('removeLabel') || 'Remove';
        var name = $option ? $.trim($option.text()) : '';
        var servingSize = $option ? $.trim($option.data('servingSize')) : '';
        var displayServings = servings > 0 ? servings : 1;
        var itemId = 'sff-recipe-ingredient-' + ingredientId + '-servings';
        var $existing = $list.find('.sff-recipe-ingredient-item[data-id="' + ingredientId + '"]');

        if ($existing.length) {
            $existing.find('.sff-recipe-ingredient-item__quantity').val(displayServings);
            $existing.find('.sff-recipe-ingredient-item__hidden').val(displayServings);
            return $existing;
        }

        var $item = $('<li>', {
            'class': 'sff-recipe-ingredient-item',
            'data-id': ingredientId
        });

        var $text = $('<div>', {'class': 'sff-recipe-ingredient-item__text'}).appendTo($item);
        $('<span>', {'class': 'sff-recipe-ingredient-item__name', text: name}).appendTo($text);
        if (servingSize) {
            $('<span>', {'class': 'sff-recipe-ingredient-item__serving-size', text: servingSize}).appendTo($text);
        }

        var $actions = $('<div>', {'class': 'sff-recipe-ingredient-item__actions'}).appendTo($item);
        $('<label>', {'for': itemId, text: servingsLabel}).appendTo($actions);
        $('<input>', {
            type: 'number',
            min: '0.01',
            step: '0.01',
            id: itemId,
            'class': 'sff-recipe-ingredient-item__quantity',
            value: displayServings
        }).appendTo($actions);
        $('<button>', {
            type: 'button',
            'class': 'button-link-delete sff-recipe-ingredient-remove',
            text: removeLabel
        }).appendTo($actions);

        $('<input>', {
            type: 'hidden',
            'class': 'sff-recipe-ingredient-item__hidden',
            name: 'sff_recipe_ingredient_servings[' + ingredientId + ']',
            value: displayServings
        }).appendTo($item);

        $list.append($item);
        return $item;
    }

    $(document).on('click', '.sff-recipe-add-ingredient', function(event){
        event.preventDefault();
        var $picker = $('#sff_recipe_ingredient_picker');
        var ingredientId = parseInt($picker.val(), 10);
        if (!ingredientId) {
            return;
        }

        var amountInput = $('#sff_recipe_ingredient_serving_amount');
        var servings = normalizeQuantityInput(amountInput);
        var $option = $picker.find('option:selected');

        addIngredientToList(ingredientId, servings, $option);
        updateSelectedCount();
        syncHiddenSelect();
        scheduleRecalc();
    });

    $(document).on('input change', '.sff-recipe-ingredient-item__quantity', function(){
        var $input = $(this);
        normalizeQuantityInput($input);
        syncHiddenSelect();
        scheduleRecalc();
    });

    $(document).on('click', '.sff-recipe-ingredient-remove', function(event){
        event.preventDefault();
        $(this).closest('.sff-recipe-ingredient-item').remove();
        updateSelectedCount();
        syncHiddenSelect();
        scheduleRecalc();
    });

    $(document).on('change input', 'input[name="sff_recipe_servings"]', scheduleRecalc);
    $(document).on('input', '#sff-recipe-ingredient-filter', filterIngredients);

    $(function(){
        updateSelectedCount();
        syncHiddenSelect();
        filterIngredients();
        toggleEmptyState();
    });
})(jQuery);
