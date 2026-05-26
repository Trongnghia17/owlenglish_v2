import { memo, useMemo } from 'react';
import { normalizeHtmlMediaSources } from '../../utils/listeningTest';

function ListeningHtmlContent({ className, html }) {
  const normalizedHtml = useMemo(() => normalizeHtmlMediaSources(html), [html]);

  if (!normalizedHtml) return null;

  return (
    <div
      className={className}
      dangerouslySetInnerHTML={{ __html: normalizedHtml }}
    />
  );
}

export default memo(ListeningHtmlContent);
