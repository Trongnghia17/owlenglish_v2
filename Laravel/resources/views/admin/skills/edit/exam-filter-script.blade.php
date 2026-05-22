        <script>
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('exam-filter-input')) return;

    const input = e.target;
    const skill = input.dataset.skill;
    const groupId = input.dataset.group;

    const allInputs = document.querySelectorAll(
        `.exam-filter-input[data-skill="${skill}"]`
    );

    if (skill === 'writing' || skill === 'speaking') {
        // ❌ chỉ được chọn 1
        allInputs.forEach(i => {
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
