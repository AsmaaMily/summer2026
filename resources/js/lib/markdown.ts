import DOMPurify from 'dompurify';
import { marked } from 'marked';

export function renderMarkdown(source: string): string {
    const html = marked.parse(source, {
        async: false,
        gfm: true,
        breaks: true,
    });

    if (typeof window === 'undefined') {
        return html;
    }

    return DOMPurify.sanitize(html);
}