import { Marked } from 'marked';
import DOMPurify from 'dompurify';

/**
 * A single configured Marked instance reused by every render call.
 *
 * - gfm: tables, task lists, strikethrough.
 * - breaks: single newlines render as <br> (chat-friendly).
 * - async: never used — rendering during streaming must stay synchronous.
 */
const marked = new Marked({
    gfm: true,
    breaks: true,
    async: false,
});

// Demote any h1/h2 heading to h3: chat answers must not use huge headings.
marked.use({
    walkTokens(token) {
        if (token.type === 'heading' && token.depth < 3) {
            token.depth = 3;
        }
    },
});

// External links always open safely in a new tab.
DOMPurify.addHook('afterSanitizeAttributes', (node) => {
    if (node.tagName === 'A') {
        node.setAttribute('target', '_blank');
        node.setAttribute('rel', 'noopener noreferrer');
    }
});

/**
 * Temporarily close markdown constructs that are still open while the
 * assistant response streams in, so the live preview never shows broken
 * raw symbols or broken layout. The next chunk re-renders everything.
 */
export function closeIncompleteMarkdown(raw: string): string {
    let text = raw;

    const fenceCount = (text.match(/^```.*$/gm) ?? []).length;
    if (fenceCount % 2 === 1) {
        text += '\n```';
    }

    const boldCount = text.split('**').length - 1;
    if (boldCount % 2 === 1) {
        text += '**';
    }

    return text;
}

/**
 * Parse accumulated markdown into HTML, then sanitize before it touches
 * the DOM. Model output can echo user-written content (e.g. transaction
 * notes), so sanitizing is mandatory on every render.
 */
export function renderMarkdown(raw: string): string {
    if (!raw || raw.trim() === '') {
        return '';
    }

    const html = marked.parse(closeIncompleteMarkdown(raw)) as string;

    return DOMPurify.sanitize(html);
}

/**
 * Whether a text is predominantly Arabic (used to pick bubble direction).
 */
export function isRtlText(text: string): boolean {
    const arabic = (text.match(/[\u0600-\u06FF]/g) ?? []).length;
    const latin = (text.match(/[A-Za-z]/g) ?? []).length;

    return arabic >= latin;
}
