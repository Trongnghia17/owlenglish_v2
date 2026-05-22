import { memo } from 'react';
import ListeningAudioPlayer from './ListeningAudioPlayer';
import ListeningHtmlContent from './ListeningHtmlContent';
import { containsInlinePlaceholders } from '../../utils/listeningTest';

function ListeningContentPanel({ groups, currentPartTab, audioUrl }) {
  return (
    <div className="listening-test__left-column">
      {groups.map((group, index) => (
        <div key={group.id} className="listening-test__content-section">
          <h2>Listening Part {currentPartTab}</h2>
          {index === 0 && <ListeningAudioPlayer audioUrl={audioUrl} />}
          {group.groupContent && !containsInlinePlaceholders(group.groupContent) && (
            <ListeningHtmlContent
              className="listening-test__group-content"
              html={group.groupContent}
            />
          )}
        </div>
      ))}
    </div>
  );
}

export default memo(ListeningContentPanel);
