import tinymce from 'tinymce/tinymce';
import 'tinymce/models/dom';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/wordcount';
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/ui/oxide/content.css';
import 'tinymce/skins/content/default/content.css';

const dashPattern = /[ \t]*[-‐‑‒–—―]+[ \t]*/gu;
const longParagraphLength = 900;
const maxSentencesPerParagraph = 4;

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function textToParagraphs(value) {
  return String(value || '')
    .replace(/\r\n?/g, '\n')
    .replace(/\u00a0/g, ' ')
    .replace(dashPattern, ' ')
    .split(/\n[ \t]*\n+/)
    .map((paragraph) => paragraph
      .split('\n')
      .map((line) => line.trim())
      .filter(Boolean)
      .join(' ')
      .replace(/[ \t]+/g, ' ')
      .trim())
    .filter(Boolean)
    .map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`)
    .join('');
}

function splitLongTextIntoParagraphs(text) {
  const normalized = String(text || '').replace(/[ \t]+/g, ' ').trim();

  if (normalized.length <= longParagraphLength) {
    return normalized ? [normalized] : [];
  }

  const sentences = normalized.match(/[^.!?]+[.!?]+["')\]]*|[^.!?]+$/g)
    ?.map((sentence) => sentence.trim())
    .filter(Boolean) || [normalized];

  const paragraphs = [];
  let current = [];
  let currentLength = 0;

  sentences.forEach((sentence) => {
    const shouldStartNewParagraph = current.length >= maxSentencesPerParagraph
      || (currentLength > 450 && current.length > 1);

    if (shouldStartNewParagraph) {
      paragraphs.push(current.join(' '));
      current = [];
      currentLength = 0;
    }

    current.push(sentence);
    currentLength += sentence.length;
  });

  if (current.length) {
    paragraphs.push(current.join(' '));
  }

  return paragraphs;
}

function paragraphHtmlFromText(text) {
  return splitLongTextIntoParagraphs(text)
    .map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`)
    .join('');
}

function textToHtmlKeepingLineBreaks(value) {
  return String(value || '')
    .replace(/\r\n?/g, '\n')
    .replace(/\u00a0/g, ' ')
    .replace(dashPattern, ' ')
    .split(/\n[ \t]*\n+/)
    .map((paragraph) => {
      const lines = paragraph
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);

      if (!lines.length) return '';

      if (lines.length === 1) {
        return paragraphHtmlFromText(lines[0]);
      }

      return `<p>${lines.map(escapeHtml).join('<br>')}</p>`;
    })
    .filter(Boolean)
    .join('');
}

function normalizeInitialContent(content) {
  if (/<[a-z][\s\S]*>/i.test(content)) {
    return splitLongSingleParagraphs(cleanDashInHtml(content));
  }

  return textToHtmlKeepingLineBreaks(content);
}

function splitLongSingleParagraphs(html) {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = html;

  wrapper.querySelectorAll('p').forEach((paragraph) => {
    const hasFormatting = paragraph.children.length > 0;
    const text = paragraph.textContent?.trim() || '';

    if (hasFormatting || text.length <= longParagraphLength) {
      return;
    }

    const template = document.createElement('template');
    template.innerHTML = paragraphHtmlFromText(text);
    paragraph.replaceWith(...template.content.childNodes);
  });

  return wrapper.innerHTML;
}

function cleanDashInHtml(html) {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = html;

  const walker = document.createTreeWalker(wrapper, NodeFilter.SHOW_TEXT);
  const textNodes = [];

  while (walker.nextNode()) {
    textNodes.push(walker.currentNode);
  }

  textNodes.forEach((node) => {
    node.nodeValue = node.nodeValue.replace(dashPattern, ' ');
  });

  return wrapper.innerHTML;
}

function normalizePastedContent(content) {
  if (!/<[a-z][\s\S]*>/i.test(content)) {
    return textToParagraphs(content);
  }

  return cleanDashInHtml(content);
}

window.tinymce = tinymce;

window.ChapterEditor = {
  init(textarea) {
    if (!textarea || textarea.dataset.editorReady === '1') return;

    if (!textarea.id) {
      textarea.id = `chapter_content_${Date.now()}_${Math.random().toString(36).slice(2)}`;
    }

    textarea.dataset.editorReady = '1';
    textarea.value = normalizeInitialContent(textarea.value);

    tinymce.init({
      target: textarea,
      license_key: 'gpl',
      menubar: false,
      branding: false,
      promotion: false,
      skin: false,
      content_css: false,
      height: 360,
      plugins: 'advlist autolink code fullscreen lists wordcount',
      toolbar: 'undo redo | blocks | bold italic underline | bullist numlist blockquote | removeformat | code fullscreen',
      block_formats: 'Paragraf=p; Judul 3=h3; Judul 4=h4',
      forced_root_block: 'p',
      paste_as_text: false,
      paste_data_images: false,
      entity_encoding: 'raw',
      valid_elements: 'p,br,strong/b,em/i,u,blockquote,ul,ol,li,h3,h4,pre,code',
      content_style: 'body{font-family:Georgia,serif;font-size:16px;line-height:1.65;color:#384551;}p{margin:0 0 1rem;}',
      paste_preprocess(_plugin, args) {
        args.content = normalizePastedContent(args.content);
      },
      setup(editor) {
        editor.on('change keyup undo redo', () => editor.save());
      },
    });
  },

  remove(textarea) {
    if (!textarea?.id) return;

    tinymce.get(textarea.id)?.remove();
  },

  syncAll() {
    tinymce.triggerSave();
  },
};

window.dispatchEvent(new Event('chapter-editor-ready'));
