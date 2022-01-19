/*********************
 * For inbrowser design projects, this data is used to help describe the shape of data to be used.
 */
import { DateTime } from "luxon"
const currentTime = DateTime.now().valueOf()

function randomPastTime() {
  return currentTime - Math.floor(Math.random() * 1000 * 60 * 60 * 24 * 7 * 5)
}

export const users = {
  user1: {
    username: "user1",
    name: "User One",
  },
  user2: {
    username: "user2",
    name: "User the Second",
  },
  user3: {
    username: "userThree",
    name: "User Three Smith",
  },
}
export const objects = {
  submission: {
    name: "A new Submission",
    publication: {
      name: "Journal of Cool",
    },
  },
  publication: {
    name: "My Awesome Journal",
  },
  submission2: {
    name: "Existing Submission",
    publication: {
      name: "Journal of Cool",
    },
  },
}

export const notificationItems = [
  {
    type: "review.requested",
    user: users.user1,
    object: objects.submission2,
    viewed: false,
    icon: "remove_red_eye",
    time: randomPastTime(),
    read_at: randomPastTime(),
  },
  {
    type: "submission.created",
    user: users.user3,
    object: objects.submission2,
    viewed: false,
    icon: "article",
    time: randomPastTime(),
    read_at: randomPastTime(),
  },
  {
    type: "review.requested",
    user: users.user2,
    object: objects.submission,
    viewed: true,
    icon: "book",
    time: randomPastTime(),
    read_at: randomPastTime(),
  },
  {
    type: "review.commentReplied",
    user: users.user2,
    object: objects.submission,
    viewed: true,
    icon: "reply",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "review.requested",
    user: users.user1,
    object: objects.submission2,
    viewed: false,
    icon: "remove_red_eye",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "submission.resubmitted",
    user: users.user2,
    object: objects.submission,
    viewed: false,
    icon: "sync",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "submission.approved",
    user: users.user2,
    object: objects.submission,
    viewed: true,
    icon: "book",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "submission.approved",
    user: users.user1,
    object: objects.submission2,
    viewed: false,
    icon: "verified",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "review.requested",
    user: users.user2,
    object: objects.submission,
    viewed: true,
    icon: "book",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "review.requested",
    user: users.user2,
    object: objects.submission,
    viewed: false,
    icon: "book",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "review.requested",
    user: users.user1,
    object: objects.submission2,
    viewed: false,
    icon: "book",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "review.requested",
    user: users.user2,
    object: objects.submission,
    viewed: false,
    icon: "book",
    time: randomPastTime(),
    read_at: null,
  },
  {
    type: "review.requested",
    user: users.user2,
    object: objects.submission,
    viewed: true,
    icon: "book",
    time: randomPastTime(),
    read_at: null,
  },
]
