import { memo } from 'react';
import ReadingHtmlContent from './ReadingHtmlContent';

function ReadingPassagePanel({ passage, groups }) {
  const passageHtml = passage || groups?.[0]?.passage || '';
  const imageUrl = groups?.[0]?.imageUrl;

  return (
    <div className="reading-test__passage-panel">
      {imageUrl && (
        <div className="reading-test__passage-image-wrapper">
          <img
            src={imageUrl}
            alt="Passage illustration"
            className="reading-test__passage-image"
          />
        </div>
      )}
      {passageHtml && (
        <ReadingHtmlContent
          className="reading-test__passage-content"
          html={passageHtml}
        />
      )}
    </div>
  );
}

export default memo(ReadingPassagePanel);
