(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Sortable === 'undefined' || typeof sffMealPlan === 'undefined') {
            return;
        }

        var recipeList = document.getElementById('sff-recipe-list');
        var calendar = document.getElementById('sff-meal-calendar');
        var hiddenInput = document.getElementById('sff_meal_data');
        var dayTypeControls = document.querySelectorAll('.sff-day-type-select');
        var hiddenDayTypeInput = document.getElementById('sff_day_types');
        if (!recipeList || !calendar || !hiddenInput) {
            return;
        }

        var schedule = sffMealPlan.schedule || {};
        var recipes = sffMealPlan.recipes || [];
        var macros = sffMealPlan.macros || {};
        var selectedDayTypes = Object.assign({}, sffMealPlan.selectedDayTypes || {});
        var dayTypeOptions = sffMealPlan.dayTypeOptions || {};
        var timeSlots = Array.isArray(sffMealPlan.timeSlots) && sffMealPlan.timeSlots.length
            ? sffMealPlan.timeSlots
            : ['6:30 AM', '9:30 AM', '12:30 PM', '3:30 PM', '6:30 PM', '8:30 PM'];
        var slotPlaceholder = (sffMealPlan.i18n && sffMealPlan.i18n.slotPlaceholder)
            ? sffMealPlan.i18n.slotPlaceholder
            : 'Drop meal here';
        var emptyStateText = (sffMealPlan.i18n && sffMealPlan.i18n.emptyDay)
            ? sffMealPlan.i18n.emptyDay
            : 'Drop recipes here';

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

        var dayTypeBadges = {};
        var dayTotals = {};
        var slotBodiesByDay = {};

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

        function getDayTypeLabel(day) {
            var type = selectedDayTypes[day];
            if (type && dayTypeOptions[type]) {
                return dayTypeOptions[type].label || type;
            }
            return '';
        }

        function getTargetsForDay(day) {
            var type = selectedDayTypes[day];
            if (type && dayTypeOptions[type]) {
                return dayTypeOptions[type];
            }
            return null;
        }

        function updateDayTypeBadge(day) {
            var badge = dayTypeBadges[day];
            if (badge) {
                badge.textContent = getDayTypeLabel(day);
            }
        }

        function serializeDayTypes() {
            if (!hiddenDayTypeInput) {
                return;
            }
            try {
                hiddenDayTypeInput.value = JSON.stringify(selectedDayTypes);
            } catch (error) {
                // noop
            }
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
            var slotBodies = dayEl.querySelectorAll('.sff-calendar-day__slot-body');

            if (badge) {
                badge.textContent = count ? count + (count === 1 ? ' meal' : ' meals') : 'Empty';
            }
            if (empty) {
                empty.style.display = count ? 'none' : '';
            }
            dayEl.classList.toggle('is-empty', count === 0);
            slotBodies.forEach(function (slotBody) {
                var hasMeals = slotBody.querySelectorAll('.sff-recipe-item').length > 0;
                slotBody.classList.toggle('is-empty', !hasMeals);
            });
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
            dayTypeBadges = {};
            dayTotals = {};
            slotBodiesByDay = {};

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

                var typeBadge = document.createElement('span');
                typeBadge.className = 'sff-calendar-day__type';
                typeBadge.textContent = getDayTypeLabel(day);
                header.appendChild(typeBadge);
                dayTypeBadges[day] = typeBadge;

                var count = document.createElement('span');
                count.className = 'sff-calendar-day__count';
                header.appendChild(count);

                column.appendChild(header);

                var body = document.createElement('div');
                body.className = 'sff-calendar-day__body';
                column.appendChild(body);

                var slotsWrapper = document.createElement('div');
                slotsWrapper.className = 'sff-calendar-day__slots';
                body.appendChild(slotsWrapper);

                slotBodiesByDay[day] = [];

                var slotsToRender = timeSlots.length ? timeSlots : ['Anytime'];
                slotsToRender.forEach(function (label, slotIndex) {
                    var slot = document.createElement('div');
                    slot.className = 'sff-calendar-day__slot';
                    slot.dataset.slot = slotIndex;

                    var slotLabel = document.createElement('span');
                    slotLabel.className = 'sff-calendar-day__slot-label';
                    slotLabel.textContent = label;
                    slot.appendChild(slotLabel);

                    var slotBody = document.createElement('div');
                    slotBody.className = 'sff-calendar-day__slot-body';
                    slotBody.dataset.placeholder = slotPlaceholder;
                    slotBody.classList.add('is-empty');
                    slot.appendChild(slotBody);

                    slotsWrapper.appendChild(slot);
                    slotBodiesByDay[day].push(slotBody);

                    Sortable.create(slotBody, {
                        group: 'recipes',
                        animation: 150,
                        ghostClass: 'sff-recipe-item--ghost',
                        chosenClass: 'sff-recipe-item--chosen',
                        onAdd: updateSchedule,
                        onUpdate: updateSchedule,
                        onRemove: updateSchedule
                    });
                });

                var emptyState = document.createElement('div');
                emptyState.className = 'sff-calendar-day__empty';
                emptyState.textContent = emptyStateText;
                column.appendChild(emptyState);

                var totalsWrapper = document.createElement('div');
                totalsWrapper.className = 'sff-calendar-day__totals is-empty is-neutral';

                var totalsTitle = document.createElement('div');
                totalsTitle.className = 'sff-calendar-day__totals-title';
                totalsTitle.textContent = 'Daily Nutrition Snapshot';
                totalsWrapper.appendChild(totalsTitle);

                var totalsGrid = document.createElement('div');
                totalsGrid.className = 'sff-calendar-day__totals-grid';
                totalsWrapper.appendChild(totalsGrid);

                var totalsEmpty = document.createElement('div');
                totalsEmpty.className = 'sff-calendar-day__totals-empty';
                totalsEmpty.textContent = 'Add recipes to see nutrition totals.';
                totalsWrapper.appendChild(totalsEmpty);

                column.appendChild(totalsWrapper);
                dayTotals[day] = { wrapper: totalsWrapper, grid: totalsGrid, empty: totalsEmpty };

                if (schedule[day]) {
                    var slotBodies = slotBodiesByDay[day];
                    var slotCount = slotBodies.length || 1;
                    var itemIndex = 0;
                    schedule[day].forEach(function (id) {
                        var recipe = recipes.find(function (r) {
                            return parseInt(r.id, 10) === parseInt(id, 10);
                        });

                        var targetSlot = slotBodies[itemIndex % slotCount] || body;
                        itemIndex += 1;

                        if (recipe) {
                            targetSlot.appendChild(createRecipeElement(recipe));
                        } else {
                            targetSlot.appendChild(createMissingRecipeElement(id));
                        }
                    });
                }

                calendar.appendChild(column);
                refreshDayState(column);
            });
            serializeDayTypes();
        }

        function updateSchedule() {
            schedule = {};

            var dayEls = calendar.querySelectorAll('.sff-calendar-day');
            dayEls.forEach(function (dayEl) {
                var day = dayEl.dataset.day;
                var slotBodies = dayEl.querySelectorAll('.sff-calendar-day__slot-body');

                schedule[day] = [];
                slotBodies.forEach(function (slotBody) {
                    slotBody.querySelectorAll('.sff-recipe-item').forEach(function (item) {
                        var id = parseInt(item.dataset.id, 10);
                        if (!isNaN(id)) {
                            schedule[day].push(id);
                        }
                    });
                });

                refreshDayState(dayEl);
            });

            hiddenInput.value = JSON.stringify(schedule);
            updateTotals();
        }

        function determineStatus(value, target) {
            var ratio = target > 0 ? value / target : 0;
            if (ratio >= 0.95) {
                return 'danger';
            }
            if (ratio <= 0.6 && value > 0) {
                return 'warn';
            }
            if (value === 0 && target > 0) {
                return 'warn';
            }
            if (target === 0 && value === 0) {
                return 'neutral';
            }
            return 'good';
        }

        function worstStatus(statuses) {
            if (statuses.indexOf('danger') !== -1) {
                return 'danger';
            }
            if (statuses.indexOf('warn') !== -1) {
                return 'warn';
            }
            if (statuses.indexOf('good') !== -1) {
                return 'good';
            }
            return 'neutral';
        }

        function updateTotals() {
            dayOrder.forEach(function (day) {
                var totalsRef = dayTotals[day];
                if (!totalsRef) {
                    return;
                }

                var wrapper = totalsRef.wrapper;
                var grid = totalsRef.grid;

                grid.innerHTML = '';
                ['is-good', 'is-warn', 'is-danger', 'is-neutral'].forEach(function (cls) {
                    wrapper.classList.remove(cls);
                });

                var daySchedule = schedule[day] || [];
                if (!daySchedule.length) {
                    wrapper.classList.add('is-empty', 'is-neutral');
                    return;
                }

                wrapper.classList.remove('is-empty');

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

                var targets = getTargetsForDay(day);
                var statuses = [];

                function appendMetric(label, valueText, status) {
                    var metric = document.createElement('div');
                    metric.className = 'sff-calendar-day__metric';
                    if (status) {
                        metric.classList.add('is-' + status);
                        statuses.push(status);
                    }

                    var labelEl = document.createElement('span');
                    labelEl.className = 'sff-calendar-day__metric-label';
                    labelEl.textContent = label;
                    metric.appendChild(labelEl);

                    var valueEl = document.createElement('span');
                    valueEl.className = 'sff-calendar-day__metric-value';
                    valueEl.textContent = valueText;
                    metric.appendChild(valueEl);

                    grid.appendChild(metric);
                }

                if (targets) {
                    var caloriesStatus = determineStatus(totals.calories, targets.calories);
                    var proteinStatus = determineStatus(totals.protein, targets.protein);
                    var carbStatus = determineStatus(totals.carbs, targets.carbs);
                    var fatStatus = determineStatus(totals.fat, targets.fat);

                    appendMetric('Calories', formatNumber(totals.calories) + ' / ' + formatNumber(targets.calories), caloriesStatus);
                    appendMetric('Protein', formatNumber(totals.protein) + 'g / ' + formatNumber(targets.protein) + 'g', proteinStatus);
                    appendMetric('Carbs', formatNumber(totals.carbs) + 'g / ' + formatNumber(targets.carbs) + 'g', carbStatus);
                    appendMetric('Fat', formatNumber(totals.fat) + 'g / ' + formatNumber(targets.fat) + 'g', fatStatus);
                } else {
                    appendMetric('Calories', formatNumber(totals.calories), null);
                    appendMetric('Protein', formatNumber(totals.protein) + 'g', null);
                    appendMetric('Carbs', formatNumber(totals.carbs) + 'g', null);
                    appendMetric('Fat', formatNumber(totals.fat) + 'g', null);
                }

                var overallStatus = targets ? worstStatus(statuses) : 'neutral';
                if (!overallStatus) {
                    overallStatus = 'neutral';
                }
                wrapper.classList.add('is-' + overallStatus);
            });
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

        if (dayTypeControls && dayTypeControls.length) {
            dayTypeControls.forEach(function (select) {
                select.addEventListener('change', function (event) {
                    var day = event.target.getAttribute('data-day');
                    var value = event.target.value;
                    if (!day) {
                        return;
                    }
                    if (dayTypeOptions[value]) {
                        selectedDayTypes[day] = value;
                        updateDayTypeBadge(day);
                        serializeDayTypes();
                        updateTotals();
                    }
                });
            });
        }
    });
})();

