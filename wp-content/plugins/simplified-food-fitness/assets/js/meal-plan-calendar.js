(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Sortable === 'undefined' || typeof sffMealPlan === 'undefined') {
            return;
        }

        var recipeList = document.getElementById('sff-recipe-list');
        var calendar = document.getElementById('sff-meal-calendar');
        var hiddenInput = document.getElementById('sff_meal_data');
        var totalsPanel = document.getElementById('sff-macro-totals');
        if (!recipeList || !calendar || !hiddenInput) {
            return;
        }

        var schedule = sffMealPlan.schedule || {};
        var recipes = sffMealPlan.recipes || [];
        var macros = sffMealPlan.macros || {};

        var dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        var dayLabels = {
            monday: 'Monday',
            tuesday: 'Tuesday',
            wednesday: 'Wednesday',
            thursday: 'Thursday',
            friday: 'Friday',
            saturday: 'Saturday',
            sunday: 'Sunday'
        };

        var numberFormatter = (typeof Intl !== 'undefined' && typeof Intl.NumberFormat === 'function')
            ? new Intl.NumberFormat(undefined, { maximumFractionDigits: 1 })
            : null;

        function formatNumber(value) {
            var num = parseFloat(value);
            if (!isFinite(num)) {
                num = 0;
            }
            if (numberFormatter) {
                return numberFormatter.format(num);
            }
            return Math.round(num * 10) / 10;
        }

        function createMacroMeta(macroData) {
            var meta = document.createElement('div');
            meta.className = 'sff-recipe-item__meta';

            if (macroData) {
                var caloriesLine = document.createElement('div');
                caloriesLine.className = 'sff-recipe-item__macro sff-recipe-item__macro--calories';
                caloriesLine.textContent = formatNumber(macroData.calories) + ' kcal';
                meta.appendChild(caloriesLine);

                var macrosLine = document.createElement('div');
                macrosLine.className = 'sff-recipe-item__macro sff-recipe-item__macro--split';

                var protein = document.createElement('span');
                protein.textContent = 'P ' + formatNumber(macroData.protein) + 'g';
                macrosLine.appendChild(protein);

                var carbs = document.createElement('span');
                carbs.textContent = 'C ' + formatNumber(macroData.carbs) + 'g';
                macrosLine.appendChild(carbs);

                var fat = document.createElement('span');
                fat.textContent = 'F ' + formatNumber(macroData.fat) + 'g';
                macrosLine.appendChild(fat);

                meta.appendChild(macrosLine);
            } else {
                var empty = document.createElement('div');
                empty.className = 'sff-recipe-item__macro sff-recipe-item__macro--empty';
                empty.textContent = 'No macro data yet';
                meta.appendChild(empty);
            }

            return meta;
        }

        function createRecipeHeader(titleText) {
            var header = document.createElement('div');
            header.className = 'sff-recipe-item__header';

            var title = document.createElement('span');
            title.className = 'sff-recipe-item__title';
            title.textContent = titleText;
            header.appendChild(title);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'sff-recipe-item__remove';
            removeBtn.setAttribute('aria-label', 'Remove recipe from this day');
            removeBtn.innerHTML = '&times;';
            header.appendChild(removeBtn);

            return header;
        }

        function createRecipeElement(recipe) {
            var el = document.createElement('div');
            el.className = 'sff-recipe-item';
            el.dataset.id = recipe.id;

            el.appendChild(createRecipeHeader(recipe.title));
            el.appendChild(createMacroMeta(macros[recipe.id]));

            return el;
        }

        function createMissingRecipeElement(id) {
            var el = document.createElement('div');
            el.className = 'sff-recipe-item sff-recipe-item--missing';
            el.dataset.id = id;

            el.appendChild(createRecipeHeader('Recipe not found (#' + id + ')'));

            var meta = document.createElement('div');
            meta.className = 'sff-recipe-item__meta';
            meta.textContent = 'This recipe is no longer available.';
            el.appendChild(meta);

            return el;
        }

        function refreshDayState(dayEl) {
            var body = dayEl.querySelector('.sff-calendar-day__body');
            if (!body) {
                return;
            }

            var count = body.querySelectorAll('.sff-recipe-item').length;
            var badge = dayEl.querySelector('.sff-calendar-day__count');
            var empty = dayEl.querySelector('.sff-calendar-day__empty');

            if (badge) {
                badge.textContent = count ? count + (count === 1 ? ' meal' : ' meals') : 'Empty';
            }
            if (empty) {
                empty.style.display = count ? 'none' : '';
            }
            dayEl.classList.toggle('is-empty', count === 0);
        }

        function renderRecipeList() {
            recipeList.innerHTML = '';

            if (!recipes.length) {
                var empty = document.createElement('div');
                empty.className = 'sff-recipe-pool__empty';
                empty.textContent = 'No recipes yet. Create a recipe to start building this plan.';
                recipeList.appendChild(empty);
            } else {
                recipes.forEach(function (recipe) {
                    recipeList.appendChild(createRecipeElement(recipe));
                });
            }

            Sortable.create(recipeList, {
                group: { name: 'recipes', pull: 'clone', put: false },
                sort: false,
                animation: 150,
                fallbackOnBody: true,
                fallbackTolerance: 6,
                ghostClass: 'sff-recipe-item--ghost',
                chosenClass: 'sff-recipe-item--chosen'
            });
        }

        function renderCalendar() {
            calendar.innerHTML = '';

            dayOrder.forEach(function (day) {
                var column = document.createElement('div');
                column.className = 'sff-calendar-day';
                column.dataset.day = day;

                var header = document.createElement('div');
                header.className = 'sff-calendar-day__header';

                var title = document.createElement('span');
                title.className = 'sff-calendar-day__title';
                title.textContent = dayLabels[day] || day;
                header.appendChild(title);

                var count = document.createElement('span');
                count.className = 'sff-calendar-day__count';
                header.appendChild(count);

                column.appendChild(header);

                var body = document.createElement('div');
                body.className = 'sff-calendar-day__body';
                column.appendChild(body);

                var emptyState = document.createElement('div');
                emptyState.className = 'sff-calendar-day__empty';
                emptyState.textContent = 'Drop recipes here';
                column.appendChild(emptyState);

                if (schedule[day]) {
                    schedule[day].forEach(function (id) {
                        var recipe = recipes.find(function (r) {
                            return parseInt(r.id, 10) === parseInt(id, 10);
                        });

                        if (recipe) {
                            body.appendChild(createRecipeElement(recipe));
                        } else {
                            body.appendChild(createMissingRecipeElement(id));
                        }
                    });
                }

                Sortable.create(body, {
                    group: 'recipes',
                    animation: 150,
                    ghostClass: 'sff-recipe-item--ghost',
                    chosenClass: 'sff-recipe-item--chosen',
                    onAdd: updateSchedule,
                    onUpdate: updateSchedule,
                    onRemove: updateSchedule
                });

                calendar.appendChild(column);
                refreshDayState(column);
            });
        }

        function updateSchedule() {
            schedule = {};

            var dayEls = calendar.querySelectorAll('.sff-calendar-day');
            dayEls.forEach(function (dayEl) {
                var day = dayEl.dataset.day;
                var items = dayEl.querySelectorAll('.sff-calendar-day__body .sff-recipe-item');

                schedule[day] = [];
                items.forEach(function (item) {
                    var id = parseInt(item.dataset.id, 10);
                    if (!isNaN(id)) {
                        schedule[day].push(id);
                    }
                });

                refreshDayState(dayEl);
            });

            hiddenInput.value = JSON.stringify(schedule);
            updateTotals();
        }

        function updateTotals() {
            if (!totalsPanel) {
                return;
            }

            var grid = totalsPanel.querySelector('.sff-macro-summary__grid');
            if (!grid) {
                return;
            }

            grid.innerHTML = '';

            var hasMeals = false;

            dayOrder.forEach(function (day) {
                var daySchedule = schedule[day] || [];
                if (!daySchedule.length) {
                    return;
                }

                hasMeals = true;

                var totals = { calories: 0, carbs: 0, protein: 0, fat: 0 };
                daySchedule.forEach(function (id) {
                    var m = macros[id];
                    if (m) {
                        totals.calories += parseFloat(m.calories) || 0;
                        totals.carbs += parseFloat(m.carbs) || 0;
                        totals.protein += parseFloat(m.protein) || 0;
                        totals.fat += parseFloat(m.fat) || 0;
                    }
                });

                var card = document.createElement('div');
                card.className = 'sff-macro-card';

                var heading = document.createElement('div');
                heading.className = 'sff-macro-card__day';
                heading.textContent = dayLabels[day] || day;
                card.appendChild(heading);

                var metrics = document.createElement('div');
                metrics.className = 'sff-macro-card__metrics';
                metrics.innerHTML = `
                    <span><strong>${formatNumber(totals.calories)}</strong> kcal</span>
                    <span><strong>${formatNumber(totals.protein)}g</strong> protein</span>
                    <span><strong>${formatNumber(totals.carbs)}g</strong> carbs</span>
                    <span><strong>${formatNumber(totals.fat)}g</strong> fat</span>
                `;
                card.appendChild(metrics);

                grid.appendChild(card);
            });

            totalsPanel.classList.toggle('is-empty', !hasMeals);

            if (!hasMeals) {
                var empty = document.createElement('div');
                empty.className = 'sff-macro-empty';
                empty.textContent = 'Drag recipes into the calendar to see daily nutrition totals.';
                grid.appendChild(empty);
            }
        }

        calendar.addEventListener('click', function (event) {
            var removeBtn = event.target.closest('.sff-recipe-item__remove');
            if (!removeBtn) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var item = removeBtn.closest('.sff-recipe-item');
            if (item && item.parentElement) {
                item.parentElement.removeChild(item);
                updateSchedule();
            }
        });

        renderRecipeList();
        renderCalendar();
        updateSchedule();
    });
})();

