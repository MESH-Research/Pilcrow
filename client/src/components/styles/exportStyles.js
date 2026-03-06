export default `
.comment-widget,
.q-btn,
.q-avatar,
.q-chip i,
.q-img {
  display: none;
}
.q-chip {
  background: #000;
  border-radius: 16px;
  color: #fff;
  display: inline-block;
  padding: 4px 16px;
}
.text-h3 {
  font-size: 24px;
  font-weight: bold;
}
.review-controls {
  display: none;
}
.comment-header .text-h4 {
  font-weight: bold;
}
.comment-header-name .text-h4:before {
  content: "#" attr(data-context-id);
  display: inline-block;
  font-size: 1rem;
  padding-right: 6px;
}
aside.q-drawer {
  margin-top: 150px;
  transform: none !important;
  width: auto !important;
}
.comment-highlight {
  background-color: #c9e5f8;
}
.comment-highlight:before {
  color: #204965;
  content: "#" attr(data-context-id);
  display: inline-block;
  font-size: 1rem;
  font-weight: bold;
  padding: 0 8px;
}
.style-criteria-section {
  display: flex;
  gap: 5px;
  padding: 8px 0;
}
.overall-comment,
.inline-comment {
  border-top: 1px solid gray;
  margin-top: 0.5em;
}
.overall-comment-replies,
.inline-comment-replies {
  padding-left: 1rem;
}
.overall-comment > .comment > .comment-header > div,
.inline-comment > .comment > .comment-header > div  {
  align-items: center;
  display: flex;
  gap: 5px;
  padding: 16px 0 8px;
}
.inline-comment > .comment > .comment-header > div > .q-btn[aria-label="Go To Highlight"] {
  cursor: pointer;
  display: inline-block;
}
.inline-comment > .comment > .comment-header > div > .q-btn[aria-label="Go To Highlight"] * {
  pointer-events: none;
}
.inline-comment > .comment > .comment-header > div > .q-btn[aria-label="Go To Highlight"] i {
  font-size: 0 !important;
}
.inline-comment > .comment > .comment-header > div > .q-btn[aria-label="Go To Highlight"] i:before {
  content: "⇧";
  display: inline-block;
  font-size: 1rem;
  font-style: normal;
}
`
