export default `
/* Submission content */
.comment-widget,
.q-btn,
.q-avatar,
.q-img,
.review-controls {
  display: none;
}
.comment-highlight {
  background-color: #c9e5f8;
  cursor: pointer;
}
.comment-highlight:before {
  color: #204965;
  content: "#" attr(data-comment-number);
  display: inline-block;
  font-size: 1rem;
  font-weight: bold;
  padding: 0 8px;
}

/* Page separator */
.page-separator {
  height: 3px;
  background-color: #888;
  border: none;
  margin: 1em 0;
}

/* Section headings */
.text-h3 {
  font-size: 24px;
  font-weight: bold;
}

/* Comment layout */
.overall-comment,
.inline-comment {
  border-top: 1px solid gray;
  margin-top: 0.5em;
}
.overall-comment-replies,
.inline-comment-replies {
  padding-left: 1rem;
}
.comment-reply {
  border-top: 1px solid #ddd;
}

/* Comment header */
.comment-header > div {
  align-items: center;
  display: flex;
  gap: 5px;
  padding: 16px 0 8px;
}
.comment-number {
  font-weight: bold;
  color: #204965;
}
.comment-author {
  font-weight: bold;
}
.text-caption {
  font-size: 0.85rem;
  color: #666;
}

/* Highlight back-link */
.highlight-link {
  font-size: 1.2rem;
  text-decoration: none;
  padding-right: 4px;
}

/* Reply reference */
.reply-reference {
  font-size: 0.85rem;
  color: #666;
  padding: 0 0 4px 1rem;
}

/* Comment content */
.comment-content {
  padding: 0 0 8px;
}
.comment-content blockquote {
  border-left: 4px solid #888;
  margin-inline-start: 1em;
  padding-left: 0.5em;
  margin-block-start: 0;
}

/* Style criteria */
.style-criteria-section {
  display: flex;
  gap: 5px;
  padding: 8px 0;
}
.style-criteria-chip {
  background: #000;
  border-radius: 16px;
  color: #fff;
  display: inline-block;
  padding: 4px 16px;
  font-size: 0.85rem;
}
`
