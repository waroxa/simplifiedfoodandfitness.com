(function () {
    function toNumber(value) {
        var num = parseFloat(value);
        return isFinite(num) ? num : 0;
    }

    function formatNumber(value, precision, locale) {
        var options = {
            minimumFractionDigits: precision,
            maximumFractionDigits: precision
        };
        try {
            return new Intl.NumberFormat(locale || undefined, options).format(value);
        } catch (error) {
            var factor = Math.pow(10, precision);
            return String(Math.round(value * factor) / factor);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof sffMealPlanPreview === 'undefined') {
            return;
        }

        var root = document.querySelector('.sff-meal-plan-preview');
        if (!root) {
            return;
        }

        var thresholds = sffMealPlanPreview.thresholds || {};
        var locale = sffMealPlanPreview.locale || undefined;
        var warnLow = typeof thresholds.warnLow === 'number' ? thresholds.warnLow : 0.85;
        var warnHigh = typeof thresholds.warnHigh === 'number' ? thresholds.warnHigh : 1.05;
        var dangerLow = typeof thresholds.dangerLow === 'number' ? thresholds.dangerLow : 0.6;
        var dangerHigh = typeof thresholds.dangerHigh === 'number' ? thresholds.dangerHigh : 1.2;

        function determineStatus(value, target) {
            value = Math.max(0, value);
            target = Math.max(0, target);

            if (target <= 0) {
                return value > 0 ? 'warn' : 'neutral';
            }

            if (value <= 0.0001) {
                return 'warn';
            }

            var ratio = value / target;
            if (ratio >= dangerHigh || ratio <= dangerLow) {
                return 'danger';
            }

            if (ratio >= warnHigh || ratio <= warnLow) {
                return 'warn';
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

        function updateDayTotals(dayEl) {
            var recipes = dayEl.querySelectorAll('.sff-preview-meal');
            var totals = {
                calories: 0,
                protein: 0,
                carbs: 0,
                fat: 0
            };

            recipes.forEach(function (recipe) {
                totals.calories += toNumber(recipe.dataset.calories);
                totals.protein += toNumber(recipe.dataset.protein);
                totals.carbs += toNumber(recipe.dataset.carbs);
                totals.fat += toNumber(recipe.dataset.fat);
            });

            var targetCalories = toNumber(dayEl.dataset.targetCalories);
            var targetProtein = toNumber(dayEl.dataset.targetProtein);
            var targetCarbs = toNumber(dayEl.dataset.targetCarbs);
            var targetFat = toNumber(dayEl.dataset.targetFat);

            var statuses = {
                calories: determineStatus(totals.calories, targetCalories),
                protein: determineStatus(totals.protein, targetProtein),
                carbs: determineStatus(totals.carbs, targetCarbs),
                fat: determineStatus(totals.fat, targetFat)
            };

            var overall = worstStatus([
                statuses.calories,
                statuses.protein,
                statuses.carbs,
                statuses.fat
            ]);

            dayEl.setAttribute('data-status', overall);
            dayEl.classList.remove('sff-calendar-day--good', 'sff-calendar-day--warn', 'sff-calendar-day--danger', 'sff-calendar-day--neutral');
            dayEl.classList.add('sff-calendar-day--' + overall);

            var badge = dayEl.querySelector('.sff-calendar-day__status-badge');
            if (badge && sffMealPlanPreview.i18n && sffMealPlanPreview.i18n.statusLabels) {
                var label = sffMealPlanPreview.i18n.statusLabels[overall] || sffMealPlanPreview.i18n.statusLabels.neutral;
                badge.textContent = label;
                badge.dataset.status = overall;
            }

            dayEl.querySelectorAll('.sff-calendar-day__macro').forEach(function (macroEl) {
                var metric = macroEl.dataset.dayMetric;
                if (!metric || typeof totals[metric] === 'undefined') {
                    return;
                }

                var precision = metric === 'calories' ? 0 : 1;
                var targetValue = 0;
                if (metric === 'calories') {
                    targetValue = targetCalories;
                } else if (metric === 'protein') {
                    targetValue = targetProtein;
                } else if (metric === 'carbs') {
                    targetValue = targetCarbs;
                } else if (metric === 'fat') {
                    targetValue = targetFat;
                }

                macroEl.dataset.status = statuses[metric];

                var numberEl = macroEl.querySelector('.sff-calendar-day__macro-number');
                var targetEl = macroEl.querySelector('.sff-calendar-day__macro-target');
                if (numberEl) {
                    numberEl.textContent = formatNumber(totals[metric], precision, locale);
                }
                if (targetEl) {
                    var targetText = formatNumber(targetValue, precision, locale);
                    var unit = targetEl.dataset.unit || '';
                    targetEl.textContent = '/ ' + targetText + (unit ? ' ' + unit : '');
                }
            });
        }

        function refreshAllDays() {
            var days = root.querySelectorAll('.sff-calendar-day');
            days.forEach(updateDayTotals);
        }

        function syncFormDayAttributes(dayEl) {
            if (!dayEl) {
                return;
            }
            var dayKey = dayEl.dataset.day || '';
            dayEl.querySelectorAll('.sff-preview-swap-form').forEach(function (form) {
                form.dataset.day = dayKey;
            });
        }

        function captureDayPayload(dayEl) {
            if (!dayEl) {
                return null;
            }

            var payload = {
                dataset: {},
                status: dayEl.dataset.status || 'neutral',
                typeText: '',
                statusBadgeText: '',
                macrosHtml: '',
                bodyHtml: ''
            };

            ['targetCalories', 'targetProtein', 'targetCarbs', 'targetFat'].forEach(function (field) {
                payload.dataset[field] = dayEl.dataset[field] || '';
            });

            var typeEl = dayEl.querySelector('.sff-calendar-day__type');
            if (typeEl) {
                payload.typeText = typeEl.textContent || '';
            }

            var badge = dayEl.querySelector('.sff-calendar-day__status-badge');
            if (badge) {
                payload.statusBadgeText = badge.textContent || '';
            }

            var macrosEl = dayEl.querySelector('.sff-calendar-day__macros');
            if (macrosEl) {
                payload.macrosHtml = macrosEl.innerHTML;
            }

            var bodyEl = dayEl.querySelector('.sff-calendar-day__body');
            if (bodyEl) {
                payload.bodyHtml = bodyEl.innerHTML;
            }

            return payload;
        }

        function applyDayPayload(dayEl, payload, preservedTitleText) {
            if (!dayEl || !payload) {
                return;
            }

            ['targetCalories', 'targetProtein', 'targetCarbs', 'targetFat'].forEach(function (field) {
                if (typeof payload.dataset[field] !== 'undefined') {
                    dayEl.dataset[field] = payload.dataset[field];
                }
            });

            var status = payload.status || 'neutral';
            dayEl.dataset.status = status;
            dayEl.classList.remove('sff-calendar-day--good', 'sff-calendar-day--warn', 'sff-calendar-day--danger', 'sff-calendar-day--neutral');
            dayEl.classList.add('sff-calendar-day--' + status);

            var typeEl = dayEl.querySelector('.sff-calendar-day__type');
            if (typeEl) {
                var typeText = payload.typeText || '';
                typeEl.textContent = typeText;
                if (typeText.trim().length) {
                    typeEl.classList.remove('is-hidden');
                } else {
                    typeEl.classList.add('is-hidden');
                }
            }

            var badge = dayEl.querySelector('.sff-calendar-day__status-badge');
            if (badge) {
                badge.dataset.status = status;
                if (payload.statusBadgeText) {
                    badge.textContent = payload.statusBadgeText;
                }
            }

            var macrosEl = dayEl.querySelector('.sff-calendar-day__macros');
            if (macrosEl) {
                macrosEl.innerHTML = payload.macrosHtml || '';
            }

            var bodyEl = dayEl.querySelector('.sff-calendar-day__body');
            if (bodyEl) {
                bodyEl.innerHTML = payload.bodyHtml || '';
            }

            var titleEl = dayEl.querySelector('.sff-calendar-day__title');
            if (titleEl && typeof preservedTitleText === 'string') {
                titleEl.textContent = preservedTitleText;
            }

            syncFormDayAttributes(dayEl);
        }

        function swapDayPayloads(dayA, dayB) {
            if (!dayA || !dayB || dayA === dayB) {
                return;
            }

            var titleAEl = dayA.querySelector('.sff-calendar-day__title');
            var titleBEl = dayB.querySelector('.sff-calendar-day__title');
            var titleAText = titleAEl ? titleAEl.textContent : '';
            var titleBText = titleBEl ? titleBEl.textContent : '';

            var payloadA = captureDayPayload(dayA);
            var payloadB = captureDayPayload(dayB);

            applyDayPayload(dayA, payloadB, titleAText);
            applyDayPayload(dayB, payloadA, titleBText);

            refreshAllDays();
        }

        function setupDayDragAndDrop() {
            var draggedDay = null;

            function clearDragState(day) {
                if (!day) {
                    return;
                }
                day.classList.remove('is-dragging');
                day.classList.remove('is-drop-target');
            }

            root.querySelectorAll('.sff-calendar-day').forEach(function (day) {
                var typeEl = day.querySelector('.sff-calendar-day__type');
                if (typeEl && !typeEl.textContent.trim().length) {
                    typeEl.classList.add('is-hidden');
                }

                day.addEventListener('dragstart', function (event) {
                    draggedDay = day;
                    day.classList.add('is-dragging');
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', day.dataset.day || '');
                    }
                });

                day.addEventListener('dragenter', function (event) {
                    if (!draggedDay || draggedDay === day) {
                        return;
                    }
                    event.preventDefault();
                    day.classList.add('is-drop-target');
                });

                day.addEventListener('dragover', function (event) {
                    if (!draggedDay || draggedDay === day) {
                        return;
                    }
                    event.preventDefault();
                    if (event.dataTransfer) {
                        event.dataTransfer.dropEffect = 'move';
                    }
                });

                day.addEventListener('dragleave', function () {
                    day.classList.remove('is-drop-target');
                });

                day.addEventListener('drop', function (event) {
                    if (!draggedDay || draggedDay === day) {
                        return;
                    }
                    event.preventDefault();
                    day.classList.remove('is-drop-target');
                    root.querySelectorAll('.sff-calendar-day.is-drop-target').forEach(function (targetDay) {
                        targetDay.classList.remove('is-drop-target');
                    });
                    swapDayPayloads(draggedDay, day);
                });

                day.addEventListener('dragend', function () {
                    clearDragState(day);
                    if (draggedDay && draggedDay !== day) {
                        clearDragState(draggedDay);
                    }
                    root.querySelectorAll('.sff-calendar-day.is-drop-target').forEach(function (targetDay) {
                        targetDay.classList.remove('is-drop-target');
                    });
                    draggedDay = null;
                });
            });
        }

        function updateRecipeMacros(recipeEl, macros) {
            if (!recipeEl || !macros) {
                return;
            }

            ['calories', 'protein', 'carbs', 'fat'].forEach(function (metric) {
                if (typeof macros[metric] === 'undefined') {
                    return;
                }
                var precision = metric === 'calories' ? 0 : 1;
                recipeEl.dataset[metric] = macros[metric];
                var field = recipeEl.querySelector('[data-metric="' + metric + '"]');
                if (field) {
                    field.textContent = formatNumber(macros[metric], precision, locale);
                }
            });
        }

        function updateBadgeState(recipeEl, hasSwaps) {
            var badge = recipeEl.querySelector('.sff-preview-meal__badge');
            if (hasSwaps) {
                if (!badge) {
                    var header = recipeEl.querySelector('.sff-preview-meal__titles');
                    if (header) {
                        badge = document.createElement('span');
                        badge.className = 'sff-preview-meal__badge';
                        badge.textContent = sffMealPlanPreview.i18n ? (sffMealPlanPreview.i18n.customizedLabel || 'Customized') : 'Customized';
                        header.appendChild(badge);
                    }
                }
            } else if (badge) {
                badge.remove();
            }
        }

        function updateSelectValues(form, swaps) {
            if (!form || !swaps) {
                return;
            }
            var selects = form.querySelectorAll('select[name^="swaps["]');
            selects.forEach(function (select) {
                var originalId = select.name.match(/swaps\[(\d+)\]/);
                var key = originalId ? originalId[1] : null;
                if (key && typeof swaps[key] !== 'undefined') {
                    select.value = String(swaps[key]);
                } else {
                    select.value = '';
                }
            });
        }

        function replaceIngredients(recipeEl, html) {
            var container = recipeEl.querySelector('.sff-preview-meal__ingredients');
            if (container) {
                container.innerHTML = html;
            }
        }

        function setResetState(form, hasSwaps) {
            var resetBtn = form.querySelector('.sff-preview-reset');
            if (!resetBtn) {
                return;
            }
            if (hasSwaps) {
                resetBtn.removeAttribute('disabled');
                resetBtn.removeAttribute('aria-disabled');
            } else {
                resetBtn.setAttribute('disabled', 'disabled');
                resetBtn.setAttribute('aria-disabled', 'true');
            }
        }

        function showFeedback(feedbackEl, message, isError) {
            if (!feedbackEl) {
                return;
            }
            feedbackEl.classList.remove('is-success', 'is-error');
            if (message) {
                feedbackEl.textContent = message;
                feedbackEl.classList.add(isError ? 'is-error' : 'is-success');
            } else {
                feedbackEl.textContent = '';
            }
        }

        function handleSwapResponse(form, recipeEl, payload) {
            if (!payload) {
                return;
            }

            if (payload.ingredients_html) {
                replaceIngredients(recipeEl, payload.ingredients_html);
            }

            if (payload.macros) {
                updateRecipeMacros(recipeEl, payload.macros);
            }

            if (payload.swaps) {
                updateSelectValues(form, payload.swaps);
            }

            var hasSwaps = !!payload.has_swaps;
            setResetState(form, hasSwaps);
            updateBadgeState(recipeEl, hasSwaps);

            refreshAllDays();
        }

        root.addEventListener('submit', function (event) {
            var form = event.target.closest('.sff-preview-swap-form');
            if (!form) {
                return;
            }

            event.preventDefault();

            var recipeEl = form.closest('.sff-preview-meal');
            var feedback = form.querySelector('.sff-preview-feedback');
            var submitBtn = form.querySelector('button[type="submit"]');

            showFeedback(feedback, sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.saving : 'Saving…', false);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.originalText = submitBtn.dataset.originalText || submitBtn.textContent;
                submitBtn.textContent = sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.savingShort : 'Saving…';
            }

            var formData = new FormData(form);
            formData.append('action', 'sff_update_recipe_swaps');
            formData.append('nonce', sffMealPlanPreview.nonce);

            fetch(sffMealPlanPreview.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }).then(function (response) {
                return response.json();
            }).then(function (json) {
                if (!json || !json.success) {
                    throw new Error('Invalid response');
                }
                handleSwapResponse(form, recipeEl, json.data);
                showFeedback(feedback, sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.saved : 'Saved', false);
            }).catch(function () {
                showFeedback(feedback, sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.error : 'Unable to save changes.', true);
            }).finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || submitBtn.textContent;
                }
            });
        });

        root.addEventListener('click', function (event) {
            var resetBtn = event.target.closest('.sff-preview-reset');
            if (!resetBtn) {
                return;
            }
            if (resetBtn.hasAttribute('disabled')) {
                return;
            }

            event.preventDefault();

            var form = resetBtn.closest('.sff-preview-swap-form');
            if (!form) {
                return;
            }

            var recipeEl = form.closest('.sff-preview-meal');
            var feedback = form.querySelector('.sff-preview-feedback');

            showFeedback(feedback, sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.saving : 'Saving…', false);
            resetBtn.disabled = true;
            resetBtn.setAttribute('aria-disabled', 'true');

            var formData = new FormData();
            formData.append('action', 'sff_update_recipe_swaps');
            formData.append('nonce', sffMealPlanPreview.nonce);
            formData.append('recipe_id', resetBtn.dataset.recipeId || '');
            formData.append('reset', '1');

            fetch(sffMealPlanPreview.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }).then(function (response) {
                return response.json();
            }).then(function (json) {
                if (!json || !json.success) {
                    throw new Error('Invalid response');
                }
                handleSwapResponse(form, recipeEl, json.data);
                showFeedback(feedback, sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.reset : 'Reset to original', false);
            }).catch(function () {
                showFeedback(feedback, sffMealPlanPreview.i18n ? sffMealPlanPreview.i18n.error : 'Unable to save changes.', true);
                resetBtn.disabled = false;
                resetBtn.removeAttribute('aria-disabled');
            });
        });

        setupDayDragAndDrop();
        refreshAllDays();
    });
})();
