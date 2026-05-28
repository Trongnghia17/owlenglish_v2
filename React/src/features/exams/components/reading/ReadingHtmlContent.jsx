import { memo, useMemo } from 'react';
import { normalizeHtmlMediaSources } from '../../utils/readingTest';

function ReadingHtmlContent({ className, html }) {
  const normalizedHtml = useMemo(() => normalizeHtmlMediaSources(html), [html]);

  if (!normalizedHtml) return null;

  return (
    <div
      className={className}
      dangerouslySetInnerHTML={{ __html: normalizedHtml }}
    />
  );
}

export default memo(ReadingHtmlContent);
