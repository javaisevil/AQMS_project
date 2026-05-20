(function () {
    const path = window.location.pathname;
    if (!path.includes('/faculty/course_edit.php')) return;

    const params = new URLSearchParams(window.location.search);
    const step = params.get('step') || '1';

    function makeSelectFromInput(input, options, placeholder, otherPlaceholder) {
        if (!input || input.dataset.selectFixed === '1') return;
        input.dataset.selectFixed = '1';

        const oldValue = input.value || '';
        const wrapper = document.createElement('div');
        const select = document.createElement('select');
        const hidden = document.createElement('input');
        const other = document.createElement('input');

        hidden.type = 'hidden';
        hidden.name = input.name;
        other.type = 'text';
        other.placeholder = otherPlaceholder || 'Specify other';
        other.className = 'other-box';

        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = placeholder || '-- Select --';
        select.appendChild(empty);

        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.textContent = opt;
            select.appendChild(option);
        });

        if (options.includes(oldValue)) {
            select.value = oldValue;
            hidden.value = oldValue;
        } else if (oldValue.trim() !== '') {
            select.value = 'Other';
            other.value = oldValue;
            other.classList.add('show');
            hidden.value = oldValue;
        }

        function sync() {
            if (select.value === 'Other') {
                other.classList.add('show');
                hidden.value = other.value.trim();
            } else {
                other.classList.remove('show');
                hidden.value = select.value;
            }
        }

        select.addEventListener('change', sync);
        other.addEventListener('input', sync);
        wrapper.appendChild(select);
        wrapper.appendChild(other);
        wrapper.appendChild(hidden);
        input.replaceWith(wrapper);
    }

    function fixNumberedTable(table, inputSelector, totalClass, totalSuffix) {
        if (!table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        function updateTotal() {
            let total = 0;
            table.querySelectorAll(inputSelector).forEach(input => total += parseFloat(input.value) || 0);
            let totalRow = table.querySelector('.' + totalClass + '-row');
            if (!totalRow) {
                const columnCount = table.querySelectorAll('thead th').length || 3;
                totalRow = document.createElement('tr');
                totalRow.className = totalClass + '-row';
                totalRow.innerHTML = '<td colspan="' + Math.max(columnCount - 2, 1) + '"><strong>Total</strong></td><td><strong class="' + totalClass + '-value">0</strong></td><td></td>';
                tbody.appendChild(totalRow);
            }
            totalRow.querySelector('.' + totalClass + '-value').textContent = total + (totalSuffix || '');
        }

        function renumber() {
            Array.from(tbody.querySelectorAll('tr:not(.' + totalClass + '-row)')).forEach((row, index) => {
                const first = row.querySelector('td');
                if (first) first.textContent = (index + 1) + '.';
            });
            updateTotal();
        }

        table.addEventListener('input', updateTotal);
        table.addEventListener('click', function () { setTimeout(renumber, 0); });
        renumber();
    }

    if (step === '1') {
        const hourInputs = Array.from(document.querySelectorAll('input[name^="hours"][name$="[hours]"]'));
        if (hourInputs.length) {
            hourInputs.forEach(input => input.addEventListener('input', function () {
                let total = 0;
                hourInputs.forEach(x => total += parseFloat(x.value) || 0);
                const totalCell = Array.from(document.querySelectorAll('td strong')).find(x => x.textContent.trim() === 'Total');
                if (totalCell && totalCell.closest('tr')) {
                    const last = totalCell.closest('tr').querySelector('td:last-child strong');
                    if (last) last.textContent = total;
                }
            }));
        }
    }

    if (step === '3') {
        const table = document.getElementById('topic-table');
        fixNumberedTable(table, 'input[name*="[contact_hours]"]', 'topic-total', '');
    }

    if (step === '4') {
        const table = document.getElementById('assess-table');
        if (!table) return;

        const options = ['Written test', 'Oral test', 'Oral presentation', 'Group project', 'Essay', 'Other'];
        table.querySelectorAll('input[name*="[activity_name]"]').forEach(input => {
            makeSelectFromInput(input, options, '-- Select assessment activity --', 'Specify other assessment activity');
        });
        fixNumberedTable(table, 'input[name*="[percentage]"]', 'assessment-total', '%');
    }

    if (step === '5') {
        document.querySelectorAll('input[name*="[resource_text]"]').forEach(input => {
            input.placeholder = 'Enter reference or learning material';
        });
        document.querySelectorAll('input[name*="[resources]"]').forEach(input => {
            input.placeholder = 'Enter required resources';
        });
    }

    if (step === '6') {
        const assessorOptions = ['Students', 'Faculty', 'Program Leaders', 'Peer Reviewers', 'Other'];
        const methodOptions = ['Direct', 'Indirect'];

        document.querySelectorAll('input[name*="[assessor]"]').forEach(input => {
            makeSelectFromInput(input, assessorOptions, '-- Select assessor --', 'Specify other assessor');
        });
        document.querySelectorAll('input[name*="[assessment_method]"]').forEach(input => {
            makeSelectFromInput(input, methodOptions, '-- Select method --', '');
        });
    }
})();
