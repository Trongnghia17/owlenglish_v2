const WritingTaskHeader = ({ group }) => (
  <div className="writing-test__task-header">
    <h1 className="writing-test__task-title">{group.taskLabel}</h1>
  </div>
);

export default WritingTaskHeader;
