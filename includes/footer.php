</div>
    </main>
</div>

<script>
(function () {
    const path = window.location.pathname;
    const params = new URLSearchParams(window.location.search);
    const isCourseStep1 = path.includes('/faculty/course_edit.php') && (params.get('step') === '1' || !params.get('step'));
    if (!isCourseStep1) return;

    const programs = [
        {id:'2', code:'ACC', name:'Accounting', college:'College of Business', department:'Accounting & Finance Department'},
        {id:'3', code:'FIN', name:'Finance', college:'College of Business', department:'Accounting & Finance Department'},
        {id:'4', code:'MGT', name:'Management', college:'College of Business', department:'Management and Marketing Department'},
        {id:'5', code:'MKT', name:'Marketing', college:'College of Business', department:'Management and Marketing Department'},
        {id:'6', code:'MIS', name:'Management Information Systems', college:'College of Business', department:'Management Information Systems'},
        {id:'7', code:'AIA', name:'Architecture', college:'College of Engineering', department:'Architecture Department'},
        {id:'8', code:'CNE', name:'Computer Network Engineering', college:'College of Engineering', department:'Computer Engineering Department'},
        {id:'1', code:'SWE', name:'Software Engineering', college:'College of Engineering', department:'Computer Engineering Department'},
        {id:'9', code:'IE', name:'Industrial Engineering', college:'College of Engineering', department:'Industrial Engineering Department'},
        {id:'10', code:'LL.B', name:'Bachelor of Law', college:'College of Law', department:'Law Department'},
        {id:'11', code:'MCS', name:'Master in Cyber Security', college:'College of Engineering', department:'Computer Engineering Department'},
        {id:'12', code:'MBA', name:'Masters of Business Administration (MBA)', college:'College of Business', department:'Management and Marketing Department'},
        {id:'13', code:'EMBA', name:'Executive Masters of Business Administration (EMBA)', college:'College of Business', department:'Management and Marketing Department'},
        {id:'14', code:'MBL', name:'Masters of Business Law', college:'College of Law', department:'Law Department'}
    ];

    function fieldByLabel(labelText) {
        const labels = Array.from(document.querySelectorAll('label'));
        const label = labels.find(l => l.textContent.trim() === labelText);
        return label ? label.closest('.form-group') : null;
    }

    const programGroup = fieldByLabel('Program');
    const departmentGroup = fieldByLabel('Department');
    const collegeGroup = fieldByLabel('College');
    const institutionGroup = fieldByLabel('Institution');
    if (!programGroup || !departmentGroup || !collegeGroup || !institutionGroup) return;

    const oldProgram = programGroup.querySelector('input, select');
    const currentId = oldProgram ? String(oldProgram.value || '1') : '1';

    const select = document.createElement('select');
    select.id = 'official_program_picker';
    select.name = 'program_display';
    programs.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.code + ' - ' + p.name;
        opt.dataset.college = p.college;
        opt.dataset.department = p.department;
        if (p.id === currentId) opt.selected = true;
        select.appendChild(opt);
    });

    programGroup.innerHTML = '<label>Program</label>';
    programGroup.appendChild(select);

    function setInput(group, name, value, readOnly) {
        group.innerHTML = '<label>' + name + '</label>';
        const input = document.createElement('input');
        input.type = 'text';
        input.name = name.toLowerCase();
        input.value = value || '';
        input.readOnly = !!readOnly;
        group.appendChild(input);
        return input;
    }

    const selected = select.options[select.selectedIndex];
    const collegeInput = setInput(collegeGroup, 'College', selected.dataset.college, true);
    const departmentInput = setInput(departmentGroup, 'Department', selected.dataset.department, true);
    setInput(institutionGroup, 'Institution', 'Al Yamamah University', true);

    select.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        collegeInput.value = opt.dataset.college || '';
        departmentInput.value = opt.dataset.department || '';
    });

    const objectives = fieldByLabel('Course Main Objective(s)');
    const form = document.querySelector('form');
    if (!objectives || !form) return;

    const urlCourseId = params.get('id') || '';
    const block = document.createElement('div');
    block.innerHTML = `
        <h3 style="margin-top:22px; color:#2c4f9e;">2. Teaching mode</h3>
        <table>
            <thead>
                <tr><th>No</th><th>Mode of Instruction</th><th>Contact Hours</th><th>Percentage</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><label style="font-weight:600;"><input type="checkbox" name="mode[0][selected]" value="1"> Traditional classroom</label><input type="hidden" name="mode[0][mode_type]" value="Traditional classroom"></td>
                    <td><input type="number" name="mode[0][contact_hours]" step="0.5"></td>
                    <td><input type="number" name="mode[0][percentage]" step="0.5"></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><label style="font-weight:600;"><input type="checkbox" name="mode[1][selected]" value="1"> E-learning</label><input type="hidden" name="mode[1][mode_type]" value="E-learning"></td>
                    <td><input type="number" name="mode[1][contact_hours]" step="0.5"></td>
                    <td><input type="number" name="mode[1][percentage]" step="0.5"></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><label style="font-weight:600;"><input type="checkbox" name="mode[2][selected]" value="1"> Hybrid</label><br><small style="margin-left:22px;">Traditional classroom + E-learning</small><input type="hidden" name="mode[2][mode_type]" value="Hybrid"></td>
                    <td><input type="number" name="mode[2][contact_hours]" step="0.5"></td>
                    <td><input type="number" name="mode[2][percentage]" step="0.5"></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td><label style="font-weight:600;"><input type="checkbox" name="mode[3][selected]" value="1"> Distance learning</label><input type="hidden" name="mode[3][mode_type]" value="Distance learning"></td>
                    <td><input type="number" name="mode[3][contact_hours]" step="0.5"></td>
                    <td><input type="number" name="mode[3][percentage]" step="0.5"></td>
                </tr>
            </tbody>
        </table>

        <h3 style="margin-top:22px; color:#2c4f9e;">3. Contact Hours</h3>
        <table>
            <thead>
                <tr><th>No</th><th>Activity</th><th>Contact Hours</th></tr>
            </thead>
            <tbody>
                ${['Lectures','Laboratory/Studio','Field','Tutorial','Others'].map((activity, i) => `
                <tr>
                    <td>${i + 1}.</td>
                    <td><strong>${activity}${activity === 'Others' ? ' (specify)' : ''}</strong><input type="hidden" name="hours[${i}][activity_type]" value="${activity}"></td>
                    <td><input type="number" name="hours[${i}][hours]" step="0.5"></td>
                </tr>`).join('')}
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td><input type="number" id="contact_total_preview" readonly></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="course_id_for_extra" value="${urlCourseId}">
    `;

    objectives.insertAdjacentElement('afterend', block);

    const hourInputs = Array.from(block.querySelectorAll('input[name^="hours"][name$="[hours]"]'));
    const total = block.querySelector('#contact_total_preview');
    function updateTotal() {
        total.value = hourInputs.reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
    }
    hourInputs.forEach(input => input.addEventListener('input', updateTotal));

    form.addEventListener('submit', function () {
        const data = new FormData();
        data.append('course_id', urlCourseId);
        Array.from(block.querySelectorAll('input')).forEach(input => {
            if ((input.type === 'checkbox' && !input.checked) || !input.name) return;
            data.append(input.name, input.value);
        });
        navigator.sendBeacon('course_teaching_contact_save.php', data);
    });
})();
</script>

</body>
</html>