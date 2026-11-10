// Minimal allowlist sanitizer for seller-authored product descriptions.
// Keeps basic formatting tags, drops every attribute (styles, handlers, data-*)
// and removes anything outside the allowlist while keeping its text.
const ALLOWED = new Set([
    'P', 'BR', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'SMALL', 'SUB', 'SUP',
    'UL', 'OL', 'LI', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'HR',
    'TABLE', 'THEAD', 'TBODY', 'TR', 'TH', 'TD', 'SPAN', 'DIV',
]);
const DROP_WITH_CONTENT = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'TEMPLATE', 'NOSCRIPT', 'SVG', 'MATH', 'FORM', 'INPUT', 'BUTTON', 'TEXTAREA', 'SELECT']);

const escapeText = (value) => value.replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));

const clean = (node, doc) => {
    [...node.childNodes].forEach((child) => {
        if (child.nodeType === Node.TEXT_NODE) return;
        if (child.nodeType !== Node.ELEMENT_NODE) {
            child.remove();
            return;
        }
        if (DROP_WITH_CONTENT.has(child.tagName)) {
            child.remove();
            return;
        }
        clean(child, doc);
        if (!ALLOWED.has(child.tagName)) {
            child.replaceWith(...child.childNodes);
            return;
        }
        [...child.attributes].forEach((attribute) => {
            if (!(['TD', 'TH'].includes(child.tagName) && ['colspan', 'rowspan'].includes(attribute.name) && /^\d{1,2}$/.test(attribute.value))) {
                child.removeAttribute(attribute.name);
            }
        });
    });
};

export const sanitizeHtml = (value) => {
    const source = String(value ?? '').trim();
    if (!source) return '';
    // Plain text: keep line breaks.
    if (!/<[a-z][\s\S]*>/i.test(source)) return escapeText(source).replace(/\n/g, '<br>');
    const doc = new DOMParser().parseFromString(`<body>${source}</body>`, 'text/html');
    clean(doc.body, doc);
    // Remove empty paragraphs/list items left behind by editors.
    doc.body.querySelectorAll('p, li, div, span').forEach((element) => {
        if (!element.textContent.trim() && !element.querySelector('br, hr')) element.remove();
    });
    return doc.body.innerHTML;
};
