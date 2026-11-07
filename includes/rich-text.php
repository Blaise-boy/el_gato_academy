<?php
/**
 * Rich text helpers for blog articles.
 *
 * - sanitize_rich_html()      : strips everything except a small whitelist of formatting tags
 * - render_blog_content()     : safe output for the public blog-details page
 *                               (also handles old plain-text articles written before the editor existed)
 * - blog_content_for_editor() : prepares stored content to be loaded into the admin editor
 */

function sanitize_rich_html($html)
{
    $html = (string) $html;
    if (trim($html) === '') {
        return '';
    }

    // Tags kept as-is, and tags renamed to a cleaner equivalent.
    $allowed = ['p', 'br', 'strong', 'em', 'u', 's', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'a'];
    $rename  = ['b' => 'strong', 'i' => 'em', 'strike' => 's', 'del' => 's', 'div' => 'p', 'h1' => 'h2', 'h4' => 'h3', 'h5' => 'h3', 'h6' => 'h3'];
    // Tags removed together with everything inside them.
    $dropWithContent = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'link', 'meta', 'head', 'title'];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="rt-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $doc->getElementById('rt-root');
    if (!$root) {
        return htmlspecialchars(strip_tags($html));
    }

    $clean = function (DOMNode $node) use (&$clean, $doc, $allowed, $rename, $dropWithContent) {
        // Iterate over a copy because we modify the tree while walking it.
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue; // text nodes are fine
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, $dropWithContent, true)) {
                $node->removeChild($child);
                continue;
            }

            if (isset($rename[$tag])) {
                $new = $doc->createElement($rename[$tag]);
                while ($child->firstChild) {
                    $new->appendChild($child->firstChild);
                }
                $node->replaceChild($new, $child);
                $child = $new;
                $tag = $rename[$tag];
            }

            if (!in_array($tag, $allowed, true)) {
                // Unknown tag (span, font, etc.): keep its text, drop the tag.
                $clean($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            // Strip every attribute; links keep a checked href only.
            $href = ($tag === 'a') ? trim($child->getAttribute('href')) : '';
            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }
            if ($tag === 'a') {
                if (preg_match('#^(https?://|mailto:)#i', $href)) {
                    $child->setAttribute('href', $href);
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                } else {
                    // No safe link - keep the text only.
                    $clean($child);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }
            }

            $clean($child);
        }
    };
    $clean($root);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }

    // Drop empty paragraphs the editor sometimes leaves behind.
    $out = preg_replace('#<p>(\s|<br\s*/?>|&nbsp;)*</p>#i', '', $out);

    return trim($out);
}

function content_has_html($content)
{
    return (bool) preg_match('#<(p|br|strong|em|u|s|h2|h3|ul|ol|li|blockquote|a)\b#i', (string) $content);
}

function render_blog_content($content)
{
    if (content_has_html($content)) {
        return sanitize_rich_html($content);
    }
    // Older articles were saved as plain text.
    return nl2br(htmlspecialchars((string) $content));
}

function blog_content_for_editor($content)
{
    if (content_has_html($content)) {
        return sanitize_rich_html($content);
    }
    $paragraphs = preg_split('/\R{2,}/', trim((string) $content));
    $html = '';
    foreach ($paragraphs as $para) {
        if (trim($para) === '') continue;
        $html .= '<p>' . nl2br(htmlspecialchars(trim($para))) . '</p>';
    }
    return $html;
}

function rich_text_is_empty($html)
{
    $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8'));
    $text = str_replace("\xC2\xA0", ' ', $text);
    return trim($text) === '';
}
