export default `
.comment-widget,
.q-btn,
.q-chip i,
.q-img {
  display: none;
}
.inline-comment > .comment > .comment-header > div > .q-btn[aria-label="Go To Highlight"] {
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
.q-chip {
  background: #000;
  color: #fff;
  display: inline-block;
  border-radius: 16px;
  padding: 4px 16px;
}
.text-h3 {
  font-size: 24px;
  font-weight: bold;
}
.review-controls {
  display: none;
}
.inline-comment {
  border-top: 1px solid gray;
  margin-top: 0.5em;
}
.inline-comment-replies {
  padding-left: 1rem;
}

.inline-comment > .comment > .comment-header > div  {
  align-items: center;
  display: flex;
  gap: 5px;
  padding: 16px 0 8px;
}
.comment-header .text-h4 {
  font-weight: bold;
}
aside.q-drawer {
  transform: none !important;
  width: auto !important;
}
.comment-highlight {
  background-color: #c9e5f8;
}
.style-criteria-section {
  display: flex;
  gap: 5px;
  padding: 8px 0;
}
`
