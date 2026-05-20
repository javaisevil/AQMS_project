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
})();
</script>

</body>
</html>