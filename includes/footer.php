</div>
    </main>
</div>

<script>
(function () {
    const path = window.location.pathname;
    if (!path.includes('/faculty/course_edit.php')) return;

    const params = new URLSearchParams(window.location.search);
    const step = params.get('step') || '1';

    if (step === '2') {
        const table = document.getElementById('clo-table');
        if (table) {
            table.style.width = '100%';
            table.style.minWidth = '980px';
            table.querySelectorAll('th, td').forEach(cell => {
                cell.style.verticalAlign = 'top';
            });
        }
    }

    if (step === '3') {
        const table = document.getElementById('topic-table');
        if (!table) return;

        function renumberTopics() {
            table.querySelectorAll('tbody tr').forEach((row, index) => {
                const first = row.querySelector('td');
                if (first) first.textContent = (index + 1) + '.';
            });
        }

        window.addTopicRow = function () {
            const tbody = table.querySelector('tbody');
            const i = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.innerHTML = '<td>' + (i + 1) + '.</td>' +
                '<td><input type="text" name="topic[' + i + '][topic_text]"></td>' +
                '<td><input type="number" name="topic[' + i + '][contact_hours]" step="0.5"></td>' +
                '<td><button type="button" class="icon-btn topic-remove">✕</button></td>';
            tbody.appendChild(tr);
        };

        table.addEventListener('click', function (event) {
            const btn = event.target.closest('.icon-btn, .topic-remove');
            if (!btn) return;
            setTimeout(renumberTopics, 0);
        });
        renumberTopics();
    }

    if (step === '4') {
        const table = document.getElementById('assess-table');
        if (!table) return;

        const options = ['Written test', 'Oral test', 'Oral presentation', 'Group project', 'Essay', 'Other'];

        function buildActivityControl(input) {
            if (!input || input.dataset.done === '1') return;
            input.dataset.done = '1';
            const oldValue = input.value || '';
            const wrapper = document.createElement('div');
            const select = document.createElement('select');
            const hidden = document.createElement('input');
            const other = document.createElement('input');

            hidden.type = 'hidden';
            hidden.name = input.name;
            other.type = 'text';
            other.placeholder = 'Specify other assessment activity';
            other.style.display = 'none';
            other.style.marginTop = '6px';

            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = '-- Select --';
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
                other.style.display = 'block';
                hidden.value = oldValue;
            }

            function sync() {
                if (select.value === 'Other') {
                    other.style.display = 'block';
                    hidden.value = other.value.trim();
                } else {
                    other.style.display = 'none';
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

        function applyAssessmentControls() {
            table.querySelectorAll('input[name*="[activity_name]"]').forEach(buildActivityControl);
        }

        const oldAddAssessRow = window.addAssessRow;
        window.addAssessRow = function () {
            if (typeof oldAddAssessRow === 'function') oldAddAssessRow();
            applyAssessmentControls();
        };

        applyAssessmentControls();
    }
})();
</script>

</body>
</html>