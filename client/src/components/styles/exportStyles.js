export default `
.comment-widget,
.q-btn,
.q-avatar,
.q-img,
.review-controls {
  display: none;
}
.text-h3 {
  font-size: 24px;
  font-weight: bold;
}
.page-separator {
  background-color: #888;
  border: none;
  height: 3px;
  margin: 1rem 0;
}
.text-caption {
  font-size: 0.85rem;
  color: #595959;
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
.overall-comment,
.inline-comment {
  border-top: 1px solid gray;
  margin-top: 0.5rem;
}
.overall-comment-replies,
.inline-comment-replies {
  padding-left: 1rem;
}
.comment-reply {
  border-top: 1px solid #ddd;
}
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
.comment-content {
  padding: 0 0 8px;
}
.comment-content blockquote {
  border-left: 4px solid #888;
  margin-block-start: 0;
  margin-inline-start: 1rem;
  padding-left: 0.5rem;
}
.reply-reference {
  color: #595959;
  font-size: 0.85rem;
  padding: 0 0 4px 1rem;
}
.link-to-comment {
  color: inherit;
  text-decoration: none;
}
.link-to-highlight {
  font-size: 1.2rem;
  padding-right: 4px;
  text-decoration: none;
}
.link-to-reply {
  color: inherit;
}
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
  font-size: 0.85rem;
  padding: 4px 16px;
}
`
