import { memo } from 'react';

function ListeningHtmlContent({ className, html }) {
  if (!html) return null;

  return (
    <div
      className={className}
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
}

export default memo(ListeningHtmlContent);
