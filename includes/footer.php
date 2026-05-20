</div>
    </main>
</div>

<script>
(function () {
    const toggle = document.getElementById('sidebarToggle');
    if (toggle) {
        if (localStorage.getItem('aqmsSidebar') === 'closed') document.body.classList.add('sidebar-collapsed');
        toggle.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('aqmsSidebar', document.body.classList.contains('sidebar-collapsed') ? 'closed' : 'open');
        });
    }

    const path = window.location.pathname;
    if (!path.includes('/faculty/course_edit.php')) return;

    const params = new URLSearchParams(window.location.search);
    const step = params.get('step') || '1';

    if (step === '2') {
        const table = document.getElementById('clo-table');
        if (!table) return;

        table.classList.add('official-table');
        const header = table.querySelector('thead tr');
        if (header) {
            const heads = Array.from(header.children);
            heads.forEach((th, index) => {
                if (index > 5) th.remove();
            });
        }

        table.querySelectorAll('tbody tr').forEach((row, rowIndex) => {
            const cells = Array.from(row.children);
            cells.forEach((td, index) => {
                if (index > 5) td.remove();
            });

            const codeCell = row.children[0];
            const outcomeCell = row.children[1];
            const ploCell = row.children[2];
            const jahiziahCell = row.children[5];

            if (codeCell) codeCell.classList.add('small-col');
            if (outcomeCell) outcomeCell.classList.add('wide-col');
            if (ploCell) ploCell.classList.add('medium-col');

            if (ploCell && !ploCell.classList.contains('fixed-plo-cell')) {
                ploCell.classList.add('fixed-plo-cell');
                const labels = Array.from(ploCell.querySelectorAll('label'));
                if (labels.length) {
                    const grid = document.createElement('div');
                    grid.className = 'checkbox-grid';
                    labels.forEach(label => {
                        label.classList.add('checkbox-pill');
                        grid.appendChild(label);
                    });
                    ploCell.innerHTML = '';
                    ploCell.appendChild(grid);
                }
            }

            if (jahiziahCell && !jahiziahCell.classList.contains('fixed-skills-cell')) {
                jahiziahCell.classList.add('fixed-skills-cell');
                const labels = Array.from(jahiziahCell.querySelectorAll('label'));
                if (labels.length) {
                    const grid = document.createElement('div');
                    grid.className = 'checkbox-grid';
                    labels.forEach(label => {
                        label.classList.add('checkbox-pill');
                        grid.appendChild(label);
                    });
                    jahiziahCell.innerHTML = '';
                    jahiziahCell.appendChild(grid);
                }
            }
        });

        const oldAddCloRow = window.addCloRow;
        window.addCloRow = function () {
            if (typeof oldAddCloRow === 'function') oldAddCloRow();
            setTimeout(() => window.location.reload(), 50);
        };
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

        table.classList.add('official-table');
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
            other.className = 'other-box';

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