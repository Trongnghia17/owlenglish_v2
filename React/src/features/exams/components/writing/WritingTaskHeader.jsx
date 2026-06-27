const WritingTaskHeader = ({ group }) => (
  <div className="writing-test__task-header">
    <h1 className="writing-test__task-title">{group.taskLabel}</h1>
    {group.title && group.title !== group.taskLabel && (
      <p className="writing-test__task-subtitle">{group.title}</p>
    )}
  </div>
);

export default WritingTaskHeader;
