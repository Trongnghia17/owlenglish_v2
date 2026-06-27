        <script>
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('exam-filter-input')) return;

    const input = e.target;
    const skill = input.dataset.skill;
    const groupId = input.dataset.group;
    const section = input.closest('.section-item') || document;

    const allInputs = section.querySelectorAll(
        `.exam-filter-input[data-skill="${skill}"]`
    );

    if (skill === 'writing' || skill === 'speaking') {
        const groupType = input.closest('.filter-group')?.dataset.groupType || '';
        const scopedInputs = groupType.startsWith('theo-dang')
            ? section.querySelectorAll('.filter-group[data-group-type^="theo-dang"] .exam-filter-input')
            : section.querySelectorAll(`.exam-filter-input[data-skill="${skill}"][data-group="${groupId}"]`);

        scopedInputs.forEach(i => {
            if (i !== input) i.checked = false;
        });
        return;
    }

    // reading / listening
    // ✅ mỗi group chỉ được chọn 1
    allInputs.forEach(i => {
        if (
            i !== input &&
            i.dataset.group === groupId
        ) {
            i.checked = false;
        }
    });
});
</script>
